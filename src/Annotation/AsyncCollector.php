<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Annotation;

use Hyperf\Di\MetadataCollector;

/**
 * 异步动作收集器
 *
 * @method static ?Async get(string $key, $default = null)
 * @method static array<string, Async> list()
 *
 * @author Verdient。
 */
class AsyncCollector extends MetadataCollector
{
    /**
     * @inheritdoc
     *
     * @author Verdient。
     */
    protected static array $container = [];

    /**
     * 收集类
     *
     * @param string $className 类名
     * @param Async $annotation 注解
     *
     * @author Verdient。
     */
    public static function collectClass(string $className, Async $annotation): void
    {
        static::$container[$className] = $annotation;
    }
}
