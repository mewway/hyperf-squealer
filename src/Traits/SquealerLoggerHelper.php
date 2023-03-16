<?php

namespace Huanhyperf\Squealer\Traits;

use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;

trait SquealerLoggerHelper
{
    /**
     * 日志记录所要用到的属性转换方法.
     * @param mixed $key
     * @param mixed $value
     * @param array $fieldEnum
     * @return mixed
     */
    public function mutateAttributes(string $key, $value, array $fieldEnum = [])
    {
        $value = empty($value) ? $this->getAttributeFromArray($key) : $value;
        if (method_exists($this, 'get' . Str::studly($key) . 'Attribute')) {
            return $this->{'get' . Str::studly($key) . 'Attribute'}($value);
        }
        if (method_exists($this, 'format' . Str::studly($key) . 'Attribute')) {
            return $this->{'format' . Str::studly($key) . 'Attribute'}($value);
        }
        if (! empty($fieldEnum)) {
            return $fieldEnum[$value] ?? $value;
        }
        return $value;
    }

    /**
     * 获取表含义
     * @param string $table
     * @return string
     */
    public function getTableAlias(string $table = ''):?string
    {
        return defined('static::MODEL_TABLE_ALIAS') ? constant('static::MODEL_TABLE_ALIAS') : null;
    }

    /**
     * 获取字段含义
     * @param string $field
     * @param string $table
     * @return string
     */
    public function getFieldAlias(string $field, string $table = ''): ?string
    {
        $map = defined('static::MODEL_FIELD_MAPPING')
            ? (constant('static::MODEL_FIELD_MAPPING') ?? [])
            : [];
        return Arr::get($map, $field) ?? null;
    }

    /**
     * 获取字段类型
     * @param string $field
     * @param string $table
     * @return string
     */
    public function getFieldType(string $field, string $table = ''): ?string
    {
        return 'string';
    }

    /**
     * 获取枚举值map
     * @param string $field
     * @param string $table
     * @return array|null
     */
    public function getEnums(string $field, string $table = ''): ?array
    {
        $map = defined('static::MODEL_FIELD_ENUMS')
            ? (constant('static::MODEL_FIELD_ENUMS') ?? [])
            : [];
        return Arr::get($map, $field) ?? null;
    }

    public function parse(string $field, string $table = ''):?array
    {
        $filedAlias = $this->getFieldAlias($field, $table);
        $enums = $this->getEnums($field, $table);
        if (empty($filedAlias) && empty($enums)) {
            return null;
        }
        return [$filedAlias, $enums];
    }
}