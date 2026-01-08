<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RateLimiterFactory $loginLimiter,
        private readonly RateLimiterFactory $passwordResetLimiter,
        private readonly RateLimiterFactory $apiLimiter
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Rate limit login endpoint
        if (str_starts_with($path, '/api/auth/login')) {
            $limiter = $this->loginLimiter->create($this->getClientIdentifier($request));
            if (!$limiter->consume()->isAccepted()) {
                $event->setResponse(new Response(
                    json_encode([
                        'success' => false,
                        'error' => [
                            'code' => 'RATE_LIMIT_EXCEEDED',
                            'message' => 'Too many login attempts. Please try again later.',
                        ],
                    ]),
                    Response::HTTP_TOO_MANY_REQUESTS,
                    ['Content-Type' => 'application/json']
                ));
                return;
            }
        }

        // Rate limit password reset endpoint
        if (str_starts_with($path, '/api/auth/password-reset')) {
            $limiter = $this->passwordResetLimiter->create($this->getClientIdentifier($request));
            if (!$limiter->consume()->isAccepted()) {
                $event->setResponse(new Response(
                    json_encode([
                        'success' => false,
                        'error' => [
                            'code' => 'RATE_LIMIT_EXCEEDED',
                            'message' => 'Too many password reset requests. Please try again later.',
                        ],
                    ]),
                    Response::HTTP_TOO_MANY_REQUESTS,
                    ['Content-Type' => 'application/json']
                ));
                return;
            }
        }

        // Rate limit API endpoints (general)
        if (str_starts_with($path, '/api/') && !str_starts_with($path, '/api/auth/login') && !str_starts_with($path, '/api/auth/password-reset')) {
            $limiter = $this->apiLimiter->create($this->getClientIdentifier($request));
            if (!$limiter->consume()->isAccepted()) {
                $event->setResponse(new Response(
                    json_encode([
                        'success' => false,
                        'error' => [
                            'code' => 'RATE_LIMIT_EXCEEDED',
                            'message' => 'Too many requests. Please slow down.',
                        ],
                    ]),
                    Response::HTTP_TOO_MANY_REQUESTS,
                    ['Content-Type' => 'application/json']
                ));
                return;
            }
        }
    }

    private function getClientIdentifier(Request $request): string
    {
        // Use IP address + user agent for identification
        $ip = $request->getClientIp() ?? 'unknown';
        $userAgent = $request->headers->get('User-Agent', 'unknown');
        
        return hash('sha256', $ip . '|' . $userAgent);
    }
}

