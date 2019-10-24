<?php
namespace qtismtest\runtime\tests;

use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmAssessmentTestSessionTestCase;
use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\runtime\tests\SessionManager;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\data\storage\xml\XmlCompactDocument;

class AssessmentTestSessionConfigInitializationAllItemsTest extends QtiSmAssessmentTestSessionTestCase
{

    /**
     * @dataProvider getBranchingTestCases
     * @param int $config
     * @param string $path
     * @param int $expectedCountOfItems
     * @throws XmlStorageException
     */
    public function testConfigInitializationAllItemsWorksProperly($config, $path, $expectedCountOfItems = 1)
    {
        $doc = new XmlCompactDocument('2.1');
        $doc->load($path);

        $manager = new SessionManager(new FileSystemFileManager());
        $testSession = $manager->createAssessmentTestSession($doc->getDocumentComponent(), null, $config);

        $testSession->beginTestSession();

        $this->assertEquals(
            $expectedCountOfItems,
            $testSession->getAssessmentItemSessionStore()->getAllAssessmentItemSessions()->count()
        );
    }

    public function getBranchingTestCases()
    {
        return array(
            // config INITIALIZE_ALL_ITEMS is enabled
            array(AssessmentTestSession::INITIALIZE_ALL_ITEMS, self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_linear.xml', 4),
            array(AssessmentTestSession::INITIALIZE_ALL_ITEMS, self::samplesDir() . 'custom/runtime/branchings/branchings_multiple_occurences.xml', 5),
            array(AssessmentTestSession::INITIALIZE_ALL_ITEMS, self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_nonlinear.xml', 4),
            // config INITIALIZE_ALL_ITEMS is disabled
            array(0, self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_linear.xml'),
            array(0, self::samplesDir() . 'custom/runtime/branchings/branchings_multiple_occurences.xml'),
            array(0, self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_nonlinear.xml'),
        );
    }
}
