<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\data\storage\xml\XmlResponseProcessingDocument;

class XmlResponseProcessingDocumentTest extends QtiSmTestCase {
	
	public function testLoadMatchCorrect() {
		$xml = new XmlResponseProcessingDocument('2.1');
		$xml->load($this->getTemplatesPath() . '2_1/match_correct.xml');
		$this->assertInstanceOf('qtism\\data\\state\\ResponseProcessing', $xml);
		$this->assertFalse($xml->hasTemplateLocation());
		$this->assertFalse($xml->hasTemplate());
		
		$responseRules = $xml->getResponseRules();
		$this->assertEquals(1, count($responseRules));
		
		$responseCondition = $responseRules[0];
		$this->assertInstanceOf('qtism\\data\\rules\\ResponseCondition', $responseCondition);
		
		$responseIf = $responseCondition->getResponseIf();
		$match = $responseIf->getExpression();
		$this->assertInstanceOf('qtism\\data\\expressions\\operators\\Match', $match);
		
		$matchExpressions = $match->getExpressions();
		$this->assertEquals(2, count($matchExpressions));
		$variable = $matchExpressions[0];
		$this->assertInstanceOf('qtism\\data\\expressions\\Variable', $variable);
		$this->assertEquals('RESPONSE', $variable->getIdentifier());
		$correct = $matchExpressions[1];
		$this->assertInstanceOf('qtism\\data\\expressions\\Correct', $correct);
		$this->assertEquals('RESPONSE', $correct->getIdentifier());
		
		// To be continued...
	}
	
	/**
	 * Returns the location of the templates on the file system
	 * WITH A TRAILING SLASH.
	 * 
	 * @return string
	 */
	public function getTemplatesPath() {
		return dirname(__FILE__) . '/../../../../../qtism/runtime/rules/templates/';
	}
}