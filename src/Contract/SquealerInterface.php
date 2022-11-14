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
namespace Huanhyperf\Squealer\Contract;

use Huanhyperf\Squealer\UserInfo;

interface SquealerInterface
{
    /**
     * 标注的忽略监听变化的字段
     */
    const IGNORE_FIELDS= [];
    /**
     * 标注的需要监听变化的字段
     */
    const WATCH_FIELDS = [];

    /**
     * 敏感字段 记录变化时不会透出到客户端
     */
    const SENSITIVE_FIELDS = [];

    /**
     * 自定义字段的含义
     */
    const CUSTOM_FIELD_MAPPING = [];

    /**
     * 策略组 根据变化触发特定的策略
     */
    const STRATEGY_GROUP = [];

    public function getCurrentUserInfo(): UserInfo;
}
