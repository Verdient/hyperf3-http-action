<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Async;

use Verdient\Hyperf3\Struct\Result;

/**
 * 前置动作接口
 *
 * @author Verdient。
 */
interface BeforeActionInterface
{
    /**
     * 前置动作
     *
     * @author Verdient。
     */
    public function beforeAction(): Result;
}
