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
    protected function getContainer()
    {
        return $this->container;
    }

    public function setUp()
    {
        parent::setUp();
        $this->container = new Container();
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->container);
    }

    /**
     * @dataProvider validValueProvider
     * @param mixed $value
     */
    public function testAddValid($value)
    {
        // Try to test any QTI runtime model compliant data
        // for addition in the container.
        $container = $this->getContainer();
        $container[] = $value;

        $this->assertTrue($container->contains($value));
    }

    /**
     * @dataProvider invalidValueProvider
     * @param mixed $value
     */
    public function testAddInvalid($value)
    {
        $container = $this->getContainer();

        $this->expectException(InvalidArgumentException::class);
        $container[] = $value;
    }

    public function testIsNull()
    {
        $container = $this->getContainer();

        $this->assertTrue($container->isNull());

        $container[] = new QtiInteger(1);
        $this->assertFalse($container->isNull());
    }

    /**
     * @dataProvider validValueCollectionProvider
     * @param ValueCollection $valueCollection
     */
    public function testCreateFromDataModelValid(ValueCollection $valueCollection)
    {
        $container = Container::createFromDataModel($valueCollection);
        $this->assertInstanceOf(Container::class, $container);
    }

    /**
     * @dataProvider validEqualsPrimitiveProvider
     * @param Container $a
     * @param Container $b
     */
    public function testEqualsPrimitiveValid(Container $a, Container $b)
    {
        $this->assertTrue($a->equals($b));
    }

    /**
     * @dataProvider invalidEqualsPrimitiveProvider
     * @param Container $a
     * @param mixed $b
     */
    public function testEqualsPrimitiveInvalid(Container $a, $b)
    {
        $this->assertFalse($a->equals($b));
    }

    /**
     * @dataProvider occurencesProvider
     * @param Container $container
     * @param mixed $lookup
     * @param mixed $expected
     */
    public function testOccurences(Container $container, $lookup, $expected)
    {
        $this->assertEquals($expected, $container->occurences($lookup));
    }

    /**
     * @return array
     */
    public function validValueProvider()
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
    public function invalidValueProvider()
    {
        return [
            [new DateTime()],
            [[]],
        ];
    }

    /**
     * @return array
     */
    public function validEqualsPrimitiveProvider()
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
    public function invalidEqualsPrimitiveProvider()
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
    public function occurencesProvider()
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
    public function validValueCollectionProvider()
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

    public function testClone()
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
        $this->assertFalse($clone === $container);
        $this->assertFalse($clone[0] === $container[0]);
        $this->assertFalse($clone[1] === $container[1]);
        $this->assertFalse($clone[2] === $container[2]);
        $this->assertFalse($clone[3] === $container[3]);
        $this->assertFalse($clone[4] === $container[4]);
        $this->assertFalse($clone[5] === $container[5]);
        $this->assertFalse($clone[6] === $container[6]);
        $this->assertFalse($clone[7] === $container[7]);
    }

    public function testContains()
    {
        $pair = new QtiPair('A', 'B');
        $container = $this->getContainer();
        $container[] = $pair;
        $this->assertTrue($container->contains(new QtiPair('A', 'B')));
    }

    public function testContains2()
    {
        $identifier = new QtiIdentifier('test');
        $container = $this->getContainer();
        $container[] = $identifier;
        $this->assertTrue($container->contains(new QtiIdentifier('test')));
    }

    /**
     * @dataProvider toStringProvider
     *
     * @param Container $container
     * @param string $expected The expected result of a __toString() call.
     */
    public function testToString(Container $container, $expected)
    {
        $this->assertEquals($expected, $container->__toString());
    }

    /**
     * @return array
     */
    public function toStringProvider()
    {
        $returnValue = [];

        $returnValue[] = [new Container(), '[]'];
        $returnValue[] = [new Container([new QtiInteger(10)]), '[10]'];
        $returnValue[] = [new Container([new QtiBoolean(true), new QtiBoolean(false)]), '[true; false]'];
        $returnValue[] = [new Container([new QtiDuration('P2DT2S'), new QtiPoint(10, 15), new QtiPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiString('String!')]), '[P2DT2S; 10 15; A B; C D; \'String!\']'];

        return $returnValue;
    }

    public function testAlwaysMultipleCardinality()
    {
        $container = new Container();
        $this->assertEquals(Cardinality::MULTIPLE, $container->getCardinality());
    }

    public function testDetach()
    {
        $object = new QtiBoolean(true);
        $container = new Container([$object]);

        $this->assertCount(1, $container);

        $container->detach($object);

        $this->assertCount(0, $container);
    }

    public function testDetachNotFound()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The object you want to detach could not be found in the collection.');

        $object = new QtiBoolean(true);
        $container = new Container([$object]);
        $container->detach(new QtiBoolean(false));
    }

    public function testDetachNotObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("You can only detach 'objects' into an AbstractCollection, 'NULL' given.");
        $container = new Container();
        $container->detach(null);
    }

    public function testReplaceNotFound()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The object you want to replace could not be found.');

        $object = new QtiBoolean(true);
        $container = new Container([$object]);
        $container->replace(new QtiBoolean(false), new QtiBoolean(false));
    }

    public function testReplaceToReplaceNotObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("You can only replace 'objects' into an AbstractCollection, 'NULL' given.");

        $object = new QtiBoolean(true);
        $container = new Container([$object]);
        $container->replace(null, new QtiBoolean(false));
    }

    public function testReplaceReplacementNotObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("You can only replace 'objects' into an AbstractCollection, 'NULL' given.");

        $object = new QtiBoolean(true);
        $container = new Container([$object]);
        $container->replace(new QtiBoolean(false), null);
    }

    public function testDiffNotCompliantTypes()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Difference may apply only on two collections of the same type.');

        $container1 = new Container();
        $container2 = new StringCollection();
        $container1->diff($container2);
    }

    public function testIntersectNotCompliantTypes()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Intersection may apply only on two collections of the same type.');

        $container1 = new Container();
        $container2 = new StringCollection();
        $container1->intersect($container2);
    }
}
