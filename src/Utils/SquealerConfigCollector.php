<?php

namespace Huanhyperf\Squealer\Utils;

use Huanhyperf\Squealer\Annotation\SquealerConfig;
use Hyperf\Di\Annotation\AnnotationCollector;

class SquealerConfigCollector
{
    public static function config(): array
    {
        $list = AnnotationCollector::list();
        $classList = [];
        foreach ($list as $class => $item) {
            if (isset($item['_c'][SquealerConfig::class])) {
                $classList[] = $class;
            }
        }
        $config = [];
        foreach ($classList as $cls) {
            if ($conf = constant("{$cls}::MODEL_SQUEALER_CONFIG")) {
                $config[] = $conf;
            }
        }

        return $config;
    }
}