<?php

namespace qtismtest;

use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\SessionManager;

/**
 * Class QtiSmAssessmentTestSessionTestCase
 */
abstract class QtiSmAssessmentTestSessionTestCase extends QtiSmTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @param $url
     * @param bool $considerMinTime
     * @return AssessmentTestSession
     * @throws XmlStorageException
     */
    protected static function instantiate($url, $considerMinTime = true)
    {
        $doc = new XmlCompactDocument();
        $doc->load($url);

        $manager = new SessionManager();
        $manager->setConsiderMinTime($considerMinTime);
        return $manager->createAssessmentTestSession($doc->getDocumentComponent());
    }
}
