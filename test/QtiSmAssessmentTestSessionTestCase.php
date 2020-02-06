<?php

use qtism\data\storage\xml\XmlCompactDocument;
use qtism\runtime\tests\SessionManager;

require_once(dirname(__FILE__) . '/../qtism/qtism.php');
require_once(dirname(__FILE__) . '/QtiSmTestCase.php');

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

    protected static function instantiate($url, $considerMinTime = true)
    {
        $doc = new XmlCompactDocument();
        $doc->load($url);

        $manager = new SessionManager();
        $manager->setConsiderMinTime($considerMinTime);
        return $manager->createAssessmentTestSession($doc->getDocumentComponent());
    }
}
