<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\HttpAction;

use BackedEnum;
use Hyperf\Contract\Arrayable;
use Override;
use UnitEnum;

/**
 * 将对象转换为数组
 *
 * @author Verdient。
 */
trait ToArray
{
    /**
     * @author Verdient。
     */
    #[Override]
    public function toArray(): array
    {
        $result = [];

        foreach (AttributeManager::get(static::class) as $attribute) {

            $attributeName = $attribute->name;

            $value = $this->$attributeName;

            if ($value === null && !AttributeManager::isInitialized($this, $attributeName)) {
                continue;
            }

            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            } else if ($value instanceof BackedEnum) {
                $value = $value->value;
            } else if ($value instanceof UnitEnum) {
                $value = $value->name;
            }

            $result[$attributeName] = $value;
        }

        return $result;
    }
}
