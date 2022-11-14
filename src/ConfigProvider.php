<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Huanhyperf\Squealer;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'migration',
                    'description' => 'user operation logger datasheet migration',
                    'source' => __DIR__ . '/../migrations/user_operation_logger_table.php',
                    'destination' => BASE_PATH . '/migrations/autoload/' . date('Y_m_d_His') . '_create_operation_logger_table.php',
                ],
            ],
        ];
    }
}
