<?php

namespace qtismtest\data;

use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Sum;
use qtism\data\QtiComponentIterator;
use qtism\data\storage\xml\XmlCompactDocument;
use qtismtest\QtiSmTestCase;

/**
 * Class QtiComponentIteratorTest
 */
class QtiComponentIteratorTest extends QtiSmTestCase
{
    public function testSimple()
    {
        $baseValues = new ExpressionCollection();
        $baseValues[] = new BaseValue(BaseType::FLOAT, 0.5);
        $baseValues[] = new BaseValue(BaseType::INTEGER, 25);
        $baseValues[] = new BaseValue(BaseType::FLOAT, 0.5);
        $sum = new Sum($baseValues);

        $iterator = new QtiComponentIterator($sum);

        $iterations = 0;
        foreach ($iterator as $k => $i) {
            $this::assertSame($sum, $iterator->parent());
            $this::assertSame($baseValues[$iterations], $i);
            $this::assertSame($sum, $iterator->getCurrentContainer());
            $this::assertEquals($k, $i->getQtiClassName());
            $iterations++;
        }

        $this::assertSame(null, $iterator->parent());
    }

    public function testNoChildComponents()
    {
        $baseValue = new BaseValue(BaseType::FLOAT, 10);
        $iterator = new QtiComponentIterator($baseValue);

        $this::assertFalse($iterator->valid());
        $this::assertSame($iterator->current(), null);

        // Just try to iterate again, just for fun...
        $iterator->next();
        $this::assertFalse($iterator->valid());
        $this::assertTrue($iterator->current() === null);
    }

    public function testAvoidRecursions()
    {
        $baseValues = new ExpressionCollection();
        $baseValues[] = new BaseValue(BaseType::FLOAT, 0.5);
        $baseValues[] = new BaseValue(BaseType::INTEGER, 25);
        $baseValues[] = new BaseValue(BaseType::FLOAT, 0.7);
        $baseValues[] = $baseValues[0]; // This could create a recursion issue.
        $baseValues[] = new BaseValue(BaseType::INTEGER, 0);

        $iterator = new QtiComponentIterator(new Sum($baseValues));

        $iterations = 0;
        foreach ($iterator as $k => $i) {
            $iterations++;
        }

        $this::assertEquals(4, $iterations);
    }

    public function testClassSelection()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');

        $iterator = new QtiComponentIterator($doc->getDocumentComponent(), ['responseProcessing']);
        $i = 0;

        foreach ($iterator as $responseProcessing) {
            $this::assertEquals('responseProcessing', $iterator->key());
            $i++;
        }

        $this::assertEquals(7, $i);
    }

    public function testOneChildComponents()
    {
        $baseValues = new ExpressionCollection();
        $baseValues[] = new BaseValue(BaseType::FLOAT, 0.5);
        $sum = new Sum($baseValues);
        $iterator = new QtiComponentIterator($sum);

        $iterations = 0;
        foreach ($iterator as $k => $i) {
            $iterations++;
        }
        $this::assertEquals(1, $iterations);
    }
}
