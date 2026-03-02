<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Override;

/**
 * 请求路径
 *
 * @author Verdient。
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class RequestPath extends AbstractAnnotation
{
    /**
     * @param string $path 请求路径
     *
     * @author Verdient。
     */
    public function __construct(public readonly string $path) {}

    /**
     * @author Verdient。
     */
    #[Override]
    public function collectClass(string $className): void
    {
        RequestPathCollector::collectClass($className, $this);
    }
}
