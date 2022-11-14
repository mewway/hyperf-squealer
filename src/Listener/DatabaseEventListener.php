<?php

declare(strict_types=1);

namespace Huanhyperf\Squealer\Listener;

use Huanhyperf\Squealer\Contract\SquealerInterface;
use Hyperf\Database\Model\Events\Created;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\ForceDeleted;
use Hyperf\Database\Model\Events\Restored;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Events\Updated;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @Listener
 */
#[Listener]
class DatabaseEventListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('squealer');
    }

    public function listen(): array
    {
        return [
            Created::class,
            Updated::class,
            Saved::class,
            Deleted::class,
            ForceDeleted::class,
            Restored::class,
        ];
    }

    /**
     * @param Created|Deleted|ForceDeleted|object|Restored|Saved|Updated $event
     */
    public function process(object $event)
    {
        $model = $event->getModel();
        if (! $model instanceof SquealerInterface) {
            $this->logger->debug(get_class($model) . ' Didn\'t Implement SquealerInterface, Skipped');
            return;
        }
        info([
            'event_name' => $event->getMethod(),
            'dirty' => $model->getDirty(),
            'before' => $model->getOriginal(),
            'after' => $model->getAttributes(),
        ]);
    }
}
