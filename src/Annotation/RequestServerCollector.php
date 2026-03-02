<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Annotation;

use Hyperf\Di\MetadataCollector;

/**
 * 请求服务器收集器
 *
 * @method static ?RequestServer[] get(string $key, $default = null)
 * @method static array<string, RequestServer[]> list()
 *
 * @author Verdient。
 */
class RequestServerCollector extends MetadataCollector
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
     * @param RequestServer $annotation 注解
     *
     * @author Verdient。
     */
    public static function collectClass(string $className, RequestServer $annotation): void
    {
        if (isset(static::$container[$className])) {
            static::$container[$className][] = $annotation;
        } else {
            static::$container[$className] = [$annotation];
        }
    }
}
