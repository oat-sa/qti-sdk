<?php
namespace qtismtest\data\state;

use qtismtest\QtiSmTestCase;
use qtism\data\state\ResponseValidityConstraint;

class ResponseValidityConstraintTest extends QtiSmTestCase {
    
    /**
     * @dataProvider successfulInstantiationProvider
     * 
     * @param integer $minConstraint
     * @param integer $maxConstraint
     */
	public function testSuccessfulInstantiation($minConstraint, $maxConstraint) {
        $responseValidityConstraint = new ResponseValidityConstraint('RESPONSE', $minConstraint, $maxConstraint);
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
     * @param string $responseIdentifier
     * @param integer $minConstraint
     * @param integer $maxConstraint
     * @param string $msg
     */
    public function testUnsuccessfulInstantiation($responseIdentifier, $minConstraint, $maxConstraint, $msg) {
        $this->setExpectedException('\\InvalidArgumentException', $msg);
        $responseValidityConstraint = new ResponseValidityConstraint($responseIdentifier, $minConstraint, $maxConstraint);
    }
    
    public function unsuccessfulInstantiationProvider() {
        return array(
            array('', 0, 0, "The 'responseIdentifier' argument must be a non-empty string."),
            array('RESPONSE', 3, 2, "The 'maxConstraint' argument must be greather or equal to than the 'minConstraint' in place."),
            array('RESPONSE', -1, 2, "The 'minConstraint' argument must be a non negative (>= 0) integer."),
            array('RESPONSE', 2, -4, "The 'maxConstraint' argument must be a non negative (>= 0) integer.")
        );
    }
}
