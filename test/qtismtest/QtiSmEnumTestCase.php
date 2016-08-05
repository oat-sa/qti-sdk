<?php
namespace qtismtest;

use qtismtest\QtiSmTestCase;

abstract class QtiSmEnumTestCase extends QtiSmTestCase 
{
    
	public function setUp() 
    {
	    parent::setUp();
	}
	
	public function tearDown() 
    {
	    parent::tearDown();
	}
    
    public function testConsistency()
    {
        $refCount = count($this->getNames());
        
        $this->assertEquals($refCount, count($this->getConstants()));
        $this->assertEquals($refCount, count($this->getKeys()));
    }
    
    public function testAsArray() 
    {
        $array = call_user_func(
            array(
                $this->getEnumerationFqcn(),
                'asArray'
            )
        );
        
        $keys = $this->getKeys();
        $constants = $this->getConstants();
        
        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            $this->assertTrue(isset($array[$key]));
            $this->assertEquals($constants[$i], $array[$key]);
        }
    }
    
    public function testGetConstantByName()
    {
        $names = $this->getNames();
        $constants = $this->getConstants();
        
        for ($i = 0; $i < count($names); $i++) {
            $name = $names[$i];
            $this->assertEquals(
                $constants[$i],
                call_user_func(
                    array(
                        $this->getEnumerationFqcn(),
                        'getConstantByName'
                    ),
                    $name
                )
            );
        }
        
        $this->assertFalse(
            call_user_func(
                array(
                    $this->getEnumerationFqcn(),
                    'getConstantByName'
                ),
                $this->getUnknownConstantName()
            )
        );
    }
    
    public function testGetNameByConstant()
    {
        $names = $this->getNames();
        $constants = $this->getConstants();
        
        for ($i = 0; $i < count($constants); $i++) {
            $constant = $constants[$i];
            $this->assertEquals(
                $names[$i],
                call_user_func(
                    array(
                        $this->getEnumerationFqcn(),
                        'getNameByConstant'
                    ),
                    $constant
                )
            );
        }
        
        $this->assertFalse(
            call_user_func(
                array(
                    $this->getEnumerationFqcn(),
                    'getNameByConstant'
                ),
                $this->getUnknownConstantValue()
            )
        );
    }
    
    protected function getUnknownConstantName()
    {
        return 'xyz';
    }
    
    protected function getUnknownConstantValue()
    {
        return PHP_INT_MAX;
    }
    
    abstract protected function getNames();
    
    abstract protected function getKeys();
    
    abstract protected function getConstants();
    
    abstract protected function getEnumerationFqcn();
}
