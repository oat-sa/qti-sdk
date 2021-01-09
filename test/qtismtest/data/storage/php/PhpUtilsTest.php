<?php

namespace qtismtest\data\storage\php;

use qtism\data\storage\php\Utils as PhpUtils;
use qtismtest\QtiSmTestCase;

/**
 * Class PhpUtilsTest
 */
class PhpUtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider doubleQuotedPhpStringDataProvider
     * @param string $input
     * @param string $expected
     */
    public function testDoubleQuotedPhpString($input, $expected)
    {
        $this::assertEquals($expected, PhpUtils::doubleQuotedPhpString($input));
    }

    /**
     * @return array
     */
    public function doubleQuotedPhpStringDataProvider()
    {
        return [
            ['', '""'],
            ['"', "\"\\\"\""],
            ['""', "\"\\\"\\\"\""],
            ["\n", "\"\\n\""],
            ["\r\n", "\"\\r\\n\""],
            ['Hello World!', '"Hello World!"'],
            ['中国是伟大的', '"中国是伟大的"'], // chinese is great
            ['/[a-z]+/ui', '"/[a-z]+/ui"'],
            ["\\nhello\\$", "\"\\\\nhello\\\\\\$\""],
            ['中国是伟$大的', "\"中国是伟\\\$大的\""],
        ];
    }
}
