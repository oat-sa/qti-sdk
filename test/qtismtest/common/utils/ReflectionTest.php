<?php
namespace qtismtest\common\utils;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\Integer;
use qtism\common\utils\Reflection;
use \ReflectionClass;

class ReflectionTest extends QtiSmTestCase {
	
    /**
     * @dataProvider shortClassNameProvider
     * @param mixed $expected
     * @param mixed $object
     */
    public function testShortClassName($expected, $object) {
        $this->assertSame($expected, Reflection::shortClassName($object));
    }
    
    public function testNewInstanceWithArguments() {
        $clazz = new ReflectionClass('\Exception');
        $args = array("A message", 12);
        $instance = Reflection::newInstance($clazz, $args);
        
        $this->assertInstanceOf('\\Exception', $instance);
        $this->assertEquals("A message", $instance->getMessage());
        $this->assertEquals(12, $instance->getCode());
    }
    
    public function testNewInstanceWithoutArguments() {
        $clazz = new ReflectionClass('\stdClass');
        $instance = Reflection::newInstance($clazz);
    
        $this->assertInstanceOf('\\stdClass', $instance);
    }
    
    public function shortClassNameProvider() {
        return array(
            array("SomeClass", "SomeClass"),
            array("Class", "My\\Class"),
            array("Class", "My\\Super\\Class"),
            array("Class", "\\My\\Super\\Class"),
                        
            array("stdClass", new \stdClass()),
            array("Integer", new Integer(10)),
                        
            array("My_Stupid_Class", "My_Stupid_Class"),
            array(false, 12),
            array(false, null),
            array(false, "\\"),       
        );
    }
}