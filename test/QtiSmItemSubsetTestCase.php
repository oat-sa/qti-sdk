<?php

use qtism\runtime\tests\AssessmentTestSessionFactory;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\data\storage\xml\XmlCompactAssessmentTestDocument;

require_once(dirname(__FILE__) . '/../qtism/qtism.php');
require_once(dirname(__FILE__) . '/QtiSmTestCase.php');

abstract class QtiSmItemSubsetTestCase extends QtiSmTestCase {
	
    /**
     * Contains the test session to be tested with itemSubset expressions.
     * 
     * @var AssessmentTestSession
     */
    private $testSession;
    
	public function setUp() {
	    parent::setUp();
	    
	    $testFilePath = self::samplesDir() . 'custom/runtime/itemsubset.xml';
	    $doc = new XmlCompactAssessmentTestDocument();
	    $doc->load($testFilePath);
	    
	    $testSessionFactory = new AssessmentTestSessionFactory($doc);
	    $testSession = AssessmentTestSession::instantiate($testSessionFactory);
	    $testSession->beginTestSession();
	    $this->setTestSession($testSession);
	}
	
	public function tearDown() {
	    parent::tearDown();
	}
	
	/**
	 * Set the AssessmentTestSession object to be tested with itemSubset expressions.
	 * 
	 * @param AssessmentTestSession $testSession An instantiated AssessmentTestSession object in INTERACTING state.
	 */
	protected function setTestSession(AssessmentTestSession $testSession) {
	    $this->testSession = $testSession;
	}
	
	/**
	 * Get the AssessmentTestSession object to be tested with itemSubset expressions.
	 *
	 * @return AssessmentTestSession An instantiated AssessmentTestSession object in INTERACTING state.
	 */
	protected function getTestSession() {
	    return $this->testSession;
	}
}