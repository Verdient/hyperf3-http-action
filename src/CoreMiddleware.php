<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use BackedEnum;
use Hyperf\Context\Context;
use Hyperf\Di\Container;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\HttpServer\Router\Dispatched;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use TypeError;
use Verdient\Hyperf3\HttpServer\CoreMiddleware as HttpServerCoreMiddleware;

use function Hyperf\Support\make;

/**
 * 核心中间件
 *
 * @author Verdient。
 */
class CoreMiddleware extends HttpServerCoreMiddleware
{
    /**
     * @author Verdient。
     */
    #[Override]
    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request): mixed
    {
        if ($this->shouldHandle($dispatched)) {
            $validatedData = Context::get('Verdient\Hyperf3\Validation\ValidatedData', null);

            /** @var ActionInterface */
            $actionInstance = make($dispatched->handler->callback[0], $dispatched->params);

            $this->injectValue($actionInstance, is_array($validatedData) ? $validatedData : []);

            if ($this->container instanceof Container) {
                $this->container->set($dispatched->handler->callback[0], $actionInstance);
            }
        }

        return parent::handleFound($dispatched, $request);
    }

    /**
     * 判断是否需要处理
     *
     * @param Dispatched $dispatched 调度对象
     *
     * @author Verdient。
     */
    protected function shouldHandle(Dispatched $dispatched): bool
    {
        return is_array($dispatched->handler->callback)
            && isset($dispatched->handler->callback[0])
            && is_subclass_of($dispatched->handler->callback[0], ActionInterface::class);
    }

    /**
     * 注入数据
     *
     * @param InputParameterInterface $object 待注入的对象
     *
     * @author Verdient。
     */
    protected function injectValue(InputParameterInterface $object, array $data): InputParameterInterface
    {
        $className = get_class($object);

        foreach (AttributeManager::get($className) as $attribute) {

            $propertyName = $attribute->name;

            if (array_key_exists($propertyName, $data)) {

                $injectValue = $this->normalizeData($attribute->type, $data[$propertyName], $className, $propertyName);

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
    protected function normalizeData(string $type, mixed $data, string $class, string $property): mixed
    {
        switch ($type) {
            case 'string':
                return $this->normalizeString($data);
            case 'int':
                return $this->normalizeInt($data);
            case 'float':
                return $this->normalizeFloat($data);
            case 'bool':
                return $this->normalizeBoolean($data);
            case 'array':
                return $this->normalizeArray($data);
            case 'true':
                return $this->normalizeTrue($data);
            case 'false':
                return $this->normalizeFalse($data);
            case 'null':
                return null;
            default:
                if (is_subclass_of($type, BackedEnum::class)) {
                    return $this->normalizeBackedEnum($type, $data);
                } else if (is_subclass_of($type, ObjectInputParameterInterface::class)) {
                    return $this->normalizeObjectInputParameter($type, $data);
                } else if (is_subclass_of($type, CollectionInputParameterInterface::class)) {
                    return $this->normalizeCollectionInputParameter($type, $data, $class, $property);
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
    protected function normalizeString(mixed $value): ?string
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
    protected function normalizeInt(mixed $value): ?int
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
    protected function normalizeFloat(mixed $value): ?float
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
    protected function normalizeArray(mixed $value): ?array
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
    protected function normalizeBoolean(mixed $value): ?bool
    {
        if ($this->normalizeTrue($value) === true) {
            return true;
        }

        if ($this->normalizeFalse($value) === false) {
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
    protected function normalizeTrue(mixed $value): ?true
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
    protected function normalizeFalse(mixed $value): ?false
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
    protected function normalizeBackedEnum(string $class, mixed $value): ?BackedEnum
    {
        $cases = $class::cases();

        if (empty($cases)) {
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
    protected function normalizeObjectInputParameter(string $class, ?array $value)
    {
        if ($value === null) {
            return null;
        }
        $injectValue = make($class);
        return $this->injectValue($injectValue, $value);
    }

    /**
     * 格式化集合输入参数
     *
     * @param class-string<CollectionInputParameterInterface> $class 类名
     * @param ?array $data 数据
     * @param string $class 类名
     * @param string $property 属性名称
     *
     * @author Verdient。
     */
    protected function normalizeCollectionInputParameter(string $class, ?array $value, string $ownedClass, string $property)
    {
        if ($value === null) {
            return null;
        }
        $collectionType = CollectionTypeManager::get($class)->type;

        $items = [];

        foreach ($value as $rowData) {
            $items[] = $this->normalizeData($collectionType, $rowData, $ownedClass, $property);
        }

        return $class::create($items);
    }
}
