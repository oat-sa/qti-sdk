<?php
require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\storage\binary\BinaryStream;
use qtism\runtime\storage\binary\BinaryStreamReader;
use qtism\runtime\storage\binary\BinaryStreamReaderException;
use qtism\runtime\storage\common\StreamReaderException;

class BinaryStreamReaderTest extends QtiSmTestCase {
	
    private $emptyStream;
    
    public function setUp() {
        parent::setUp();
        
        $this->emptyStream = new BinaryStream();
        $this->emptyStream->open();
    }
    
    public function tearDown() {
        parent::tearDown();
        
        unset($this->emptyStream);
    }
    
    /**
     * Get an open empty stream
     * 
     * @return BinaryStream
     */
    public function getEmptyStream() {
        return $this->emptyStream;
    }
    
    public function testReadTinyInt() {
        $stream = new BinaryStream("\x00\x01\x0A");
        $stream->open();
        
        $reader = new BinaryStreamReader($stream);
        $tinyInt = $reader->readTinyInt();
        $this->assertInternalType('integer', $tinyInt);
        $this->assertEquals(0, $tinyInt);
        
        $tinyInt = $reader->readTinyInt();
        $this->assertInternalType('integer', $tinyInt);
        $this->assertEquals(1, $tinyInt);
        
        $tinyInt = $reader->readTinyInt();
        $this->assertInternalType('integer', $tinyInt);
        $this->assertEquals(10, $tinyInt);
        
        try {
            // EOF reached.
            $tinyInt = $reader->readTinyInt();
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::TINYINT, $e->getCode());
        }
        
        $stream = $this->getEmptyStream();
        try {
            $reader = new BinaryStreamReader($stream);
            $stream->close();
            $reader->readTinyInt();
            $this->assertTrue(false);
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::NOT_OPEN, $e->getCode());
        }
    }
    
    public function testReadShort() {
        $stream = new BinaryStream(pack('S', 0) . pack('S', 1) . pack ('S', 65535));
        $stream->open();
        $reader = new BinaryStreamReader($stream);
        
        $short = $reader->readShort();
        $this->assertInternalType('integer', $short);
        $this->assertEquals(0, $short);
        
        $short = $reader->readShort();
        $this->assertInternalType('integer', $short);
        $this->assertEquals(1, $short);
        
        $short = $reader->readShort();
        $this->assertInternalType('integer', $short);
        $this->assertEquals(65535, $short);
        
        // go beyond EOF.
        try {
            $short = $reader->readShort();
            $this->assertTrue(false);
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::SHORT, $e->getCode());
        }
        
        // try to read on a closed stream.
        try {
            $stream = $this->getEmptyStream();
            $stream->close();
            $reader = new BinaryStreamReader($stream);
            $short = $reader->readShort();
            $this->assertTrue(false);
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::NOT_OPEN, $e->getCode());
        }
    }
    
    public function testReadInt() {
        $stream = new BinaryStream(pack('l', 0) . pack('l', 1) . pack('l', -1) . pack('l', 2147483647) . pack('l', -2147483648));
        $stream->open();
        $reader = new BinaryStreamReader($stream);
        
        $int = $reader->readInt();
        $this->assertInternalType('integer', $int);
        $this->assertEquals(0, $int);
        
        $int = $reader->readInt();
        $this->assertInternalType('integer', $int);
        $this->assertEquals(1, $int);
        
        $int = $reader->readInt();
        $this->assertInternalType('integer', $int);
        $this->assertEquals(-1, $int);
        
        $int = $reader->readInt();
        $this->assertInternalType('integer', $int);
        $this->assertEquals(2147483647, $int);
        
        $int = $reader->readInt();
        $this->assertInternalType('integer', $int);
        $this->assertEquals(-2147483648, $int);
        
        // reach EOF.
        try {
            $int = $reader->readInt();
            $this->assertTrue(false);
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::INT, $e->getCode());
        }
        
        // close the stream and read.
        try {
            $stream = $this->getEmptyStream();
            $reader = new BinaryStreamReader($stream);
            $stream->close();
            $int = $reader->readInt();
            $this->assertTrue(false);
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::NOT_OPEN, $e->getCode());
        }
    }
    
    public function testReadBool() {
        $stream = new BinaryStream("\x00\x01");
        $stream->open();
        $reader = new BinaryStreamReader($stream);
        
        $bool = $reader->readBool();
        $this->assertInternalType('boolean', $bool);
        $this->assertFalse($bool);
        
        $bool = $reader->readBool();
        $this->assertInternalType('boolean', $bool);
        $this->assertTrue($bool);
        
        try {
            $bool = $reader->readBool();
            $this->assertTrue(false);
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::BOOLEAN, $e->getCode());
        }
        
        try {
            $stream = $this->getEmptyStream();
            $reader = new BinaryStreamReader($stream);
            $stream->close();
            $bool = $reader->readBool();
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::NOT_OPEN, $e->getCode());
        }
    }
    
    public function testReadString() {
        $stream = new BinaryStream(pack('S', 0) . '' . pack('S', 1) . 'A' . pack ('S', 6) . 'binary');
        $stream->open();
        $reader = new BinaryStreamReader($stream);
        
        $string = $reader->readString();
        $this->assertInternalType('string', $string);
        $this->assertEquals('', $string);
        
        $string = $reader->readString();
        $this->assertInternalType('string', $string);
        $this->assertEquals('A', $string);
        
        $string = $reader->readString();
        $this->assertInternalType('string', $string);
        $this->assertEquals('binary', $string);
        
        try {
            $string = $reader->readString();
            $this->assertTrue(false);
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::STRING, $e->getCode());
        }
        
        try {
            $stream = $this->getEmptyStream();
            $reader = new BinaryStreamReader($stream);
            $stream->close();
            $string = $reader->readString();
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::NOT_OPEN, $e->getCode());
        }
    }
    
    public function testReadFloat() {
        $stream = new BinaryStream(pack('d', 0.0) . pack('d', -M_PI) . pack('d', M_2_PI));
        $stream->open();
        $reader = new BinaryStreamReader($stream);
        
        $float = $reader->readFloat();
        $this->assertInternalType('float', $float);
        $this->assertEquals(round(0.0, 3), round($float, 3));
        
        $float = $reader->readFloat();
        $this->assertInternalType('float', $float);
        $this->assertEquals(round(-M_PI, 3), round($float, 3));
        
        $float = $reader->readFloat();
        $this->assertInternalType('float', $float);
        $this->assertEquals(round(M_2_PI, 3), round($float, 3));
        
        try {
            $float = $reader->readFloat();
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::FLOAT, $e->getCode());
        }
        
        try {
            $stream = $this->getEmptyStream();
            $reader = new BinaryStreamReader($stream);
            $stream->close();
            $float = $reader->readFloat();
        }
        catch (StreamReaderException $e) {
            $this->assertEquals(BinaryStreamReaderException::NOT_OPEN, $e->getCode());
        }
    }
}