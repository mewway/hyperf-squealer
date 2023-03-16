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

/**
 * @property  array IGNORE_FIELDS 标注的忽略监听变化的字段
 * @property  array WATCH_FIELDS 标注的需要监听变化的字段
 * @property  array SENSITIVE_FIELDS 敏感字段 记录变化时不会透出到客户端
 * @property  array STRATEGY_GROUP 策略组 根据变化触发特定的策略
 * @property  array MODEL_TABLE_ALIAS 数据表的记录别名
 * @property  array MODEL_FIELD_MAPPING 自定义字段的含义
 * 模型监听的配置 形如：
 * [
 *   'class_name' => '',
 *   'short_tag' => '',
 *   'tagged_field' => '',
 *   'related_field' => '',
 *   'relation' => [],
 * ]
 * @property  array MODEL_SQUEALER_CONFIG 模型监听的配置
 * @property  array MODEL_FIELD_ENUMS 枚举值配置
 */
interface SquealerInterface
{
    public function getCurrentUserInfo(): UserInfo;
}
