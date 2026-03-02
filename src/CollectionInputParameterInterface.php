<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use ArrayAccess;
use Hyperf\Contract\Arrayable;

/**
 * 集合输入参数接口
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @extends Arrayable<TKey,array>
 * @extends ArrayAccess<TKey,TValue>
 *
 * @author Verdient。
 */
interface CollectionInputParameterInterface extends Arrayable, ArrayAccess
{
    /**
     * @param array<TKey,TValue> $objects 对象数组
     *
     * @author Verdient。
     */
    public static function create(array $objects): static;
}
