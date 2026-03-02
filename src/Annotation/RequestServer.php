<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Override;

/**
 * 请求服务器
 *
 * @author Verdient。
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class RequestServer extends AbstractAnnotation
{
    /**
     * @param ?string $server 服务器
     *
     * @author Verdient。
     */
    public function __construct(public readonly string $server) {}

    /**
     * @author Verdient。
     */
    #[Override]
    public function collectClass(string $className): void
    {
        RequestServerCollector::collectClass($className, $this);
    }
}
