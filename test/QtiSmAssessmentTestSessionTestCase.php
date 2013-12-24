<?php
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionFactory;
use qtism\data\storage\xml\XmlCompactDocument;

require_once(dirname(__FILE__) . '/../qtism/qtism.php');
require_once(dirname(__FILE__) . '/QtiSmTestCase.php');



abstract class QtiSmAssessmentTestSessionTestCase extends QtiSmTestCase {
    
	public function setUp() {
	    parent::setUp();
	}
	
	public function tearDown() {
	    parent::tearDown();
	}
	
	protected static function instantiate($url) {
	    $doc = new XmlCompactDocument();
	    $doc->load($url);
	     
	    $factory = new AssessmentTestSessionFactory($doc->getDocumentComponent());
	    return AssessmentTestSession::instantiate($factory);
	}
}