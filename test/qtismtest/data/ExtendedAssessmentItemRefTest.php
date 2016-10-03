<?php
namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\AssessmentItemRef;
use qtism\data\state\Weight;
use qtism\data\state\WeightCollection;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\TemplateDeclaration;
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
    
    public function testRemoveOutcomeDeclaration()
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');
        $outcomeDeclaration = new OutcomeDeclaration('OUTCOME', BaseType::IDENTIFIER, Cardinality::SINGLE);
        $assessmentItemRef->addOutcomeDeclaration($outcomeDeclaration);
        
        $this->assertCount(1, $assessmentItemRef->getOutcomeDeclarations());
        $assessmentItemRef->removeOutcomeDeclaration($outcomeDeclaration);
        $this->assertCount(0, $assessmentItemRef->getOutcomeDeclarations());
    }
    
    public function testRemoveResponseDeclaration()
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');
        $responseDeclaration = new ResponseDeclaration('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE);
        $assessmentItemRef->addResponseDeclaration($responseDeclaration);
        
        $this->assertCount(1, $assessmentItemRef->getResponseDeclarations());
        $assessmentItemRef->removeResponseDeclaration($responseDeclaration);
        $this->assertCount(0, $assessmentItemRef->getResponseDeclarations());
    }
    
    public function testAddTemplateDeclaration()
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');
        $templateDeclaration = new TemplateDeclaration('TEMPLATE', BaseType::IDENTIFIER, Cardinality::SINGLE);
        $assessmentItemRef->addTemplateDeclaration($templateDeclaration);
        
        $this->assertCount(1, $assessmentItemRef->getTemplateDeclarations());
    }
}
