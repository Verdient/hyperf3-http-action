<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

/**
 * 动作属性
 *
 * @author Verdient。
 */
class ActionAttribute
{
    /**
     * @param string $name 名称
     * @param string $type 类型
     * @param bool $allowsNull 是否允许为空
     * @param bool $hasDefault 是否有默认值
     * @param mixed $defaultValue 默认值
     * @param array<int,object> $annotations 注解集合
     *
     * @author Verdient。
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly bool $allowsNull,
        public readonly bool $hasDefault,
        public readonly mixed $defaultValue,
        public readonly array $annotations
    ) {}
}
