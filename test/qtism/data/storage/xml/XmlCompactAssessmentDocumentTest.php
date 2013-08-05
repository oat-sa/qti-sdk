<?php

use qtism\data\NavigationMode;

use qtism\data\storage\xml\XmlAssessmentTestDocument;
use qtism\data\AssessmentTest;
use \DOMDocument;
use qtism\data\storage\xml\XmlCompactAssessmentTestDocument;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class XmlCompactAssessmentDocumentTest extends QtiSmTestCase {
	
	public function testSchemaValid() {
		$doc = new DOMDocument('1.0', 'UTF-8');
		$file = self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml';
		$doc->load($file, LIBXML_COMPACT|LIBXML_NONET|LIBXML_XINCLUDE);
		
		$schema = dirname(__FILE__) . '/../../../../../qtism/data/storage/xml/schemes/qticompact_v1p0.xsd';
		$this->assertTrue($doc->schemaValidate($schema));
	}
	
	public function testLoad(XmlCompactAssessmentTestDocument $doc = null) {
		if (empty($doc)) {
			
			$doc = new XmlCompactAssessmentTestDocument('1.0');
			$this->assertInstanceOf('qtism\\data\\AssessmentTest', $doc);
			
			$file = self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml';
			$doc->load($file);
		}
		
		$doc->schemaValidate();

		$testParts = $doc->getTestParts();
		$this->assertEquals(1, count($testParts));
		$assessmentSections = $testParts['testpartID']->getAssessmentSections();
		$this->assertEquals(1, count($assessmentSections));
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
		$doc = new XmlCompactAssessmentTestDocument('1.0');
		$file = self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml';
		$doc->load($file);
		
		$file = tempnam('/tmp', 'qsm');
		$doc->save($file);
		$this->assertTrue(file_exists($file));
		
		$doc = new XmlCompactAssessmentTestDocument('1.0');
		$doc->load($file);
		
		// retest content...
		$this->testLoad($doc);
		
		unlink($file);
		$this->assertFalse(file_exists($file));
	}
	
	public function testCreateFrom() {
		$doc = new XmlAssessmentTestDocument('2.1');
		$file = self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml';
		$doc->load($file);
		
		$compactDoc = XmlCompactAssessmentTestDocument::createFromXmlAssessmentTestDocument($doc);
		$file = tempnam('/tmp', 'qsm');
		$compactDoc->save($file);
		$this->assertTrue(file_exists($file));
		
		$compactDoc = new XmlCompactAssessmentTestDocument('1.0');
		$compactDoc->load($file);
		$this->testLoad($compactDoc);
		
		unlink($file);
		$this->assertFalse(file_exists($file));
	}
	
	public function testCreateFormExploded(XmlCompactAssessmentTestDocument $compactDoc = null) {
		$doc = new XmlAssessmentTestDocument('2.1');
		$file = self::samplesDir() . 'custom/interaction_mix_saschen_assessmentsectionref/interaction_mix_sachsen.xml';
		$doc->load($file);
		$compactDoc = XmlCompactAssessmentTestDocument::createFromXmlAssessmentTestDocument($doc);
		
		$this->assertInstanceOf('qtism\\data\\storage\\xml\\XmlCompactAssessmentTestDocument', $compactDoc);
		$this->assertEquals('InteractionMixSachsen_1901710679', $compactDoc->getIdentifier());
		$this->assertEquals('Interaction Mix (Sachsen)', $compactDoc->getTitle());
		
		$outcomeDeclarations = $compactDoc->getOutcomeDeclarations();
		$this->assertEquals(2, count($outcomeDeclarations));
		$this->assertEquals('SCORE', $outcomeDeclarations['SCORE']->getIdentifier());
		
		$testParts = $compactDoc->getTestParts();
		$this->assertEquals(1, count($testParts));
		$this->assertEquals('testpartID', $testParts['testpartID']->getIdentifier());
		$this->assertEquals(NavigationMode::NONLINEAR, $testParts['testpartID']->getNavigationMode());
		
		$assessmentSections1stLvl = $testParts['testpartID']->getAssessmentSections();
		$this->assertEquals(1, count($assessmentSections1stLvl));
		$this->assertEquals('Container_45665458', $assessmentSections1stLvl['Container_45665458']->getIdentifier());
		
		$assessmentSections2ndLvl = $assessmentSections1stLvl['Container_45665458']->getSectionParts();
		$this->assertEquals(1, count($assessmentSections2ndLvl));
		$this->assertInstanceOf('qtism\\data\\AssessmentSection', $assessmentSections2ndLvl['Sektion_181865064']);
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
		
		$compactDoc = new XmlCompactAssessmentTestDocument('1.0');
		$compactDoc->load($file);
		$compactDoc->schemaValidate();
		
		unlink($file);
		$this->assertFalse(file_exists($file));
	}
}