<?php

namespace Huanhyperf\Squealer\Utils;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQL57Platform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;

class DatabaseValueParser
{
    public static function format($value, Column $column, ?AbstractPlatform  $platform = null)
    {
        $platform ??= new MySQL57Platform();
        $type = $column->getType()->getName();
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
            case Types::TEXT:
                $value = $column->getType()->convertToDatabaseValue($value, $platform);
                break;
            case Types::JSON:
                $value = is_string($value) ? json_decode($value, true) : (array) $value;
                break;
            // 其他的暂时不处理
            default:
                $value = null;
                break;
        }

        return $value;
    }
}