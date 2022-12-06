<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\OrderingException;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

/**
 * Class AssessmentTestSessionConfigInitializationAllItemsTest
 */
class AssessmentTestSessionConfigInitializationAllItemsTest extends QtiSmAssessmentTestSessionTestCase
{
    /**
     * @dataProvider getBranchingTestCases
     * @param int $config
     * @param string $path
     * @param int $expectedCountOfItems
     * @throws XmlStorageException
     */
    public function testConfigInitializationAllItemsWorksProperly($config, $path, $expectedCountOfItems = 1): void
    {
        $doc = new XmlCompactDocument('2.1');
        $doc->load($path);

        $manager = new SessionManager(new FileSystemFileManager());
        $testSession = $manager->createAssessmentTestSession($doc->getDocumentComponent(), null, $config);

        $testSession->beginTestSession();

        $this::assertEquals(
            $expectedCountOfItems,
            $testSession->getAssessmentItemSessionStore()->getAllAssessmentItemSessions()->count()
        );
    }

    /**
     * @return array
     */
    public function getBranchingTestCases(): array
    {
        return [
            // config INITIALIZE_ALL_ITEMS is enabled
            [AssessmentTestSession::INITIALIZE_ALL_ITEMS, self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_linear.xml', 4],
            [AssessmentTestSession::INITIALIZE_ALL_ITEMS, self::samplesDir() . 'custom/runtime/branchings/branchings_multiple_occurences.xml', 5],
            [AssessmentTestSession::INITIALIZE_ALL_ITEMS, self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_nonlinear.xml', 4],
            // config INITIALIZE_ALL_ITEMS is disabled
            [0, self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_linear.xml'],
            [0, self::samplesDir() . 'custom/runtime/branchings/branchings_multiple_occurences.xml'],
            [0, self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_nonlinear.xml'],
        ];
    }
}
