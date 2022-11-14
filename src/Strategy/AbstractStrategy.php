<?php

namespace Huanhyperf\Squealer\Strategy;

use Huanhyperf\Squealer\Contract\StrategyInterface;

abstract class AbstractStrategy implements StrategyInterface
{
    public function verify($current, $before)
    {
        if ($this->strategy($current, $before) === true) {
            $this->trigger();
        }
    }
}