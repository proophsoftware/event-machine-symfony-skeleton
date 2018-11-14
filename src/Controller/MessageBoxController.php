<?php

declare(strict_types=1);

namespace App\Controller;

use Prooph\EventMachine\EventMachine;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", methods={"POST"})
 */
class MessageBoxController extends Controller
{
    /**
     * @Route("/messagebox", name="messagebox")
     */
    public function handle(Request $request, EventMachine $eventMachine, KernelInterface $kernel)
    {
        $messageBox = $eventMachine->httpMessageBox();

        $psr7Factory = new DiactorosFactory();
        $psrRequest = $psr7Factory->createRequest($request);

        $httpFoundationFactory = new HttpFoundationFactory();
        $zendResponse = $messageBox->handle($psrRequest);

        return $httpFoundationFactory->createResponse($zendResponse);
    }

    /**
     * @Route("/messagebox/{message_name}", name="messagebox.action", requirements={"message_name"="[A-Za-z0-9_.-\/]+"})
     */
    public function handleQuery(Request $request, EventMachine $eventMachine, KernelInterface $kernel)
    {
        $messageBox = $eventMachine->httpMessageBox();

        $psr7Factory = new DiactorosFactory();
        $psrRequest = $psr7Factory->createRequest($request);

        $psrRequest = $psrRequest->withParsedBody(\json_decode($request->getContent(), true));

        $httpFoundationFactory = new HttpFoundationFactory();
        $zendResponse = $messageBox->handle($psrRequest);

        return $httpFoundationFactory->createResponse($zendResponse);
    }
}
