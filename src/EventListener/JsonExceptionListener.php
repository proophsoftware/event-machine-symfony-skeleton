<?php

declare(strict_types=1);

namespace App\EventListener;

use Prooph\ServiceBus\Exception\MessageDispatchException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['handleJsonException'],
            ],
        ];
    }

    public function handleJsonException(GetResponseForExceptionEvent $e)
    {
        $request = $e->getRequest();

        if ('json' !== $request->getContentType()) {
            return;
        }

        $exception = $e->getException();

        if ($exception instanceof MessageDispatchException) {
            $e->setResponse(
                new JsonResponse(
                    [
                        'exception_type' => \get_class($exception->getPrevious()),
                        'exception_message' => $exception->getPrevious()->getMessage(),
                    ],
                    $exception instanceof HttpException ? $exception->getStatusCode() : Response::HTTP_SERVICE_UNAVAILABLE
                )
            );

            return;
        }

        $e->setResponse(
            new JsonResponse(
                [
                    'exception_message' => $exception->getMessage(),
                    'exception_type' => \get_class($exception),
                ],
                $exception instanceof HttpException ? $exception->getStatusCode() : Response::HTTP_SERVICE_UNAVAILABLE
            )
        );
    }
}
