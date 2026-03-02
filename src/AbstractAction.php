<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use Hyperf\Stringable\Str;
use Override;

/**
 * 抽象动作
 *
 * @author Verdient。
 */
abstract class AbstractAction implements ActionInterface
{
    use ToArray;

    /**
     * @author Verdient。
     */
    #[Override]
    public function inputs(bool $snakeCase = false): array
    {
        if ($snakeCase) {
            return $this->changeKeyToSnakeCase($this->toArray());
        }

        return $this->toArray();
    }

    /**
     * 将数组的键转为下划线格式
     *
     * @param array $array 待转换的数组
     *
     * @author Verdient。
     */
    private function changeKeyToSnakeCase(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_string($key)) {
                $key = Str::snake($key);
            }

            $result[$key] = is_array($value) ? $this->changeKeyToSnakeCase($value) : $value;
        }

        return $result;
    }
}
