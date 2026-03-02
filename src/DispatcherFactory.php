<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use Verdient\Hyperf3\HttpAction\Annotation\RequestMethodCollector;
use Verdient\Hyperf3\HttpServer\DispatcherFactory as HttpServerDispatcherFactory;

/**
 * 调度器工厂
 *
 * @author Verdient。
 */
class DispatcherFactory extends HttpServerDispatcherFactory
{
    /**
     * @author Verdient。
     */
    public function __construct()
    {
        foreach (RequestMethodCollector::list() as $className => $_) {

            if (!$action = ActionManager::get($className)) {
                continue;
            }

            foreach ($action->servers as $serverName) {
                $router = $this->getRouter($serverName);

                foreach ($action->paths as $path) {
                    $router->addRoute(array_map(fn($value) => $value->name, $action->methods), $path, [$className, 'handle'], [
                        'middleware' => $action->middlewares,
                    ]);
                }
            }
        }

        parent::__construct();
    }
}
