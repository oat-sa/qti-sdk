<?php

namespace qtismtest;

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\OrderingException;
use qtism\runtime\tests\SessionManager;

/**
 * Class QtiSmAssessmentTestSessionTestCase
 *
 * @package qtismtest
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
     * @param bool $validate
     * @param int $config
     * @return AssessmentTestSession
     * @throws XmlStorageException
     */
    protected static function instantiate($url, $validate = false, $config = 0)
    {
        $doc = new XmlCompactDocument();
        $doc->load($url, $validate);

        $manager = new SessionManager(new FileSystemFileManager());
        return $manager->createAssessmentTestSession($doc->getDocumentComponent(), null, $config);
    }
}
