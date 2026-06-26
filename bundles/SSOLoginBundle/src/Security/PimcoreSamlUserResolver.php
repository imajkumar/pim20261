<?php
declare(strict_types=1);

namespace SSOLogin\Bundle\SSOLoginBundle\Security;

use OneLogin\Saml2\Auth;
use Pimcore\Model\User;

/**
 * Maps validated SAML assertions to existing Pimcore backend users.
 */
final class PimcoreSamlUserResolver
{
    public function __construct(
        private readonly string $usernameAttribute = 'email',
    ) {
    }

    public function resolve(Auth $auth): User
    {
        $username = $this->extractUsername($auth);
        if ($username === '') {
            throw new \RuntimeException(
                'SAML response did not contain a username. Check SSO_LOGIN_SAML_USERNAME_ATTRIBUTE and Ping attribute mapping.'
            );
        }

        $user = User::getByName($username);
        if (!$user instanceof User || !$user->getId()) {
            throw new \RuntimeException(
                sprintf('No Pimcore user found for SAML identity "%s". Create the user in Pimcore Admin first.', $username)
            );
        }

        if (!$user->isActive()) {
            throw new \RuntimeException(sprintf('Pimcore user "%s" is inactive.', $username));
        }

        return $user;
    }

    private function extractUsername(Auth $auth): string
    {
        if (strcasecmp($this->usernameAttribute, 'NameID') === 0) {
            return trim((string) $auth->getNameId());
        }

        $attributes = $auth->getAttributes();
        $key = $this->usernameAttribute;

        foreach ([$key, strtolower($key), strtoupper($key)] as $candidate) {
            if (!isset($attributes[$candidate])) {
                continue;
            }
            $value = $attributes[$candidate];
            if (is_array($value) && isset($value[0])) {
                return trim((string) $value[0]);
            }
            if (is_string($value)) {
                return trim($value);
            }
        }

        // Fallback: common Ping / AD attribute names
        foreach (['email', 'mail', 'sAMAccountName', 'username', 'uid'] as $fallback) {
            if (!isset($attributes[$fallback])) {
                continue;
            }
            $value = $attributes[$fallback];
            if (is_array($value) && isset($value[0])) {
                return trim((string) $value[0]);
            }
        }

        return trim((string) $auth->getNameId());
    }
}
