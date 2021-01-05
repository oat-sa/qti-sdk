<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiDuration;
use qtism\data\AssessmentTest;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmTestCase;
use qtism\runtime\tests\AssessmentTestSession;

/**
 * Class SessionManagerTest
 */
class SessionManagerTest extends QtiSmTestCase
{
    private $test;

    public function setUp(): void
    {
        parent::setUp();

        $test = new XmlCompactDocument();
        $test->load(self::samplesDir() . 'custom/runtime/linear_5_items.xml');
        $this->setTest($test->getDocumentComponent());
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->test);
    }

    /**
     * @param AssessmentTest $test
     */
    private function setTest(AssessmentTest $test)
    {
        $this->test = $test;
    }

    /**
     * @return AssessmentTest
     */
    private function getTest()
    {
        return $this->test;
    }

    public function testDefaultAssessmentTestSessionCreation()
    {
        // Default acceptable latency is PT0S.
        // default considerMinTime is true.
        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($this->getTest());

        $this->assertInstanceOf(AssessmentTestSession::class, $session);
        $this->assertTrue($session->mustConsiderMinTime());
        $this->assertTrue($session->getAcceptableLatency()->equals(new QtiDuration('PT0S')), 'The default acceptable latency must be PT0S');
    }

    public function testParametricAssessmentTestSessionCreation()
    {
        $acceptableLatency = new QtiDuration('PT5S');
        $considerMinTime = false;

        $manager = new SessionManager();
        $manager->setAcceptableLatency($acceptableLatency);
        $manager->setConsiderMinTime($considerMinTime);

        $session = $manager->createAssessmentTestSession($this->getTest());

        $this->assertInstanceOf(AssessmentTestSession::class, $session);
        $this->assertFalse($session->mustConsiderMinTime());
        $this->assertTrue($session->getAcceptableLatency()->equals(new QtiDuration('PT5S')), 'The custom acceptable latency must be PT5S');
    }
}
