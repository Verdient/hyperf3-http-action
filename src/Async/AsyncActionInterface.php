<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Async;

/**
 * 异步动作接口
 *
 * @author Verdient。
 */
interface AsyncActionInterface
{
    /**
     * 创建异步动作
     *
     * @author Verdient。
     */
    public static function create(
        string $class,
        ?array $params,
        ?array $properties,
        ?string $queue
    ): static;

    /**
     * 获取类
     *
     * @author Verdient。
     */
    public function class(): string;

    /**
     * 获取类
     *
     * @author Verdient。
     */
    public function params(): ?array;

    /**
     * 获取属性
     *
     * @author Verdient。
     */
    public function properties(): ?array;

    /**
     * 获取队列
     *
     * @author Verdient。
     */
    public function queue(): ?string;
}
