<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use Hyperf\HttpServer\PriorityMiddleware;

/**
 * 动作
 *
 * @author Verdient。
 */
class Action
{
    /**
     * @param array<int,Method> $methods 请求方法集合
     * @param array<int,string> $paths 请求路径集合
     * @param array<int,string> $servers 服务器集合
     * @param array<int,PriorityMiddleware> $middlewares 中间件集合
     * @param array<int,object> $attributes 注解
     * @param array<int,array<int,object>> $inheritedAttributes 继承的注解
     *
     * @author Verdient。
     */
    public function __construct(
        public readonly array $methods,
        public readonly array $paths,
        public readonly array $servers,
        public readonly array $middlewares,
        public readonly array $attributes,
        public readonly array $inheritedAttributes,
    ) {}
}
