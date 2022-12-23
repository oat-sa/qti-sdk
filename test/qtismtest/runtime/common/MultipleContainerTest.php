<?php

namespace qtismtest\runtime\common;

use InvalidArgumentException;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtism\runtime\common\MultipleContainer;
use qtismtest\QtiSmTestCase;

/**
 * Class MultipleContainerTest
 */
class MultipleContainerTest extends QtiSmTestCase
{
    public function testCreationEmpty(): void
    {
        $container = new MultipleContainer(BaseType::BOOLEAN);
        $this::assertEquals(BaseType::BOOLEAN, $container->getBaseType());
        $this::assertCount(0, $container);
        $this::assertEquals(Cardinality::MULTIPLE, $container->getCardinality());
    }

    public function testCreationWithValues(): void
    {
        $data = [new QtiInteger(10), new QtiInteger(20), new QtiInteger(20), new QtiInteger(30), new QtiInteger(40), new QtiInteger(50)];
        $container = new MultipleContainer(BaseType::INTEGER, $data);
        $this::assertCount(6, $container);
        $this::assertEquals(BaseType::INTEGER, $container->getBaseType());
        $this::assertEquals($data, $container->getArrayCopy());
        $this::assertEquals(20, $container[1]->getValue());
    }

    public function testCreationEmptyWrongBaseType1(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $container = new MultipleContainer('invalid');
    }

    public function testCreationEmptyWrongBaseType2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $container = new MultipleContainer(14);
    }

    public function testCreationWithWrongValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $data = [new QtiPoint(20, 20)];
        $container = new MultipleContainer(BaseType::DURATION, $data);
    }

    public function testCreateFromDataModel(): void
    {
        $valueCollection = new ValueCollection();
        $valueCollection[] = new Value(new QtiPoint(10, 30), BaseType::POINT);
        $valueCollection[] = new Value(new QtiPoint(20, 40), BaseType::POINT);

        $container = MultipleContainer::createFromDataModel($valueCollection, BaseType::POINT);
        $this::assertInstanceOf(MultipleContainer::class, $container);
        $this::assertCount(2, $container);
        $this::assertEquals(BaseType::POINT, $container->getBaseType());
        $this::assertEquals(Cardinality::MULTIPLE, $container->getCardinality());
        $this::assertTrue($container->contains($valueCollection[0]->getValue()));
        $this::assertTrue($container->contains($valueCollection[1]->getValue()));
    }

    /**
     * @dataProvider validCreateFromDataModelProvider
     * @param int $baseType
     * @param ValueCollection $valueCollection
     */
    public function testCreateFromDataModelValid($baseType, ValueCollection $valueCollection): void
    {
        $container = MultipleContainer::createFromDataModel($valueCollection, $baseType);
        $this::assertInstanceOf(MultipleContainer::class, $container);
    }

    /**
     * @dataProvider invalidCreateFromDataModelProvider
     * @param int $baseType
     * @param ValueCollection $valueCollection
     */
    public function testCreateFromDataModelInvalid($baseType, ValueCollection $valueCollection): void
    {
        $this->expectException(InvalidArgumentException::class);
        $container = MultipleContainer::createFromDataModel($valueCollection, $baseType);
    }

    /**
     * @return array
     */
    public function invalidCreateFromDataModelProvider(): array
    {
        $returnValue = [];

        $valueCollection = new ValueCollection();
        $valueCollection[] = new Value(new QtiPoint(20, 30), BaseType::POINT);
        $valueCollection[] = new Value(10, BaseType::INTEGER);
        $returnValue[] = [BaseType::INTEGER, $valueCollection];

        return $returnValue;
    }

    /**
     * @return array
     */
    public function validCreateFromDataModelProvider(): array
    {
        $returnValue = [];

        $valueCollection = new ValueCollection();
        $returnValue[] = [BaseType::DURATION, $valueCollection];

        $valueCollection = new ValueCollection();
        $valueCollection[] = new Value(10, BaseType::INTEGER);
        $valueCollection[] = new Value(-20, BaseType::INTEGER);
        $returnValue[] = [BaseType::INTEGER, $valueCollection];

        return $returnValue;
    }

    public function testEquals(): void
    {
        $c1 = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(5), new QtiInteger(4), new QtiInteger(3), new QtiInteger(2), new QtiInteger(1)]);
        $c2 = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(6), new QtiInteger(7), new QtiInteger(8), new QtiInteger(5)]);
        $this::assertFalse($c1->equals($c2));
        $this::assertFalse($c2->equals($c1));
    }

    public function testEqualsTwo(): void
    {
        $c1 = new MultipleContainer(BaseType::FLOAT, [new QtiFloat(2.75), new QtiFloat(1.65)]);
        $c2 = new MultipleContainer(BaseType::FLOAT, [new QtiFloat(2.75), new QtiFloat(1.65)]);
        $this::assertTrue($c1->equals($c2));
        $this::assertTrue($c2->equals($c1));
    }

    public function testEqualsEmpty(): void
    {
        $c1 = new MultipleContainer(BaseType::FLOAT);
        $c2 = new MultipleContainer(BaseType::FLOAT);
        $this::assertTrue($c1->equals($c2));
        $this::assertTrue($c2->equals($c1));
    }

    /**
     * @dataProvider distinctProvider
     * @param MultipleContainer $originalContainer
     * @param MultipleContainer $expectedContainer
     */
    public function testDistinct(MultipleContainer $originalContainer, MultipleContainer $expectedContainer): void
    {
        $distinctContainer = $originalContainer->distinct();
        $this::assertTrue($distinctContainer->equals($expectedContainer));
        $this::assertTrue($expectedContainer->equals($distinctContainer));
    }

    /**
     * @return array
     */
    public function distinctProvider(): array
    {
        return [
            [new MultipleContainer(BaseType::INTEGER), new MultipleContainer(BaseType::INTEGER)],
            [new MultipleContainer(BaseType::INTEGER, [new QtiInteger(5), new QtiInteger(5)]), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(5)])],
            [new MultipleContainer(BaseType::INTEGER, [new QtiInteger(5), null, new QtiInteger(5)]), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(5), null])],
            [new MultipleContainer(BaseType::INTEGER, [new QtiInteger(5), null, new QtiInteger(5), null]), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(5), null])],
        ];
    }
}
