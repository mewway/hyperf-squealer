<?php

namespace Huanhyperf\Squealer\Handler;

use Huanhyperf\Squealer\Contract\SquealerInterface;
use Huanhyperf\Squealer\LoggedEvent;
use Hyperf\Context\Context;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Model;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @method static updated(Model $modelName, string $desc);
 * @method static created(Model $modelName, string $desc);
 * @method static deleted(Model $modelName, string $desc);
 */
class ManualRecordHandler extends AbstractEventHandler
{
    public function process(?Event $event, string $eventType)
    {
        return null;
    }

    public static function __callStatic($name, $arguments):?LoggedEvent
    {
        if (!in_array($name, array_keys(self::EVENT_MAPPING))) {
            return null;
        }
        [$model, $desc] = $arguments;
        $req = Context::get(RequestInterface::class);
        $ip  = $req ? $req->header('x-forwarded-for', '') : '127.0.0.1';
        /**
         * @var self $instance
         */
        $instance = make(static::class);
        return $instance->handleManually($name, $model, $desc, $ip);
    }

    public function handleManually(string $eventType, Model $model, string $desc, string $clientIp = '0.0.0.0')
    {
        $this->getModelShortTagMapping();
        /**
         * @var Model|SquealerInterface $model
         */
        $loggedEvent = new LoggedEvent();
        $loggedEvent->setUserName($model->getCurrentUserInfo()->userName);
        $loggedEvent->setTriggerTime(date('Y-m-d H:i:s'));
        $loggedEvent->setClientIp($clientIp);
        $loggedEvent->setChangeContent('');
        $loggedEvent->setEventDesc($desc);
        $className = get_class($model);
        $loggedEvent->setTriggerClass($className);
        $loggedEvent->setAssociatedId((string) $model->getAttribute('id'));
        $relationKey = $this->relatedKeyMapping[$className] ?? '';
        $loggedEvent->setAssociatedValue((string) $model->getAttribute($relationKey));
        return $loggedEvent;
    }
}