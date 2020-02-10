<?php

namespace qtismtest\runtime\rendering\css;

use qtism\runtime\rendering\css\CssScoper;
use qtismtest\QtiSmTestCase;

class CssScoperTest extends QtiSmTestCase
{
    /**
     * @dataProvider testOutputProvider
     *
     * @param string $inputFile
     * @param string $outputFile
     * @param string $id
     */
    public function testOutput($inputFile, $outputFile, $id, $cssMapping = false)
    {
        $cssScoper = new CssScoper($cssMapping);
        $expected = file_get_contents($outputFile);
        $actual = $cssScoper->render($inputFile, $id);
        $this->assertEquals($expected, $actual);
    }

    public function testOutputProvider()
    {
        return [
            [self::samplesDir() . 'rendering/css/css_input1.css', self::samplesDir() . 'rendering/css/css_output1.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input2.css', self::samplesDir() . 'rendering/css/css_output2.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input3.css', self::samplesDir() . 'rendering/css/css_output3.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input4.css', self::samplesDir() . 'rendering/css/css_output4.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input5.css', self::samplesDir() . 'rendering/css/css_output5.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input6.css', self::samplesDir() . 'rendering/css/css_output6.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input7.css', self::samplesDir() . 'rendering/css/css_output7.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input8.css', self::samplesDir() . 'rendering/css/css_output8.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input9.css', self::samplesDir() . 'rendering/css/css_output9.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input10.css', self::samplesDir() . 'rendering/css/css_output10.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input11.css', self::samplesDir() . 'rendering/css/css_output11.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input12.css', self::samplesDir() . 'rendering/css/css_output12.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input13.css', self::samplesDir() . 'rendering/css/css_output13.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input14.css', self::samplesDir() . 'rendering/css/css_output14.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input15.css', self::samplesDir() . 'rendering/css/css_output15.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input16.css', self::samplesDir() . 'rendering/css/css_output16.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input17.css', self::samplesDir() . 'rendering/css/css_output17.css', 'myId', true],
            [self::samplesDir() . 'rendering/css/css_input18.css', self::samplesDir() . 'rendering/css/css_output18.css', 'myId', true],
        ];
    }
}
