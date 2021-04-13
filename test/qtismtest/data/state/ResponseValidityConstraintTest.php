<?php

namespace qtismtest\data\state;

use InvalidArgumentException;
use qtism\data\state\AssociationValidityConstraint;
use qtism\data\state\AssociationValidityConstraintCollection;
use qtism\data\state\ResponseValidityConstraint;
use qtismtest\QtiSmTestCase;

/**
 * Class ResponseValidityConstraintTest
 */
class ResponseValidityConstraintTest extends QtiSmTestCase
{
    /**
     * @dataProvider successfulInstantiationBasicProvider
     *
     * @param int $minConstraint
     * @param int $maxConstraint
     * @param string $patternMask
     */
    public function testSuccessfulInstantiationBasic($minConstraint, $maxConstraint, $patternMask = '')
    {
        $responseValidityConstraint = new ResponseValidityConstraint('RESPONSE', $minConstraint, $maxConstraint, $patternMask);
        $this::assertEquals('RESPONSE', $responseValidityConstraint->getResponseIdentifier());
        $this::assertEquals($minConstraint, $responseValidityConstraint->getMinConstraint());
        $this::assertEquals($maxConstraint, $responseValidityConstraint->getMaxConstraint());
        $this::assertEquals($patternMask, $responseValidityConstraint->getPatternMask());
    }

    /**
     * @return array
     */
    public function successfulInstantiationBasicProvider()
    {
        return [
            [0, 1],
            [0, 0],
            [2, 2],
            [0, 2],
            [1, 0],
            [1, 0, '/.+/iu'],
        ];
    }

    /**
     * @dataProvider unsuccessfulInstantiationProvider
     *
     * @param string $responseIdentifier
     * @param int $minConstraint
     * @param int $maxConstraint
     * @param string $msg
     * @param string $patternMask
     */
    public function testUnsuccessfulInstantiation($responseIdentifier, $minConstraint, $maxConstraint, $msg, $patternMask = '')
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);
        $responseValidityConstraint = new ResponseValidityConstraint($responseIdentifier, $minConstraint, $maxConstraint, $patternMask);
    }

    /**
     * @return array
     */
    public function unsuccessfulInstantiationProvider()
    {
        return [
            ['', 0, 0, "The 'responseIdentifier' argument must be a non-empty string."],
            ['RESPONSE', 3, 2, "The 'maxConstraint' argument must be greather or equal to than the 'minConstraint' in place."],
            ['RESPONSE', -1, 2, "The 'minConstraint' argument must be a non negative (>= 0) integer."],
            ['RESPONSE', 2, -4, "The 'maxConstraint' argument must be a non negative (>= 0) integer."],
            ['RESPONSE', 0, 1, "The 'patternMask' argument must be a string, 'integer' given.", 25],
        ];
    }

    public function testAssociations()
    {
        $responseValidityConstraint = new ResponseValidityConstraint('RESPONSE', 0, 0);
        $responseValidityConstraint->addAssociationValidityConstraint(
            new AssociationValidityConstraint('IDENTIFIER', 0, 1)
        );

        $this::assertCount(1, $responseValidityConstraint->getAssociationValidityConstraints());
        $this::assertEquals('IDENTIFIER', $responseValidityConstraint->getAssociationValidityConstraints()[0]->getIdentifier());

        $responseValidityConstraint->removeAssociationValidityConstraint($responseValidityConstraint->getAssociationValidityConstraints()[0]);
        $this::assertCount(0, $responseValidityConstraint->getAssociationValidityConstraints());

        $associationValidityConstraints = new AssociationValidityConstraintCollection(
            [
                new AssociationValidityConstraint('MYID', 0, 1),
            ]
        );
        $responseValidityConstraint->setAssociationValidityConstraints($associationValidityConstraints);
        $this::assertCount(1, $responseValidityConstraint->getAssociationValidityConstraints());
        $this::assertEquals('MYID', $responseValidityConstraint->getAssociationValidityConstraints()[0]->getIdentifier());
    }
}
