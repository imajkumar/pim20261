<?php
declare(strict_types=1);

namespace SSOLogin\Bundle\SSOLoginBundle\Security;

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Builds OneLogin SAML Auth instances for Ping / generic SAML IdPs.
 */
final class PingSamlAuthFactory
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $idpEntityId,
        private readonly string $idpSsoUrl,
        private readonly string $idpSloUrl,
        private readonly string $idpX509Cert,
        private readonly string $spEntityId,
        private readonly string $spBaseUrl,
        private readonly string $spX509Cert,
        private readonly string $spPrivateKey,
        private readonly bool $debug,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->idpEntityId !== ''
            && $this->idpSsoUrl !== ''
            && $this->idpX509Cert !== '';
    }

    /**
     * @throws Error
     */
    public function createAuth(): Auth
    {
        return new Auth($this->buildSettings());
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSettings(): array
    {
        $baseUrl = rtrim($this->spBaseUrl, '/');
        $entityId = $this->spEntityId !== ''
            ? $this->spEntityId
            : $baseUrl . $this->urlGenerator->generate('sso_login_saml_metadata', [], UrlGeneratorInterface::ABSOLUTE_PATH);

        $acsUrl = $baseUrl . $this->urlGenerator->generate('sso_login_saml_acs', [], UrlGeneratorInterface::ABSOLUTE_PATH);
        $sloUrl = $baseUrl . $this->urlGenerator->generate('sso_login_saml_logout', [], UrlGeneratorInterface::ABSOLUTE_PATH);

        $settings = [
            'strict' => true,
            'debug' => $this->debug,
            'baseurl' => $baseUrl,
            'sp' => [
                'entityId' => $entityId,
                'assertionConsumerService' => [
                    'url' => $acsUrl,
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ],
                'singleLogoutService' => [
                    'url' => $sloUrl,
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
            ],
            'idp' => [
                'entityId' => $this->idpEntityId,
                'singleSignOnService' => [
                    'url' => $this->idpSsoUrl,
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'x509cert' => $this->normalizeCertificate($this->idpX509Cert),
            ],
        ];

        if ($this->idpSloUrl !== '') {
            $settings['idp']['singleLogoutService'] = [
                'url' => $this->idpSloUrl,
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            ];
        }

        if ($this->spX509Cert !== '') {
            $settings['sp']['x509cert'] = $this->normalizeCertificate($this->spX509Cert);
        }

        if ($this->spPrivateKey !== '') {
            $settings['sp']['privateKey'] = $this->normalizePrivateKey($this->spPrivateKey);
        }

        return $settings;
    }

    private function normalizeCertificate(string $cert): string
    {
        $cert = trim($cert);
        if ($cert === '') {
            return '';
        }

        $cert = str_replace(["\r\n", "\r"], "\n", $cert);
        $cert = preg_replace('/-----BEGIN CERTIFICATE-----/', '', $cert) ?? $cert;
        $cert = preg_replace('/-----END CERTIFICATE-----/', '', $cert) ?? $cert;

        return trim(preg_replace('/\s+/', '', $cert) ?? $cert);
    }

    private function normalizePrivateKey(string $key): string
    {
        $key = trim(str_replace(["\r\n", "\r"], "\n", $key));
        if (str_contains($key, 'BEGIN')) {
            return $key;
        }

        return "-----BEGIN PRIVATE KEY-----\n"
            . chunk_split(preg_replace('/\s+/', '', $key) ?? $key, 64, "\n")
            . "-----END PRIVATE KEY-----";
    }
}
