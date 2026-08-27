<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Async;

use Throwable;

/**
 * 适配器接口
 *
 * @author Verdient。
 */
interface AdapterInterface
{
    /**
     * 获取空闲间隔
     *
     * @author Verdient。
     */
    public function idleGap(): int|float;

    /**
     * 推送异步动作
     *
     * @param AsyncActionInterface $asyncAction 待推送的异步动作
     *
     * @author Verdient。
     */
    public function push(AsyncActionInterface $asyncAction): int|string|float|false;

    /**
     * 弹出任务
     *
     * @param string $queue 队列名称
     *
     * @author Verdient。
     */
    public function pop(string $queue): ?AsyncActionInterface;

    /**
     * 提交任务
     *
     * @param AsyncActionInterface $asyncAction 待提交的异步动作
     * @param ?array $data 数据
     *
     * @author Verdient。
     */
    public function commit(AsyncActionInterface $asyncAction, ?array $data): void;

    /**
     * 故障
     *
     * @param AsyncActionInterface $asyncAction 故障的任务
     * @param Throwable $throwable 异常
     *
     * @author Verdient。
     */
    public function fault(AsyncActionInterface $asyncAction, Throwable $throwable): void;

    /**
     * 重试任务
     *
     * @param AsyncActionInterface $asyncAction 重试的任务
     *
     * @author Verdient。
     */
    public function retry(AsyncActionInterface $asyncAction): void;
}
