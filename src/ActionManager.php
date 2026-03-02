<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\PriorityMiddleware;
use Hyperf\Server\Server;
use Hyperf\Stringable\Str;
use SplFileInfo;
use Verdient\Hyperf3\HttpAction\Annotation\RequestMethodCollector;
use Verdient\Hyperf3\HttpAction\Annotation\RequestPath;
use Verdient\Hyperf3\HttpAction\Annotation\RequestServer;

use function Hyperf\Config\config;
use function Hyperf\Support\class_basename;

/**
 * 动作管理器
 *
 * @author Verdient。
 */
class ActionManager
{
    /**
     * @var Action[] 缓存的动作集合
     *
     * @author Verdient。
     */
    protected static array $cachedActions = [];

    /**
     * @var array<string,object[]> 缓存的
     *
     * @author Verdient。
     */
    protected static array $cachedAttributes = [];

    /**
     * 缓存的默认的服务器名称
     *
     * @author Verdient。
     */
    protected static string|null|false $cachedDefaultServerName = null;

    /**
     * 缓存的PSR4配置
     *
     * @author Verdient。
     */
    protected static ?array $cachedComposerPSR4 = null;

    /**
     * 缓存的前置路径
     *
     * @author Verdient。
     */
    protected static ?array $cachedPrecedingPaths = null;

    /**
     * 获取动作
     *
     * @author Verdient。
     */
    public static function get(string $className): ?Action
    {
        if (array_key_exists($className, static::$cachedActions)) {
            return static::$cachedActions[$className];
        }

        if (!$requestMethods = RequestMethodCollector::get($className)) {
            static::$cachedActions[$className] = null;
            return null;
        }

        $methods = [];

        foreach ($requestMethods as $requestMethod) {
            $methods = array_merge($methods, $requestMethod->methods);
        }

        $methods = array_values(array_unique($methods));

        $reflectionClass = ReflectionManager::reflectClass($className);

        $attributes = [];

        foreach ($reflectionClass->getAttributes() as $reflectionAttribute) {
            $attributes[] = $reflectionAttribute->newInstance();
        }

        $inheritedAttributes = [];

        $dirName = dirname($reflectionClass->getFileName());

        while ($dirName !== BASE_PATH) {
            if ($dirAttributes = static::getAttributes($dirName)) {
                $inheritedAttributes[] = $dirAttributes;
            }
            $dirName = dirname($dirName);
        }

        return new Action(
            methods: $methods,
            paths: static::getPaths($className, $attributes),
            servers: static::getServers($attributes, $inheritedAttributes),
            middlewares: static::getMiddlewares($attributes, $inheritedAttributes),
            attributes: $attributes,
            inheritedAttributes: $inheritedAttributes
        );
    }

    /**
     * 获取路径集合
     *
     * @param object[] $attributes 注解
     * @param string $className 类名
     *
     * @return string[]
     * @author Verdient。
     */
    protected static function getPaths(string $className, array $attributes): array
    {
        $classPaths = [];

        foreach ($attributes as $attribute) {
            if ($attribute instanceof RequestPath && $attribute->path) {
                $classPaths[] = $attribute->path;
            }
        }

        if (empty($classPaths)) {
            $classPaths = [static::getPathByClassName($className)];
        }

        $result = [];

        foreach ($classPaths as $classPath) {
            if (str_starts_with($classPath, '/')) {
                $result[] = $classPath;
            } else {
                $groupPaths = static::precedingPaths($className);

                foreach ($groupPaths as $groupPath) {
                    if ($groupPath === '/') {
                        $path = '/' . $classPath;
                    } else {
                        if ($classPath) {
                            $path = $groupPath . '/' . $classPath;
                        } else {
                            $path = $groupPath;
                        }
                    }
                    $result[] = $path;
                }
            }
        }

        return $result;
    }

    /**
     * 获取服务器集合
     *
     * @param string $className 类名
     * @param object[] $attributes 注解
     * @param array<int,object[]> $inheritedAttributes 继承的注解
     *
     * @return string[]
     * @author Verdient。
     */
    protected static function getServers(array $attributes, array $inheritedAttributes): array
    {
        $result = [];

        foreach ($attributes as $attribute) {
            if ($attribute instanceof RequestServer) {
                $result[] = $attribute->server;
            }
        }

        if (!empty($result)) {
            return $result;
        }

        foreach ($inheritedAttributes as $inheritedAttribute) {
            foreach ($inheritedAttribute as $attribute) {
                if ($attribute instanceof RequestServer) {
                    $result[] = $attribute->server;
                }
            }

            if (!empty($result)) {
                return $result;
            }
        }

        return [static::getDefaultServerName()];
    }

    /**
     * 获取默认的服务器名称
     *
     * @author Verdient。
     */
    protected static function getDefaultServerName(): ?string
    {
        if (static::$cachedDefaultServerName) {
            return static::$cachedDefaultServerName;
        }

        if (static::$cachedDefaultServerName === false) {
            return null;
        }

        static::$cachedDefaultServerName = false;

        foreach (config('server.servers') as $server) {
            if ($server['type'] == Server::SERVER_HTTP) {
                static::$cachedDefaultServerName = $server['name'];
                break;
            }
        }

        return static::$cachedDefaultServerName ?: null;
    }

    /**
     * 获取中间件集合
     *
     * @param string $className 类名
     * @param object[] $attributes 注解
     * @param array<int,object[]> $inheritedAttributes 继承的注解
     *
     * @return PriorityMiddleware[]
     * @author Verdient。
     */
    protected static function getMiddlewares(array $attributes, array $inheritedAttributes): array
    {
        $mergedAttributes = $attributes;

        foreach ($inheritedAttributes as $inheritedAttributes2) {
            foreach ($inheritedAttributes2 as $inheritedAttribute) {
                $mergedAttributes[] = $inheritedAttribute;
            }
        }

        $addedMiddlewares = [];

        $result = [];

        foreach ($mergedAttributes as $attribute) {
            $middlewares = [];

            if ($attribute instanceof Middleware) {
                if (in_array($attribute->middleware, $addedMiddlewares)) {
                    continue;
                }
                $middlewares = [$attribute];
            }

            if ($attribute instanceof Middlewares) {
                foreach ($attribute->middlewares as $middleware) {
                    if (in_array($middleware->middleware, $addedMiddlewares)) {
                        continue;
                    }
                    $middlewares[] = $middleware;
                }
            }

            foreach ($middlewares as $middleware) {
                $result[] = $middleware->priorityMiddleware;
                $addedMiddlewares[] = $middleware->middleware;
            }
        }

        return $result;
    }

    /**
     * 获取路径下的注解
     *
     * @param string $path 路径
     *
     * @author Verdient。
     */
    protected static function getAttributes(string $path): ?array
    {
        if (array_key_exists($path, static::$cachedAttributes)) {
            return static::$cachedAttributes[$path];
        }

        $attributesFileName = $path . DIRECTORY_SEPARATOR . 'attributes.php';

        if (!file_exists($attributesFileName)) {
            static::$cachedAttributes[$path] = null;
            return null;
        }

        $attributes = require $attributesFileName;

        if (!is_array($attributes)) {
            static::$cachedAttributes[$path] = null;
            return null;
        }

        static::$cachedAttributes[$path] = $attributes;

        return $attributes;
    }

    /**
     * 获取Composer PSR4 配置
     *
     * @author Verdient。
     */
    protected static function getComposerPSR4(): array
    {
        if (static::$cachedComposerPSR4 === null) {
            static::$cachedComposerPSR4 = [];
            $composerJsonPath = BASE_PATH . '/composer.json';

            if (file_exists($composerJsonPath)) {
                $composerJson = json_decode(file_get_contents($composerJsonPath), true);
                static::$cachedComposerPSR4 = $composerJson['autoload']['psr-4'] ?? [];
                uksort(static::$cachedComposerPSR4, function ($a, $b) {
                    return substr_count((string) $b, '\\') <=> substr_count((string) $a, '\\');
                });
            }
        }

        return static::$cachedComposerPSR4;
    }

    /**
     * 获取命名空间的路径
     *
     * @author Verdient。
     */
    protected static function getNamespacePath(string $namespace): ?string
    {
        $composerPSR4 = static::getComposerPSR4();

        if (empty($composerPSR4)) {
            return null;
        }

        foreach ($composerPSR4 as $namespacePrefix => $path) {
            if (!str_starts_with($namespace, $namespacePrefix)) {
                continue;
            }

            $remainNamespaceName = substr($namespace, strlen($namespacePrefix));

            if ($path[0] === '/') {
                $splFileInfo = new SplFileInfo($path . '/' . str_replace('\\', '/', $remainNamespaceName));
            } else {
                $splFileInfo = new SplFileInfo(BASE_PATH . '/' . $path . '/' . str_replace('\\', '/', $remainNamespaceName));
            }

            return $splFileInfo->getPathname();
        }

        return null;
    }

    /**
     * 获取前置的路径
     *
     * @param string $className 类名
     *
     * @return string[]
     * @author Verdient。
     */
    protected static function precedingPaths(string $className): array
    {
        $reflectionClass = ReflectionManager::reflectClass($className);

        $namespaceName = $reflectionClass->getNamespaceName();

        if (isset(static::$cachedPrecedingPaths[$namespaceName])) {
            return static::$cachedPrecedingPaths[$namespaceName];
        }

        if (!$dirName = static::getNamespacePath($namespaceName)) {
            $dirName = dirname($reflectionClass->getFileName());
        }

        $dirPaths = [];

        while ($dirName !== BASE_PATH) {

            $dirPaths[] = static::dirPaths($dirName);

            $dirName = dirname($dirName);
        }

        $paths = static::combinePaths($dirPaths);

        static::$cachedPrecedingPaths[$namespaceName] = $paths;

        return static::$cachedPrecedingPaths[$namespaceName];
    }

    /**
     * 组合路径
     *
     * @param array<int,string[]> $paths 路径集合
     *
     * @return string[]
     * @author Verdient。
     */
    protected static function combinePaths(array $paths): array
    {
        $results = [];

        $recurse = function (int $index, string $current = '') use (&$recurse, &$paths, &$results) {
            if ($index === count($paths)) {
                $results[] = $current;
                return;
            }

            foreach ($paths[$index] as $part) {
                if (str_starts_with($part, '/')) {
                    if ($part === '/') {
                        $results[] = $part . $current;
                    } else {
                        $results[] = $part . '/' . $current;
                    }
                } else {
                    $newPath = $current === '' ? $part : $part . '/' . $current;
                    $recurse($index + 1, $newPath);
                }
            }
        };

        $recurse(0);

        $results = array_map(function ($path) {
            if (!str_starts_with($path, '/')) {
                $path = '/' . $path;
            }

            if ($path !== '/') {
                $path = rtrim($path, '/');
            }

            return $path;
        }, $results);

        return array_values(array_unique($results));
    }

    /**
     * 获取文件夹的路径集合
     *
     * @param string $path 文件夹路径
     *
     * @return string[]
     * @author Verdient。
     */
    protected static function dirPaths(string $path): array
    {
        $result = [];

        if ($attributes = static::getAttributes($path)) {
            foreach ($attributes as $attribute) {
                if ($attribute instanceof RequestPath && $attribute->path) {
                    $result[] = $attribute->path;
                }
            }
        }

        if (!empty($result)) {
            return $result;
        }

        return [Str::kebab(basename($path))];
    }

    /**
     * 通过类名获取路径
     *
     * @param string $className 类名
     *
     * @author Verdiant。
     */
    protected static function getPathByClassName(string $className): string
    {
        $className = class_basename($className);

        if (str_ends_with($className, 'Action') && $className !== 'Action') {
            $className = substr($className, 0, -6);
        }

        if ($className === 'Index') {
            return '';
        }

        return Str::kebab($className);
    }
}
