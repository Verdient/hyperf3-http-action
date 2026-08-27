<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use Hyperf\Context\Context;
use Hyperf\Di\Container;
use Hyperf\HttpMessage\Exception\ForbiddenHttpException;
use Hyperf\HttpServer\Router\Dispatched;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use Verdient\Hyperf3\Di\Container as DiContainer;
use Verdient\Hyperf3\HttpAction\Annotation\AsyncCollector;
use Verdient\Hyperf3\HttpAction\Async\AdapterInterface;
use Verdient\Hyperf3\HttpAction\Async\ApprovalInterface;
use Verdient\Hyperf3\HttpAction\Async\BeforeActionInterface;
use Verdient\Hyperf3\HttpServer\CoreMiddleware as HttpServerCoreMiddleware;
use Verdient\Hyperf3\HttpServer\DataBag;

use function Hyperf\Support\make;

/**
 * 核心中间件
 *
 * @author Verdient。
 */
class CoreMiddleware extends HttpServerCoreMiddleware
{
    /**
     * @author Verdient。
     */
    #[Override]
    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request): mixed
    {
        if ($this->shouldHandle($dispatched)) {
            $validatedData = Context::get('Verdient\Hyperf3\Validation\ValidatedData', null);

            $actionClass = $dispatched->handler->callback[0];

            /** @var ActionInterface */
            $actionInstance = make($actionClass, $dispatched->params);

            Utils::injectValue($actionInstance, is_array($validatedData) ? $validatedData : []);

            if ($actionInstance instanceof BeforeActionInterface) {
                $result = $actionInstance->beforeAction();

                if (!$result->getIsOK()) {
                    return DataBag::failed($result->getMessage());
                }
            }

            if ($async = AsyncCollector::get($actionClass)) {
                return DataBag::succeed([
                    'identifier' => DiContainer::get(AdapterInterface::class)->push(Utils::toAsyncAction($actionInstance, $async->queue)),
                    'approval' => $actionInstance instanceof ApprovalInterface
                ]);
            }

            if ($actionInstance instanceof ApprovalInterface) {
                if ($actionInstance->approval() !== true) {
                    throw new ForbiddenHttpException();
                }
            }

            if ($this->container instanceof Container) {
                $this->container->set($dispatched->handler->callback[0], $actionInstance);
            }
        }

        return parent::handleFound($dispatched, $request);
    }

    /**
     * 判断是否需要处理
     *
     * @param Dispatched $dispatched 调度对象
     *
     * @author Verdient。
     */
    protected function shouldHandle(Dispatched $dispatched): bool
    {
        return is_array($dispatched->handler->callback)
            && isset($dispatched->handler->callback[0])
            && is_subclass_of($dispatched->handler->callback[0], ActionInterface::class);
    }
}
