<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use BackedEnum;
use Hyperf\Contract\Arrayable;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpMessage\Upload\UploadedFile;
use ReflectionIntersectionType;
use ReflectionUnionType;
use TypeError;
use UnitEnum;
use Verdient\Hyperf3\HttpAction\Async\AsyncActionInterface;

use function Hyperf\Config\config;
use function Hyperf\Support\make;

/**
 * 工具
 *
 * @author Verdient。
 */
class Utils
{
    /**
     *
     * @author Verdient。
     */
    public static function createActionFromAsyncAction(AsyncActionInterface $asyncAction): ActionInterface
    {
        /** @var ActionInterface */
        $actionInstance = make($asyncAction->class(), $asyncAction->params() ?: []);

        $properties = $asyncAction->properties();

        if (is_array($properties)) {

            $class = $asyncAction->class();

            Utils::injectValue($actionInstance, $properties);

            foreach (ReflectionManager::reflectClass($class)->getProperties() as $reflectionProperty) {
                if ($reflectionProperty->isInitialized($actionInstance)) {
                    continue;
                }

                $name = $reflectionProperty->getName();

                if (!array_key_exists($name, $properties)) {
                    continue;
                }

                $propertyPrompt = 'Property ' . $class . '::' . $name;

                if (!$type = $reflectionProperty->getType()) {
                    throw new TypeError($propertyPrompt . '  must have type.');
                } else if ($type instanceof ReflectionUnionType) {
                    throw new TypeError($propertyPrompt . ' type cannot be defined as union type.');
                } else if ($type instanceof ReflectionIntersectionType) {
                    throw new TypeError($propertyPrompt . ' type cannot be defined as intersection type.');
                }

                $reflectionProperty->setValue($actionInstance, Utils::normalizeData($type->getName(), $properties[$name], $class, $name));
            }
        }

        return $actionInstance;
    }

    /**
     * 注入数据
     *
     * @param InputParameterInterface $object 待注入的对象
     *
     * @author Verdient。
     */
    public static function injectValue(InputParameterInterface $object, array $data): InputParameterInterface
    {
        $className = get_class($object);

        foreach (AttributeManager::get($className) as $attribute) {

            $propertyName = $attribute->name;

            if (array_key_exists($propertyName, $data)) {

                $injectValue = static::normalizeData($attribute->type, $data[$propertyName], $className, $propertyName);

                if ($injectValue === null) {
                    $injectValue = $attribute->defaultValue;
                }

                AttributeManager::setInitialized($object, $propertyName);

                ReflectionManager::reflectProperty($className, $propertyName)
                    ->setValue($object, $injectValue);
            } else {
                if (!$attribute->hasDefault && $attribute->allowsNull) {
                    $reflectionProperty = ReflectionManager::reflectProperty($className, $propertyName);
                    $reflectionProperty->setValue($object, null);
                }
            }
        }

        return $object;
    }

    /**
     * 格式化数据
     *
     * @param string $type 类型
     * @param mixed $data 数据
     * @param string $class 类名
     * @param string $property 属性名称
     *
     * @author Verdient。
     */
    public static function normalizeData(string $type, mixed $data, string $class, string $property): mixed
    {
        switch ($type) {
            case 'string':
                return static::normalizeString($data);
            case 'int':
                return static::normalizeInt($data);
            case 'float':
                return static::normalizeFloat($data);
            case 'bool':
                return static::normalizeBoolean($data);
            case 'array':
                return static::normalizeArray($data);
            case 'true':
                return static::normalizeTrue($data);
            case 'false':
                return static::normalizeFalse($data);
            case 'null':
                return null;
            default:
                if (is_subclass_of($type, BackedEnum::class)) {
                    return static::normalizeBackedEnum($type, $data);
                } else if (is_subclass_of($type, ObjectInputParameterInterface::class)) {
                    return static::normalizeObjectInputParameter($type, $data);
                } else if (is_subclass_of($type, CollectionInputParameterInterface::class)) {
                    return static::normalizeCollectionInputParameter($type, $data, $class, $property);
                } else if ($type === UploadedFile::class || is_subclass_of($type, UploadedFile::class)) {
                    return $data;
                } else {
                    throw new TypeError('Property ' . $class . '::' . $property . ' type cannot be defined as ' . $type . '.');
                }
        }
    }

    /**
     * 格式化字符串
     *
     * @param mixed $value 待格式化的值
     *
     * @author Verdient。
     */
    protected static function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value;
    }

    /**
     * 格式化整数
     *
     * @param mixed $value 待格式化的值
     *
     * @author Verdient。
     */
    protected static function normalizeInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * 格式化浮点数
     *
     * @param mixed $value 待格式化的值
     *
     * @author Verdient。
     */
    protected static function normalizeFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    /**
     * 格式化数组
     *
     * @param mixed $value 待格式化的值
     *
     * @author Verdient。
     */
    protected static function normalizeArray(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        return (array) $value;
    }

    /**
     * 格式化布尔
     *
     * @param mixed $value 待格式化的值
     *
     * @author Verdient。
     */
    protected static function normalizeBoolean(mixed $value): ?bool
    {
        if (static::normalizeTrue($value) === true) {
            return true;
        }

        if (static::normalizeFalse($value) === false) {
            return false;
        }

        return null;
    }

    /**
     * 格式化真值
     *
     * @param mixed $value 待格式化的值
     *
     * @author Verdient。
     */
    protected static function normalizeTrue(mixed $value): ?true
    {
        if ($value === null) {
            return null;
        }

        return in_array($value, ['yes', 'on', '1', 1, true, 'true'], true) ? true : null;
    }

    /**
     * 格式化假值
     *
     * @param mixed $value 待格式化的值
     *
     * @author Verdient。
     */
    protected static function normalizeFalse(mixed $value): ?false
    {
        if ($value === null) {
            return null;
        }

        return in_array($value, ['no', 'off', '0', 0, false, 'false'], true) ? false : null;
    }

    /**
     * 格式化枚举
     *
     * @param string $class 枚举类
     * @param mixed $value 待格式化的值
     *
     * @author Verdient。
     */
    protected static function normalizeBackedEnum(string $class, mixed $value): ?BackedEnum
    {
        $cases = $class::cases();

        if (empty($cases)) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            if ($value::class === $class) {
                return $value;
            }
            return null;
        }

        if (is_int($cases[0]->value)) {
            return $class::tryFrom((int) $value);
        }

        return $class::tryFrom((string) $value);
    }

    /**
     * 格式化输入参数
     *
     * @param class-string<ObjectInputParameterInterface> $class 类名
     * @param ?array $value 待格式化的值
     *
     * @author Verdient。
     */
    protected static function normalizeObjectInputParameter(string $class, ?array $value)
    {
        if ($value === null) {
            return null;
        }
        $injectValue = make($class);
        return static::injectValue($injectValue, $value);
    }

    /**
     * 格式化集合输入参数
     *
     * @param class-string<CollectionInputParameterInterface> $class 类名
     * @param ?array $value 数据
     * @param string $class 类名
     * @param string $property 属性名称
     *
     * @author Verdient。
     */
    protected static function normalizeCollectionInputParameter(string $class, ?array $value, string $ownedClass, string $property)
    {
        if ($value === null) {
            return null;
        }
        $collectionType = CollectionTypeManager::get($class)->type;

        $items = [];

        foreach ($value as $rowData) {
            $items[] = static::normalizeData($collectionType, $rowData, $ownedClass, $property);
        }

        return $class::create($items);
    }

    /**
     * 转换为异步任务
     *
     * @param ActionInterface $action 动作
     * @param ?string $queue 队列
     *
     * @author Verdient。
     */
    public static function toAsyncAction(ActionInterface $action, ?string $queue): AsyncActionInterface
    {
        $params = [];

        $reflectionClass = ReflectionManager::reflectClass($action::class);

        if ($constructor = $reflectionClass->getConstructor()) {
            foreach ($constructor->getParameters() as $reflectionParameter) {
                if ($reflectionParameter->isPromoted()) {
                    $name = $reflectionParameter->getName();
                    $params[$name] = $reflectionParameter->$name;
                }
            }
        }

        $properties = $action->toArray();

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (!$reflectionProperty->isInitialized($action)) {
                continue;
            }

            $name = $reflectionProperty->getName();

            if (!array_key_exists($name, $properties)) {
                $value = $reflectionProperty->getValue($action);

                if ($reflectionProperty->isDefault() && $value === $reflectionProperty->getDefaultValue()) {
                    continue;
                }

                if ($value === null && $reflectionProperty->getType()->allowsNull()) {
                    continue;
                }

                $value = $reflectionProperty->getValue($action);

                if ($value instanceof Arrayable) {
                    $value = $value->toArray();
                } else if ($value instanceof BackedEnum) {
                    $value = $value->value;
                } else if ($value instanceof UnitEnum) {
                    $value = $value->name;
                }

                $properties[$name] = $value;
            }
        }

        return make(AsyncActionInterface::class, [
            'class' => $action::class,
            'params' => empty($params) ? null : $params,
            'properties' => empty($properties) ? null : $properties,
            'queue' => $queue ?? config('http_action.async.default_queue')
        ]);
    }
}
