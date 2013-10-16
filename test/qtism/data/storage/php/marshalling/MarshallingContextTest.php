<?php

use qtism\data\storage\php\marshalling\MarshallingContext;
use \RuntimeException;
use \InvalidArgumentException;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class MarshallingContextTest extends QtiSmTestCase {
	
    public function testMarshallingContext() {
        $ctx = new MarshallingContext();
        $this->assertFalse($ctx->mustFormatOutput());
        
        $ctx->setFormatOutput(true);
        $this->assertTrue($ctx->mustFormatOutput());
        
        $ctx->pushOnVariableStack('foo');
        $this->assertEquals('foo', $ctx->popFromVariableStack());
        
        $ctx->pushOnVariableStack(array('foo', 'bar'));
        $this->assertEquals(array('bar', 'foo'), $ctx->popFromVariableStack(2));
    }
    
    public function testMarshallingTooLargeQuantity() {
        $ctx = new MarshallingContext();
        $ctx->pushOnVariableStack(array('foo', 'bar', '2000'));
        
        try {
            $values = $ctx->popFromVariableStack(4);
            $this->assertFalse(true, "An exception must be thrown because the requested quantity is too large.");
        }
        catch (RuntimeException $e) {
            $this->assertTrue(true);
        }
    }
    
    public function testMarshallingEmptyStack() {
        $ctx = new MarshallingContext();
        
        try {
            $value = $ctx->popFromVariableStack();
            $this->assertFalse(true, "An exception must be thrown because the variable names stack is empty.");
        }
        catch (RuntimeException $e) {
            $this->assertTrue(true);
        }
    }
    
    public function testWrongQuantity() {
        $ctx = new MarshallingContext();
        $ctx->pushOnVariableStack('foo');
        
        try {
            $value = $ctx->popFromVariableStack(0);
            $this->assertTrue(false, "An exception must be thrown because the 'quantity' argument must be >= 1");
        }
        catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
}