<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Annotation;

use Hyperf\Di\MetadataCollector;

/**
 * 请求方法收集器
 *
 * @method static ?RequestMethod[] get(string $key, $default = null)
 * @method static array<string, RequestMethod[]> list()
 *
 * @author Verdient。
 */
class RequestMethodCollector extends MetadataCollector
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
     * @param RequestMethod $annotation 注解
     *
     * @author Verdient。
     */
    public static function collectClass(string $className, RequestMethod $annotation): void
    {
        if (isset(static::$container[$className])) {
            static::$container[$className][] = $annotation;
        } else {
            static::$container[$className] = [$annotation];
        }
    }
}
