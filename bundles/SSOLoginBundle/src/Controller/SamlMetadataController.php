<?php
declare(strict_types=1);

namespace SSOLogin\Bundle\SSOLoginBundle\Controller;

use OneLogin\Saml2\Error;
use OneLogin\Saml2\Settings;
use SSOLogin\Bundle\SSOLoginBundle\Security\PingSamlAuthFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SamlMetadataController extends AbstractController
{
    public function __construct(
        private readonly PingSamlAuthFactory $authFactory,
    ) {
    }

    #[Route('/sso-login/saml/metadata', name: 'sso_login_saml_metadata', methods: ['GET'])]
    public function metadata(): Response
    {
        if (!$this->authFactory->isConfigured()) {
            return new Response(
                'SAML metadata unavailable — IdP settings missing in .env',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        try {
            $settings = new Settings($this->authFactory->buildSettings(), true);
            $metadata = $settings->getSPMetadata();
            $errors = $settings->validateMetadata($metadata);

            if (!empty($errors)) {
                return new Response(
                    'Invalid SP metadata: ' . implode(', ', $errors),
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            return new Response($metadata, Response::HTTP_OK, [
                'Content-Type' => 'application/samlmetadata+xml',
            ]);
        } catch (Error $e) {
            return new Response('Metadata error: ' . $e->getMessage(), Response::HTTP_BAD_GATEWAY);
        }
    }

    #[Route('/sso-login/saml/logout', name: 'sso_login_saml_logout', methods: ['GET', 'POST'])]
    public function logout(): Response
    {
        return $this->redirect('/pimcore-studio/login');
    }
}
