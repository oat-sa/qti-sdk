<?php

use qtism\data\storage\php\marshalling\PhpMarshallingContext;
use qtism\common\storage\BinaryStream;
use qtism\data\storage\php\PhpStreamAccess;

require_once(dirname(__FILE__) . '/../qtism/qtism.php');
require_once(dirname(__FILE__) . '/QtiSmTestCase.php');

abstract class QtiSmPhpMarshallerTestCase extends QtiSmTestCase {
    
    /**
     * An access to an open PHP source code stream.
     * 
     * @var PhpStreamAccess
     */
    private $streamAccess;
    
    /**
     * A stream
     * 
     * @var BinaryStream
     */
    private $stream;
    
	public function setUp() {
	    parent::setUp();
	    
	    $stream = new BinaryStream();
	    $stream->open();
	    $this->setStream($stream);
	    $this->setStreamAccess(new PhpStreamAccess($this->getStream()));
	}
	
	public function tearDown() {
	    parent::tearDown();
	    
	    $streamAccess = $this->getStreamAccess();
	    unset($streamAccess);
	    
	    $stream = $this->getStream();
	    unset($stream);
	}
	
	public function createMarshallingContext() {
	    $ctx = new PhpMarshallingContext($this->getStreamAccess());
	    $ctx->setFormatOutput(true);
	    return $ctx;
	}
	
	protected function setStream(BinaryStream $stream) {
	    $this->stream = $stream;
	}
	
	/**
	 * 
	 * @return BinaryStream
	 */
	protected function getStream() {
	    return $this->stream;
	}
	
	/**
	 * 
	 * @return PhpStreamAccess
	 */
	protected function getStreamAccess() {
	    return $this->streamAccess;
	}
	
	protected function setStreamAccess(PhpStreamAccess $streamAccess) {
	    $this->streamAccess = $streamAccess;
	}
}