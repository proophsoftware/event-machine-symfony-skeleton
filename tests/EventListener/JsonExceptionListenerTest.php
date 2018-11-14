<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\EventListener\JsonExceptionListener;
use App\Kernel;
use JsonSchema\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class JsonExceptionListenerTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                'kernel.exception' => [
                    ['handleJsonException'],
                ],
            ],
            JsonExceptionListener::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider provideContentTypes
     */
    public function testDoNothingOnContentType(?string $contentType)
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'CONTENT_TYPE' => $contentType,
        ]);

        $exception = new NotFoundHttpException();
        $kernel = $this->prophesize(Kernel::class);
        $getResponseForException = new GetResponseForExceptionEvent(
            $kernel->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );

        $listener = new JsonExceptionListener();
        $listener->handleJsonException($getResponseForException);

        $this->assertNull($getResponseForException->getResponse());
    }

    /**
     * @dataProvider provideExceptions
     */
    public function testHandleJsonException(int $statusCode, \Exception $exception)
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $kernel = $this->prophesize(Kernel::class);
        $getResponseForException = new GetResponseForExceptionEvent(
            $kernel->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );

        $listener = new JsonExceptionListener();
        $listener->handleJsonException($getResponseForException);

        $response = $getResponseForException->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($statusCode, $response->getStatusCode());
    }

    public function provideContentTypes()
    {
        yield [null];
        yield ['text/html'];
    }

    public function provideExceptions()
    {
        yield [404, new NotFoundHttpException()];
        yield [503, MessageDispatchException::failed(new InvalidArgumentException())];
    }
}
