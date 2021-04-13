<?php

namespace qtismtest\runtime\rendering\css;

use qtism\common\storage\MemoryStreamException;
use qtism\runtime\rendering\css\CssScoper;
use qtismtest\QtiSmTestCase;
use qtism\runtime\rendering\RenderingException;

/**
 * Class CssScoperTest
 */
class CssScoperTest extends QtiSmTestCase
{
    /**
     * @dataProvider outputProvider
     *
     * @param string $inputFile
     * @param string $outputFile
     * @param string $id
     * @param bool $cssMapping
     * @param bool $pseudoClassMapping
     * @param bool $wcFriendly
     * @throws RenderingException
     * @throws MemoryStreamException
     */
    public function testOutput($inputFile, $outputFile, $id, $cssMapping = false, $pseudoClassMapping = true, $wcFriendly = false)
    {
        $cssScoper = new CssScoper($cssMapping, $pseudoClassMapping);
        $cssScoper->setWebComponentFriendly($wcFriendly);
        $expected = file_get_contents($outputFile);
        $actual = $cssScoper->render($inputFile, $id);
        $this::assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function outputProvider()
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
            [self::samplesDir() . 'rendering/css/css_input19.css', self::samplesDir() . 'rendering/css/css_output19.css', 'myId', true],
            [self::samplesDir() . 'rendering/css/css_input20.css', self::samplesDir() . 'rendering/css/css_output20.css', 'myId'],
            [self::samplesDir() . 'rendering/css/css_input21.css', self::samplesDir() . 'rendering/css/css_output21.css', 'myId', true, false, true],
        ];
    }

    public function testOutputIdGenerated()
    {
        $cssScoper = new CssScoper();
        $actual = $cssScoper->render(self::samplesDir() . 'rendering/css/css_input1.css');
        $pattern = "/^@CHARSET \"UTF-8\";\n\n#[0-9a-z]+ \\.myClass {\n    border:1px solid #fff;\n    background-color: white;\n}/u";
        $this::assertSame(1, preg_match($pattern, $actual));
    }

    public function testOutputUnknownFile()
    {
        $cssScoper = new CssScoper();

        $this->expectException(RenderingException::class);
        $this->expectExceptionMessage("The CSS file '/root/css_input1.css' could not be open.");

        $cssScoper->render('/root/css_input1.css');
    }
}
