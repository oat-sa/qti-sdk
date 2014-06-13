<?php
use qtism\runtime\tests\SessionFactory;
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
	
	protected static function instantiate($url, $considerMinTime = true) {
	    $doc = new XmlCompactDocument();
	    $doc->load($url);
	     
	    $factory = new SessionFactory();
	    $factory->setConsiderMinTime($considerMinTime);
	    return $factory->createAssessmentTestSession($doc->getDocumentComponent());
	}
}