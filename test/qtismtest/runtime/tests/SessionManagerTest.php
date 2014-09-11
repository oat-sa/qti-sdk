<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\Duration;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\AssessmentTest;
use qtism\runtime\tests\SessionManager;

class SessionManagerTest extends QtiSmTestCase {
	
    private $test;
    
    public function setUp() {
        parent::setUp();
        
        $test = new XmlCompactDocument();
        $test->load(self::samplesDir() . 'custom/runtime/linear_5_items.xml');
        $this->setTest($test->getDocumentComponent());
    }
    
    public function tearDown() {
        parent::tearDown();
        unset($this->test);
    }
    
    /**
     * 
     * @param AssessmentTest $test
     */
    private function setTest(AssessmentTest $test) {
        $this->test = $test;
    }
    
    /**
     * 
     * @return AssessmentTest
     */
    private function getTest() {
        return $this->test;
    }
    
    public function testDefaultAssessmentTestSessionCreation() {
        // default considerMinTime is true.
        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($this->getTest());
        
        $this->assertInstanceOf('qtism\\runtime\\tests\\AssessmentTestSession', $session);
        $this->assertTrue($session->mustConsiderMinTime());
    }
    
    public function testParametricAssessmentTestSessionCreation() {
        $considerMinTime = false;
        
        $manager = new SessionManager();
        $manager->setConsiderMinTime($considerMinTime);
        
        $session = $manager->createAssessmentTestSession($this->getTest());
        
        $this->assertInstanceOf('qtism\\runtime\\tests\\AssessmentTestSession', $session);
        $this->assertFalse($session->mustConsiderMinTime());
    }
}