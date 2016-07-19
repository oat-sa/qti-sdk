<?php
namespace qtismtest\data\state;

use qtismtest\QtiSmTestCase;
use qtism\data\state\AssociationValidityConstraint;

class AssociationValidityConstraintTest extends QtiSmTestCase {
    
    /**
     * @dataProvider successfulInstantiationProvider
     * 
     * @param integer $identifier
     * @param integer $minConstraint
     * @param integer $maxConstraint
     */
	public function testSuccessfulInstantiation($minConstraint, $maxConstraint) {
        $associationValidityConstraint = new AssociationValidityConstraint('IDENTIFIER', $minConstraint, $maxConstraint);
        $this->assertEquals('IDENTIFIER', $associationValidityConstraint->getIdentifier());
        $this->assertEquals($minConstraint, $associationValidityConstraint->getMinConstraint());
        $this->assertEquals($maxConstraint, $associationValidityConstraint->getMaxConstraint());
    }
    
    public function successfulInstantiationProvider() {
        return array(
            array(0, 1),
            array(0, 0),
            array(2, 2),
            array(0, 2),
            array(1, 0)
        );
    }
    
    /**
     * @dataProvider unsuccessfulInstantiationProvider
     * 
     * @param string $identifier
     * @param integer $minConstraint
     * @param integer $maxConstraint
     * @param string $msg
     */
    public function testUnsuccessfulInstantiation($identifier, $minConstraint, $maxConstraint, $msg) {
        $this->setExpectedException('\\InvalidArgumentException', $msg);
        $associationValidityConstraint = new AssociationValidityConstraint($identifier, $minConstraint, $maxConstraint);
    }
    
    public function unsuccessfulInstantiationProvider() {
        return array(
            array('', 0, 0, "The 'identifier' argument must be a non-empty string."),
            array('IDENTIFIER', 3, 2, "The 'maxConstraint' argument must be greather or equal to than the 'minConstraint' in place."),
            array('IDENTIFIER', -1, 2, "The 'minConstraint' argument must be a non negative (>= 0) integer."),
            array('IDENTIFIER', 2, -4, "The 'maxConstraint' argument must be a non negative (>= 0) integer."),
        );
    }
}
