<?php

namespace qtismtest\common\utils;

use qtism\common\utils\Url;
use qtismtest\QtiSmTestCase;

/**
 * Class UrlTest
 */
class UrlTest extends QtiSmTestCase
{
    /**
     * @dataProvider validRelativeUrlProvider
     * @param string $url
     */
    public function testValidRelativeUrl($url)
    {
        $this::assertTrue(Url::isRelative($url));
    }

    /**
     * @dataProvider invalidRelativeUrlProvider
     * @param unknown_type $url
     */
    public function testInvalidRelativeUrl($url)
    {
        $this::assertFalse(Url::isRelative($url));
    }

    public function testTrim()
    {
        $this::assertEquals('hello', Url::trim("/hello/\n"));
    }

    public function testLtrim()
    {
        $this::assertEquals("hello/\n", Url::ltrim("/hello/\n"));
    }

    public function testRtrim()
    {
        $this::assertEquals('/hello', Url::rtrim("/hello/\n"));
    }

    /**
     * @return array
     */
    public function validRelativeUrlProvider()
    {
        return [
            ['./path'],
            ['path'],
            ['../my-path'],
            ['./my-path'],
            ['path/to/something'],
            ['path/to/something/'],
            ['path/./to/../something'],
        ];
    }

    /**
     * @return array
     */
    public function invalidRelativeUrlProvider()
    {
        return [
            ['/'],
            ['http://www.google.com'],
            ['my+cool://www.funk.org'],
            ['my.cool.way://crazy.peop.le/funkmusik'],
            ['/home/jerome/dev'],
            ['/home/../dev'],
            ['mailto:jerome@taotesting.com'],
            ['mail:to/my-friend/'],
        ];
    }
}
