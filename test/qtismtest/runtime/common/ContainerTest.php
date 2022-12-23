<?php

namespace qtismtest\runtime\common;

use DateTime;
use Exception;
use InvalidArgumentException;
use qtism\common\collections\StringCollection;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtism\runtime\common\Container;
use qtismtest\QtiSmTestCase;
use UnexpectedValueException;

/**
 * Class ContainerTest
 */
class ContainerTest extends QtiSmTestCase
{
    /**
     * A Container object reset at each test.
     *
     * @var Container
     */
    private $container;

    /**
     * Get the Container object.
     *
     * @return Container A Container object.
     */
    protected function getContainer(): Container
    {
        return $this->container;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->container);
    }

    /**
     * @dataProvider validValueProvider
     * @param mixed $value
     */
    public function testAddValid($value): void
    {
        // Try to test any QTI runtime model compliant data
        // for addition in the container.
        $container = $this->getContainer();
        $container[] = $value;

        $this::assertTrue($container->contains($value));
    }

    /**
     * @dataProvider invalidValueProvider
     * @param mixed $value
     */
    public function testAddInvalid($value): void
    {
        $container = $this->getContainer();

        $this->expectException(InvalidArgumentException::class);
        $container[] = $value;
    }

    public function testIsNull(): void
    {
        $container = $this->getContainer();

        $this::assertTrue($container->isNull());

        $container[] = new QtiInteger(1);
        $this::assertFalse($container->isNull());
    }

    /**
     * @dataProvider validValueCollectionProvider
     * @param ValueCollection $valueCollection
     */
    public function testCreateFromDataModelValid(ValueCollection $valueCollection): void
    {
        $container = Container::createFromDataModel($valueCollection);
        $this::assertInstanceOf(Container::class, $container);
    }

    /**
     * @dataProvider validEqualsPrimitiveProvider
     * @param Container $a
     * @param Container $b
     */
    public function testEqualsPrimitiveValid(Container $a, Container $b): void
    {
        $this::assertTrue($a->equals($b));
    }

    /**
     * @dataProvider invalidEqualsPrimitiveProvider
     * @param Container $a
     * @param mixed $b
     */
    public function testEqualsPrimitiveInvalid(Container $a, $b): void
    {
        $this::assertFalse($a->equals($b));
    }

    /**
     * @dataProvider occurencesProvider
     * @param Container $container
     * @param mixed $lookup
     * @param mixed $expected
     */
    public function testOccurences(Container $container, $lookup, $expected): void
    {
        $this::assertEquals($expected, $container->occurences($lookup));
    }

    /**
     * @return array
     */
    public function validValueProvider(): array
    {
        return [
            [new QtiInteger(25)],
            [new QtiFloat(25.3)],
            [new QtiInteger(0)],
            [new QtiString('')],
            [new QtiString('super')],
            [new QtiBoolean(true)],
            [new QtiBoolean(false)],
            [new QtiDuration('P1D')],
            [new QtiPoint(20, 20)],
            [new QtiPair('A', 'B')],
            [new QtiDirectedPair('C', 'D')],
            [null],
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function invalidValueProvider(): array
    {
        return [
            [new DateTime()],
            [[]],
        ];
    }

    /**
     * @return array
     */
    public function validEqualsPrimitiveProvider(): array
    {
        return [
            [new Container([new QtiBoolean(true), new QtiBoolean(false)]), new Container([new QtiBoolean(false), new QtiBoolean(true)])],
            [new Container([new QtiInteger(14), new QtiInteger(13)]), new Container([new QtiInteger(13), new QtiInteger(14)])],
            [new Container([null]), new Container([null])],
            [new Container([new QtiInteger(0)]), new Container([new QtiInteger(0)])],
            [new Container([new QtiString('string')]), new Container([new QtiString('string')])],
            [new Container([new QtiFloat(14.5)]), new Container([new QtiFloat(14.5)])],
            [new Container([new QtiString('string1'), new QtiString('string2')]), new Container([new QtiString('string1'), new QtiString('string2')])],
            [new Container(), new Container()],
        ];
    }

    /**
     * @return array
     */
    public function invalidEqualsPrimitiveProvider(): array
    {
        return [
            [new Container([new QtiInteger(14)]), new Container([new QtiInteger(13)])],
            [new Container([new QtiInteger(14)]), new Container([new QtiString('string')])],
            [new Container([null]), new Container([new QtiInteger(0)])],
            [new Container(), new Container([new QtiInteger(13)])],
            [new Container([new QtiBoolean(true)]), new QtiBoolean(true)],
        ];
    }

    /**
     * @return array
     */
    public function occurencesProvider(): array
    {
        return [
            [new Container([new QtiInteger(15)]), new QtiInteger(15), 1],
            [new Container([new QtiFloat(14.3)]), new QtiFloat(14.3), 1],
            [new Container([new QtiBoolean(true)]), new QtiBoolean(true), 1],
            [new Container([new QtiBoolean(false)]), new QtiBoolean(false), 1],
            [new Container([new QtiString('string')]), new QtiString('string'), 1],
            [new Container([new QtiInteger(0)]), new QtiInteger(0), 1],
            [new Container([null]), null, 1],
            [new Container([new QtiInteger(15), new QtiString('string'), new QtiInteger(15)]), new QtiInteger(15), 2],
            [new Container([new QtiFloat(14.3), new QtiInteger(143), new QtiFloat(14.3)]), new QtiFloat(14.3), 2],
            [new Container([new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(false)]), new QtiBoolean(false), 2],
            [new Container([new QtiString('string'), new QtiInteger(2), new QtiString('str'), new QtiString('string'), new QtiString('string')]), new QtiString('string'), 3],
            [new Container([new QtiString('null'), null]), null, 1],
            [new Container([new QtiInteger(14), new QtiInteger(15), new QtiInteger(16)]), true, 0],
            [new Container([new QtiString('string'), new QtiInteger(1), new QtiBoolean(true), new QtiFloat(14.3), new QtiPoint(20, 20), new QtiPoint(20, 21)]), new QtiPoint(20, 20), 1],
            [new Container([null]), new QtiInteger(1), 0],
        ];
    }

    /**
     * @return array
     */
    public function validValueCollectionProvider(): array
    {
        $returnValue = [];

        $valueCollection = new ValueCollection();
        $returnValue[] = [$valueCollection];

        $valueCollection = new ValueCollection();
        $valueCollection[] = new Value(15, BaseType::INTEGER);
        $valueCollection[] = new Value('string', BaseType::STRING);
        $valueCollection[] = new Value(true, BaseType::BOOLEAN);
        $returnValue[] = [$valueCollection];

        return $returnValue;
    }

    public function testClone(): void
    {
        $container = $this->getContainer();
        $container[] = new QtiPoint(10, 20);
        $container[] = new QtiDuration('P2D'); // 2 days.
        $container[] = new QtiPair('A', 'B');
        $container[] = new QtiDirectedPair('C', 'D');
        $container[] = new QtiInteger(20);
        $container[] = new QtiFloat(20.1);
        $container[] = new QtiBoolean(true);
        $container[] = new QtiString('String!');

        $clone = clone $container;
        $this::assertNotSame($clone, $container);
        $this::assertNotSame($clone[0], $container[0]);
        $this::assertNotSame($clone[1], $container[1]);
        $this::assertNotSame($clone[2], $container[2]);
        $this::assertNotSame($clone[3], $container[3]);
        $this::assertNotSame($clone[4], $container[4]);
        $this::assertNotSame($clone[5], $container[5]);
        $this::assertNotSame($clone[6], $container[6]);
        $this::assertNotSame($clone[7], $container[7]);
    }

    public function testContains(): void
    {
        $pair = new QtiPair('A', 'B');
        $container = $this->getContainer();
        $container[] = $pair;
        $this::assertTrue($container->contains(new QtiPair('A', 'B')));
    }

    public function testContains2(): void
    {
        $identifier = new QtiIdentifier('test');
        $container = $this->getContainer();
        $container[] = $identifier;
        $this::assertTrue($container->contains(new QtiIdentifier('test')));
    }

    /**
     * @dataProvider toStringProvider
     *
     * @param Container $container
     * @param string $expected The expected result of a __toString() call.
     */
    public function testToString(Container $container, $expected): void
    {
        $this::assertEquals($expected, $container->__toString());
    }

    /**
     * @return array
     */
    public function toStringProvider(): array
    {
        $returnValue = [];

        $returnValue[] = [new Container(), '[]'];
        $returnValue[] = [new Container([new QtiInteger(10)]), '[10]'];
        $returnValue[] = [new Container([new QtiBoolean(true), new QtiBoolean(false)]), '[true; false]'];
        $returnValue[] = [new Container([new QtiDuration('P2DT2S'), new QtiPoint(10, 15), new QtiPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiString('String!')]), '[P2DT2S; 10 15; A B; C D; \'String!\']'];

        return $returnValue;
    }

    /**
     * @dataProvider invalidDatatypeProvider
     *
     * @param mixed $value
     * @param string $expectedMsg
     */
    public function testInvalidDatatype($value, $expectedMsg): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMsg);
        $container = new Container([$value]);
    }

    /**
     * @return array
     */
    public function invalidDatatypeProvider(): array
    {
        $message = 'A value is not compliant with the QTI runtime model datatypes: Null, QTI Boolean, QTI Coords, QTI DirectedPair, QTI Duration, QTI File, QTI Float, QTI Identifier, QTI Integer, QTI IntOrIdentifier, QTI Pair, QTI Point, QTI String, QTI Uri. "%s" given.';

        return [
            [10, sprintf($message, 'integer')],
            [12.2, sprintf($message, 'double')],
            ['str', sprintf($message, 'string')],
            [true, sprintf($message, 'boolean')],
            [[], sprintf($message, 'array')],
            [new Container(), sprintf($message, Container::class)],
        ];
    }

    public function testAlwaysMultipleCardinality(): void
    {
        $container = new Container();
        $this::assertEquals(Cardinality::MULTIPLE, $container->getCardinality());
    }

    public function testDetach(): void
    {
        $object = new QtiBoolean(true);
        $container = new Container([$object]);

        $this::assertCount(1, $container);

        $container->detach($object);

        $this::assertCount(0, $container);
    }

    public function testDetachNotFound(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The object you want to detach could not be found in the collection.');

        $object = new QtiBoolean(true);
        $container = new Container([$object]);
        $container->detach(new QtiBoolean(false));
    }

    public function testDetachNotObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("You can only detach 'objects' into an AbstractCollection, 'NULL' given.");
        $container = new Container();
        $container->detach(null);
    }

    public function testReplaceNotFound(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The object you want to replace could not be found.');

        $object = new QtiBoolean(true);
        $container = new Container([$object]);
        $container->replace(new QtiBoolean(false), new QtiBoolean(false));
    }

    public function testReplaceToReplaceNotObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("You can only replace 'objects' into an AbstractCollection, 'NULL' given.");

        $object = new QtiBoolean(true);
        $container = new Container([$object]);
        $container->replace(null, new QtiBoolean(false));
    }

    public function testReplaceReplacementNotObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("You can only replace 'objects' into an AbstractCollection, 'NULL' given.");

        $object = new QtiBoolean(true);
        $container = new Container([$object]);
        $container->replace(new QtiBoolean(false), null);
    }

    public function testMergeNotCompliantTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only collections with compliant types can be merged ("qtism\runtime\common\Container" vs "qtism\common\collections\StringCollection").');

        $container1 = new Container();
        $container2 = new StringCollection();
        $container1->merge($container2);
    }

    public function testDiffNotCompliantTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Difference may apply only on two collections of the same type.');

        $container1 = new Container();
        $container2 = new StringCollection();
        $container1->diff($container2);
    }

    public function testIntersectNotCompliantTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Intersection may apply only on two collections of the same type.');

        $container1 = new Container();
        $container2 = new StringCollection();
        $container1->intersect($container2);
    }
}
