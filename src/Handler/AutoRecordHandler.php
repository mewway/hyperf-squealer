<?php

namespace Huanhyperf\Squealer\Handler;

use Hyperf\Context\Context;
use Hyperf\Database\Model\Events\Event;
use Hyperf\HttpServer\Contract\RequestInterface;

class AutoRecordHandler extends AbstractEventHandler
{

    public function process(?Event $event, string $eventType)
    {
        $req = Context::get(RequestInterface::class);
        $ip  = $req ? $req->header('x-forwarded-for', '') : '127.0.0.1';
        $loggedEvent = $this->getLoggedEvent($event->getModel(), $eventType, $ip);
        var_dump($loggedEvent);
    }
}