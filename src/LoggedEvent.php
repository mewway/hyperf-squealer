<?php
declare(strict_types=1);
namespace Huanhyperf\Squealer;

use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Str;

/**
 * Class LoggedEvent.
 * @method void setTriggerClass(string $value)
 * @method void setTriggerTime(string $value)
 * @method void setUserName(string $value)
 * @method void setAssociatedId(string $value)
 * @method void setAssociatedValue(string $value)
 * @method void setClientIp(string $value)
 * @method void setChangeContent(string $value)
 * @method void setEventDesc(string $value)
 * @method string getTriggerClass()
 * @method string getTriggerTime()
 * @method string getUserName()
 * @method string getAssociatedId()
 * @method string getAssociatedValue()
 * @method string getClientIp()
 * @method string getChangeContent()
 * @method string getEventDesc()
 */
class LoggedEvent implements Arrayable
{
    protected $attributes = [];

    public function __call($name, $args)
    {
        $paramName = Str::snake(substr($name, 3));
        if (strpos($name, 'get') === 0) {
            return $this->attributes[$paramName] ?? '';
        }
        if (strpos($name, 'set') === 0) {
            $this->attributes[$paramName] = $args[0] ?? '';
            return null;
        }
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
