<?php
namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\data\AssessmentItemRef;
use qtism\data\state\Weight;
use qtism\data\state\WeightCollection;
use qtism\data\ExtendedAssessmentItemRef;

class ExtendedAssessmentItemRefTest extends QtiSmTestCase 
{
	
    public function testCreateFromAssessmentItemRef()
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $extendedAssessmentItemRef = ExtendedAssessmentItemRef::createFromAssessmentItemRef($assessmentItemRef);
        
        $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentItemRef', $extendedAssessmentItemRef);
        $this->assertEquals('Q01', $extendedAssessmentItemRef->getIdentifier());
        $this->assertEquals('Q01.xml', $extendedAssessmentItemRef->getHref());
    }
    
    /**
     * @depends testCreateFromAssessmentItemRef
     */
    public function testCreateFromAssessmentItemRefWithWeights()
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $assessmentItemRef->setWeights(
            new WeightCollection(
                array(
                    new Weight('WEIGHT', 2.)
                )
            )
        );
        
        $extendedAssessmentItemRef = ExtendedAssessmentItemRef::createFromAssessmentItemRef($assessmentItemRef);
        $weights = $extendedAssessmentItemRef->getWeights();
        
        $this->assertCount(1, $weights);
        $this->assertEquals('WEIGHT', $weights['WEIGHT']->getIdentifier());
        $this->assertEquals(2., $weights['WEIGHT']->getValue());
    }
}
