<?php

declare(strict_types=1);

namespace qtismtest;

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\OrderingException;
use qtism\runtime\tests\SessionManager;

/**
 * Class QtiSmAssessmentTestSessionTestCase
 */
abstract class QtiSmAssessmentTestSessionTestCase extends QtiSmTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
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
    protected static function instantiate($url, $validate = false, $config = 0): AssessmentTestSession
    {
        $doc = new XmlCompactDocument();
        $doc->load($url, $validate);

        $manager = new SessionManager(new FileSystemFileManager());
        return $manager->createAssessmentTestSession($doc->getDocumentComponent(), null, $config);
    }
}
