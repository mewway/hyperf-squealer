<?php

namespace Huanhyperf\Squealer\Contract;

interface StrategyInterface
{
    /**
     * 策略检测是否触发的方法
     * @param $current
     * @param $before
     * @return mixed
     */
    public function verify($current, $before);

    /**
     * 是否触发策略 true的时候触发trigger 否则不处理
     * @param $current
     * @param $before
     * @return bool
     */
    public function strategy($current, $before): bool;
    /**
     * 触发策略后的动作
     * @return mixed
     */
    public function trigger();
}