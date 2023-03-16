<?php

namespace Huanhyperf\Squealer\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
#[[Attribute(Attribute::TARGET_CLASS)]
class SquealerConfig extends AbstractAnnotation
{
    public function getSquealerConfig(): array
    {
        if ($conf = constant('static::' . 'MODEL_SQUEALER_CONFIG')) {
            return $conf;
        }
        return [];
    }
}