<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmAssessmentTestSessionTestCase;
use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\runtime\tests\SessionManager;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\data\storage\xml\XmlCompactDocument;

class AssessmentTestSessionConfigInitializationAllItemsTest extends QtiSmAssessmentTestSessionTestCase {

    /**
     * @dataProvider getBranchingTestCases
     * @param string $path
     * @param int $expectedCountOfItems
     * @throws \qtism\data\storage\xml\XmlStorageException
     */
    public function testConfigInitializationAllItemsWorksProperly($path, $expectedCountOfItems)
    {
        $doc = new XmlCompactDocument('2.1');
        $doc->load($path);
        
        $manager = new SessionManager(new FileSystemFileManager());
        $testSession = $manager->createAssessmentTestSession(
            $doc->getDocumentComponent(),
            null,
            AssessmentTestSession::INITIALIZE_ALL_ITEMS
        );

        $testSession->beginTestSession();

        $this->assertEquals(
            $expectedCountOfItems,
            $testSession->getAssessmentItemSessionStore()->getAllAssessmentItemSessions()->count()
        );
    }

    public function getBranchingTestCases()
    {
        return array(
            array(self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_linear.xml', 4),
            array(self::samplesDir() . 'custom/runtime/branchings/branchings_multiple_occurences.xml', 5),
            array(self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_nonlinear.xml', 4),
        );
    }
}
