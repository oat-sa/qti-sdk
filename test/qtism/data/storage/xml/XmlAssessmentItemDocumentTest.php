<?php

use qtism\data\View;
use qtism\data\storage\xml\XmlAssessmentItemDocument;
use qtism\data\AssessmentItem;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class XmlAssessmentItemDocumentTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider validFileProvider
	 */
	public function testLoad($uri) {
		$doc = new XmlAssessmentItemDocument('2.1');
		$doc->load($uri);
		
		$assessmentItem = $doc;
		$this->assertInstanceOf('qtism\\data\\AssessmentItem', $assessmentItem);
	}
	
	/**
	 * @dataProvider validFileProvider
	 */
	public function testWrite($uri) {
		$doc = new XmlAssessmentItemDocument('2.1');
		$doc->load($uri);
		
		$assessmentItem = $doc;
		$this->assertInstanceOf('qtism\\data\\AssessmentItem', $assessmentItem);
		
		$file = tempnam('/tmp', 'qsm');
		$doc->save($file);
		
		$this->assertTrue(file_exists($file));
		$this->testLoad($file);
		
		unlink($file);
		// Nobody else touched it?
		$this->assertFalse(file_exists($file));
	}
	
	public function testLoad21() {
		$file = self::samplesDir() . 'ims/items/2_1/associate.xml';
		$doc = new XmlAssessmentItemDocument();
		$doc->load($file);
		
		$this->assertEquals('2.1', $doc->getVersion());
	}
	
	public function testLoad20() {
		$file = self::samplesDir() . 'ims/items/2_0/associate.xml';
		$doc = new XmlAssessmentItemDocument();
		$doc->load($file);
		
		$this->assertEquals('2.0', $doc->getVersion());
	}
	
	public function validFileProvider() {
		return array(
			array(self::decorateUri('associate.xml')),
			array(self::decorateUri('choice_fixed.xml')),
			// @todo C10 is invalid identifier? Double check!
			//array(self::decorateUri('choice_multiple_chocolade.xml')),
			array(self::decorateUri('choice_multiple.xml')),
			array(self::decorateUri('choice.xml')),
			array(self::decorateUri('extended_text_rubric.xml')),
			array(self::decorateUri('extended_text.xml')),
			array(self::decorateUri('gap_match.xml')),
			array(self::decorateUri('graphic_associate.xml')),
			array(self::decorateUri('graphic_gap_match.xml')),
			array(self::decorateUri('hotspot.xml')),
			array(self::decorateUri('hottext.xml')),
			array(self::decorateUri('inline_choice.xml')),
			array(self::decorateUri('match.xml')),
			array(self::decorateUri('multi-input.xml')),
			array(self::decorateUri('order.xml')),
			array(self::decorateUri('position_object.xml')),
			array(self::decorateUri('select_point.xml')),
			array(self::decorateUri('slider.xml')),
			array(self::decorateUri('text_entry.xml')),
			array(self::decorateUri('associate.xml', '2.0')),
			array(self::decorateUri('adaptive.xml', '2.0')),
			array(self::decorateUri('choice_multiple.xml', '2.0')),
			array(self::decorateUri('choice.xml', '2.0')),
			array(self::decorateUri('drawing.xml', '2.0')),
			array(self::decorateUri('extended_text.xml', '2.0')),
			array(self::decorateUri('feedback.xml', '2.0')),
			array(self::decorateUri('gap_match.xml', '2.0')),
			array(self::decorateUri('graphic_associate.xml', '2.0')),
			array(self::decorateUri('graphic_gap_match.xml', '2.0')),
			array(self::decorateUri('graphic_order.xml', '2.0')),
			array(self::decorateUri('hint.xml', '2.0')),
			array(self::decorateUri('hotspot.xml', '2.0')),
			//array(self::decorateUri('hottext.xml', '2.0')),
			array(self::decorateUri('inline_choice.xml', '2.0')),
			array(self::decorateUri('likert.xml', '2.0')),
			array(self::decorateUri('match.xml', '2.0')),
			array(self::decorateUri('nested_object.xml', '2.0')),
			array(self::decorateUri('order_partial_scoring.xml', '2.0')),
			array(self::decorateUri('order.xml', '2.0')),
			array(self::decorateUri('orkney1.xml', '2.0')),
			//array(self::decorateUri('position_object.xml', '2.0')),
			array(self::decorateUri('select_point.xml', '2.0')),
			array(self::decorateUri('slider.xml', '2.0')),
			array(self::decorateUri('template_image.xml', '2.0')),
			array(self::decorateUri('template.xml', '2.0')),
			array(self::decorateUri('text_entry.xml', '2.0')),
			array(self::decorateUri('upload_composite.xml', '2.0')),
			array(self::decorateUri('upload.xml', '2.0')),
		);
	}
	
	private static function decorateUri($uri, $version = '2.1') {
		if ($version === '2.1') {
			return self::samplesDir() . 'ims/items/2_1/' . $uri;
		}
		else {
			return self::samplesDir() . 'ims/items/2_0/' . $uri;
		}
	}
}