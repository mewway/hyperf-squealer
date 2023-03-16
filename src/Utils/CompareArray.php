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

class CompareArray
{
    /**
     * @param array $input
     *
     * @return array<int|string, mixed>
     */
    public static function flatten(array $input, string $separator = '.', ?string $path = null): array
    {
        $data = [];

        if ($path !== null) {
            $path .= $separator;
        } else {
            $path = '';
        }

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                foreach (self::flatten($value, $separator, $path . $key) as $newKey => $newValue) {
                    $data[$newKey] = $newValue;
                }
            } else {
                $data[$path . $key] = $value;
            }
        }

        return $data;
    }

    /**
     * 通过别名比较前后差异 提升人为可读性
     * @param array $old
     * @param array $new
     * @param array $alias
     * @return array|ComparedValue[]
     */
    public static function diffWithAlias(array $old, array $new, array $alias = []): array
    {
        $newDiff = [];
        $diff = self::flatten(self::diff($old, $new));
        foreach ($diff as $key => $value) {
            $newKey = preg_replace('/\.(\d+)/', '.*', $key);
            if (isset($alias[$newKey])) {
                $count1 = substr_count($newKey, '*');
                // 如果map中配置的星号和规则的星号一致 要按位解析其原意
                if ($count1 === substr_count($alias[$newKey], '*') && $count1 > 0) {
                    if (preg_match_all('/\.(\d+)/', $key, $matches)) {
                        $alias[$newKey] = preg_replace_callback('/\.(\*)/', function ($match) use (&$matches) {
                            $current = array_shift($matches[1]);
                            return '之' . ($current + 1);
                        }, $alias[$newKey]);
                        $alias[$newKey] = str_replace('.', '的', $alias[$newKey]);
                    }
                }
                $newDiff[$alias[$newKey]] = $value;
            } elseif (isset($alias[$key])) {
                $newDiff[$alias[$key]] = $value;
            }
        }
        return $newDiff;
    }
    /**
     * @param array $old
     * @param array $new
     *
     * @return array|ComparedValue[]
     */
    public static function diff(array $old, array $new): array
    {
        $diff = [];

        if ($old === $new) {
            return $diff;
        }

        foreach ($old as $key => $value) {
            if (! array_key_exists($key, $new)) {
                $diff[$key] = self::singular(ComparedValue::TYPE_REMOVED, $value);

                continue;
            }

            $valueNew = $new[$key];

            // Force values to be proportional arrays
            if (is_array($value) && ! is_array($valueNew)) {
                $valueNew = [$valueNew];
            }

            if (is_array($valueNew)) {
                if (! is_array($value)) {
                    $value = [$value];
                }

                $temp = self::diff($value, $valueNew);

                if (! empty($temp)) {
                    $diff[$key] = $temp;
                }

                continue;
            }

            if (is_float($value) && is_float($valueNew)
                && ! is_infinite($value) && ! is_infinite($valueNew)
                && ! is_nan($value) && ! is_nan($valueNew)) {
                $areValuesDifferent = abs($value - $valueNew) >= PHP_FLOAT_EPSILON;
            } else {
                $areValuesDifferent = $value !== $valueNew;
            }

            if ($areValuesDifferent) {
                $diff[$key] = new ComparedValue(ComparedValue::TYPE_MODIFIED, $value, $valueNew);
            }
        }

        foreach ($new as $key => $value) {
            if (! array_key_exists($key, $old)) {
                $diff[$key] = self::singular(ComparedValue::TYPE_ADDED, $value);
            }
        }

        return $diff;
    }

    /**
     * mixed[] return type because the array can be arbitrarily deep.
     *
     * @param mixed $value
     * @return ComparedValue|ComparedValue[]
     */
    private static function singular(string $type, $value)
    {
        if (is_array($value)) {
            $diff = [];

            foreach ($value as $key => $value2) {
                $diff[$key] = self::singular($type, $value2);
            }

            return $diff;
        }

        if ($type === ComparedValue::TYPE_REMOVED) {
            return new ComparedValue($type, $value, null);
        }

        return new ComparedValue($type, null, $value);
    }
}
