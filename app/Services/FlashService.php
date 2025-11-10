<?php

declare(strict_types=1);

namespace CoreNewspaper\Services;

use CoreNewspaper\Core\Request;

final class FlashService
{
    private const KEY = '_flash_messages';

    public function add(Request $request, string $type, string $message): void
    {
        $session =& $request->session();
        $session[self::KEY][$type][] = $message;
    }

    public function consume(Request $request): array
    {
        $session =& $request->session();
        $messages = $session[self::KEY] ?? [];
        unset($session[self::KEY]);
        return $messages;
    }
}
