<?php

namespace Huanhyperf\Squealer\Utils;

use Doctrine\DBAL\Platforms\MySQL57Platform;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Huanhyperf\Squealer\Contract\ParseFieldInterface;
use Huanhyperf\Squealer\Traits\CommentParser;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Schema\Schema;
use Hyperf\Utils\ApplicationContext;
use Psr\SimpleCache\CacheInterface;

class FieldDbParser implements ParseFieldInterface
{
    use CommentParser;

    const CACHE_KEY = 'user:operation:change_log:';

    const CACHE_TIME = 0;

    /**
     * @var MySQL57Platform|MySQL80Platform
     */
    protected $mysqlPlatform;

    /**
     * @var AbstractSchemaManager
     */
    protected $schemaManager;

    public function __construct()
    {
        $mysqlVersion = config('squealer.mysql_version', 5.7);
        $this->mysqlPlatform = $mysqlVersion == 5.7
            ? new MySQL57Platform()
            : new MySQL80Platform();
        // 数据库管理器
        $this->schemaManager = $this->getSchemaManager();
    }

    /**
     * 根据模型获取表结构信息.
     */
    protected function getTableDetailByModel(Model $model): Table
    {
        // 完整表名
        $fullTable = $model->getConnection()->getTablePrefix() . $model->getTable();
        return $this->getTableDetail($fullTable);
    }

    /**
     * 有缓存地获取表结构详情.
     * @param string $fullTable
     */
    protected function getTableDetail(string $fullTable): Table
    {
        $cache = ApplicationContext::getContainer()->make(CacheInterface::class);
        if ($cache->has(self::CACHE_KEY . 'table_detail:' . $fullTable)) {
            return $cache->get(self::CACHE_KEY . 'table_detail:' . $fullTable);
        }
        $detail = $this->schemaManager->listTableDetails($fullTable);
        $cache->set(self::CACHE_KEY . 'table_detail:' . $fullTable, $detail, self::CACHE_TIME);
        return $detail;
    }

    public function getTableAlias(string $table): string
    {
        $tableDetail = $this->getTableDetail($table);
        $tableName = $tableDetail->getComment();
        $tableNameArr = explode(' ', $tableName);
        return reset($tableNameArr) ?: $table;
    }

    public function getFieldAlias(string $field, string $table): string
    {
        $tableDetail = $this->getTableDetail($table);
        $columns = $tableDetail->getColumns();
        foreach ($columns as $column) {
            if ($column->getName() === $field) {
                return $this->parse($column->getComment())->getColumn();
            }
        }

        return $field;
    }

    public function getEnums(string $field, string $table): ?array
    {
        $tableDetail = $this->getTableDetail($table);
        $columns = $tableDetail->getColumns();
        foreach ($columns as $column) {
            if ($column->getName() === $field) {
                return $this->getEnumeration();
            }
        }

        return null;
    }

    public function getFieldType(string $field, string $table): ?string
    {
        $tableDetail = $this->getTableDetail($table);
        $columns = $tableDetail->getColumns();
        foreach ($columns as $column) {
            if ($column->getName() === $field) {
                return $column->getType()->getName();
            }
        }

        return null;
    }

    /**
     * 获取数据库管理器.
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    private function getSchemaManager()
    {
        return $this->schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
    }
}