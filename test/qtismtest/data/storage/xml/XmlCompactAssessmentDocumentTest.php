<?php
namespace qtismtest\data\storage\xml;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\ShowHide;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\LocalFileResolver;
use qtism\data\NavigationMode;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\AssessmentTest;
use qtism\data\storage\xml\XmlStorageException;
use \DOMDocument;

class XmlCompactAssessmentDocumentTest extends QtiSmTestCase {
	
	public function testLoad(XmlCompactDocument $doc = null) {
		if (empty($doc)) {
			
			$doc = new XmlCompactDocument('2.1');
			
			$file = self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml';
			$doc->load($file);
		}
		
		$doc->schemaValidate();

		$testParts = $doc->getDocumentComponent()->getTestParts();
		$this->assertEquals(1, count($testParts));
		$assessmentSections = $testParts['testpartID']->getAssessmentSections();
		$this->assertEquals(1, count($assessmentSections));
		$assessmentSection = $assessmentSections['Sektion_181865064'];
		$this->assertInstanceOf('qtism\\data\\ExtendedAssessmentSection', $assessmentSection);
		
		$assessmentItemRefs = $assessmentSections['Sektion_181865064']->getSectionParts();
		
		$itemCount = 0;
		foreach ($assessmentItemRefs as $k => $ref) {
			$this->assertInstanceOf('qtism\\data\\ExtendedAssessmentItemRef', $assessmentItemRefs[$k]);
			$this->assertTrue($assessmentItemRefs[$k]->hasResponseProcessing());
			$this->assertFalse($assessmentItemRefs[$k]->isTimeDependent());
			$this->assertFalse($assessmentItemRefs[$k]->isAdaptive());
			$itemCount++;
		}
		$this->assertEquals($itemCount, 13); // contains 13 assessmentItemRef elements.
		
		// Pick up 3 for a test...
		$assessmentItemRef = $assessmentItemRefs['Choicemultiple_871212949'];
		$this->assertEquals('Choicemultiple_871212949', $assessmentItemRef->getIdentifier());
		$responseDeclarations = $assessmentItemRef->getResponseDeclarations();
		$this->assertEquals(1, count($responseDeclarations));
		$this->assertEquals('RESPONSE_27966883', $responseDeclarations['RESPONSE_27966883']->getIdentifier());
		$outcomeDeclarations = $assessmentItemRef->getOutcomeDeclarations();
		$this->assertEquals(10, count($outcomeDeclarations));
		$this->assertEquals('MAXSCORE', $outcomeDeclarations['MAXSCORE']->getIdentifier());
	}
	
	public function testSave() {
		$doc = new XmlCompactDocument('2.1.0');
		$file = self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml';
		$doc->load($file);
		
		$file = tempnam('/tmp', 'qsm');
		$doc->save($file);
		$this->assertTrue(file_exists($file));
		
		$doc = new XmlCompactDocument('2.1.0');
		$doc->load($file);
		
		// retest content...
		$this->testLoad($doc);
		
		unlink($file);
		$this->assertFalse(file_exists($file));
	}
	
	public function testCreateFrom() {
		$doc = new XmlDocument('2.1');
		$file = self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml';
		$doc->load($file);
		
		$compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc);
		
		$file = tempnam('/tmp', 'qsm');
		$compactDoc->save($file);
		$this->assertTrue(file_exists($file));
		
		$compactDoc = new XmlCompactDocument('2.1.0');
		$compactDoc->load($file);
		$this->testLoad($compactDoc);
		unlink($file);
		$this->assertFalse(file_exists($file));
	}
	
	public function testCreateFromExploded(XmlCompactDocument $compactDoc = null) {
		$doc = new XmlDocument('2.1');
		$file = self::samplesDir() . 'custom/interaction_mix_saschen_assessmentsectionref/interaction_mix_sachsen.xml';
		$doc->load($file);
		$compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());
		
		$this->assertInstanceOf('qtism\\data\\storage\\xml\\XmlCompactDocument', $compactDoc);
		$this->assertEquals('InteractionMixSachsen_1901710679', $compactDoc->getDocumentComponent()->getIdentifier());
		$this->assertEquals('Interaction Mix (Sachsen)', $compactDoc->getDocumentComponent()->getTitle());
		
		$outcomeDeclarations = $compactDoc->getDocumentComponent()->getOutcomeDeclarations();
		$this->assertEquals(2, count($outcomeDeclarations));
		$this->assertEquals('SCORE', $outcomeDeclarations['SCORE']->getIdentifier());
		
		$testParts = $compactDoc->getDocumentComponent()->getTestParts();
		$this->assertEquals(1, count($testParts));
		$this->assertEquals('testpartID', $testParts['testpartID']->getIdentifier());
		$this->assertEquals(NavigationMode::NONLINEAR, $testParts['testpartID']->getNavigationMode());
		
		$assessmentSections1stLvl = $testParts['testpartID']->getAssessmentSections();
		$this->assertEquals(1, count($assessmentSections1stLvl));
		$this->assertEquals('Container_45665458', $assessmentSections1stLvl['Container_45665458']->getIdentifier());
		
		$assessmentSections2ndLvl = $assessmentSections1stLvl['Container_45665458']->getSectionParts();
		$this->assertEquals(1, count($assessmentSections2ndLvl));
		$this->assertInstanceOf('qtism\\data\\ExtendedAssessmentSection', $assessmentSections2ndLvl['Sektion_181865064']);
		$this->assertEquals(0, count($assessmentSections2ndLvl['Sektion_181865064']->getRubricBlockRefs()));
		$this->assertEquals('Sektion_181865064', $assessmentSections2ndLvl['Sektion_181865064']->getIdentifier());
		
		$assessmentItemRefs = $assessmentSections2ndLvl['Sektion_181865064']->getSectionParts();
		$this->assertEquals(13, count($assessmentItemRefs));
		
		// Pick up 4 for a test...
		$assessmentItemRef = $assessmentItemRefs['Hotspot_278940407'];
		$this->assertInstanceOf('qtism\\data\\ExtendedAssessmentItemRef', $assessmentItemRef);
		$this->assertEquals('Hotspot_278940407', $assessmentItemRef->getIdentifier());
		$responseDeclarations = $assessmentItemRef->getResponseDeclarations();
		$this->assertEquals(1, count($responseDeclarations));
		$this->assertEquals('RESPONSE', $responseDeclarations['RESPONSE']->getIdentifier());
		$outcomeDeclarations = $assessmentItemRef->getOutcomeDeclarations();
		$this->assertEquals(5, count($outcomeDeclarations));
		$this->assertEquals('FEEDBACKBASIC', $outcomeDeclarations['FEEDBACKBASIC']->getIdentifier());
		
		$file = tempnam('/tmp', 'qsm');
		$compactDoc->save($file);
		$this->assertTrue(file_exists($file));
		
		$compactDoc = new XmlCompactDocument('2.1.0');
		$compactDoc->load($file);
		$compactDoc->schemaValidate();
		
		unlink($file);
		$this->assertFalse(file_exists($file));
	}
    
    public function testCreateFromTestWithShuffledInteractions() {
        $doc = new XmlDocument('2.1');
		$file = self::samplesDir() . 'custom/tests/shufflings.xml';
		$doc->load($file);
		$compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());
        $compactTest = $compactDoc->getDocumentComponent();
        
        // Checking Q01 (choiceInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q01');
        $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentItemRef', $itemRef);
        
        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());
        
        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(array('ChoiceA', 'ChoiceB', 'ChoiceC', 'ChoiceD'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        
        // Checking Q02 (orderInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q02');
        $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentItemRef', $itemRef);
        
        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());
        
        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(array('DriverA', 'DriverB', 'DriverC'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        
        // Checking Q03 (associateInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q03');
        $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentItemRef', $itemRef);
        
        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());
        
        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(array('A', 'C', 'D', 'L', 'M', 'P'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        
        // Checking Q04 (matchInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q04');
        $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentItemRef', $itemRef);
        
        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());
        
        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(2, count($shufflingGroups));
        $this->assertEquals(array('C', 'D', 'L', 'P'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this->assertEquals(array('M', 'R', 'T'), $shufflingGroups[1]->getIdentifiers()->getArrayCopy());
        
        // Checking Q05 (gapMatchInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q05');
        $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentItemRef', $itemRef);
        
        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());
        
        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(array('W', 'Sp', 'Su', 'A'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        
        // Checking Q06 (inlineChoiceInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q06');
        $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentItemRef', $itemRef);
        
        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());
        
        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(array('G', 'L', 'Y'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        
        // Checking Q07 (inlineChoiceInteraction) shufflings with shuffle attribute set to FALSE.
        $itemRef = $compactTest->getComponentByIdentifier('Q07');
        $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentItemRef', $itemRef);
        
        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(0, count($shufflings));
    }
	
	public function testLoadRubricBlockRefs(XmlCompactDocument $doc = null) {
	    if (empty($doc) === true) {
	        $src = self::samplesDir() . 'custom/runtime/rubricblockref.xml';
	        $doc = new XmlCompactDocument();
	        $doc->load($src, true);
	    }
	    
	    // It validates !
	    $this->assertInstanceOf('qtism\\data\\AssessmentTest', $doc->getDocumentComponent());
	    
	    // Did we retrieve the section as ExtendedAssessmentSection objects?
	    $sections = $doc->getDocumentComponent()->getComponentsByClassName('assessmentSection');
	    $this->assertEquals(1, count($sections));
	    $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentSection', $sections[0]);
	    
	    // Retrieve rubricBlockRefs.
	    $rubricBlockRefs = $doc->getDocumentComponent()->getComponentsByClassName('rubricBlockRef');
	    $this->assertEquals(1, count($rubricBlockRefs));
	    $rubricBlockRef = $rubricBlockRefs[0];
	    $this->assertInstanceOf('qtism\\data\\content\\RubricBlockRef', $rubricBlockRef);
	    $this->assertEquals('R01', $rubricBlockRef->getIdentifier());
	    $this->assertEquals('./R01.xml', $rubricBlockRef->getHref());
	}
	
	public function testSaveRubricBlockRefs() {
	    $src = self::samplesDir() . 'custom/runtime/rubricblockref.xml';
	    $doc = new XmlCompactDocument();
	    $doc->load($src);
	    
	    $file = tempnam('/tmp', 'qsm');
	    $doc->save($file);
	    
	    $this->assertTrue(file_exists($file));
	    $this->testLoadRubricBlockRefs($doc);
	    
	    unlink($file);
	    $this->assertFalse(file_exists($file));
	}
	
	public function testExplodeRubricBlocks() {
	    $src = self::samplesDir() . 'custom/runtime/rubricblockrefs_explosion.xml';
	    $doc = new XmlCompactDocument();
	    $doc->load($src, true);
	    $doc->setExplodeRubricBlocks(true);
	    
	    $file = tempnam('/tmp', 'qsm');
	    
	    $doc->save($file);
	    
	    // Are external rubricBlocks set?
	    $pathinfo = pathinfo($file);
	    
	    $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'rubricBlock_RB_S01_1.xml';
	    $this->assertTrue(file_exists($path));
	    unlink($path);
	    $this->assertFalse(file_exists($path));
	    
	    $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'rubricBlock_RB_S01_2.xml';
	    $this->assertTrue(file_exists($path));
	    unlink($path);
	    $this->assertFalse(file_exists($path));
	    
	    unlink($file);
	}
	
	public function testExplodeTestFeedbacks() {
	    $src = self::samplesDir() . 'custom/runtime/testfeedbackrefs_explosion.xml';
	    $doc = new XmlCompactDocument();
	    $doc->load($src, true);
	    $doc->setExplodeTestFeedbacks(true);
	    
	    $file = tempnam('/tmp', 'qsm');
	    $doc->save($file);
	    $pathinfo = pathinfo($file);
	    
	    $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_P01_1.xml';
	    $this->assertTrue(file_exists($path));
	    $tfDoc = new XmlDocument();
	    $tfDoc->load($path);
	    $this->assertEquals('feedback1', $tfDoc->getDocumentComponent()->getIdentifier());
	    
	    $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_P01_2.xml';
	    $this->assertTrue(file_exists($path));
	    $tfDoc = new XmlDocument();
	    $tfDoc->load($path);
	    $this->assertEquals('feedback2', $tfDoc->getDocumentComponent()->getIdentifier());
	    
	    $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_testfeedbackrefs_explosion_1.xml';
	    $this->assertTrue(file_exists($path));
	    $tfDoc = new XmlDocument();
	    $tfDoc->load($path);
	    $this->assertEquals('mainfeedback1', $tfDoc->getDocumentComponent()->getIdentifier());
	    
	    $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_testfeedbackrefs_explosion_2.xml';
	    $this->assertTrue(file_exists($path));
	    $tfDoc = new XmlDocument();
	    $tfDoc->load($path);
	    $this->assertEquals('mainfeedback2', $tfDoc->getDocumentComponent()->getIdentifier());
	    
	    $this->assertEquals(0, $doc->getDocumentComponent()->containsComponentWithClassName('testFeedback'));
	}
	
	public function testModalFeedbackRuleLoad() {
	    $src = self::samplesDir() . 'custom/runtime/modalfeedbackrules.xml';
	    $doc = new XmlCompactDocument();
	    $doc->load($src, true);
	    
	    $test = $doc->getDocumentComponent();
	    $itemRefs = $test->getComponentsByClassName('assessmentItemRef', true);
	    $this->assertEquals(1, count($itemRefs));
	    
	    $feedbackRules = $itemRefs[0]->getModalFeedbackRules();
	    $this->assertEquals(2, count($feedbackRules));
	    
	    $this->assertEquals('LOOKUP', $feedbackRules[0]->getOutcomeIdentifier());
	    $this->assertEquals('SHOWME', $feedbackRules[0]->getIdentifier());
	    $this->assertEquals(ShowHide::SHOW, $feedbackRules[0]->getShowHide());
	    $this->assertEquals('Feedback 1', $feedbackRules[0]->getTitle());
	    
	    $this->assertEquals('LOOKUP2', $feedbackRules[1]->getOutcomeIdentifier());
	    $this->assertEquals('HIDEME', $feedbackRules[1]->getIdentifier());
	    $this->assertEquals(ShowHide::HIDE, $feedbackRules[1]->getShowHide());
	    $this->assertFalse($feedbackRules[1]->hasTitle());
	}
	
	/**
	 * @depends testModalFeedbackRuleLoad
	 */
	public function testModalFeedbackRuleSave() {
	    $src = self::samplesDir() . 'custom/runtime/modalfeedbackrules.xml';
	    $doc = new XmlCompactDocument();
	    $doc->load($src);
	    
	    $file = tempnam('/tmp', 'qsm');
	    $doc->save($file);
	    
	    // Let's load the document as DOMDocument...
	    $doc = new DOMDocument('1.0', 'UTF-8');
	    $doc->load($file);
	    
	    $modalFeedbackRuleElts = $doc->documentElement->getElementsByTagName('modalFeedbackRule');
	    $modalFeedbackRule1 = $modalFeedbackRuleElts->item(0);
	    $this->assertEquals('LOOKUP', $modalFeedbackRule1->getAttribute('outcomeIdentifier'));
	    $this->assertEquals('SHOWME', $modalFeedbackRule1->getAttribute('identifier'));
	    $this->assertEquals('show', $modalFeedbackRule1->getAttribute('showHide'));
	    $this->assertEquals('Feedback 1', $modalFeedbackRule1->getAttribute('title'));
	    
	    $modalFeedbackRule2 = $modalFeedbackRuleElts->item(1);
	    $this->assertEquals('LOOKUP2', $modalFeedbackRule2->getAttribute('outcomeIdentifier'));
	    $this->assertEquals('HIDEME', $modalFeedbackRule2->getAttribute('identifier'));
	    $this->assertEquals('hide', $modalFeedbackRule2->getAttribute('showHide'));
	    $this->assertEquals('', $modalFeedbackRule2->getAttribute('title'));
	    
	    unlink($file);
	}
    
    /**
     * @dataProvider testSchemaValidProvider
     */
    public function testSchemaValid($path) {
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->load($path, LIBXML_COMPACT|LIBXML_NONET|LIBXML_XINCLUDE);
		
		$schema = dirname(__FILE__) . '/../../../../../src/qtism/data/storage/xml/schemes/qticompact_v1p0.xsd';
		$this->assertTrue($doc->schemaValidate($schema));
	}
    
    public function testSchemaValidProvider() {
        return array(
            array(self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml'),
            array(self::samplesDir() . 'custom/runtime/test_feedback_refs.xml'),
            array(self::samplesDir() . 'custom/runtime/endAttemptIdentifiers.xml'),
            array(self::samplesDir() . 'custom/runtime/shuffling/shuffling_groups.xml'),
            array(self::samplesDir() . 'custom/runtime/response_validity_constraints.xml'),
        );
    }
	
	public function testTestFeedbackRefLoad() {
	    $src = self::samplesDir() . 'custom/runtime/test_feedback_refs.xml';
	    $doc = new XmlCompactDocument();
	    $doc->load($src, true);
	     
	    $test = $doc->getDocumentComponent();
	    $testFeedbackRefs = $test->getComponentsByClassName('testFeedbackRef');
	    $this->assertEquals(3, count($testFeedbackRefs));
	}
	
	/**
	 * @depends testTestFeedbackRefLoad
	 */
	public function testFeedbackRefSave() {
	    $src = self::samplesDir() . 'custom/runtime/test_feedback_refs.xml';
	    $doc = new XmlCompactDocument();
	    $doc->load($src, true);
	    
	    $file = tempnam('/tmp', 'qsm');
	    $doc->save($file);
	    
	    $doc = new DOMDocument('1.0', 'UTF-8');
	    $doc->load($file);
	    
	    $testFeedbackRefElts = $doc->getElementsByTagName('testFeedbackRef');
	    $this->assertEquals(3, $testFeedbackRefElts->length);
	    
	    $testFeedbackRefElt1 = $testFeedbackRefElts->item(0);
	    $this->assertEquals('feedback1', $testFeedbackRefElt1->getAttribute('identifier'));
	    $this->assertEquals('atEnd', $testFeedbackRefElt1->getAttribute('access'));
	    $this->assertEquals('show', $testFeedbackRefElt1->getAttribute('showHide'));
	    $this->assertEquals('showme', $testFeedbackRefElt1->getAttribute('outcomeIdentifier'));
	    $this->assertEquals('./TF01.xml', $testFeedbackRefElt1->getAttribute('href'));
	    
	    $testFeedbackRefElt2 = $testFeedbackRefElts->item(1);
	    $this->assertEquals('feedback2', $testFeedbackRefElt2->getAttribute('identifier'));
	    $this->assertEquals('atEnd', $testFeedbackRefElt2->getAttribute('access'));
	    $this->assertEquals('show', $testFeedbackRefElt2->getAttribute('showHide'));
	    $this->assertEquals('showme', $testFeedbackRefElt2->getAttribute('outcomeIdentifier'));
	    $this->assertEquals('./TF02.xml', $testFeedbackRefElt2->getAttribute('href'));
	    
	    $testFeedbackRefElt3 = $testFeedbackRefElts->item(2);
	    $this->assertEquals('mainfeedback1', $testFeedbackRefElt3->getAttribute('identifier'));
	    $this->assertEquals('during', $testFeedbackRefElt3->getAttribute('access'));
	    $this->assertEquals('show', $testFeedbackRefElt3->getAttribute('showHide'));
	    $this->assertEquals('showme', $testFeedbackRefElt3->getAttribute('outcomeIdentifier'));
	    $this->assertEquals('./TFMAIN.xml', $testFeedbackRefElt3->getAttribute('href'));
	}
	
	public function testCreateFromAssessmentTestEndAttemptIdentifiers() {
	    $doc = new XmlDocument('2.1');
	    $file = self::samplesDir() . 'custom/test_contains_endattemptinteractions.xml';
	    $doc->load($file);
	    $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());
	    
	    // ExtendedAssessmentItemRefs!
	    $assessmentItemRefs = $compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef');
	    $this->assertEquals(2, count($assessmentItemRefs));
	    
	    $assessmentItemRef = $assessmentItemRefs[0];
	    $endAttemptIdentifiers = $assessmentItemRef->getEndAttemptIdentifiers();
	    $this->assertEquals(1, count($endAttemptIdentifiers));
	    $this->assertEquals('HINT', $endAttemptIdentifiers[0]);
	    
	    $assessmentItemRef = $assessmentItemRefs[1];
	    $endAttemptIdentifiers = $assessmentItemRef->getEndAttemptIdentifiers();
	    $this->assertEquals(2, count($endAttemptIdentifiers));
	    $this->assertEquals('LOST', $endAttemptIdentifiers[0]);
	    $this->assertEquals('LOST2', $endAttemptIdentifiers[1]);
	}
    
    public function testCreateFromAssessmentTestInvalidAssessmentItemRefResolution() {
        $this->setExpectedException(
            '\\qtism\\data\\storage\\xml\\XmlStorageException',
            "An error occured while unreferencing item reference with identifier 'Q01'.",
            XmlStorageException::RESOLUTION
        );
        
	    $doc = new XmlDocument('2.1');
	    $file = self::samplesDir() . 'custom/tests/invalidassessmentitemref.xml';
	    $doc->load($file);
	    $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());
	}
}
