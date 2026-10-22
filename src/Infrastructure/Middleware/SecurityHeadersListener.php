<?php

declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use Symfony\Component\HttpKernel\Event\RequestEvent;

final class SecurityHeadersListener
{
    private const HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ];

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        foreach (self::HEADERS as $header => $value) {
            $request->headers->set($header, $value);
        }
    }
}
