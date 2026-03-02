<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use Hyperf\HttpServer\CoreMiddleware as HttpServerCoreMiddleware;
use Hyperf\HttpServer\Router\DispatcherFactory as RouterDispatcherFactory;
use Verdient\Hyperf3\HttpAction\Annotation\RequestMethodCollector;
use Verdient\Hyperf3\HttpAction\Annotation\RequestPathCollector;
use Verdient\Hyperf3\HttpAction\Annotation\RequestServerCollector;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                RouterDispatcherFactory::class => DispatcherFactory::class,
                HttpServerCoreMiddleware::class => CoreMiddleware::class,
            ],
            'annotations' => [
                'scan' => [
                    'collectors' => [
                        RequestMethodCollector::class,
                        RequestPathCollector::class,
                        RequestServerCollector::class,
                    ]
                ]
            ]
        ];
    }
}
