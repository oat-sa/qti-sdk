<?php

namespace qtismtest\data;

use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\P;
use qtism\data\QtiComponentCollection;
use qtismtest\QtiSmTestCase;
use stdClass;

class QtiComponentCollectionTest extends QtiSmTestCase
{
    public function testInsertWrongType()
    {
        $collection = new QtiComponentCollection();

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "QtiComponentCollection class only accept QtiComponent objects, 'stdClass' given."
        );

        $collection[] = new stdClass();
    }

    public function testInsertWrongCall()
    {
        $collection = new QtiComponentCollection();

        $this->setExpectedException(
            \RuntimeException::class,
            "QtiComponentCollection must be used as a bag (specific key 'index' given)."
        );

        $collection['index'] = new stdClass();
    }

    public function testExclusivelyContainsComponentsWithClassNameNotFoundRecursive()
    {
        $collection = new QtiComponentCollection();
        $component = new P();
        $component->setContent(new InlineCollection([
            new TextRun('content'),
        ]));

        $collection[] = $component;

        $this->assertFalse($collection->exclusivelyContainsComponentsWithClassName('p', true));
    }
}
