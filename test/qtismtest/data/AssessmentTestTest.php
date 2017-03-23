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
use qtism\data\BranchRuleTargetException;
use qtism\data\NavigationMode;
use qtism\data\AssessmentItemRefCollection;

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
        $doc->load(self::samplesDir() . 'custom/tests/branchingpath.xml');
        $test = $doc->getDocumentComponent();

        $itemq1 = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');
        $itemq2 = $doc->getDocumentComponent()->getComponentByIdentifier('Q02');
        $itemq3 = $doc->getDocumentComponent()->getComponentByIdentifier('Q03');
        $itemq4 = $doc->getDocumentComponent()->getComponentByIdentifier('Q04');
        $itemq5 = $doc->getDocumentComponent()->getComponentByIdentifier('Q05');
        $itemq6 = $doc->getDocumentComponent()->getComponentByIdentifier('Q06');

        $possible_paths = [];
        $possible_paths2 = [];

        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq2, $itemq3, $itemq4, $itemq5, $itemq6]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq3, $itemq4, $itemq5, $itemq6]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq2, $itemq4, $itemq5, $itemq6]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq2, $itemq3, $itemq6]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq3, $itemq6]);

        foreach ($possible_paths as $path) {
            $possible_paths2[] = $path->getArrayCopy();
        }

        $this->assertEquals($possible_paths, $test->getPossiblePaths(false));
        $this->assertEquals($possible_paths2, $test->getPossiblePaths(true));

        // Test with no item

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/assessmentwithnoitem.xml');
        $test = $doc->getDocumentComponent();

        $possible_paths = [];
        $possible_paths[] = new AssessmentItemRefCollection();
        $this->assertEquals($possible_paths, $test->getPossiblePaths(false));
    }

    public function testPossiblePathswithPreCondition()
    {
        // Case 1

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingpathwithpre.xml');
        $test = $doc->getDocumentComponent();

        $itemq1 = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');
        $itemq2 = $doc->getDocumentComponent()->getComponentByIdentifier('Q02');
        $itemq3 = $doc->getDocumentComponent()->getComponentByIdentifier('Q03');
        $itemq4 = $doc->getDocumentComponent()->getComponentByIdentifier('Q04');
        $itemq5 = $doc->getDocumentComponent()->getComponentByIdentifier('Q05');

        $possible_paths = [];
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq2, $itemq3, $itemq4, $itemq5]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq3, $itemq4, $itemq5]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq2, $itemq3, $itemq5]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq3, $itemq5]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq2, $itemq3, $itemq4]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq3, $itemq4]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq2, $itemq3]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq3]);

        $this->assertEquals($possible_paths, $test->getPossiblePaths(false));

        // Case with duplicates

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/testnoduplicatepaths.xml');
        $test = $doc->getDocumentComponent();

        $itemq1 = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');
        $itemq2 = $doc->getDocumentComponent()->getComponentByIdentifier('Q02');
        $itemq3 = $doc->getDocumentComponent()->getComponentByIdentifier('Q03');

        $possible_paths = [];
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq2, $itemq3]);
        $possible_paths[] = new AssessmentItemRefCollection([$itemq1, $itemq3]);

        $this->assertEquals($possible_paths, $test->getPossiblePaths(false));
    }

    // Testing special cases

    public function testRecursiveBranching()
    {
        $this->expectException(BranchRuleTargetException::class);
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingrecursive.xml');
        $test = $doc->getDocumentComponent();
        $test->getPossiblePaths(false);
    }

    public function testBackwardBranching()
    {
        $this->expectException(BranchRuleTargetException::class);
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingbackward.xml');
        $test = $doc->getDocumentComponent();
        $test->getPossiblePaths(false);
    }


    public function testGetShortestPaths()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingpath.xml');
        $test = $doc->getDocumentComponent();

        $shortest_paths = [];
        $path = new AssessmentItemRefCollection();

        $path[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');
        $path[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q03');
        $path[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q06');

        $shortest_paths[] = $path;

        $this->assertEquals($shortest_paths, $test->getShortestPaths());
        
        // With multiple shortest paths

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/multipleshortpaths.xml');
        $test = $doc->getDocumentComponent();

        $shortest_paths = [];
        $path1 = new AssessmentItemRefCollection();
        $path2 = new AssessmentItemRefCollection();

        $path1[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');
        $path1[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q02');
        $path1[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q04');

        $path2[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');
        $path2[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q03');
        $path2[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q04');

        $shortest_paths[] = $path2;
        $shortest_paths[] = $path1;

        $this->assertEquals($shortest_paths, $test->getShortestPaths());
    }

    public function testGetLongestPaths()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingpath.xml');
        $test = $doc->getDocumentComponent();

        $longest_paths = [];
        $path = new AssessmentItemRefCollection();

        $path[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');
        $path[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q02');
        $path[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q03');
        $path[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q04');
        $path[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q05');
        $path[] = $doc->getDocumentComponent()->getComponentByIdentifier('Q06');

        $longest_paths[] = $path;

        $this->assertEquals($longest_paths, $test->getLongestPaths());
    }
}
