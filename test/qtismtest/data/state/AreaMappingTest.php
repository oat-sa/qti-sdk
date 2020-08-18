<?php

namespace qtismtest\data\state;

use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\state\AreaMapEntry;
use qtism\data\state\AreaMapEntryCollection;
use qtism\data\state\AreaMapping;
use qtismtest\QtiSmTestCase;

class AreaMappingTest extends QtiSmTestCase
{
    public function testCreateNoAreaMapEntries()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'An AreaMapping object must contain at least one AreaMapEntry object. none given.'
        );

        $mapping = new AreaMapping(
            new AreaMapEntryCollection(
                []
            )
        );
    }

    public function testSetLowerBoundWrongType()
    {
        $mapping = new AreaMapping(
            new AreaMapEntryCollection(
                [
                    new AreaMapEntry(QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1]), 0.0),
                ]
            )
        );

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The lowerBound argument must be a float or false if no lower bound, 'boolean' given."
        );

        $mapping->setLowerBound(true);
    }

    public function testSetUpperBoundWrongType()
    {
        $mapping = new AreaMapping(
            new AreaMapEntryCollection(
                [
                    new AreaMapEntry(QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1]), 0.0),
                ]
            )
        );

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The upperBound argument must be a float or false if no upper bound, 'boolean' given."
        );

        $mapping->setUpperBound(true);
    }

    public function testSetDefaultValueWrongType()
    {
        $mapping = new AreaMapping(
            new AreaMapEntryCollection(
                [
                    new AreaMapEntry(QtiShape::RECT, new QtiCoords(QtiShape::RECT, [0, 0, 1, 1]), 0.0),
                ]
            )
        );

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The defaultValue argument must be a numeric value, 'boolean'."
        );

        $mapping->setDefaultValue(true);
    }
}
