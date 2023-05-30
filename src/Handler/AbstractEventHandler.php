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

use Doctrine\DBAL\Schema\Column;
use Huanhyperf\Squealer\Contract\SquealerInterface;
use Huanhyperf\Squealer\Traits\SquealerLoggerHelper;
use Huanhyperf\Squealer\Utils\CompareArray;
use Huanhyperf\Squealer\Utils\DatabaseValueParser;
use Huanhyperf\Squealer\Utils\FieldDbParserDatabase;
use Huanhyperf\Squealer\Utils\SquealerConfigCollector;
use Hyperf\Database\Model\Model;
use Huanhyperf\Squealer\LoggedEvent;
use Huanhyperf\Squealer\Traits\CommentParser;
use Hyperf\Database\Model\Events\Event;

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
        $ignoreList = array_merge(self::IGNORE_LIST, config('squealer.ignore_list', ['id', '_at']));

        $this->ignoreList = array_unique($ignoreList);
        $this->shortTagMapping = $this->getModelShortTagMapping();
    }

    /**
     * 事件处理方法.
     * @param string $eventType
     * @return mixed
     */
    abstract public function process(?Event $event, string $eventType);

    /**
     * 获取可记录的loggedEvent对象
     * @param string $clientIp
     */
    public function getLoggedEvent(Model $model, string $eventType = '', string $clientIp = ''): LoggedEvent
    {
        /**
         * @var Model|SquealerInterface $model
         */
        $loggedEvent = new LoggedEvent();
        $loggedEvent->setUserName($model->getCurrentUserInfo()->userName);
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
        /**
         * @var SquealerLoggerHelper|SquealerInterface|Model $model
         */
        $className = get_class($model);
        $original = $model->getOriginal();
        $changes = $model->getChanges();
        // 忽略清单
        $ignoreList = $this->ignoreList;
        // 软删除字段不计入更新
        if (defined("{$model}::DELETED_AT")) {
            $ignoreList[] = $model::DELETED_AT;
        }
        /**
         * 获取表结构信息
         * @var FieldDbParserDatabase $databaseFieldParser
         */
        $databaseFieldParser = make(FieldDbParserDatabase::class);
        $tableDetail = $databaseFieldParser->getTableDetailByModel($model);
        // 优先取短标记 没有取表名
        $objectName = $model->getTableAlias() ?? $databaseFieldParser->getTableAlias($model->getTable()) ?? $model->getTable();

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
                // 优先从类中取字段和枚举值定义 否则从数据库中取
                $fieldGroup = [$fieldName, $fieldEnum] = $model->parse($field);
                if (is_null($fieldGroup) || array_filter($fieldGroup) !== $fieldGroup) {
                    $comment = $column->getComment();
                    [$newFieldName, $newFieldEnums] = $this->parse($comment);
                    $fieldName ??= $newFieldName;
                    $fieldEnum ??= $newFieldEnums;
                }

                $value = DatabaseValueParser::format($value, $column);

                $before = $original[$field] ?? null;
                if (isset($original[$field]) && !is_array($original[$field])) {
                    $before = $model->mutateAttributes($field, $original[$field], $fieldEnum);
                }

                if (is_null($value)) {
                    $after = null;
                    $changeContent = $before ? '【%s】由 %s 变为： %s；' . PHP_EOL : '【%s】更新为：%s%s；' . PHP_EOL;
                } elseif (is_array($value)) {
                    $alias = defined("{$model}::MODEL_FIELD_MAPPING") ? ($model::MODEL_FIELD_MAPPING[$field] ?? []) : [];
                    $diff = CompareArray::diffWithAlias($before, $value, $alias);
                    $after = '';
                    foreach ($diff as $kName => $comparedValue) {
                        $after .= $comparedValue->toHumanReadable($kName) . PHP_EOL;
                    }
                    $before = '';
                    $after = $after ?: null;
                    $changeContent = '【%s】： %s%s；' . PHP_EOL;
                } else {
                    $after = $model->mutateAttributes($field, $value, $fieldEnum);
                    $changeContent = $before ? '【%s】由 %s 变为： %s；' . PHP_EOL : '【%s】更新为：%s%s；' . PHP_EOL;
                }
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
        $config = SquealerConfigCollector::config();
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
}
