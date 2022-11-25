<?php

namespace Huanhyperf\Squealer\Traits;

use Hyperf\Utils\Str;

trait SquealerLoggerHelper
{
    protected $squealerConfig = [
        'class_name' => '',
        'short_tag' => '',
        'tagged_field' => '',
        'related_field' => '',
        'relation' => [],
    ];

    public function getClassName()
    {
        return $this->squealerConfig['class_name'];
    }

    public function getShortTag()
    {
        return $this->squealerConfig['short_tag'];
    }

    public function getTaggedField()
    {
        return $this->squealerConfig['tagged_field'];
    }

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
}