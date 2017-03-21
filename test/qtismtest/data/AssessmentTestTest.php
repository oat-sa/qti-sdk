<?php
namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\QtiDuration;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\AssessmentTest;
use qtism\data\TestPart;
use qtism\data\TestPartCollection;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\TimeLimits;
use qtism\data\NavigationMode;

class AssessmentTestTest extends QtiSmTestCase 
{
    
	public function testTimeLimits() {
	    $doc = new XmlDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/timelimits.xml');
	    
	    $testPart = $doc->getDocumentComponent()->getComponentByIdentifier('testPartId');
	    $this->assertTrue($testPart->hasTimeLimits());
	    $timeLimits = $testPart->getTimeLimits();
	    
	    $this->assertTrue($timeLimits->getMinTime()->equals(new QtiDuration('PT60S')));
	    $this->assertTrue($timeLimits->getMaxTime()->equals(new QtiDuration('PT120S')));
	    $this->assertTrue($timeLimits->doesAllowLateSubmission());
	}
    
    public function testCreateAssessmentTestWrongIdentifier()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'999' is not a valid QTI Identifier."
        );
        
        $test = new AssessmentTest('999', 'Nine Nine Nine');
    }
    
    public function testCreateAssessmentTestWrongTitle()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Title must be a string, 'integer' given."
        );
        
        $test = new AssessmentTest('ABC', 999);
    }
    
    public function testSetToolNameWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Toolname must be a string, 'integer' given."
        );
        
        $test = new AssessmentTest('ABC', 'ABC');
        $test->setToolName(999);
    }
    
    public function testSetToolVersionWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "ToolVersion must be a string, 'integer' given."
        );
        
        $test = new AssessmentTest('ABC', 'ABC');
        $test->setToolVersion(999);
    }
    
    public function testComponentsWithTimeLimits()
    {
        $test = new AssessmentTest('ABC', 'ABC');
        $test->setTimeLimits(
            new TimeLimits()
        );
        
        $components = $test->getComponents();
        $this->assertInstanceOf('\\qtism\\data\\TimeLimits', $components[count($components) - 1]);
    }
    
    public function testIsExclusivelyLinearNoTestParts()
    {
        $test = new AssessmentTest('ABC', 'ABC');
        $this->assertFalse($test->isExclusivelyLinear());
    }
    
    public function testIsExclusivelyLinear()
    {
        $test = new AssessmentTest('ABC', 'ABC');
        
        $testPart = new TestPart(
            'ABCD',
            new AssessmentSectionCollection(
                array(
                    new AssessmentSection('ABCDE', 'ABCDE', true)
                )
            ),
            NavigationMode::NONLINEAR
        );
        
        $test->setTestParts(
            new TestPartCollection(
                array(
                    $testPart
                )
            )
        );
        
        $this->assertFalse($test->isExclusivelyLinear());
        
        $testPart->setNavigationMode(NavigationMode::LINEAR);
        $this->assertTrue($test->isExclusivelyLinear());
    }
    
    public function testGetPossiblePaths()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branching.xml');
        $test = $doc->getDocumentComponent()->getComponentByIdentifier('branching-based-on-the-response-to-an-assessmentItem');

        $itemq1 = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');
        $itemq2 = $doc->getDocumentComponent()->getComponentByIdentifier('Q02');
        $itemq3 = $doc->getDocumentComponent()->getComponentByIdentifier('Q03');
        $itemq4 = $doc->getDocumentComponent()->getComponentByIdentifier('Q04');
        $itemq5 = $doc->getDocumentComponent()->getComponentByIdentifier('Q05');
        $itemq6 = $doc->getDocumentComponent()->getComponentByIdentifier('Q06');

        $possible_paths = array();

        $possible_paths[] = [$itemq1, $itemq2, $itemq4, $itemq5, $itemq6];
        $possible_paths[] = [$itemq1, $itemq2, $itemq3, $itemq6];
        $possible_paths[] = [$itemq1, $itemq3, $itemq6];

        $this->assertEquals($test->getPossiblePaths(), $possible_paths);
    }

    public function testGetShortestPaths()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branching.xml');
        $test = $doc->getDocumentComponent()->getComponentByIdentifier('branching-based-on-the-response-to-an-assessmentItem');

        $itemq1 = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');
        $itemq3 = $doc->getDocumentComponent()->getComponentByIdentifier('Q03');
        $itemq6 = $doc->getDocumentComponent()->getComponentByIdentifier('Q06');

        $shortest_paths = array();

        $shortest_paths[] = [$itemq1, $itemq3, $itemq6];

        $this->assertEquals($test->getShortestPath(), $shortest_paths);
    }

    public function testGetLongestPaths()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branching.xml');
        $test = $doc->getDocumentComponent()->getComponentByIdentifier('branching-based-on-the-response-to-an-assessmentItem');

        $itemq1 = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');
        $itemq2 = $doc->getDocumentComponent()->getComponentByIdentifier('Q02');
        $itemq4 = $doc->getDocumentComponent()->getComponentByIdentifier('Q04');
        $itemq5 = $doc->getDocumentComponent()->getComponentByIdentifier('Q05');
        $itemq6 = $doc->getDocumentComponent()->getComponentByIdentifier('Q06');

        $longest_paths = array();

        $longest_paths[] = [$itemq1, $itemq2, $itemq4, $itemq5, $itemq6];

        $this->assertEquals($test->getLongestPaths(), $longest_paths);
    }
}
