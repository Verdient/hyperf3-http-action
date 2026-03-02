<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Override;
use Verdient\Hyperf3\HttpServer\Method;

/**
 * 请求方法
 *
 * @author Verdient。
 */
#[Attribute(Attribute::TARGET_CLASS)]
class RequestMethod extends AbstractAnnotation
{
    /**
     * @param array $methods 请求方法
     *
     * @author Verdient。
     */
    public function __construct(
        public readonly array $methods = [
            Method::GET,
            Method::POST,
            Method::PUT,
            Method::PATCH,
            Method::DELETE,
            Method::HEAD,
            Method::OPTIONS
        ]
    ) {}

    /**
     * @author Verdient。
     */
    #[Override]
    public function collectClass(string $className): void
    {
        RequestMethodCollector::collectClass($className, $this);
    }
}
