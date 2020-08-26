<?php

namespace qtismtest\data\state;

use InvalidArgumentException;
use qtism\data\state\MapEntry;
use qtism\data\state\MapEntryCollection;
use qtism\data\state\Mapping;
use qtismtest\QtiSmTestCase;

/**
 * Class MappingTest
 */
class MappingTest extends QtiSmTestCase
{
    public function testCreateNoMapEntries()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A Mapping object must contain at least one MapEntry object, none given.');

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

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'lowerBound' attribute must be a float or false, 'boolean' given.");

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

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'upperBound' argument must be a float or false, 'boolean' given.");

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

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'defaultValue' argument must be a numeric value, 'boolean' given.");

        $mapping->setDefaultValue(true);
    }
}
