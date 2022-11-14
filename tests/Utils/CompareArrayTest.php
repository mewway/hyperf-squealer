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
namespace HyperfTest\Utils;

use Huanhyperf\Squealer\Utils\CompareArray;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CompareArrayTest extends TestCase
{
    public function testFlatten()
    {
        $arr = [
            'a' => [
                'b' => [
                    'c' => [4, 5, 6],
                ],
            ],
            'd' => [1, 2, 3],
        ];
        $resp = CompareArray::flatten($arr);
        $this->assertIsArray($resp);
        print_r($resp);
    }

    public function testDiff()
    {
        $str1 = <<<STR1
{"specs":[{"code":"color","extra":{"color":1,"status":"normal","values":56,"required":false,"allow_alias":null},"name":"颜色分类","prop_id":1627207,"type":"color","values":[{"name":"米色","value":"米色","checked":true,"isCustom":true,"old_value":"米色","error":{"name":[],"remark":[],"alias":[]}}]},{"code":"size","extra":{"size":1,"status":"normal","values":38,"required":false,"allow_alias":null},"group":"中国码","name":"尺码","prop_id":20509,"type":"size","values":[{"name":"S","value":"28314","checked":true,"isCustom":true,"old_value":"28314","error":{"name":[],"remark":[],"alias":[]}},{"name":"M","value":"28315","checked":true,"isCustom":true,"old_value":"28315","error":{"name":[],"remark":[],"alias":[]}},{"name":"L","value":"28316","checked":true,"isCustom":true,"old_value":"28316","error":{"name":[],"remark":[],"alias":[]}}]}],"price":"1799.00","price_range":[1799,1799],"description_image":"https:\/\/assets.alicdn.com\/kissy\/1.0.0\/build\/imglazyload\/spaceball.gif"}
STR1;
        $str2 = <<<STR1
{"specs":[{"code":"color","extra":{"color":1,"status":"normal","values":56,"required":false,"allow_alias":null},"name":"颜色分类","prop_id":1627207,"type":"color","values":[{"name":"米色","value":"米色","checked":true,"isCustom":true,"old_value":"米色","error":{"name":[],"remark":[],"alias":[]},"remark":"米色色"},{"name":"羞色","value":"羞色095bd58b84c61","checked":true,"remark":"黄色","isCreate":true,"error":{"name":[],"remark":[],"alias":[]}}]},{"code":"size","extra":{"size":1,"status":"normal","values":38,"required":false,"allow_alias":null},"group":"中国码","name":"尺码","prop_id":20509,"type":"size","values":[{"name":"S","value":"28314","checked":true,"isCustom":true,"old_value":"28314","error":{"name":[],"remark":[],"alias":[]},"remark":"SL"},{"name":"M","value":"28315","checked":true,"isCustom":true,"old_value":"28315","error":{"name":[],"remark":[],"alias":[]},"remark":"ML"},{"name":"L","value":"28316","checked":true,"isCustom":true,"old_value":"28316","error":{"name":[],"remark":[],"alias":[]},"remark":"LL"}]}],"price":"1799.00","price_range":["",1799],"description_image":"https:\/\/assets.alicdn.com\/kissy\/1.0.0\/build\/imglazyload\/spaceball.gif"}
STR1;
        $arr1 = json_decode($str1, true);
        $arr2 = json_decode($str2, true);
        $diff = CompareArray::diff($arr1, $arr2);
        $this->assertIsArray($diff);
        $resp = CompareArray::flatten($diff);
        print_r($resp);
    }
}
