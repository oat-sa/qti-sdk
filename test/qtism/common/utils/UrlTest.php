<?php

use qtism\common\utils\Url;

require_once(dirname(__FILE__) . '/../../../QtiSmTestCase.php');

class UrlTest extends QtiSmTestCase
{
    /**
     * @dataProvider validRelativeUrlProvider
     * @param string $url
     */
    public function testValidRelativeUrl($url)
    {
        $this->assertTrue(Url::isRelative($url));
    }

    /**
     * @dataProvider invalidRelativeUrlProvider
     * @param unknown_type $url
     */
    public function testInvalidRelativeUrl($url)
    {
        $this->assertFalse(Url::isRelative($url));
    }

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
