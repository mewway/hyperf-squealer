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
namespace Huanhyperf\Squealer\Handler;

use Doctrine\DBAL\Platforms\MySQL57Platform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Huanhyperf\Database\Model\Model;
use Huanhyperf\Squealer\LoggedEvent;
use Huanhyperf\Squealer\Traits\CommentParser;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Schema\Schema;
use Hyperf\Utils\ApplicationContext;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractEventHandler
{
    use CommentParser;

    public const CACHE_KEY = 'user:operation:change_log:';

    public const CACHE_TIME = 0;

    public const IGNORE_LIST = [
        'id',
        '_time',
        '_at',
    ];

    public const EVENT_MAPPING = [
        'updated' => '更新',
        'created' => '新增',
        'deleted' => '删除',
    ];

    /**
     * @var MySQL57Platform
     */
    protected $mySQL57Platform;

    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected $schemaManager;

    /**
     * @var array
     */
    protected $shortTagMapping = [];

    /**
     * @var array
     */
    protected $taggedFieldMapping = [];

    /**
     * @var array
     */
    protected $relatedKeyMapping = [];

    protected $eventDescription;

    /**
     * @var array
     */
    protected $ignoreList;

    public function __construct()
    {
        $this->mySQL57Platform = new MySQL57Platform();
        // 数据库管理器
        $this->schemaManager = $this->getSchemaManager();
        $ignoreList = array_merge(self::IGNORE_LIST, config('operation_logger.ignore_list'));
        $this->ignoreList = array_unique($ignoreList);
        $this->shortTagMapping = $this->getModelShortTagMapping();
    }

    /**
     * 事件处理方法.
     * @return mixed
     */
    abstract public function process(Event $event);

    /**
     * 获取可记录的loggedEvent对象
     * @param string $clientIp
     */
    public function getLoggedEvent(Model $model, string $eventType = '', string $clientIp = ''): LoggedEvent
    {
        /**
         * @var Model $model
         */
        $loggedEvent = new LoggedEvent();
        $loggedEvent->setUserName($model->getUserName());
        $loggedEvent->setTriggerTime(date('Y-m-d H:i:s'));
        $loggedEvent->setClientIp($clientIp);
        $loggedEvent->setChangeContent($this->loadChangeContent($model, $eventType));
        $loggedEvent->setEventDesc($this->eventDescription);
        $className = get_class($model);
        $loggedEvent->setTriggerClass($className);
        $loggedEvent->setAssociatedId((string) $model->getAttribute('id'));
        $relationKey = $this->relatedKeyMapping[$className] ?? '';
        $loggedEvent->setAssociatedValue((string) $model->getAttribute($relationKey));
        return $loggedEvent;
    }

    /**
     * 对比模型事件前后的数据变化及事件描述.
     * @return string
     */
    public function loadChangeContent(Model $model, string $eventType = '')
    {
        $className = get_class($model);
        $original = $model->getOriginal();
        $changes = $model->getChanges();
        // 忽略清单
        $ignoreList = $this->ignoreList;
        // 软删除字段不计入更新
        if (defined("{$model}::DELETED_AT")) {
            $ignoreList[] = $model::DELETED_AT;
        }
        // 获取表结构信息
        $tableDetail = $this->getTableDetailByModel($model);
        // 优先取短标记 没有取表名
        $objectName = $this->shortTagMapping[$className] ?? $this->getTableNameFromDbal($tableDetail) ?? $model->getTable();

        $columns = $tableDetail->getColumns();
        $changeContents = '';
        foreach ($changes as $field => $value) {
            $check = false;
            // 过滤的不处理
            foreach ($ignoreList as $needle) {
                if (stripos($field, $needle) !== false) {
                    $check = true;
                    break;
                }
            }
            if (! $check) {
                /**
                 * @var null|Column $column
                 */
                $column = $columns[$field] ?? [];
                // 列空则跳过
                if (empty($column)) {
                    continue;
                }

                $type = $column->getType()->getName();
                $comment = $column->getComment();
                $parser = $this->parse($comment);
                $fieldName = $parser->getColumn();
                $fieldEnum = $parser->getEnumeration();
                switch ($type) {
                    // 数值型
                    case Types::INTEGER:
                    case Types::SMALLINT:
                    case Types::BOOLEAN:
                        $value = intval($value);
                        break;
                    case Types::FLOAT:
                        $scale = $column->getScale();
                        $value = round(floatval($value), $scale);
                        break;
                    case Types::BIGINT:
                    case Types::DECIMAL:
                        $scale = $column->getScale();
                        $value = number_format(floatval($value), $scale, '.', '');
                        break;
                        // 字符型
                    case Types::STRING:
                        $value = $column->getType()->convertToDatabaseValue($value, $this->mySQL57Platform);
                        break;
                        // 其他的暂时都不处理其变化
                    default:
                        $value = null;
                        break;
                }

                $before = '';
                if (isset($original[$field])) {
                    $before = $model->getFormatAttributeValue($field, $original[$field], $fieldEnum);
                }

                $after = is_null($value) ? null : $model->getFormatAttributeValue($field, $value, $fieldEnum);
                $changeContent = $before ? '【%s】由 %s 变为： %s；' . PHP_EOL : '【%s】更新为：%s%s；' . PHP_EOL;
                if ($before != $after && ! is_null($after) && is_string($before) && is_string($after)) {
                    $changeContents .= sprintf($changeContent, $fieldName, $before, $after);
                }
            }
        }

        $operationDescription = '【%s 了 %s】';
        $operationName = self::EVENT_MAPPING[$eventType] ?? '操作';
        $operationDescription = sprintf($operationDescription, $operationName, $objectName);
        $this->eventDescription = $operationDescription;
        return $changeContents;
    }

    /**
     * 获取类映射数组.
     * @param bool $rootOnly
     * @return array
     */
    public static function getClasses($rootOnly = true)
    {
        $config = config('operation_logger', []);
        $classes = [];
        foreach ($config as $class) {
            $classes[] = [
                'class_name' => $class['class_name'] ?? '',
                'short_tag' => $class['short_tag'] ?? '',
                'short_tag_en' => $class['short_tag_en'] ?? '',
                'tagged_field' => $class['tagged_field'] ?? '',
                'related_key' => $class['related_key'] ?? '',
            ];

            if (! $rootOnly) {
                foreach ($class['related_with'] ?? [] as $item) {
                    $classes[] = [
                        'class_name' => $item['class_name'] ?? '',
                        'short_tag' => $item['short_tag'] ?? '',
                        'short_tag_en' => $item['short_tag_en'] ?? '',
                        'tagged_field' => $item['tagged_field'] ?? '',
                        'related_key' => $item['related_key'] ?? '',
                    ];
                }
            }
        }
        return $classes;
    }

    /**
     * 获取key => value 形式的映射数据.
     * @return array
     */
    public static function getMapping(string $field1, string $field2, bool $rootOnly = true)
    {
        $classes = self::getClasses($rootOnly);
        $mapping = [];
        foreach ($classes as $class) {
            $mapping[$class[$field1] ?? ''] = $class[$field2] ?? '';
        }
        return $mapping;
    }

    /**
     * 有缓存地获取表结构详情.
     * @param string $fullTable
     */
    protected function getTableDetailWithCache($fullTable): Table
    {
        $cache = ApplicationContext::getContainer()->get(CacheInterface::class);
        if ($cache->has(self::CACHE_KEY . 'table_detail:' . $fullTable)) {
            return $cache->get(self::CACHE_KEY . 'table_detail:' . $fullTable);
        }
        $detail = $this->schemaManager->listTableDetails($fullTable);
        $cache->set(self::CACHE_KEY . 'table_detail:' . $fullTable, $detail, self::CACHE_TIME);
        return $detail;
    }

    /**
     * 初始化标识字段和获取短标记映射.
     * @return array
     */
    protected function getModelShortTagMapping()
    {
        $mapping = static::getMapping('class_name', 'short_tag', false);
        $this->taggedFieldMapping = static::getMapping('class_name', 'tagged_field');
        $this->relatedKeyMapping = static::getMapping('class_name', 'related_key');
        return $mapping;
    }

    /**
     * 获取数据库中的表名.
     * @return mixed
     */
    protected function getTableNameFromDbal(Table $tableDetail)
    {
        $operationObjectName = $tableDetail->getComment();
        $nameArr = explode(' ', $operationObjectName);
        return reset($nameArr) ?: null;
    }

    /**
     * 根据模型获取表结构信息.
     */
    protected function getTableDetailByModel(Model $model): Table
    {
        // 完整表名
        $fullTable = $model->getConnection()->getTablePrefix() . $model->getTable();
        return $this->getTableDetailWithCache($fullTable);
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
