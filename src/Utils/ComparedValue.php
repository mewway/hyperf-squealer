<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Huanhyperf\Squealer\Utils;

class ComparedValue
{
    const TYPE_ADDED = 'created';

    const TYPE_REMOVED = 'deleted';

    const TYPE_MODIFIED = 'updated';

    const TYPE_MAP = [
        self::TYPE_ADDED => '新增',
        self::TYPE_REMOVED => '移除',
        self::TYPE_MODIFIED => '修改',
    ];

    public $oldValue;

    public $newValue;

    public $type;

    /**
     * @param self::TYPE_* $type
     */
    public function __construct(string $type, $oldValue, $newValue)
    {
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
        $this->type = $type;
    }

    public function toHumanReadable(string $objName): string
    {
        $operation = self::TYPE_MAP[$this->type] ?? '操作';
        $desc = sprintf('值为： %s', $this->newValue);
        if ($this->type === self::TYPE_MODIFIED) {
            $desc = sprintf('值由： %s 变为： %s', $this->oldValue, $this->newValue);
        } elseif ($this->type === self::TYPE_REMOVED) {
            $desc = sprintf('原值为： %s', $this->oldValue);
        }

        return sprintf('【%s 了 %s】%s', $operation, $objName, $desc);
    }
}
