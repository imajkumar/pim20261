<?php
declare(strict_types=1);

namespace SSOLogin\Bundle\SSOLoginBundle\Controller;

use OneLogin\Saml2\Error;
use Pimcore\Tool\Authentication;
use SSOLogin\Bundle\SSOLoginBundle\Security\PimcoreSamlUserResolver;
use SSOLogin\Bundle\SSOLoginBundle\Security\PingSamlAuthFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SamlAcsController extends AbstractController
{
    public function __construct(
        private readonly PingSamlAuthFactory $authFactory,
        private readonly PimcoreSamlUserResolver $userResolver,
    ) {
    }

    #[Route('/sso-login/saml/acs', name: 'sso_login_saml_acs', methods: ['POST'])]
    public function acs(Request $request): Response
    {
        if (!$this->authFactory->isConfigured()) {
            return new Response('SAML SSO is not configured.', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            $auth = $this->authFactory->createAuth();
            $auth->processResponse();

            if (!$auth->isAuthenticated()) {
                $errors = $auth->getErrors();
                $reason = $auth->getLastErrorReason();

                return new Response(
                    'SAML authentication failed: ' . implode('; ', $errors) . ($reason !== '' ? ' — ' . $reason : ''),
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $user = $this->userResolver->resolve($auth);
            $token = Authentication::generateTokenByUser($user);

            return $this->redirect('/pimcore-studio/login?token=' . rawurlencode($token));
        } catch (Error $e) {
            return new Response('SAML ACS error: ' . $e->getMessage(), Response::HTTP_BAD_GATEWAY);
        } catch (\RuntimeException $e) {
            return $this->redirect('/pimcore-studio/login?saml_error=' . rawurlencode($e->getMessage()));
        }
    }
}
