<?php

declare(strict_types=1);

namespace Huanhyperf\Squealer\Listener;

use Huanhyperf\Database\Model\Model;
use Huanhyperf\Squealer\Contract\SquealerInterface;
use Huanhyperf\Squealer\Handler\AutoRecordHandler;
use Hyperf\Database\Model\Events\Created;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\ForceDeleted;
use Hyperf\Database\Model\Events\Restored;
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
    const EVENT_MAP = [
        Created::class => 'created',
        Updated::class => 'updated',
        Deleted::class => 'deleted',
        ForceDeleted::class => 'deleted',
        Restored::class => 'restored',
    ];
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
        return array_keys(static::EVENT_MAP);
    }

    /**
     * @param Created|Deleted|ForceDeleted|object|Restored|Updated $event
     */
    public function process(object $event)
    {
        /**
         * @var Model|SquealerInterface $model
         */
        $model = $event->getModel();
        if (! $model instanceof SquealerInterface) {
            $this->logger->debug(get_class($model) . ' Didn\'t Implement SquealerInterface, Skipped');
            return;
        }

        make(AutoRecordHandler::class)->process($event, static::EVENT_MAP[get_class($event)]);
    }
}
