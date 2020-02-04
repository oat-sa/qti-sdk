<?php

namespace qtismtest;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\runtime\tests\SessionManager;
use qtism\data\storage\xml\XmlCompactDocument;

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
    
    protected static function instantiate($url, $validate = false, $config = 0)
    {
        $doc = new XmlCompactDocument();
        $doc->load($url, $validate);
         
        $manager = new SessionManager(new FileSystemFileManager());
        return $manager->createAssessmentTestSession($doc->getDocumentComponent(), null, $config);
    }
}
