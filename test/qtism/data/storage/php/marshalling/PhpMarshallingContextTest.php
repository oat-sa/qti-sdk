<?php

use qtism\common\storage\BinaryStream;
use qtism\data\storage\php\marshalling\PhpMarshallingContext;
use qtism\data\storage\php\PhpStreamAccess;
use \RuntimeException;
use \InvalidArgumentException;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class PhpMarshallingContextTest extends QtiSmTestCase {
	
    /**
     * An open access to a PHP source code stream. 
     * 
     * @param PhpStreamAccess
     */
    private $streamAccess;
    
    protected function setStreamAccess(PhpStreamAccess $streamAccess) {
        $this->streamAccess = $streamAccess;
    }
    
    protected function getStreamAccess() {
        return $this->streamAccess;
    }
    
    public function setUp() {
        parent::setUp();
        
        $stream = new BinaryStream();
        $stream->open();
        $this->setStreamAccess(new PhpStreamAccess($stream));
    }
    
    public function tearDown() {
        parent::tearDown();
        
        $streamAccess = $this->getStreamAccess();
        unset($streamAccess);
    }
    
    public function testPhpMarshallingContext() {
        $ctx = new PhpMarshallingContext($this->getStreamAccess());
        $this->assertFalse($ctx->mustFormatOutput());
        
        $ctx->setFormatOutput(true);
        $this->assertTrue($ctx->mustFormatOutput());
        
        $ctx->pushOnVariableStack('foo');
        $this->assertEquals('foo', $ctx->popFromVariableStack());
        
        $ctx->pushOnVariableStack(array('foo', 'bar'));
        $this->assertEquals(array('bar', 'foo'), $ctx->popFromVariableStack(2));
        
        $this->assertInstanceOf('qtism\\data\\storage\\php\\PhpStreamAccess', $ctx->getStreamAccess());
    }
    
    public function testPhpMarshallingTooLargeQuantity() {
        $ctx = new PhpMarshallingContext($this->getStreamAccess());
        $ctx->pushOnVariableStack(array('foo', 'bar', '2000'));
        
        try {
            $values = $ctx->popFromVariableStack(4);
            $this->assertFalse(true, "An exception must be thrown because the requested quantity is too large.");
        }
        catch (RuntimeException $e) {
            $this->assertTrue(true);
        }
    }
    
    public function testPhpMarshallingEmptyStack() {
        $ctx = new PhpMarshallingContext($this->getStreamAccess());
        
        try {
            $value = $ctx->popFromVariableStack();
            $this->assertFalse(true, "An exception must be thrown because the variable names stack is empty.");
        }
        catch (RuntimeException $e) {
            $this->assertTrue(true);
        }
    }
    
    public function testWrongQuantity() {
        $ctx = new PhpMarshallingContext($this->getStreamAccess());
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