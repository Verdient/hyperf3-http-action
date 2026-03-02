<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\ReflectionManager;
use Hyperf\Stringable\Str;
use InvalidArgumentException;
use ReflectionIntersectionType;
use ReflectionUnionType;
use TypeError;
use Verdient\Hyperf3\HttpAction\Validation\Annotation\Inherent;

/**
 * 属性管理器
 *
 * @author Verdient。
 */
class AttributeManager
{
    /**
     * 缓存的属性
     *
     * @author Verdient。
     */
    protected static array $attributes = [];

    /**
     * @param class-string<InputParameterInterface> 类名
     *
     * @return array<int,ActionAttribute>
     * @author Verdient。
     */
    public static function get(string $class): array
    {
        if (isset(static::$attributes[$class])) {
            return static::$attributes[$class];
        }

        if (!is_subclass_of($class, InputParameterInterface::class)) {
            return [];
        }

        $reflectionClass = ReflectionManager::reflectClass($class);

        $promotedPropertyNames = [];

        if ($constructor = $reflectionClass->getConstructor()) {
            foreach ($constructor->getParameters() as $reflectionParameter) {
                if ($reflectionParameter->isPromoted()) {
                    $promotedPropertyNames[$reflectionParameter->getName()] = true;
                }
            }
        }

        $result = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {

            $propertyName = $reflectionProperty->getName();

            if (isset($promotedPropertyNames[$propertyName])) {
                continue;
            }

            if (!empty($reflectionProperty->getAttributes(Inherent::class))) {
                continue;
            }

            if ($propertyName !== Str::camel($propertyName)) {
                throw new InvalidArgumentException('Input parameter property name must be camelCase.');
            }

            if (!empty($reflectionProperty->getAttributes(Inject::class))) {
                throw new InvalidArgumentException('Please use constructor parameters instead of Inject annotation to inject dependencies.');
            }

            $propertyPrompt = 'Property ' . $reflectionClass->getName() . '::' . $propertyName;

            if (!$type = $reflectionProperty->getType()) {
                throw new TypeError($propertyPrompt . '  must have type.');
            } else if ($type instanceof ReflectionUnionType) {
                throw new TypeError($propertyPrompt . ' type cannot be defined as union type.');
            } else if ($type instanceof ReflectionIntersectionType) {
                throw new TypeError($propertyPrompt . ' type cannot be defined as intersection type.');
            }

            $attributes = [];

            foreach ($reflectionProperty->getAttributes() as $reflectionAttribute) {
                $attributes[] = $reflectionAttribute->newInstance();
            }

            $result[] = new ActionAttribute(
                $propertyName,
                $type->getName(),
                $type->allowsNull(),
                $reflectionProperty->hasDefaultValue(),
                $reflectionProperty->getDefaultValue(),
                $attributes
            );
        }

        return static::$attributes[$class] = $result;
    }

    /**
     * 获取属性是否已初始化的键名
     *
     * @param InputParameterInterface $inputParameter 输入参数
     * @param string $attributeName 属性名称
     *
     * @author Verdient。
     */
    protected static function initializedKey(InputParameterInterface $object, string $attributeName): string
    {
        return static::class . '::' . spl_object_id($object) . '::' . $attributeName;
    }

    /**
     * 判断一个属性是否已初始化
     *
     * @param InputParameterInterface $inputParameter 输入参数
     * @param string $attributeName 属性名称
     *
     * @author Verdient。
     */
    public static function isInitialized(InputParameterInterface $object, string $attributeName): bool
    {
        return Context::get(static::initializedKey($object, $attributeName)) === true;
    }

    /**
     * 将属性设置为已初始化
     *
     * @param InputParameterInterface $inputParameter 输入参数
     * @param string $attributeName 属性名称
     *
     * @author Verdient。
     */
    public static function setInitialized(InputParameterInterface $object, string $attributeName): void
    {
        Context::set(static::initializedKey($object, $attributeName), true);
    }
}
