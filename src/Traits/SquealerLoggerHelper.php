<?php

namespace Huanhyperf\Squealer\Traits;

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
}