<?php
namespace qtismtest\data\storage\php;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\php\PhpVariable;
use qtism\data\storage\php\PhpArgument;
use qtism\data\storage\php\PhpArgumentCollection;
use qtism\data\storage\php\PhpStreamAccess;
use qtism\common\storage\MemoryStream;
use qtism\common\storage\IStream;
use \stdClass;

class PhpStreamAccessTest extends QtiSmTestCase {
	
    /**
     * A stream to be used in each test of this test case.
     * 
     * @var MemoryStream
     */
    private $stream;
    
    /**
     * Set the stream to be used in each test of this test case.
     * 
     * @param MemoryStream $stream
     */
    protected function setStream(MemoryStream $stream) {
        $this->stream = $stream;
    }
    
    /**
     * Get the stream to be used in each test of this test case.
     * 
     * @return MemoryStream
     */
    protected function getStream() {
        return $this->stream;
    }
    
    public function setUp() {
        parent::setUp();
        
        $stream = new MemoryStream();
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
    
    public function testWriteScalarCloseStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            "An error occured while writing the scalar value '10'."
        );
        
        $access->writeScalar(10);
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
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing the PHP equality symbol (=).'
        );
        
        $access->writeEquals();
    }
    
    public function testWriteNewline() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeNewline();
        $this->assertEquals("\n", $this->getStream()->getBinary());
    }
    
    public function testWriteNewlineClosedStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a newline escape sequence (\n).'
        );
        
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
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a PHP opening tag (<?php).'
        );
        
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
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a PHP closing tag (?>).'
        );
        
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
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a semicolon (;).'
        );
        
        $access->writeSemicolon();
    }
    
    public function testWriteScopeResolution() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeScopeResolution();
        $this->assertEquals("::", $this->getStream()->getBinary());
    }
    
    public function testWriteScopeResolutionClosedStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a scope resolution operator (::).'
        );
        
        $access->writeScopeResolution();
    }
    
    public function testWriteOpeningParenthesis() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeOpeningParenthesis();
        $this->assertEquals("(", $this->getStream()->getBinary());
    }
    
    public function testWriteOpeningParenthesisClosedStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing an opening parenthesis (().'
        );
        
        $access->writeOpeningParenthesis();
    }
    
    public function testWriteClosingParenthesis() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeClosingParenthesis();
        $this->assertEquals(")", $this->getStream()->getBinary());
    }
    
    public function testWriteClosingParenthesisClosedStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a closing parenthesis ()).'
        );
        
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
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a comma (,).'
        );
        
        $access->writeComma();
    }
    
    public function testWriteSpace() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeSpace();
        $this->assertEquals(" ", $this->getStream()->getBinary());
    }
    
    public function testWriteSpaceClosedStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a white space ( ).'
        );
        
        $access->writeSpace();
    }
    
    public function testWriteVariable() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeVariable('foobar');
        $this->assertEquals('$foobar', $this->getStream()->getBinary());
    }
    
    public function testWriteVariableClosedStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a variable reference.'
        );
        
        $access->writeVariable('foobar');
    }
    
    public function testWriteObjectOperator() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeObjectOperator();
        $this->assertEquals("->", $this->getStream()->getBinary());
    }
    
    public function testWriteObjectOperatorClosedStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing an object operator (->).'
        );
        
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
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a function call.'
        );
        
        $access->writeFunctionCall('callMe');
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
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a new operator.'
        );
        
        $access->writeNew();
    }
    
    public function testWriteColon() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeColon();
        $this->assertEquals(":", $this->getStream()->getBinary());
    }
    
    public function testWriteColonClosedStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a colon (:).'
        );
        
        $access->writeColon();
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
    
    public function testWriteInstantiationClosedStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing an object instantiation.'
        );
        
        $access->writeInstantiation('stdClass');
    }
    
    public function testWritePaamayimNekudotayim() {
        $access = new PhpStreamAccess($this->getStream());
        $access->writePaamayimNekudotayim();
        $this->assertEquals("::", $this->getStream()->getBinary());
    }
    
    public function testWritePaamayimNekudotayimClosedStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a Paamayim Nekudotayim.'
        );
        
        $access->writePaamayimNekudotayim();
    }
    
    public function testWriteStaticMethodCall() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $access->writeMethodCall('foo', 'bar', null, true);
        $stream->rewind();
        $this->assertEquals('$foo::bar()', $stream->getBinary());
    }
    
    public function testWriteStaticMethodCallClosedStream() {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a method call.'
        );
        
        $access->writeMethodCall('foo', 'bar', null, true);
    }
    
    public function testWriteArgumentsCloseStream() {
        $arguments = new PhpArgumentCollection(array(new PhpArgument(10)));
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();
        
        $this->setExpectedException(
            'qtism\\common\\storage\\StreamAccessException',
            'An error occured while writing a sequence of arguments.'
        );
        
        $access->writeArguments($arguments);
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
            array('call_user_func_array($array)', 'call_user_func_array', new PhpArgumentCollection(array(new PhpArgument(new PhpVariable('array'))))),
            array('a(true, "This is a test!", 20, 20.3, null, $foo)', 'a', new PhpArgumentCollection(array(new PhpArgument(true), new PhpArgument('This is a test!'), new PhpArgument(20), new PhpArgument(20.3), new PhpArgument(null), new PhpArgument(new PhpVariable('foo')))))
        );
    }
    
    public function writeInstantiationDataProvider() {
        return array(
            array('new stdClass()', 'stdClass', null),
            array('new A(true, "This is a test!", 20, 20.3, null, $foo)', 'A', new PhpArgumentCollection(array(new PhpArgument(true), new PhpArgument('This is a test!'), new PhpArgument(20), new PhpArgument(20.3), new PhpArgument(null), new PhpArgument(new PhpVariable('foo')))))
        );
    }
}
