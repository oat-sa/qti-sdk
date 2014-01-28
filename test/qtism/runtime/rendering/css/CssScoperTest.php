<?php
use qtism\runtime\rendering\css\CssScoper;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class CssScoperTest extends QtiSmTestCase {
    
    /**
     * @dataProvider testOutputProvider
     * 
     * @param string $inputFile
     * @param string $outputFile
     * @param string $id
     */
    public function testOutput ($inputFile, $outputFile, $id) {
        $cssScoper = new CssScoper();
        $expected = file_get_contents($outputFile);
        $actual = $cssScoper->render($inputFile, $id);
        $this->assertEquals($expected, $actual);
    }
    
    public function testOutputProvider() {
        return array(
            array(self::samplesDir() . 'rendering/css/css_input1.css', self::samplesDir() . 'rendering/css/css_output1.css', 'myId'),
            array(self::samplesDir() . 'rendering/css/css_input2.css', self::samplesDir() . 'rendering/css/css_output2.css', 'myId'),
            array(self::samplesDir() . 'rendering/css/css_input3.css', self::samplesDir() . 'rendering/css/css_output3.css', 'myId'),
            array(self::samplesDir() . 'rendering/css/css_input4.css', self::samplesDir() . 'rendering/css/css_output4.css', 'myId'),
            array(self::samplesDir() . 'rendering/css/css_input5.css', self::samplesDir() . 'rendering/css/css_output5.css', 'myId'),
            array(self::samplesDir() . 'rendering/css/css_input6.css', self::samplesDir() . 'rendering/css/css_output6.css', 'myId'),
        );
    }
}