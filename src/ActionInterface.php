<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use Hyperf\Contract\Arrayable;
use Psr\Http\Message\ResponseInterface;
use Verdient\Hyperf3\HttpServer\DataBag;

/**
 * 动作接口
 *
 * @author Verdient。
 */
interface ActionInterface extends Arrayable, InputParameterInterface
{
    /**
     * 请求处理程序
     *
     * @author Verdient。
     */
    public function handle(): DataBag|ResponseInterface|null;

    /**
     * 获取入参集合
     *
     * @param bool $snakeCase 是否使用蛇形命名
     *
     * @author Verdient。
     */
    public function inputs(bool $snakeCase = true): array;
}
