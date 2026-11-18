<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use Hyperf\HttpServer\CoreMiddleware as HttpServerCoreMiddleware;
use Hyperf\HttpServer\Router\DispatcherFactory as RouterDispatcherFactory;
use Verdient\Hyperf3\HttpAction\Annotation\AsyncCollector;
use Verdient\Hyperf3\HttpAction\Annotation\RequestMethodCollector;
use Verdient\Hyperf3\HttpAction\Annotation\RequestPathCollector;
use Verdient\Hyperf3\HttpAction\Annotation\RequestServerCollector;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for HTTP action.',
                    'source' => dirname(__DIR__) . '/publish/http_action.php',
                    'destination' => constant('BASE_PATH') . '/config/autoload/http_action.php',
                ]
            ],
            'dependencies' => [
                RouterDispatcherFactory::class => DispatcherFactory::class,
                HttpServerCoreMiddleware::class => CoreMiddleware::class,
            ],
            'annotations' => [
                'scan' => [
                    'collectors' => [
                        AsyncCollector::class,
                        RequestMethodCollector::class,
                        RequestPathCollector::class,
                        RequestServerCollector::class,
                    ]
                ]
            ]
        ];
    }
}
