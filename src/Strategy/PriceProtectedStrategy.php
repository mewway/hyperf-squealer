<?php

namespace Huanhyperf\Squealer\Strategy;

/**
 * 价格保护策略 当监听价格变化符合策略保护机制的时候触发
 */
class PriceProtectedStrategy extends AbstractStrategy
{
    /**
     * 最小可定义价格
     * @var int|float
     */
    protected $minPrice = 1;

    /**
     * 最大允许降价幅度
     * @var int
     */
    protected $maxReductionRatio = 50;

    public function __construct($minPrice = 1, $maxReductionRatio = 50)
    {
        $this->minPrice = $minPrice;
        $this->maxReductionRatio = $maxReductionRatio;
    }

    /**
     * @param $current
     * @param $before
     * @return bool
     */
    public function strategy($current, $before = null): bool
    {
        // 当前值不是数值， 错误字段传入 不处理
        if (!is_numeric($current)) {
            return false;
        }
        // 格式化价格为浮点数 精度三位小小数
        $current = round($current, 3);

        $ratio = 0;
        // 初始化新增的时候 不去对比变化策略
        if (!is_null($before)) {
            $before = round($before, 3);
            if($current < $before && !empty($before)) {
                $ratio = abs(($current - $before) / $before);
            }
        }
        // 价格小于最低限制 或 变化幅度超过最大降价比例
        return $current <= $this->minPrice || $ratio >= $this->maxReductionRatio / 100;
    }

    public function trigger()
    {

    }
}