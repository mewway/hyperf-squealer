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
namespace Huanhyperf\Squealer;

use Hyperf\Utils\Contracts\Arrayable;

class UserInfo implements Arrayable
{
    public $userName;

    public $userId;

    public $extra = [];

    public function __construct(string $name, $userId = null, array $extra = [])
    {
        $this->userName = $name;
        $this->userId = $userId;
        $this->extra = $extra;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'extra' => $this->extra,
        ];
    }
}
