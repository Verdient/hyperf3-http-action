<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Async;

/**
 * 批准接口
 *
 * @author Verdient。
 */
interface ApprovalInterface
{
    /**
     * 判断是否批准
     *
     * @author Verdient。
     */
    public function approval(): bool|int;
}
