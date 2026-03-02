<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Annotation;

use Attribute;
use Verdient\Hyperf3\HttpServer\Method;

/**
 * GET请求
 *
 * @author Verdient。
 */
#[Attribute(Attribute::TARGET_CLASS)]
class GetRequest extends RequestMethod
{
    /**
     * @param ?string $server 服务器
     *
     * @author Verdient。
     */
    public function __construct()
    {
        parent::__construct(methods: [Method::GET]);
    }
}
