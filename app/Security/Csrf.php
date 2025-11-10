<?php

declare(strict_types=1);

namespace CoreNewspaper\Security;

use CoreNewspaper\Core\Request;

final class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    public function generateToken(Request $request): string
    {
        $session =& $request->session();
        if (!isset($session[self::TOKEN_KEY])) {
            $session[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }

        return $session[self::TOKEN_KEY];
    }

    public function validate(Request $request): bool
    {
        $session =& $request->session();
        $submitted = $request->post('_csrf_token');
        if (!is_string($submitted) || $submitted === '') {
            return false;
        }

        return isset($session[self::TOKEN_KEY]) && hash_equals($session[self::TOKEN_KEY], $submitted);
    }
}
