<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Huanhyperf\Squealer\Traits;

trait CommentParser
{
    protected $column;

    protected $enumeration = [];

    protected $fieldRegex = '/^(?<name>\w+)\s*[:：]?\s*\s*/u';

    protected $enumRegex = '/(?<key>\w+)\s*[:：-]?\s*(?<value>\w+)\s*[;,；，]?\s*/u';

    public function parse(string $comment): array
    {
        $comment = preg_replace_callback($this->fieldRegex, function ($match) use (&$fieldName) {
            $fieldName = $match['name'] ?? '';
            return '';
        }, $comment);
        $column = $fieldName;
        preg_match_all($this->enumRegex, $comment, $match);
        $enumeration = $this->parseEnumeration($match);
        return [$column, $enumeration];
    }

    private function parseEnumeration(array $match): array
    {
        $keys = $match['key'] ?? [];

        $values = $match['value'] ?? [];
        return array_combine($keys, $values);
    }
}
