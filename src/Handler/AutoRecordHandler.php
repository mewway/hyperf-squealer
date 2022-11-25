<?php

namespace Huanhyperf\Squealer\Handler;

use Hyperf\Database\Model\Events\Event;

class AutoRecordHandler extends AbstractEventHandler
{

    public function process(?Event $event)
    {
        $loggedEvent = $this->getLoggedEvent($event->getModel(), '修改', '127.0.0.1');
        error($loggedEvent->toArray(), __METHOD__);
    }
}