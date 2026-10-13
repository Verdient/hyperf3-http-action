<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Override;

/**
 * 异步动作
 *
 * @author Verdient。
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Async extends AbstractAnnotation
{
    /**
     * @author Verdient。
     */
    public function __construct(public readonly ?string $queue = null) {}

    /**
     * @author Verdient。
     */
    #[Override]
    public function collectClass(string $className): void
    {
        AsyncCollector::collectClass($className, $this);
    }
}
