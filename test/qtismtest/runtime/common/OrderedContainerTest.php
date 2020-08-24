<?php

namespace qtismtest\runtime\common;

use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiUri;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\OrderedContainer;
use qtismtest\QtiSmTestCase;

class OrderedContainerTest extends QtiSmTestCase
{
    /**
     * @dataProvider equalsValidProvider
     * @param OrderedContainer $containerA
     * @param OrderedContainer $containerB
     */
    public function testEqualsValid(OrderedContainer $containerA, OrderedContainer $containerB)
    {
        $this->assertTrue($containerA->equals($containerB));
        $this->assertTrue($containerB->equals($containerA));
    }

    /**
     * @dataProvider equalsInvalidProvider
     * @param OrderedContainer $containerA
     * @param OrderedContainer $containerB
     */
    public function testEqualsInvalid(OrderedContainer $containerA, OrderedContainer $containerB)
    {
        $this->assertFalse($containerA->equals($containerB));
        $this->assertFalse($containerB->equals($containerA));
    }

    public function testCreationEmpty()
    {
        $container = new OrderedContainer(BaseType::INTEGER);
        $this->assertEquals(0, count($container));
        $this->assertEquals(BaseType::INTEGER, $container->getBaseType());
        $this->assertEquals(Cardinality::ORDERED, $container->getCardinality());
    }

    public function equalsValidProvider()
    {
        return [
            [new OrderedContainer(BaseType::INTEGER), new OrderedContainer(BaseType::INTEGER)],
            [new OrderedContainer(BaseType::INTEGER, [new QtiInteger(20)]), new OrderedContainer(BaseType::INTEGER, [new QtiInteger(20)])],
            [new OrderedContainer(BaseType::URI, [new QtiUri('http://www.taotesting.com'), new QtiUri('http://www.tao.lu')]), new OrderedContainer(BaseType::URI, [new QtiUri('http://www.taotesting.com'), new QtiUri('http://www.tao.lu')])],
            [new OrderedContainer(BaseType::PAIR, [new QtiPair('abc', 'def')]), new OrderedContainer(BaseType::PAIR, [new QtiPair('def', 'abc')])],
        ];
    }

    public function equalsInvalidProvider()
    {
        return [
            [new OrderedContainer(BaseType::INTEGER, [new QtiInteger(20)]), new OrderedContainer(BaseType::INTEGER, [new QtiInteger(30)])],
            [new OrderedContainer(BaseType::URI, [new QtiUri('http://www.taotesting.com'), new QtiUri('http://www.tao.lu')]), new OrderedContainer(BaseType::URI, [new QtiUri('http://www.tao.lu'), new QtiUri('http://www.taotesting.com')])],
            [new OrderedContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('abc', 'def')]), new OrderedContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('def', 'abc')])],
        ];
    }

    public function testEqualsNull()
    {
        $container = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10)]);
        $this->assertFalse($container->equals(null));
    }
}
