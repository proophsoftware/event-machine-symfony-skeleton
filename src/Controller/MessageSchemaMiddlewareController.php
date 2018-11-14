<?php

declare(strict_types=1);

namespace App\Controller;

use Prooph\Common\Messaging\Message;
use Prooph\EventMachine\EventMachine;
use Psr\Http\Message\UriInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessageSchemaMiddlewareController extends Controller
{
    /**
     * @Route("api/messagebox-schema", name="message.schema.middleware")
     */
    public function handle(Request $request, EventMachine $eventMachine)
    {
        $psr7Factory = new DiactorosFactory();
        $psrRequest = $psr7Factory->createRequest($request);

        /** @var UriInterface $uri */
        $uri = $psrRequest->getAttribute('original_uri', $psrRequest->getUri());

        $serverUrl = $uri->withPath(\str_replace('-schema', '', $uri->getPath()));

        $eventMachineSchema = $eventMachine->messageBoxSchema();

        $paths = [];

        foreach ($eventMachineSchema['properties']['commands'] as $messageName => $schema) {
            [$path, $pathDef] = $this->messageSchemaToPath($messageName, Message::TYPE_COMMAND, $schema);
            $paths[$path] = $pathDef;
        }

        foreach ($eventMachineSchema['properties']['events'] as $messageName => $schema) {
            [$path, $pathDef] = $this->messageSchemaToPath($messageName, Message::TYPE_EVENT, $schema);
            $paths[$path] = $pathDef;
        }

        foreach ($eventMachineSchema['properties']['queries'] as $messageName => $schema) {
            [$path, $pathDef] = $this->messageSchemaToPath($messageName, Message::TYPE_QUERY, $schema);
            $paths[$path] = $pathDef;
        }

        $componentSchemas = [];

        foreach ($eventMachineSchema['definitions'] ?? [] as $componentName => $componentSchema) {
            $componentSchemas[$componentName] = $this->jsonSchemaToOpenApiSchema($componentSchema);
        }

        $schema = [
            'openapi' => '3.0.0',
            'servers' => [
                [
                    'description' => 'Event Machine '.$eventMachine->env().' server',
                    'url' => (string) $serverUrl,
                ],
            ],
            'info' => [
                'description' => 'An endpoint for sending messages to the application.',
                'version' => $eventMachine->appVersion(),
                'title' => 'Event Machine Message Box',
            ],
            'tags' => [
                [
                    'name' => 'queries',
                    'description' => 'Requests to read data from the system',
                ],
                [
                    'name' => 'commands',
                    'description' => 'Requests to write data to the system or execute an action',
                ],
                [
                    'name' => 'events',
                    'description' => 'Requests to add an event to the system',
                ],
            ],
            'paths' => $paths,
            'components' => ['schemas' => $componentSchemas],
        ];

        return new JsonResponse($schema);
    }

    private function messageSchemaToPath(string $messageName, string $messageType, array $messageSchema = null): array
    {
        $responses = [];

        if (Message::TYPE_QUERY === $messageType) {
            $responses['200'] = [
                'description' => $messageSchema['response']['description'] ?? $messageName,
                'content' => [
                    'application/json' => [
                        'schema' => $this->jsonSchemaToOpenApiSchema($messageSchema['response']),
                    ],
                ],
            ];

            unset($messageSchema['response']);
        } else {
            $responses['202'] = [
                'description' => "$messageType accepted",
            ];
        }

        switch ($messageType) {
            case Message::TYPE_COMMAND:
                $tag = 'commands';
                break;
            case Message::TYPE_QUERY:
                $tag = 'queries';
                break;
            case Message::TYPE_EVENT:
                $tag = 'events';
                break;
            default:
                throw new \RuntimeException("Unknown message type given. Got $messageType");
        }

        return [
            "/{$messageName}",
            [
                'post' => [
                    'tags' => [$tag],
                    'summary' => $messageName,
                    'operationId' => "$messageType.$messageName",
                    'description' => $messageSchema['description'] ?? "Send a $messageName $messageType",
                    'requestBody' => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'payload' => $this->jsonSchemaToOpenApiSchema($messageSchema),
                                    ],
                                    'required' => ['payload'],
                                ],
                            ],
                        ],
                    ],
                    'responses' => $responses,
                ],
            ],
        ];
    }

    private function jsonSchemaToOpenApiSchema(array $jsonSchema): array
    {
        if (isset($jsonSchema['type']) && \is_array($jsonSchema['type'])) {
            $type = null;
            $containsNull = false;
            foreach ($jsonSchema['type'] as $possibleType) {
                if ('null' !== \mb_strtolower($possibleType)) {
                    if ($type) {
                        throw new \RuntimeException('Got JSON Schema type defined as an array with more than one type + NULL set. '.\json_encode($jsonSchema));
                    }
                    $type = $possibleType;
                } else {
                    $containsNull = true;
                }
            }
            $jsonSchema['type'] = $type;
            if ($containsNull) {
                $jsonSchema['nullable'] = true;
            }
        }

        if (isset($jsonSchema['properties']) && \is_array($jsonSchema['properties'])) {
            foreach ($jsonSchema['properties'] as $propName => $propSchema) {
                $jsonSchema['properties'][$propName] = $this->jsonSchemaToOpenApiSchema($propSchema);
            }
        }

        if (isset($jsonSchema['items']) && \is_array($jsonSchema['items'])) {
            $jsonSchema['items'] = $this->jsonSchemaToOpenApiSchema($jsonSchema['items']);
        }

        if (isset($jsonSchema['$ref'])) {
            $jsonSchema['$ref'] = \str_replace('definitions', 'components/schemas', $jsonSchema['$ref']);
        }

        return $jsonSchema;
    }
}
