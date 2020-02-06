<?php

use qtism\runtime\rendering\css\Utils as CssUtils;

require_once(dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class CssUtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider mapSelectorProvider
     *
     * @param string $expected
     * @param string $selector
     * @param array $map
     */
    public function testMapSelector($selector, $expected, $map)
    {
        $this->assertEquals($expected, CssUtils::mapSelector($selector, $map));
    }

    public function mapSelectorProvider()
    {
        $map = [
            'div' => 'qtism-div',
            'prompt' => 'qtism-prompt',
            'a' => 'qtism-a',
            'b' => 'qtism-b',
        ];

        return [
            ['div', '.qtism-div', $map],
            ['prompt', '.qtism-prompt', $map],
            ['div prompt', '.qtism-div .qtism-prompt', $map],
            ['div > prompt', '.qtism-div > .qtism-prompt', $map],
            ['div>prompt', '.qtism-div>.qtism-prompt', $map],
            ['div div .a', '.qtism-div .qtism-div .a', $map],
            ['div >a', '.qtism-div >.qtism-a', $map],
            ['div.div', '.qtism-div.div', $map],
            ['.div~div', '.div~.qtism-div', $map],
            [
                'div > .cool +div + division + * .div,division + qti-div + div.golgoth div .hello.div~div div+div divdiv div>div',
                '.qtism-div > .cool +.qtism-div + division + * .div,division + qti-div + .qtism-div.golgoth .qtism-div .hello.div~.qtism-div .qtism-div+.qtism-div divdiv .qtism-div>.qtism-div',
                $map,
            ],
            ['a:hover', '.qtism-a:hover', $map],
            ['a:hover>a:hover', '.qtism-a:hover>.qtism-a:hover', $map],
            ['a[target=_blank]', '.qtism-a[target=_blank]', $map],
            ['prompt > b', '.qtism-prompt > .qtism-b', $map],
        ];
    }
}
