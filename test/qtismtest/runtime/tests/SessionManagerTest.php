<?php

declare(strict_types=1);

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\files\FileSystemFileManager;
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
    private function setTest(AssessmentTest $test): void
    {
        $this->test = $test;
    }

    /**
     * @return AssessmentTest
     */
    private function getTest(): AssessmentTest
    {
        return $this->test;
    }

    public function testDefaultAssessmentTestSessionCreation(): void
    {
        $manager = new SessionManager(new FileSystemFileManager());
        $session = $manager->createAssessmentTestSession($this->getTest());

        $this::assertInstanceOf(AssessmentTestSession::class, $session);
    }
}
