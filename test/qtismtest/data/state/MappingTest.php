<?php

namespace qtismtest\data\state;

use qtism\data\state\MapEntry;
use qtism\data\state\MapEntryCollection;
use qtism\data\state\Mapping;
use qtismtest\QtiSmTestCase;

class MappingTest extends QtiSmTestCase
{
    public function testCreateNoMapEntries()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "A Mapping object must contain at least one MapEntry object, none given."
        );

        $mapping = new Mapping(
            new MapEntryCollection(
                []
            )
        );
    }

    public function testSetLowerBoundWrongType()
    {
        $mapping = new Mapping(
            new MapEntryCollection(
                [
                    new MapEntry('key1', 0.0),
                ]
            )
        );

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'lowerBound' attribute must be a float or false, 'boolean' given."
        );

        $mapping->setLowerBound(true);
    }

    public function testSetUpperBoundWrongType()
    {
        $mapping = new Mapping(
            new MapEntryCollection(
                [
                    new MapEntry('key1', 0.0),
                ]
            )
        );

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'upperBound' argument must be a float or false, 'boolean' given."
        );

        $mapping->setUpperBound(true);
    }

    public function testSetDefaultValueWrongType()
    {
        $mapping = new Mapping(
            new MapEntryCollection(
                [
                    new MapEntry('key1', 0.0),
                ]
            )
        );

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'defaultValue' argument must be a numeric value, 'boolean' given."
        );

        $mapping->setDefaultValue(true);
    }
}
