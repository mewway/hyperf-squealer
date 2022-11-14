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
            if (\is_array($value)) {
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
            if (! \array_key_exists($key, $new)) {
                $diff[$key] = self::singular(ComparedValue::TYPE_REMOVED, $value);

                continue;
            }

            $valueNew = $new[$key];

            // Force values to be proportional arrays
            if (\is_array($value) && ! \is_array($valueNew)) {
                $valueNew = [$valueNew];
            }

            if (\is_array($valueNew)) {
                if (! \is_array($value)) {
                    $value = [$value];
                }

                $temp = self::diff($value, $valueNew);

                if (! empty($temp)) {
                    $diff[$key] = $temp;
                }

                continue;
            }

            if (\is_float($value) && \is_float($valueNew)
                && ! \is_infinite($value) && ! \is_infinite($valueNew)
                && ! \is_nan($value) && ! \is_nan($valueNew)) {
                $areValuesDifferent = \abs($value - $valueNew) >= PHP_FLOAT_EPSILON;
            } else {
                $areValuesDifferent = $value !== $valueNew;
            }

            if ($areValuesDifferent) {
                $diff[$key] = new ComparedValue(ComparedValue::TYPE_MODIFIED, $value, $valueNew);
            }
        }

        foreach ($new as $key => $value) {
            if (! \array_key_exists($key, $old)) {
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
        if (\is_array($value)) {
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
