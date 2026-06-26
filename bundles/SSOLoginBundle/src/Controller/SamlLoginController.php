<?php
declare(strict_types=1);

namespace SSOLogin\Bundle\SSOLoginBundle\Controller;

use OneLogin\Saml2\Error;
use SSOLogin\Bundle\SSOLoginBundle\Security\PingSamlAuthFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SamlLoginController extends AbstractController
{
    public function __construct(
        private readonly PingSamlAuthFactory $authFactory,
    ) {
    }

    #[Route('/sso-login/saml/login', name: 'sso_login_saml_login', methods: ['GET'])]
    public function login(Request $request): Response
    {
        if (!$this->authFactory->isConfigured()) {
            return new Response(
                'Ping SAML SSO is not configured. Set SSO_LOGIN_SAML_IDP_ENTITY_ID, SSO_LOGIN_SAML_IDP_SSO_URL, '
                . 'and SSO_LOGIN_SAML_IDP_X509_CERT in .env. See bundles/SSOLoginBundle/doc/PING_SAML_SSO.md',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        try {
            $auth = $this->authFactory->createAuth();
            $returnTo = $request->query->get('returnTo');
            $returnTo = is_string($returnTo) && $returnTo !== '' ? $returnTo : null;
            $ssoUrl = $auth->login($returnTo, [], false, false, true);

            return $this->redirect($ssoUrl);
        } catch (Error $e) {
            return new Response('SAML login failed: ' . $e->getMessage(), Response::HTTP_BAD_GATEWAY);
        }
    }
}
