<?php

namespace qtismtest\data\storage\php;

use InvalidArgumentException;
use qtism\common\storage\MemoryStream;
use qtism\common\storage\StreamAccessException;
use qtism\data\storage\php\PhpArgument;
use qtism\data\storage\php\PhpArgumentCollection;
use qtism\data\storage\php\PhpStreamAccess;
use qtism\data\storage\php\PhpVariable;
use qtismtest\QtiSmTestCase;
use stdClass;

/**
 * Class PhpStreamAccessTest
 */
class PhpStreamAccessTest extends QtiSmTestCase
{
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
    protected function setStream(MemoryStream $stream): void
    {
        $this->stream = $stream;
    }

    /**
     * Get the stream to be used in each test of this test case.
     *
     * @return MemoryStream
     */
    protected function getStream(): MemoryStream
    {
        return $this->stream;
    }

    public function setUp(): void
    {
        parent::setUp();

        $stream = new MemoryStream();
        $stream->open();
        $this->setStream($stream);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        if ($this->getStream()->isOpen()) {
            $this->getStream()->close();
        }
    }

    public function testInstantiation(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $this::assertInstanceOf(PhpStreamAccess::class, $access);
    }

    /**
     * @dataProvider writeScalarDataProvider
     * @param string $toWrite
     * @param string $expected
     * @throws StreamAccessException
     */
    public function testWriteScalar($toWrite, $expected): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeScalar($toWrite);
        $this::assertEquals($expected, $this->getStream()->getBinary());
    }

    public function testWriteScalarInvalidData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $access = new PhpStreamAccess($this->getStream());
        $access->writeScalar(new stdClass());
    }

    public function testWriteScalarCloseStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage("An error occurred while writing the scalar value '10'.");

        $access->writeScalar(10);
    }

    public function testWriteEquals(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeEquals();
        $this::assertEquals(' = ', $this->getStream()->getBinary());

        $this->getStream()->flush();
        $access->writeEquals(false);
        $this::assertEquals('=', $this->getStream()->getBinary());
    }

    public function testWriteEqualsClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing the PHP equality symbol (=).');

        $access->writeEquals();
    }

    public function testWriteNewline(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeNewline();
        $this::assertEquals("\n", $this->getStream()->getBinary());
    }

    public function testWriteNewlineClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a newline escape sequence (\n).');

        $access->writeNewline();
    }

    public function testWriteOpeningTag(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeOpeningTag();
        $this::assertEquals("<?php\n", $this->getStream()->getBinary());

        $this->getStream()->flush();
        $access->writeOpeningTag(false);
        $this::assertEquals('<?php', $this->getStream()->getBinary());
    }

    public function testWriteOpeningTagClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a PHP opening tag (<?php).');

        $access->writeOpeningTag();
    }

    public function testWriteClosingTag(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeClosingTag();
        $this::assertEquals("\n?>", $this->getStream()->getBinary());

        $this->getStream()->flush();
        $access = new PhpStreamAccess($this->getStream());
        $access->writeClosingTag(false);
        $this::assertEquals('?>', $this->getStream()->getBinary());
    }

    public function testWriteClosingTagClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a PHP closing tag (?>).');

        $access->writeClosingTag();
    }

    public function testWriteSemicolon(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeSemicolon();
        $this::assertEquals(";\n", $this->getStream()->getBinary());

        $this->getStream()->flush();
        $access->writeSemicolon(false);
        $this::assertEquals(';', $this->getStream()->getBinary());
    }

    public function testWriteSemicolonClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a semicolon (;).');

        $access->writeSemicolon();
    }

    public function testWriteScopeResolution(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeScopeResolution();
        $this::assertEquals('::', $this->getStream()->getBinary());
    }

    public function testWriteScopeResolutionClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a scope resolution operator (::).');

        $access->writeScopeResolution();
    }

    public function testWriteOpeningParenthesis(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeOpeningParenthesis();
        $this::assertEquals('(', $this->getStream()->getBinary());
    }

    public function testWriteOpeningParenthesisClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing an opening parenthesis (().');

        $access->writeOpeningParenthesis();
    }

    public function testWriteClosingParenthesis(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeClosingParenthesis();
        $this::assertEquals(')', $this->getStream()->getBinary());
    }

    public function testWriteClosingParenthesisClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a closing parenthesis ()).');

        $access->writeClosingParenthesis();
    }

    public function testWriteComma(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeComma();
        $this::assertEquals(', ', $this->getStream()->getBinary());

        $this->getStream()->flush();
        $access->writeComma(false);
        $this::assertEquals(',', $this->getStream()->getBinary());
    }

    public function testWriteCommaClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a comma (,).');

        $access->writeComma();
    }

    public function testWriteSpace(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeSpace();
        $this::assertEquals(' ', $this->getStream()->getBinary());
    }

    public function testWriteSpaceClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a white space ( ).');

        $access->writeSpace();
    }

    public function testWriteVariable(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeVariable('foobar');
        $this::assertEquals('$foobar', $this->getStream()->getBinary());
    }

    public function testWriteVariableClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a variable reference.');

        $access->writeVariable('foobar');
    }

    public function testWriteObjectOperator(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeObjectOperator();
        $this::assertEquals('->', $this->getStream()->getBinary());
    }

    public function testWriteObjectOperatorClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing an object operator (->).');

        $access->writeObjectOperator();
    }

    /**
     * @dataProvider writeFunctionCallDataProvider
     * @param string $expected
     * @param string $funcname
     * @param PhpArgumentCollection $arguments
     * @throws StreamAccessException
     */
    public function testWriteFunctionCall($expected, $funcname, PhpArgumentCollection $arguments = null): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeFunctionCall($funcname, $arguments);
        $this::assertEquals($expected, $this->getStream()->getBinary());
    }

    public function testWriteFunctionCallClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a function call.');

        $access->writeFunctionCall('callMe');
    }

    public function testWriteNew(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeNew();
        $this::assertEquals('new ', $this->getStream()->getBinary());

        $this->getStream()->flush();
        $access->writeNew(false);
        $this::assertEquals('new', $this->getStream()->getBinary());
    }

    public function testWriteNewClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a new operator.');

        $access->writeNew();
    }

    public function testWriteColon(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeColon();
        $this::assertEquals(':', $this->getStream()->getBinary());
    }

    public function testWriteColonClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a colon (:).');

        $access->writeColon();
    }

    /**
     * @dataProvider writeInstantiationDataProvider
     * @param string $expected
     * @param string $classname
     * @param PhpArgumentCollection $arguments
     * @throws StreamAccessException
     */
    public function testWriteInstantiation($expected, $classname, PhpArgumentCollection $arguments = null): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writeInstantiation($classname, $arguments);
        $this::assertEquals($expected, $this->getStream()->getBinary());
    }

    public function testWriteInstantiationClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing an object instantiation.');

        $access->writeInstantiation('stdClass');
    }

    public function testWritePaamayimNekudotayim(): void
    {
        $access = new PhpStreamAccess($this->getStream());
        $access->writePaamayimNekudotayim();
        $this::assertEquals('::', $this->getStream()->getBinary());
    }

    public function testWritePaamayimNekudotayimClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a Paamayim Nekudotayim.');

        $access->writePaamayimNekudotayim();
    }

    public function testWriteStaticMethodCall(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $access->writeMethodCall('foo', 'bar', null, true);
        $stream->rewind();
        $this::assertEquals('$foo::bar()', $stream->getBinary());
    }

    public function testWriteStaticMethodCallClosedStream(): void
    {
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a method call.');

        $access->writeMethodCall('foo', 'bar', null, true);
    }

    public function testWriteArgumentsCloseStream(): void
    {
        $arguments = new PhpArgumentCollection([new PhpArgument(10)]);
        $stream = $this->getStream();
        $access = new PhpStreamAccess($stream);
        $stream->close();

        $this->expectException(StreamAccessException::class);
        $this->expectExceptionMessage('An error occurred while writing a sequence of arguments.');

        $access->writeArguments($arguments);
    }

    /**
     * @return array
     */
    public function writeScalarDataProvider(): array
    {
        return [
            ['', '""'],
            ['"', "\"\\\"\""],
            ['""', "\"\\\"\\\"\""],
            ["\n", "\"\\n\""],
            ["\r\n", "\"\\r\\n\""],
            ['Hello World!', '"Hello World!"'],
            ['中国是伟大的', '"中国是伟大的"'], // chinese is great
            ['/[a-z]+/ui', '"/[a-z]+/ui"'],
            [true, 'true'],
            [false, 'false'],
            [0, '0'],
            [10, '10'],
            [-10, '-10'],
            [0.0, '0.0'],
            [10.1337, '10.1337'],
            [-10.1337, '-10.1337'],
            [null, 'null'],
        ];
    }

    /**
     * @return array
     */
    public function writeFunctionCallDataProvider(): array
    {
        return [
            ['call_user_func()', 'call_user_func', null],
            ['call_user_func_array($array)', 'call_user_func_array', new PhpArgumentCollection([new PhpArgument(new PhpVariable('array'))])],
            ['a(true, "This is a test!", 20, 20.3, null, $foo)', 'a', new PhpArgumentCollection([new PhpArgument(true), new PhpArgument('This is a test!'), new PhpArgument(20), new PhpArgument(20.3), new PhpArgument(null), new PhpArgument(new PhpVariable('foo'))])],
        ];
    }

    /**
     * @return array
     */
    public function writeInstantiationDataProvider(): array
    {
        return [
            ['new stdClass()', 'stdClass', null],
            ['new A(true, "This is a test!", 20, 20.3, null, $foo)', 'A', new PhpArgumentCollection([new PhpArgument(true), new PhpArgument('This is a test!'), new PhpArgument(20), new PhpArgument(20.3), new PhpArgument(null), new PhpArgument(new PhpVariable('foo'))])],
        ];
    }
}
