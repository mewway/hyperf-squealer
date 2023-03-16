<?php

namespace Huanhyperf\Squealer\Contract;

interface DatabaseParseFieldInterface
{
    /**
     * 获取表含义
     * @param string $table
     * @return string
     */
    public function getTableAlias(string $table = ''):string;

    /**
     * 获取字段含义
     * @param string $field
     * @param string $table
     * @return string
     */
    public function getFieldAlias(string $field, string $table = ''): string;

    /**
     * 获取字段类型
     * @param string $field
     * @param string $table
     * @return string
     */
    public function getFieldType(string $field, string $table = ''): ?string;

    /**
     * 获取枚举值map
     * @param string $field
     * @param string $table
     * @return array|null
     */
    public function getEnums(string $field, string $table = ''): ?array;
}