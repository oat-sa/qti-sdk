<?php

namespace qtismtest\runtime\storage\binary;

use qtism\common\Comparable;
use qtism\common\datatypes\files\DefaultFileManager;
use qtism\common\datatypes\files\FileHash;
use qtism\common\datatypes\files\FileSystemFile;
use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiIntOrIdentifier;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\storage\BinaryStreamAccessException;
use qtism\common\storage\MemoryStream;
use qtism\common\storage\MemoryStreamException;
use qtism\common\storage\StreamAccessException;
use qtism\data\NavigationMode;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\SubmissionMode;
use qtism\runtime\common\Container;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\common\Variable;
use qtism\runtime\storage\binary\QtiBinaryStreamAccess;
use qtism\runtime\storage\binary\QtiBinaryStreamAccessException;
use qtism\runtime\storage\binary\QtiBinaryVersion;
use qtism\runtime\storage\common\AssessmentTestSeeker;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmTestCase;
use ReflectionException;
use ReflectionProperty;

/**
 * Class QtiBinaryStreamAccessTest
 */
class QtiBinaryStreamAccessTest extends QtiSmTestCase
{
    /**
     * @dataProvider readVariableValueProvider
     *
     * @param Variable $variable
     * @param string $binary
     * @param mixed $expectedValue
     * @throws BinaryStreamAccessException
     * @throws MemoryStreamException
     * @throws StreamAccessException
     */
    public function testReadVariableValue(Variable $variable, $binary, $expectedValue)
    {
        $stream = new MemoryStream($binary);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        $access->readVariableValue($variable);

        if (is_scalar($expectedValue)) {
            $this::assertEquals($expectedValue, $variable->getValue()->getValue());
        } elseif ($expectedValue === null) {
            $this::assertSame($expectedValue, $variable->getValue());
        } elseif ($expectedValue instanceof RecordContainer) {
            $this::assertEquals($expectedValue->getCardinality(), $variable->getCardinality());
            $this::assertTrue($expectedValue->equals($variable->getValue()));
        } elseif ($expectedValue instanceof Container) {
            $this::assertEquals($expectedValue->getCardinality(), $variable->getCardinality());
            $this::assertEquals($expectedValue->getBaseType(), $variable->getBaseType());
            $this::assertTrue($expectedValue->equals($variable->getValue()));
        } elseif ($expectedValue instanceof Comparable) {
            // Duration, Point, Pair, ...
            $this::assertTrue($expectedValue->equals($variable->getValue()));
        } else {
            // can't happen.
            $this::assertTrue(false);
        }
    }

    /**
     * @return array
     */
    public function readVariableValueProvider()
    {
        $returnValue = [];

        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(45)), "\x01", null];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER), "\x00" . "\x01" . pack('l', 45), 45];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('l', 0) . "\x00" . pack('l', -20) . "\x00" . pack('l', 65000),
            new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0), new QtiInteger(-20), new QtiInteger(65000)]),
        ];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('l', 0) . "\x01" . "\x00" . pack('l', 65000), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0), null, new QtiInteger(65000)])];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('l', 1337), new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1337)])];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::INTEGER)];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1)])), "\x01", null];

        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(45.5)), "\x01", null];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT), "\x00" . "\x01" . pack('d', 45.5), 45.5];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('d', 0.0) . "\x00" . pack('d', -20.666) . "\x00" . pack('d', 65000.56),
            new MultipleContainer(BaseType::FLOAT, [new QtiFloat(0.0), new QtiFloat(-20.666), new QtiFloat(65000.56)]),
        ];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('d', 1337.666), new OrderedContainer(BaseType::FLOAT, [new QtiFloat(1337.666)])];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::FLOAT)];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, [new QtiFloat(0.0)])), "\x01", null];

        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true)), "\x01", null];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN), "\x00" . "\x01" . "\x01", true];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN), "\x00" . "\x01" . "\x00", false];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 3) . "\x00\x00\x00\x01\x00\x00", new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(false)])];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 1) . "\x00\x01", new OrderedContainer(BaseType::BOOLEAN, [new QtiBoolean(true)])];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::BOOLEAN)];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, [new QtiBoolean(true)])), "\x01", null];

        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::STRING, new QtiString('String!')), "\x01", null];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::STRING), "\x00" . "\x01" . pack('S', 0) . '', ''];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::STRING), "\x00" . "\x01" . pack('S', 7) . 'String!', 'String!'];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::STRING),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 3) . 'ABC' . "\x00" . pack('S', 0) . '' . "\x00" . pack('S', 7) . 'String!',
            new MultipleContainer(BaseType::STRING, [new QtiString('ABC'), new QtiString(''), new QtiString('String!')]),
        ];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::STRING), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 7) . 'String!', new OrderedContainer(BaseType::STRING, [new QtiString('String!')])];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::STRING), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::STRING)];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::STRING, new OrderedContainer(BaseType::STRING, [new QtiString('pouet')])), "\x01", null];

        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('Identifier')), "\x01", null];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER), "\x00" . "\x01" . pack('S', 1) . 'A', 'A'];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER), "\x00" . "\x01" . pack('S', 10) . 'Identifier', 'Identifier'];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 3) . 'Q01' . "\x00" . pack('S', 1) . 'A' . "\x00" . pack('S', 3) . 'Q02',
            new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('Q01'), new QtiIdentifier('A'), new QtiIdentifier('Q02')]),
        ];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 10) . 'Identifier', new OrderedContainer(BaseType::IDENTIFIER, [new QtiIdentifier('Identifier')])];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::IDENTIFIER)];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, [new QtiIdentifier('OUTCOMEX')])), "\x01", null];

        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT1S')), "\x01", null];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::DURATION), "\x00" . "\x01" . pack('S', 4) . 'PT1S', new QtiDuration('PT1S')];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . 'PT0S' . "\x00" . pack('S', 4) . 'PT1S' . "\x00" . pack('S', 4) . 'PT2S',
            new MultipleContainer(BaseType::DURATION, [new QtiDuration('PT0S'), new QtiDuration('PT1S'), new QtiDuration('PT2S')]),
        ];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DURATION), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 6) . 'PT2M2S', new OrderedContainer(BaseType::DURATION, [new QtiDuration('PT2M2S')])];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::DURATION)];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, [new QtiDuration('PT1S')])), "\x01", null];

        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::PAIR, new QtiPair('A', 'B')), "\x01", null];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::PAIR), "\x00" . "\x01" . pack('S', 1) . 'A' . pack('S', 1) . 'B', new QtiPair('A', 'B')];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 1) . 'A' . pack('S', 1) . 'B' . "\x00" . pack('S', 1) . 'C' . pack('S', 1) . 'D' . "\x00" . pack('S', 1) . 'E' . pack('S', 1) . 'F',
            new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'B'), new QtiPair('C', 'D'), new QtiPair('E', 'F')]),
        ];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::PAIR), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 2) . 'P1' . pack('S', 2) . 'P2', new OrderedContainer(BaseType::PAIR, [new QtiPair('P1', 'P2')])];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::PAIR)];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, [new QtiPair('my', 'pair')])), "\x01", null];

        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR, new QtiDirectedPair('A', 'B')), "\x01", null];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR), "\x00" . "\x01" . pack('S', 1) . 'A' . pack('S', 1) . 'B', new QtiDirectedPair('A', 'B')];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 1) . 'A' . pack('S', 1) . 'B' . "\x00" . pack('S', 1) . 'C' . pack('S', 1) . 'D' . "\x00" . pack('S', 1) . 'E' . pack('S', 1) . 'F',
            new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F')]),
        ];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 2) . 'P1' . pack('S', 2) . 'P2', new OrderedContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('P1', 'P2')])];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::DIRECTED_PAIR)];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('my', 'pair')])), "\x01", null];

        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(0, 1)), "\x01", null];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::POINT), "\x00" . "\x01" . pack('S', 0) . pack('S', 0), new QtiPoint(0, 0)];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . pack('S', 3) . "\x01" . "\x00" . pack('S', 2) . pack('S', 1),
            new MultipleContainer(BaseType::POINT, [new QtiPoint(4, 3), null, new QtiPoint(2, 1)]),
        ];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::POINT), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 6) . pack('S', 1234), new OrderedContainer(BaseType::POINT, [new QtiPoint(6, 1234)])];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::POINT)];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 1)])), "\x01", null];

        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, new QtiIntOrIdentifier(45)), "\x01", null];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x01" . "\x01" . pack('l', 45), 45];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x01" . "\x00" . pack('S', 10) . 'Identifier', 'Identifier'];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x01" . pack('l', 0) . "\x00" . "\x01" . pack('l', -20) . "\x00" . "\x01" . pack('l', 65000),
            new MultipleContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier(0), new QtiIntOrIdentifier(-20), new QtiIntOrIdentifier(65000)]),
        ];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x00" . pack('S', 1) . 'A' . "\x00" . "\x00" . pack('S', 1) . 'B' . "\x00" . "\x00" . pack('S', 1) . 'C',
            new MultipleContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier('A'), new QtiIntOrIdentifier('B'), new QtiIntOrIdentifier('C')]),
        ];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x00" . pack('S', 1) . 'A' . "\x00" . "\x01" . pack('l', 1337) . "\x00" . "\x00" . pack('S', 1) . 'C',
            new MultipleContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier('A'), new QtiIntOrIdentifier(1337), new QtiIntOrIdentifier('C')]),
        ];
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x01" . pack('l', 0) . "\x01" . "\x00" . "\x01" . pack('l', 65000),
            new MultipleContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier(0), null, new QtiIntOrIdentifier(65000)]),
        ];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 1) . "\x00" . "\x01" . pack('l', 1337), new OrderedContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier(1337)])];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::INT_OR_IDENTIFIER)];
        $returnValue[] = [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier(1)])), "\x01", null];

        // Files
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FILE), "\x01", null];
        $path = self::samplesDir() . 'datatypes/file/text-plain_text_data.txt';
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FILE), "\x00" . "\x01" . pack('S', strlen($path)) . $path, FileSystemFile::retrieveFile($path)];
        $path1 = $path;
        $path2 = self::samplesDir() . 'datatypes/file/text-plain_noname.txt';
        $returnValue[] = [
            new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FILE),
            "\x00" . "\x00" . pack('S', 2) . "\x00" . pack('S', strlen($path1)) . $path1 . "\x00" . pack('S', strlen($path2)) . $path2,
            new MultipleContainer(BaseType::FILE, [FileSystemFile::retrieveFile($path1), FileSystemFile::retrieveFile($path2)]),
        ];

        // Records
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 0), new RecordContainer()];
        $returnValue[] = [new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337), new RecordContainer(['key1' => new QtiInteger(1337)])];
        $returnValue[] = [
            new ResponseVariable('VAR', Cardinality::RECORD),
            "\x00" . "\x00" . pack('S', 2) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337) . "\x00" . pack('S', 4) . 'key2' . "\x04" . pack('S', 7) . 'String!',
            new RecordContainer(['key1' => new QtiInteger(1337), 'key2' => new QtiString('String!')]),
        ];
        $returnValue[] = [
            new ResponseVariable('VAR', Cardinality::RECORD),
            "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337) . "\x01" . pack('S', 4) . 'key2' . "\x00" . pack('S', 4) . 'key3' . "\x04" . pack('S', 7) . 'String!',
            new RecordContainer(['key1' => new QtiInteger(1337), 'key2' => null, 'key3' => new QtiString('String!')]),
        ];

        return $returnValue;
    }

    public function testReadVariableValueError()
    {
        // 'XYZ' is not a valid duration datatype.
        $binary = "\x00" . "\x01" . pack('S', 3) . 'XYZ';
        $variable = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::DURATION);

        $stream = new MemoryStream($binary);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());

        $this->expectException(QtiBinaryStreamAccessException::class);
        $this->expectExceptionMessage("Datatype mismatch for variable 'VAR'");
        $access->readVariableValue($variable);
    }

    /**
     * @dataProvider writeVariableValueProvider
     *
     * @param Variable $variable
     * @throws QtiBinaryStreamAccessException
     * @throws BinaryStreamAccessException
     * @throws MemoryStreamException
     * @throws StreamAccessException
     */
    public function testWriteVariableValue(Variable $variable)
    {
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());

        // Write the variable value.
        $access->writeVariableValue($variable);
        $stream->rewind();

        $testVariable = clone $variable;
        // Reset the value of $testVariable.
        $testVariable->setValue(null);

        // Read what we just wrote.
        $access->readVariableValue($testVariable);

        $originalValue = $variable->getValue();
        $readValue = $testVariable->getValue();

        // Compare.
        if ($originalValue === null) {
            $this::assertSame($originalValue, $readValue);
        } elseif (is_scalar($originalValue)) {
            $this::assertEquals($originalValue, $readValue);
        } elseif ($originalValue instanceof RecordContainer) {
            $this::assertEquals($originalValue->getCardinality(), $readValue->getCardinality());
            $this::assertTrue($readValue->equals($originalValue));
        } elseif ($originalValue instanceof Container) {
            // MULTIPLE or ORDERED container.
            $this::assertEquals($originalValue->getCardinality(), $readValue->getCardinality());
            $this::assertEquals($readValue->getBaseType(), $readValue->getBaseType());
            $this::assertTrue($readValue->equals($originalValue));
        } elseif ($originalValue instanceof Comparable) {
            // Complex QTI Runtime object.
            $this::assertTrue($readValue->equals($originalValue));
        } else {
            // Unknown datatype.
            $this::assertTrue(false);
        }
    }

    /**
     * @return array
     */
    public function writeVariableValueProvider()
    {
        return [
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(26))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(-34455))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER)],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER, [new QtiInteger(-2147483647)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, [new QtiInteger(2147483647)]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0), new QtiInteger(-1), new QtiInteger(1), new QtiInteger(-200000), new QtiInteger(200000)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, [new QtiInteger(0), new QtiInteger(-1), new QtiInteger(1), new QtiInteger(-200000), new QtiInteger(200000)]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER, [null]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, [null]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0), null, new QtiInteger(1), null, new QtiInteger(200000)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, [new QtiInteger(0), null, new QtiInteger(1), null, new QtiInteger(200000)]))],

            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(26.1))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(-34455.0))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT)],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT, new MultipleContainer(BaseType::FLOAT, [new QtiFloat(-21474.654)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, [new QtiFloat(21474.3)]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT, new MultipleContainer(BaseType::FLOAT, [new QtiFloat(0.0), new QtiFloat(-1.1), new QtiFloat(1.1), new QtiFloat(-200000.005), new QtiFloat(200000.005)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, [new QtiFloat(0.0), new QtiFloat(-1.1), new QtiFloat(1.1), new QtiFloat(-200000.005), new QtiFloat(200000.005)]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT, new MultipleContainer(BaseType::FLOAT, [null]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, [null]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT, new MultipleContainer(BaseType::FLOAT, [new QtiFloat(0.0), null, new QtiFloat(1.1), null, new QtiFloat(200000.005)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, [new QtiFloat(0.0), null, new QtiFloat(1.1), null, new QtiFloat(200000.005)]))],

            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN)],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(true)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, [new QtiBoolean(false)]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(true)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, [new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(false)]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, [null]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, [null]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(false), null, new QtiBoolean(true), null, new QtiBoolean(false)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, [new QtiBoolean(false), null, new QtiBoolean(true), null, new QtiBoolean(false)]))],

            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('identifier'))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('non-identifier'))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER)],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('identifier')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, [new QtiIdentifier('identifier')]))],
            [
                new OutcomeVariable(
                    'VAR',
                    Cardinality::MULTIPLE,
                    BaseType::IDENTIFIER,
                    new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('identifier1'), new QtiIdentifier('identifier2'), new QtiIdentifier('identifier3'), new QtiIdentifier('identifier4'), new QtiIdentifier('identifier5')])
                ),
            ],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, [new QtiIdentifier('identifier1'), new QtiIdentifier('identifier2'), new QtiIdentifier('identifier3'), new QtiIdentifier('X-Y-Z'), new QtiIdentifier('identifier4')]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [null]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, [null]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('identifier1'), null, new QtiIdentifier('identifier2'), null, new QtiIdentifier('identifier3')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, [new QtiIdentifier('identifier1'), null, new QtiIdentifier('identifier2'), null, new QtiIdentifier('identifier3')]))],

            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::URI, new QtiUri('http://www.my.uri'))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::URI, new QtiUri('http://www.my.uri'))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::URI)],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::URI)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::URI)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::URI, new MultipleContainer(BaseType::URI, [new QtiUri('http://www.my.uri')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::URI, new OrderedContainer(BaseType::URI, [new QtiUri('http://www.my.uri')]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::URI, new MultipleContainer(BaseType::URI, [new QtiUri('http://www.my.uri1'), new QtiUri('http://www.my.uri2'), new QtiUri('http://www.my.uri3'), new QtiUri('http://www.my.uri4'), new QtiUri('http://www.my.uri6')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::URI, new OrderedContainer(BaseType::URI, [new QtiUri('http://www.my.uri1'), new QtiUri('http://www.my.uri2'), new QtiUri('http://www.my.uri3'), new QtiUri('http://www.my.uri4'), new QtiUri('http://www.my.uri5')]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::URI, new MultipleContainer(BaseType::URI, [null]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::URI, new OrderedContainer(BaseType::URI, [null]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::URI, new MultipleContainer(BaseType::URI, [new QtiUri('http://www.my.uri1'), null, new QtiUri('http://www.my.uri2'), null, new QtiUri('http://www.my.uri3')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::URI, new OrderedContainer(BaseType::URI, [new QtiUri('http://www.my.uri1'), null, new QtiUri('http://www.my.uri2'), null, new QtiUri('http://www.my.uri3')]))],

            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('P3DT2H1S'))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DURATION)],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, [new QtiDuration('PT2S')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, [new QtiDuration('P2YT2S')]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, [new QtiDuration('PT1S'), new QtiDuration('PT2S'), new QtiDuration('PT3S'), new QtiDuration('PT4S'), new QtiDuration('PT5S')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, [new QtiDuration('PT1S'), new QtiDuration('PT2S'), new QtiDuration('PT3S'), new QtiDuration('PT4S'), new QtiDuration('PT5S')]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, [null]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, [null]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, [new QtiDuration('P4D'), null, new QtiDuration('P10D'), null, new QtiDuration('P20D')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, [new QtiDuration('P4D'), null, new QtiDuration('P10D'), null, new QtiDuration('P20D')]))],

            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::PAIR, new QtiPair('A', 'B'))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::PAIR)],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'B')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, [new QtiPair('A', 'B')]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'B'), new QtiPair('C', 'D'), new QtiPair('E', 'F'), new QtiPair('G', 'H'), new QtiPair('I', 'J')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, [new QtiPair('A', 'B'), new QtiPair('C', 'D'), new QtiPair('E', 'F'), new QtiPair('G', 'H'), new QtiPair('I', 'J')]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [null]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, [null]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'B'), null, new QtiPair('C', 'D'), null, new QtiPair('E', 'F')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, [new QtiPair('A', 'B'), null, new QtiPair('D', 'E'), null, new QtiPair('F', 'G')]))],

            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR, new QtiDirectedPair('A', 'B'))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR)],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B')]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F'), new QtiDirectedPair('G', 'H'), new QtiDirectedPair('I', 'J')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F'), new QtiDirectedPair('G', 'H'), new QtiDirectedPair('I', 'J')]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, [null]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, [null]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B'), null, new QtiDirectedPair('C', 'D'), null, new QtiDirectedPair('E', 'F')]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B'), null, new QtiDirectedPair('D', 'E'), null, new QtiDirectedPair('F', 'G')]))],

            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(50, 50))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::POINT)],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT, new MultipleContainer(BaseType::POINT, [new QtiPoint(50, 50)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, [new QtiPoint(50, 50)]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT, new MultipleContainer(BaseType::POINT, [new QtiPoint(50, 50), new QtiPoint(0, 0), new QtiPoint(100, 50), new QtiPoint(150, 3), new QtiPoint(50, 50)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, [new QtiPoint(50, 50), new QtiPoint(0, 35), new QtiPoint(30, 50), new QtiPoint(40, 55), new QtiPoint(0, 0)]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT, new MultipleContainer(BaseType::POINT, [null]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, [null]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT, new MultipleContainer(BaseType::POINT, [new QtiPoint(30, 50), null, new QtiPoint(20, 50), null, new QtiPoint(45, 32)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, [new QtiPoint(20, 11), null, new QtiPoint(36, 43), null, new QtiPoint(50, 44)]))],

            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, new QtiIntOrIdentifier(26))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, new QtiIntOrIdentifier('Q01'))],
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER)],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER)],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER, new MultipleContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier(-2147483647)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier('Section1')]))],
            [
                new OutcomeVariable(
                    'VAR',
                    Cardinality::MULTIPLE,
                    BaseType::INT_OR_IDENTIFIER,
                    new MultipleContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier(0), new QtiIntOrIdentifier('Q01'), new QtiIntOrIdentifier('Q02'), new QtiIntOrIdentifier(-200000), new QtiIntOrIdentifier(200000)])
                ),
            ],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier(0), new QtiIntOrIdentifier(-1), new QtiIntOrIdentifier(1), new QtiIntOrIdentifier(-200000), new QtiIntOrIdentifier('Q05')]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER, new MultipleContainer(BaseType::INT_OR_IDENTIFIER, [null]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, [null]))],
            [new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER, new MultipleContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier(0), null, new QtiIntOrIdentifier(1), null, new QtiIntOrIdentifier(200000)]))],
            [new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier(0), null, new QtiIntOrIdentifier('Q01'), null, new QtiIntOrIdentifier(200000)]))],

            // Files
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FILE, FileSystemFile::retrieveFile(self::samplesDir() . 'datatypes/file/text-plain_text_data.txt'))],
            [
                new OutcomeVariable(
                    'VAR',
                    Cardinality::MULTIPLE,
                    BaseType::FILE,
                    new MultipleContainer(BaseType::FILE, [FileSystemFile::retrieveFile(self::samplesDir() . 'datatypes/file/text-plain_text_data.txt'), FileSystemFile::retrieveFile(self::samplesDir() . 'datatypes/file/text-plain_noname.txt')])
                ),
            ],

            // FileHash
            [new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FILE, new FileHash('id', 'text/plain', 'my_file.txt', 'AA025C4DB95A39A2CB8525F83F1387C1A2B13595C8E4C337CDF9AD7B043518A3'))],

            // Records
            [new OutcomeVariable('VAR', Cardinality::RECORD)],
            [new OutcomeVariable('VAR', Cardinality::RECORD, -1, new RecordContainer(['key1' => null]))],
            [new OutcomeVariable('Var', Cardinality::RECORD, -1, new RecordContainer(['key1' => new QtiDuration('PT1S'), 'key2' => new QtiFloat(25.5), 'key3' => new QtiInteger(2), 'key4' => new QtiString('String!'), 'key5' => null, 'key6' => new QtiBoolean(true)]))],
        ];
    }

    public function testReadAssessmentItemSession()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');

        $position = pack('S', 0); // Q01
        $state = "\x01"; // INTERACTING
        $navigationMode = "\x00"; // LINEAR
        $submissionMode = "\x00"; // INDIVIDUAL
        $attempting = "\x00"; // false -> Here we assume we're in version >= 2.
        $hasItemSessionControl = "\x00"; // false
        $numAttempts = "\x02"; // 2
        $duration = pack('S', 4) . 'PT0S'; // 0 seconds recorded yet.
        $completionStatus = pack('S', 10) . 'incomplete';
        $timeReference = pack('l', 1378302030); //  Wednesday, September 4th 2013, 13:40:30 (GMT)
        $varCount = "\x02"; // 2 variables (SCORE & RESPONSE).

        $score = "\x01" . pack('S', 8) . "\x00" . "\x01" . pack('d', 1.0);
        $response = "\x00" . pack('S', 0) . "\x00" . "\x01" . pack('S', 7) . 'ChoiceA';

        $bin = implode('', [$position, $state, $navigationMode, $submissionMode, $attempting, $hasItemSessionControl, $numAttempts, $duration, $completionStatus, $timeReference, $varCount, $score, $response]);
        $stream = new MemoryStream($bin);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), ['assessmentItemRef', 'outcomeDeclaration', 'responseDeclaration', 'itemSessionControl']);

        $version = $this->createVersionMock(2);
        $session = $access->readAssessmentItemSession(new SessionManager(), $seeker, $version);

        $this::assertEquals('Q01', $session->getAssessmentItem()->getIdentifier());
        $this::assertEquals(AssessmentItemSessionState::INTERACTING, $session->getState());
        $this::assertEquals(NavigationMode::LINEAR, $session->getNavigationMode());
        $this::assertEquals(SubmissionMode::INDIVIDUAL, $session->getSubmissionMode());
        $this::assertEquals(2, $session['numAttempts']->getValue());
        $this::assertEquals('PT0S', $session['duration']->__toString());
        $this::assertEquals('incomplete', $session['completionStatus']->getValue());
        $this::assertInstanceOf(OutcomeVariable::class, $session->getVariable('scoring'));
        $this::assertInstanceOf(QtiFloat::class, $session['scoring']);
        $this::assertEquals(1.0, $session['scoring']->getValue());
        $this::assertInstanceOf(ResponseVariable::class, $session->getVariable('RESPONSE'));
        $this::assertEquals(BaseType::IDENTIFIER, $session->getVariable('RESPONSE')->getBaseType());
        $this::assertInstanceOf(QtiString::class, $session['RESPONSE']);
        $this::assertEquals('ChoiceA', $session['RESPONSE']->getValue());
    }

    public function testWriteAssessmentItemSession()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');

        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), ['assessmentItemRef', 'outcomeDeclaration', 'responseDeclaration', 'itemSessionControl']);
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());

        $session = new AssessmentItemSession($doc->getDocumentComponent()->getComponentByIdentifier('Q02'), new SessionManager());
        $session->beginItemSession();

        $access->writeAssessmentItemSession($seeker, $session);

        $stream->rewind();

        $version = $this->createVersionMock(2);
        $session = $access->readAssessmentItemSession(new SessionManager(), $seeker, $version);

        $this::assertEquals(AssessmentItemSessionState::INITIAL, $session->getState());
        $this::assertEquals(NavigationMode::LINEAR, $session->getNavigationMode());
        $this::assertEquals(SubmissionMode::INDIVIDUAL, $session->getSubmissionMode());
        $this::assertEquals('PT0S', $session['duration']->__toString());
        $this::assertEquals(0, $session['numAttempts']->getValue());
        $this::assertEquals('not_attempted', $session['completionStatus']->getValue());
        $this::assertFalse($session->isAttempting());
        $this::assertEquals(0.0, $session['SCORE']->getValue());
        $this::assertTrue($session['RESPONSE']->equals(new MultipleContainer(BaseType::PAIR)));
    }

    public function testReadRouteItem()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');

        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), ['assessmentItemRef', 'assessmentSection', 'testPart', 'outcomeDeclaration', 'responseDeclaration', 'branchRule', 'preCondition']);
        $bin = '';
        $bin .= "\x00"; // occurence = 0
        $bin .= pack('S', 2); // item-tree-position = Q03
        $bin .= pack('S', 0); // part-tree-position = P01
        $bin .= "\x01"; // sections-count = 1 -> Here we assume we're in version >= 3
        $bin .= pack('S', 0); // section-tree-position = S01
        $bin .= "\x00"; // branchrules-count = 0
        $bin .= "\x00"; // preconditions-count = 0

        $stream = new MemoryStream($bin);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());

        $version = $this->createVersionMock(3);
        $routeItem = $access->readRouteItem($seeker, $version);

        $this::assertEquals('Q03', $routeItem->getAssessmentItemRef()->getIdentifier());
        $this::assertEquals('S01', $routeItem->getAssessmentSection()->getIdentifier());
        $this::assertEquals('P01', $routeItem->getTestPart()->getIdentifier());
        $this::assertIsInt($routeItem->getOccurence());
        $this::assertEquals(0, $routeItem->getOccurence());
        $this::assertCount(0, $routeItem->getBranchRules());
        $this::assertCount(0, $routeItem->getPreConditions());
    }

    public function testWriteRouteItem()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');

        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), ['assessmentItemRef', 'assessmentSection', 'testPart', 'outcomeDeclaration', 'responseDeclaration', 'branchRule', 'preCondition']);
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());

        // Get route item at index 2 which is the route item describing
        // item occurence 0 of Q03.
        $sessionManager = new SessionManager();
        $testSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $routeItem = $testSession->getRoute()->getRouteItemAt(2);

        $access->writeRouteItem($seeker, $routeItem);
        $stream->rewind();

        $version = $this->createVersionMock(3);
        $routeItem = $access->readRouteItem($seeker, $version);

        $this::assertEquals('Q03', $routeItem->getAssessmentItemRef()->getIdentifier());
        $this::assertEquals('S01', $routeItem->getAssessmentSection()->getIdentifier());
        $this::assertEquals('P01', $routeItem->getTestPart()->getIdentifier());
        $this::assertIsInt($routeItem->getOccurence());
        $this::assertEquals(0, $routeItem->getOccurence());
        $this::assertCount(0, $routeItem->getBranchRules());
        $this::assertCount(0, $routeItem->getPreConditions());
    }

    public function testReadPendingResponses()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset_simultaneous.xml');

        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), ['assessmentItemRef', 'assessmentSection', 'testPart', 'outcomeDeclaration', 'responseDeclaration', 'branchRule', 'preCondition']);
        $bin = '';
        $bin .= "\x01"; // variable-count = 1.
        $bin .= pack('S', 0); // response-declaration-position = 0
        $bin .= pack('S', 0) . "\x00" . "\x01" . pack('S', 7) . 'ChoiceA'; // variable-value = 'ChoiceA' (identifier)
        $bin .= pack('S', 0); // item-tree-position = 0
        $bin .= "\x00"; // occurence = 0

        $stream = new MemoryStream($bin);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());

        $pendingResponses = $access->readPendingResponses($seeker);
        $state = $pendingResponses->getState();
        $this::assertCount(1, $state);
        $this::assertInstanceOf(ResponseVariable::class, $state->getVariable('RESPONSE'));
        $this::assertEquals('ChoiceA', $state['RESPONSE']->getValue());

        $itemRef = $pendingResponses->getAssessmentItemRef();
        $this::assertEquals('Q01', $itemRef->getIdentifier());

        $this::assertEquals(0, $pendingResponses->getOccurence());
        $this::assertIsInt($pendingResponses->getOccurence());
    }

    public function testWritePendingResponses()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset_simultaneous.xml');

        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), ['assessmentItemRef', 'assessmentSection', 'testPart', 'outcomeDeclaration', 'responseDeclaration', 'branchRule', 'preCondition']);
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());

        $factory = new SessionManager();
        $session = $factory->createAssessmentTestSession($doc->getDocumentComponent());
        $session->beginTestSession();

        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));

        $store = $session->getPendingResponseStore();
        $pendingResponses = $store->getPendingResponses($doc->getDocumentComponent()->getComponentByIdentifier('Q01'));
        $access->writePendingResponses($seeker, $pendingResponses);

        $stream->rewind();
        $pendingResponses = $access->readPendingResponses($seeker);

        $state = $pendingResponses->getState();
        $this::assertEquals('ChoiceB', $state['RESPONSE']->getValue());
        $this::assertEquals('Q01', $pendingResponses->getAssessmentItemRef()->getIdentifier());
        $this::assertEquals(0, $pendingResponses->getOccurence());
        $this::assertIsInt($pendingResponses->getOccurence());
    }

    /**
     * @param int $versionNumber
     * @return QtiBinaryVersion
     * @throws ReflectionException
     */
    public function createVersionMock(int $versionNumber): QtiBinaryVersion
    {
        $version = new QtiBinaryVersion();
        $property = new ReflectionProperty(QtiBinaryVersion::class, 'version');
        $property->setAccessible(true);
        $property->setValue($version, $versionNumber);
        $property->setAccessible(false);

        return $version;
    }
}
