<?php

namespace qtismtest\data\state;

use InvalidArgumentException;
use qtism\data\state\AssociationValidityConstraint;
use qtismtest\QtiSmTestCase;

class AssociationValidityConstraintTest extends QtiSmTestCase
{
    /**
     * @dataProvider successfulInstantiationProvider
     *
     * @param int $identifier
     * @param int $minConstraint
     * @param int $maxConstraint
     */
    public function testSuccessfulInstantiation($minConstraint, $maxConstraint)
    {
        $associationValidityConstraint = new AssociationValidityConstraint('IDENTIFIER', $minConstraint, $maxConstraint);
        $this->assertEquals('IDENTIFIER', $associationValidityConstraint->getIdentifier());
        $this->assertEquals($minConstraint, $associationValidityConstraint->getMinConstraint());
        $this->assertEquals($maxConstraint, $associationValidityConstraint->getMaxConstraint());
    }

    public function successfulInstantiationProvider()
    {
        return [
            [0, 1],
            [0, 0],
            [2, 2],
            [0, 2],
            [1, 0],
        ];
    }

    /**
     * @dataProvider unsuccessfulInstantiationProvider
     *
     * @param string $identifier
     * @param int $minConstraint
     * @param int $maxConstraint
     * @param string $msg
     */
    public function testUnsuccessfulInstantiation($identifier, $minConstraint, $maxConstraint, $msg)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);
        $associationValidityConstraint = new AssociationValidityConstraint($identifier, $minConstraint, $maxConstraint);
    }

    public function unsuccessfulInstantiationProvider()
    {
        return [
            ['', 0, 0, "The 'identifier' argument must be a non-empty string."],
            ['IDENTIFIER', 3, 2, "The 'maxConstraint' argument must be greather or equal to than the 'minConstraint' in place."],
            ['IDENTIFIER', -1, 2, "The 'minConstraint' argument must be a non negative (>= 0) integer."],
            ['IDENTIFIER', 2, -4, "The 'maxConstraint' argument must be a non negative (>= 0) integer."],
        ];
    }
}
