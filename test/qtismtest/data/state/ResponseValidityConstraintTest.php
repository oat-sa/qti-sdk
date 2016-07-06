<?php
namespace qtismtest\data\state;

use qtismtest\QtiSmTestCase;
use qtism\data\state\ResponseValidityConstraint;
use qtism\data\state\AssociationValidityConstraint;
use qtism\data\state\AssociationValidityConstraintCollection;

class ResponseValidityConstraintTest extends QtiSmTestCase {
    
    /**
     * @dataProvider successfulInstantiationBasicProvider
     * 
     * @param integer $minConstraint
     * @param integer $maxConstraint
     */
	public function testSuccessfulInstantiationBasic($minConstraint, $maxConstraint, $patternMask = '') {
        $responseValidityConstraint = new ResponseValidityConstraint('RESPONSE', $minConstraint, $maxConstraint, $patternMask);
        $this->assertEquals('RESPONSE', $responseValidityConstraint->getResponseIdentifier());
        $this->assertEquals($minConstraint, $responseValidityConstraint->getMinConstraint());
        $this->assertEquals($maxConstraint, $responseValidityConstraint->getMaxConstraint());
        $this->assertEquals($patternMask, $responseValidityConstraint->getPatternMask());
    }
    
    public function successfulInstantiationBasicProvider() {
        return array(
            array(0, 1),
            array(0, 0),
            array(2, 2),
            array(0, 2),
            array(1, 0),
            array(1, 0, "/.+/iu")
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
    public function testUnsuccessfulInstantiation($responseIdentifier, $minConstraint, $maxConstraint, $msg, $patternMask = '') {
        $this->setExpectedException('\\InvalidArgumentException', $msg);
        $responseValidityConstraint = new ResponseValidityConstraint($responseIdentifier, $minConstraint, $maxConstraint, $patternMask);
    }
    
    public function unsuccessfulInstantiationProvider() {
        return array(
            array('', 0, 0, "The 'responseIdentifier' argument must be a non-empty string."),
            array('RESPONSE', 3, 2, "The 'maxConstraint' argument must be greather or equal to than the 'minConstraint' in place."),
            array('RESPONSE', -1, 2, "The 'minConstraint' argument must be a non negative (>= 0) integer."),
            array('RESPONSE', 2, -4, "The 'maxConstraint' argument must be a non negative (>= 0) integer."),
            array('RESPONSE', 0, 1, "The 'patternMask' argument must be a string, 'integer' given.", 25)
        );
    }
    
    public function testAssociations() {
        $responseValidityConstraint = new ResponseValidityConstraint('RESPONSE', 0, 0);
        $responseValidityConstraint->addAssociationValidityConstraint(
            new AssociationValidityConstraint('IDENTIFIER', 0, 1)
        );
        
        $this->assertEquals(1, count($responseValidityConstraint->getAssociationValidityConstraints()));
        $this->assertEquals('IDENTIFIER', $responseValidityConstraint->getAssociationValidityConstraints()[0]->getIdentifier());
        
        $responseValidityConstraint->removeAssociationValidityConstraint($responseValidityConstraint->getAssociationValidityConstraints()[0]);
        $this->assertEquals(0, count($responseValidityConstraint->getAssociationValidityConstraints()));
        
        $associationValidityConstraints = new AssociationValidityConstraintCollection(
            array(
                new AssociationValidityConstraint('MYID', 0, 1)
            )
        );
        $responseValidityConstraint->setAssociationValidityConstraints($associationValidityConstraints);
        $this->assertEquals(1, count($responseValidityConstraint->getAssociationValidityConstraints()));
        $this->assertEquals('MYID', $responseValidityConstraint->getAssociationValidityConstraints()[0]->getIdentifier());
    }
}
