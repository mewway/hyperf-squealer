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
    public const TYPE_ADDED = 'added';

    public const TYPE_REMOVED = 'removed';

    public const TYPE_MODIFIED = 'modified';

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
}
