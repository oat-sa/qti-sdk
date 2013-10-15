<?php

use qtism\data\storage\php\PhpArgument;

use qtism\data\storage\php\PhpArgumentCollection;
use qtism\data\storage\php\PhpStreamAccess;
use qtism\common\storage\BinaryStream;
use qtism\common\storage\IStream;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class PhpStreamAccessTest extends QtiSmTestCase {
	
    /**
     * A stream to be used in each test of this test case.
     * 
     * @var BinaryStream
     */
    private $stream;
    
    /**
     * Set the stream to be used in each test of this test case.
     * 
     * @param BinaryStream $stream
     */
    protected function setStream(BinaryStream $stream) {
        $this->stream = $stream;
    }
    
    /**
     * Get the stream to be used in each test of this test case.
     * 
     * @return BinaryStream
     */
    protected function getStream() {
        return $this->stream;
    }
    
    public function setUp() {
        parent::setUp();
        
        $stream = new BinaryStream();
        $stream->open();
        $this->setStream($stream);
    }
    
    public function tearDown() {
        parent::tearDown();
        
        if ($this->getStream()->isOpen()) {
            $this->getStream()->close();
        }
    }
    
    public function testInstantiation() {
        $access = new PhpStreamAccess($this->getStream());
        $this->assertInstanceOf('qtism\\data\\storage\\php\\PhpStreamAccess', $access);
    }
    
    public function testInstantiationNotOpen() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess(new BinaryStream());
    }
    
    /**
     * @dataProvider writeScalarDataProvider
     * @param string $toWrite
     * @param string $expected
     */
    public function testWriteScalar($toWrite, $expected) {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeScalar($toWrite);
        $this->assertEquals($expected, $this->getStream()->getBinary());
    }
    
    public function testWriteScalarInvalidData() {
        $this->setExpectedException('\\InvalidArgumentException');
        $access = new PhpStreamAccess($this->getStream());
        $access->writeScalar(new stdClass());
    }
    
    public function testWriteScalarNotOpenStream() {
        $this->setExpectedException('\\qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeScalar('scalar');
    }
    
    public function testWriteEquals() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeEquals();
        $this->assertEquals(" = ", $this->getStream()->getBinary());
        
        $this->getStream()->flush();
        $access->writeEquals(false);
        $this->assertEquals("=", $this->getStream()->getBinary());
    }
    
    public function testWriteEqualsClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeEquals();
    }
    
    public function testWriteNewline() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeNewline();
        $this->assertEquals("\n", $this->getStream()->getBinary());
    }
    
    public function testWriteNewLineClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeNewline();
    }
    
    public function testWriteOpeningTag() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeOpeningTag();
        $this->assertEquals("<?php\n", $this->getStream()->getBinary());
        
        $this->getStream()->flush();
        $access->writeOpeningTag(false);
        $this->assertEquals("<?php", $this->getStream()->getBinary());
    }
    
    public function testWriteOpeningTagClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeOpeningTag();
    }
    
    public function testWriteClosingTag() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeClosingTag();
        $this->assertEquals("\n?>", $this->getStream()->getBinary());
        
        $this->getStream()->flush();
        $access = new PhpStreamAccess($this->getStream());
        $access->writeClosingTag(false);
        $this->assertEquals("?>", $this->getStream()->getBinary());
    }
    
    public function testWriteClosingTagClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeClosingTag();
    }
    
    public function testWriteSemicolon() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeSemicolon();
        $this->assertEquals(";\n", $this->getStream()->getBinary());
        
        $this->getStream()->flush();
        $access->writeSemicolon(false);
        $this->assertEquals(";", $this->getStream()->getBinary());
    }
    
    public function testWriteSemicolonClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeSemicolon();
    }
    
    public function testWriteScopeResolution() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeScopeResolution();
        $this->assertEquals("::", $this->getStream()->getBinary());
    }
    
    public function testWriteScopeResolutionClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeScopeResolution();
    }
    
    public function testWriteOpeningParenthesis() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeOpeningParenthesis();
        $this->assertEquals("(", $this->getStream()->getBinary());
    }
    
    public function testWriteOpeningParenthesisClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeOpeningParenthesis();
    }
    
    public function testWriteClosingParenthesis() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeClosingParenthesis();
        $this->assertEquals(")", $this->getStream()->getBinary());
    }
    
    public function testWriteClosingParenthesisClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeClosingParenthesis();
    }
    
    public function testWriteComma() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeComma();
        $this->assertEquals(", ", $this->getStream()->getBinary());
        
        $this->getStream()->flush();
        $access->writeComma(false);
        $this->assertEquals(",", $this->getStream()->getBinary());
    }
    
    public function testWriteCommaClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeComma();
    }
    
    public function testWriteSpace() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeSpace();
        $this->assertEquals(" ", $this->getStream()->getBinary());
    }
    
    public function testWriteSpaceClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeSpace();
    }
    
    public function testWriteVariable() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeVariable('foobar');
        $this->assertEquals('$foobar', $this->getStream()->getBinary());
    }
    
    public function testWriteVariableClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeOpeningTag();
    }
    
    public function testWriteObjectOperator() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeObjectOperator();
        $this->assertEquals("->", $this->getStream()->getBinary());
    }
    
    public function testWriteObjectOperatorClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeObjectOperator();
    }
    
    public function testWriteObjectClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeObjectOperator();
    }
    
    /**
     * 
     * @dataProvider writeFunctionCallDataProvider
     * @param string $expected
     * @param string $funcname
     * @param PhpArgumentCollection $arguments
     */
    public function testWriteFunctionCall($expected, $funcname, PhpArgumentCollection $arguments = null) {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeFunctionCall($funcname, $arguments);
        $this->assertEquals($expected, $this->getStream()->getBinary());
    }
    
    public function testWriteFunctionCallClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeFunctionCall('test');
    }
    
    public function testWriteNew() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeNew();
        $this->assertEquals('new ', $this->getStream()->getBinary());
        
        $this->getStream()->flush();
        $access->writeNew(false);
        $this->assertEquals('new', $this->getStream()->getBinary());
    }
    
    public function testWriteNewClosedStream() {
        $this->setExpectedException('qtism\\common\\storage\\StreamAccessException');
        $access = new PhpStreamAccess($this->getStream());
        $this->getStream()->close();
        $access->writeNew();
    }
    
    /**
     * 
     * @dataProvider writeInstantiationDataProvider
     * @param string $expected
     * @param string $classname
     * @param PhpArgumentCollection $arguments
     */
    public function testWriteInstantiation($expected, $classname, PhpArgumentCollection $arguments = null) {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeInstantiation($classname, $arguments);
        $this->assertEquals($expected, $this->getStream()->getBinary());
    }
    
    public function writeScalarDataProvider() {
        return array(
            array('', '""'),
            array("\"", "\"\\\"\""),
            array("\"\"", "\"\\\"\\\"\""),
            array("\n", "\"\\n\""),
            array("\r\n", "\"\\r\\n\""),
            array("Hello World!", "\"Hello World!\""),
            array("中国是伟大的", "\"中国是伟大的\""), // chinese is great
            array("/[a-z]+/ui", "\"/[a-z]+/ui\""),
            array(true, "true"),
            array(false, "false"),
            array(0, "0"),
            array(10, "10"),
            array(-10, "-10"),
            array(0.0, "0.0"),
            array(10.1337, "10.1337"),
            array(-10.1337, "-10.1337"),
            array(null, "null")
        );
    }
    
    public function writeFunctionCallDataProvider() {
        return array(
            array('call_user_func()', 'call_user_func', null),
            array('call_user_func_array($array)', 'call_user_func_array', new PhpArgumentCollection(array(new PhpArgument('$array')))),
            array('a(true, "This is a test!", 20, 20.3, null, $foo)', 'a', new PhpArgumentCollection(array(new PhpArgument(true), new PhpArgument('This is a test!'), new PhpArgument(20), new PhpArgument(20.3), new PhpArgument(null), new PhpArgument('$foo'))))
        );
    }
    
    public function writeInstantiationDataProvider() {
        return array(
            array('new stdClass()', 'stdClass', null),
            array('new A(true, "This is a test!", 20, 20.3, null, $foo)', 'A', new PhpArgumentCollection(array(new PhpArgument(true), new PhpArgument('This is a test!'), new PhpArgument(20), new PhpArgument(20.3), new PhpArgument(null), new PhpArgument('$foo'))))
        );
    }
}