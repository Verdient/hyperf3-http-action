<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Annotation;

use Hyperf\Di\MetadataCollector;

/**
 * 请求路径收集器
 *
 * @method static ?RequestPath[] get(string $key, $default = null)
 * @method static array<string, RequestPath[]> list()
 *
 * @author Verdient。
 */
class RequestPathCollector extends MetadataCollector
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
     * @param RequestPath $annotation 注解
     *
     * @author Verdient。
     */
    public static function collectClass(string $className, RequestPath $annotation): void
    {
        if (isset(static::$container[$className])) {
            static::$container[$className][] = $annotation;
        } else {
            static::$container[$className] = [$annotation];
        }
    }
}
