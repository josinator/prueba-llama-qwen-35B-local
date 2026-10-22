<?php

declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

final class RateLimitingMiddleware
{
    /** @var array<string, list<float>> */
    private array $requestLog = [];

    private int $window = 60;

    private int $limit = 100;

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $ip = $request->getClientIp() ?? 'unknown';
        $currentTime = microtime(true);

        if (!isset($this->requestLog[$ip])) {
            $this->requestLog[$ip] = [];
        }

        $this->requestLog[$ip] = array_values(array_filter(
            $this->requestLog[$ip],
            fn (float $timestamp) => ($currentTime - $timestamp) < $this->window
        ));

        if (count($this->requestLog[$ip]) >= $this->limit) {
            throw new TooManyRequestsHttpException(
                $this->window,
                'Demasiadas solicitudes. Inténtalo nuevamente en un minuto.'
            );
        }

        $this->requestLog[$ip][] = $currentTime;
    }
}
