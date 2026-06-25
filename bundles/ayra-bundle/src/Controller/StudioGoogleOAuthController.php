<?php
declare(strict_types=1);

namespace Ayra\Bundle\AyraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Browser OAuth flow entry for the Studio "Continue with Google" button.
 *
 * After Google redirects back with a code, the callback exchanges the code when
 * {@see AYRA_GOOGLE_OAUTH_CLIENT_SECRET} is set. Mapping the Google identity to a
 * Pimcore Studio session (e.g. issuing a Studio login token) is project-specific and
 * must be added where indicated in {@see callback()}.
 */
final class StudioGoogleOAuthController extends AbstractController
{
    private const string GOOGLE_AUTH = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const string GOOGLE_TOKEN = 'https://oauth2.googleapis.com/token';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {
    }

    #[Route('/ayra/oauth/google/connect', name: 'ayra_studio_google_oauth_connect', methods: ['GET'])]
    public function connect(Request $request): Response
    {
        if ($this->clientId === '') {
            return new Response(
                'Google sign-in is not configured. Set AYRA_GOOGLE_OAUTH_CLIENT_ID in your environment ' .
                'and add the authorized redirect URI in Google Cloud Console (this app uses the ' .
                'absolute URL of route ayra_studio_google_oauth_callback).',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        $session = $request->getSession();
        $state = bin2hex(random_bytes(16));
        $session->set('ayra_google_oauth_state', $state);

        $redirectUri = $this->generateUrl(
            'ayra_studio_google_oauth_callback',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ], '', '&', PHP_QUERY_RFC3986);

        return $this->redirect(self::GOOGLE_AUTH . '?' . $query);
    }

    #[Route('/ayra/oauth/google/callback', name: 'ayra_studio_google_oauth_callback', methods: ['GET'])]
    public function callback(Request $request): Response
    {
        $session = $request->getSession();
        $expectedState = $session->get('ayra_google_oauth_state');
        $session->remove('ayra_google_oauth_state');

        $state = (string) $request->query->get('state', '');
        if (!is_string($expectedState) || $expectedState === '' || !hash_equals($expectedState, $state)) {
            return new Response('Invalid OAuth state. Start sign-in again from Pimcore Studio.', Response::HTTP_BAD_REQUEST);
        }

        if ($request->query->get('error') !== null) {
            return $this->redirect('/pimcore-studio/login/?oauth_error=1');
        }

        $code = $request->query->get('code');
        if (!is_string($code) || $code === '') {
            return new Response('Missing authorization code.', Response::HTTP_BAD_REQUEST);
        }

        if ($this->clientSecret === '') {
            return new Response(
                'Google returned an authorization code, but AYRA_GOOGLE_OAUTH_CLIENT_SECRET is empty. ' .
                'Set the secret to enable the code exchange step, then implement mapping to a Studio session.',
                Response::HTTP_NOT_IMPLEMENTED
            );
        }

        $redirectUri = $this->generateUrl(
            'ayra_studio_google_oauth_callback',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        try {
            $tokenResponse = $this->httpClient->request('POST', self::GOOGLE_TOKEN, [
                'body' => [
                    'code' => $code,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code',
                ],
            ]);
            $status = $tokenResponse->getStatusCode();
            if ($status < 200 || $status >= 300) {
                return new Response(
                    'Token endpoint returned HTTP ' . $status . '.',
                    Response::HTTP_BAD_GATEWAY
                );
            }
        } catch (\Throwable $e) {
            return new Response('Token exchange failed: ' . $e->getMessage(), Response::HTTP_BAD_GATEWAY);
        }

        // Next step for a full SSO flow: map Google subject/email to a Pimcore user and issue a Studio login token,
        // then redirect to /pimcore-studio/login/?token=...
        return $this->redirect('/pimcore-studio/login/?oauth_pending=1');
    }
}
