<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use Hyperf\Di\ReflectionManager;
use UnexpectedValueException;
use Verdient\Hyperf3\HttpAction\Validation\Annotation\Type;

/**
 * 集合类型管理器
 *
 * @author Verdient。
 */
class CollectionTypeManager
{
    /**
     * 缓存的类型
     *
     * @author Verdient。
     */
    protected static array $types = [];

    /**
     * @param class-string<CollectionInputParameterInterface> 类名
     *
     * @author Verdient。
     */
    public static function get(string $class): Type
    {
        if (isset(static::$types[$class])) {
            return static::$types[$class];
        }

        $reflectionClass = ReflectionManager::reflectClass($class);

        $typeAttributes = $reflectionClass->getAttributes(Type::class);

        if (empty($typeAttributes)) {
            throw new UnexpectedValueException($class . ' cannot be used without #[Type(string $type)] annotation.');
        }

        static::$types[$class] = $typeAttributes[0]->newInstance();

        return static::$types[$class];
    }
}
