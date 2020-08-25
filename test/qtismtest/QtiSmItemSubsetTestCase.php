<?php

namespace qtismtest;

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\SessionManager;

/**
 * Class QtiSmItemSubsetTestCase
 *
 * @package qtismtest
 */
abstract class QtiSmItemSubsetTestCase extends QtiSmTestCase
{
    /**
     * Contains the test session to be tested with itemSubset expressions.
     *
     * @var AssessmentTestSession
     */
    private $testSession;

    public function setUp()
    {
        parent::setUp();

        $testFilePath = self::samplesDir() . 'custom/runtime/itemsubset.xml';
        $doc = new XmlCompactDocument();
        $doc->load($testFilePath);

        $sessionManager = new SessionManager(new FileSystemFileManager());
        $testSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $testSession->beginTestSession();
        $this->setTestSession($testSession);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Set the AssessmentTestSession object to be tested with itemSubset expressions.
     *
     * @param AssessmentTestSession $testSession An instantiated AssessmentTestSession object in INTERACTING state.
     */
    protected function setTestSession(AssessmentTestSession $testSession)
    {
        $this->testSession = $testSession;
    }

    /**
     * Get the AssessmentTestSession object to be tested with itemSubset expressions.
     *
     * @return AssessmentTestSession An instantiated AssessmentTestSession object in INTERACTING state.
     */
    protected function getTestSession()
    {
        return $this->testSession;
    }
}
