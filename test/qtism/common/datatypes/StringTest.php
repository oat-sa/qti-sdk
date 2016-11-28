<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;

class StringTest extends QtiSmTestCase {
    
    public function testWrongValue() {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            'The String Datatype only accepts to store string values.'
        );
        $string = new QtiString(1337);
    }
    
    public function testEmptyString() {
        $string = new QtiString('');
        $this->assertEquals('', $string->getValue());
    }
    
    /**
     * @dataProvider equalProvider
     * 
     * @param string $str
     * @param mixed $val
     */
    public function testEqual($str, $val) {
        $qtiString = new QtiString($str);
        $this->assertTrue($qtiString->equals($val));
    }
    
    public function equalProvider() {
        return array(
            array('', null),
            array('', ''),
            array('', new QtiString('')),
            array('test', 'test'),
            array('test', new QtiString('test'))
        );
    }
    
    /**
     * @dataProvider notEqualProvider
     * 
     * @param string $str
     * @param mixed $val
     */
    public function testNotEqual($str, $val) {
        $qtiString = new QtiString($str);
        $this->assertFalse($qtiString->equals($val));
    }
    
    public function notEqualProvider() {
        return array(
            array('test', null),
            array('', 'test'),
            array('', new QtiString('test')),
            array('test', ''),
            array('test', new QtiString('')),
            array('Test', 'test'),
            array('Test', new QtiString('test'))
        );
    }
}
