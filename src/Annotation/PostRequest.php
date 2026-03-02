<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction\Annotation;

use Attribute;
use Verdient\Hyperf3\HttpServer\Method;

/**
 * POST请求
 *
 * @author Verdient。
 */
#[Attribute(Attribute::TARGET_CLASS)]
class PostRequest extends RequestMethod
{
    /**
     * @param ?string $server 服务器
     *
     * @author Verdient。
     */
    public function __construct()
    {
        parent::__construct(methods: [Method::POST]);
    }
}
