<?php

namespace qtismtest\data\storage\xml;

use DOMDocument;
use qtism\data\NavigationMode;
use qtism\data\storage\LocalFileResolver;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\versions\QtiVersionException;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmTestCase;
use qtism\data\content\RubricBlockRef;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\AssessmentTest;
use qtism\data\ExtendedAssessmentItemRef;

/**
 * Class XmlCompactAssessmentDocumentTest
 */
class XmlCompactAssessmentDocumentTest extends QtiSmTestCase
{
    /**
     * @param XmlCompactDocument|null $doc
     * @throws XmlStorageException
     */
    public function testLoad(XmlCompactDocument $doc = null)
    {
        if (empty($doc)) {
            $doc = new XmlCompactDocument('2.1');
            $this::assertEquals('2.1.0', $doc->getVersion());

            $file = self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml';
            $doc->load($file);
        }

        $doc->schemaValidate();

        $testParts = $doc->getDocumentComponent()->getTestParts();
        $this::assertCount(1, $testParts);
        $assessmentSections = $testParts['testpartID']->getAssessmentSections();
        $this::assertCount(1, $assessmentSections);
        $assessmentSection = $assessmentSections['Sektion_181865064'];
        $this::assertInstanceOf(ExtendedAssessmentSection::class, $assessmentSection);

        $assessmentItemRefs = $assessmentSections['Sektion_181865064']->getSectionParts();

        $itemCount = 0;
        foreach ($assessmentItemRefs as $k => $ref) {
            $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $assessmentItemRefs[$k]);
            $this::assertTrue($assessmentItemRefs[$k]->hasResponseProcessing());
            $this::assertFalse($assessmentItemRefs[$k]->isTimeDependent());
            $this::assertFalse($assessmentItemRefs[$k]->isAdaptive());
            $itemCount++;
        }
        $this::assertEquals(13, $itemCount); // contains 13 assessmentItemRef elements.

        // Pick up 3 for a test...
        $assessmentItemRef = $assessmentItemRefs['Choicemultiple_871212949'];
        $this::assertEquals('Choicemultiple_871212949', $assessmentItemRef->getIdentifier());
        $responseDeclarations = $assessmentItemRef->getResponseDeclarations();
        $this::assertCount(1, $responseDeclarations);
        $this::assertEquals('RESPONSE_27966883', $responseDeclarations['RESPONSE_27966883']->getIdentifier());
        $outcomeDeclarations = $assessmentItemRef->getOutcomeDeclarations();
        $this::assertCount(10, $outcomeDeclarations);
        $this::assertEquals('MAXSCORE', $outcomeDeclarations['MAXSCORE']->getIdentifier());
    }

    /**
     * @dataProvider versionsToTest
     * @param string $version
     */
    public function testSave(string $version)
    {
        // Version 1.0 for XmlCompactDocuments was in use by legacy code. Let's make it BC.
        $doc = new XmlCompactDocument($version);
        $file = self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml';
        $doc->load($file);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);
        $this::assertFileExists($file);

        $doc = new XmlCompactDocument('2.1.0');
        $doc->load($file);

        // retest content...
        $this->testLoad($doc);

        unlink($file);
        $this::assertFileNotExists($file);
    }

    public function versionsToTest()
    {
        return [['2.1.0'],['1.0']];
    }

    /**
     * @dataProvider schemaValidateProvider
     * @param string $path
     */
    public function testSchemaValidate($path)
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load($path, LIBXML_COMPACT | LIBXML_NONET | LIBXML_XINCLUDE);

        $schema = __DIR__ . '/../../../../../qtism/data/storage/xml/schemes/qticompact_v2p1.xsd';
        $this::assertTrue($doc->schemaValidate($schema));
    }

    /**
     * @return array
     */
    public function schemaValidateProvider(): array
    {
        return [
            [self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml'],
            [self::samplesDir() . 'custom/runtime/test_feedback_refs.xml'],
            [self::samplesDir() . 'custom/runtime/endAttemptIdentifiers.xml'],
            [self::samplesDir() . 'custom/runtime/shuffling/shuffling_groups.xml'],
            [self::samplesDir() . 'custom/runtime/validate_response/response_validity_constraints.xml'],
        ];
    }

    /**
     * @dataProvider createFromXmlAssessmentTestDocumentProvider
     * @param string $version
     * @param string $file
     * @param string $expectedFile
     * @throws XmlStorageException
     * @throws MarshallingException
     */
    public function testCreateFromXmlAssessmentTestDocument($version, $file, $expectedFile)
    {
        $doc = new XmlDocument($version);
        $doc->load($file);

        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, null, null, $version);

        $newFile = tempnam('/tmp', 'qsm');
        $compactDoc->save($newFile);
        $this::assertFileExists($newFile);

        $compactDoc = new XmlCompactDocument($version);
        $compactDoc->load($newFile, true);

        $expectedDoc = new XmlCompactDocument($version);
        $expectedDoc->load($expectedFile, true);
        $this::assertEquals($expectedDoc->saveToString(), $compactDoc->saveToString());
        
        unlink($newFile);
        $this::assertFileNotExists($newFile);
    }

    /**
     * @return array
     */
    public function createFromXmlAssessmentTestDocumentProvider(): array
    {
        return [
            [
                '2.1',
                self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml',
                self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml',
            ],
            [
                '2.2',
                self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen_2_2.xml',
                self::samplesDir() . 'custom/interaction_mix_sachsen_compact_2_2.xml',
            ],
        ];
    }

    /**
     * @dataProvider createFromWithUnresolvableAssessmentSectionRefProvider
     * @param $file
     * @throws XmlStorageException
     */
    public function testCreateFromWithUnresolvableAssessmentSectionRef($file)
    {
        $doc = new XmlDocument('2.1');

        $doc->load($file);

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage("An error occurred while unreferencing section reference with identifier 'Sektion_181865064'");

        XmlCompactDocument::createFromXmlAssessmentTestDocument($doc);
    }

    /**
     * @return array
     */
    public function createFromWithUnresolvableAssessmentSectionRefProvider()
    {
        return [
            [self::samplesDir() . 'custom/interaction_mix_saschen_assessmentsectionref/interaction_mix_sachsen3.xml'],
        ];
    }

    /**
     * @dataProvider createFromExplodedProvider
     * @param string $version
     * @param int $sectionCount
     * @throws XmlStorageException
     * @throws MarshallingException
     */
    public function testCreateFromExploded($version, $sectionCount)
    {
        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . 'custom/interaction_mix_saschen_assessmentsectionref/interaction_mix_sachsen' . $version . '.xml';
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());

        $this::assertInstanceOf(XmlCompactDocument::class, $compactDoc);
        $this::assertEquals('InteractionMixSachsen_1901710679', $compactDoc->getDocumentComponent()->getIdentifier());
        $this::assertEquals('Interaction Mix (Sachsen)', $compactDoc->getDocumentComponent()->getTitle());

        $outcomeDeclarations = $compactDoc->getDocumentComponent()->getOutcomeDeclarations();
        $this::assertCount(2, $outcomeDeclarations);
        $this::assertEquals('SCORE', $outcomeDeclarations['SCORE']->getIdentifier());

        $testParts = $compactDoc->getDocumentComponent()->getTestParts();
        $this::assertCount(1, $testParts);
        $this::assertEquals('testpartID', $testParts['testpartID']->getIdentifier());
        $this::assertEquals(NavigationMode::NONLINEAR, $testParts['testpartID']->getNavigationMode());

        $assessmentSections1stLvl = $testParts['testpartID']->getAssessmentSections();
        $this::assertCount(1, $assessmentSections1stLvl);
        $this::assertEquals('Container_45665458', $assessmentSections1stLvl['Container_45665458']->getIdentifier());

        $assessmentSections2ndLvl = $assessmentSections1stLvl['Container_45665458']->getSectionParts();
        $this::assertCount(1, $assessmentSections2ndLvl);
        $this::assertInstanceOf(ExtendedAssessmentSection::class, $assessmentSections2ndLvl['Sektion_181865064']);
        $this::assertCount(0, $assessmentSections2ndLvl['Sektion_181865064']->getRubricBlockRefs());
        $this::assertEquals('Sektion_181865064', $assessmentSections2ndLvl['Sektion_181865064']->getIdentifier());

        $assessmentItemRefs = $assessmentSections2ndLvl['Sektion_181865064']->getSectionParts();
        $this::assertCount(13, $assessmentItemRefs);

        // Globally, we should have only one testPart, 2 sections, 13 items
        $this::assertCount(1, $compactDoc->getDocumentComponent()->getComponentsByClassName('testPart'));
        $this::assertEquals($sectionCount, count($compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentSection')));
        $this::assertCount(13, $compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef'));
        // And no more assessmentSectionRef, as they have been resolved!
        $this::assertCount(0, $compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentSectionRef'));

        // Pick up 4 for a test...
        $assessmentItemRef = $assessmentItemRefs['Hotspot_278940407'];
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $assessmentItemRef);
        $this::assertEquals('Hotspot_278940407', $assessmentItemRef->getIdentifier());
        $responseDeclarations = $assessmentItemRef->getResponseDeclarations();
        $this::assertCount(1, $responseDeclarations);
        $this::assertEquals('RESPONSE', $responseDeclarations['RESPONSE']->getIdentifier());
        $outcomeDeclarations = $assessmentItemRef->getOutcomeDeclarations();
        $this::assertCount(5, $outcomeDeclarations);
        $this::assertEquals('FEEDBACKBASIC', $outcomeDeclarations['FEEDBACKBASIC']->getIdentifier());

        $file = tempnam('/tmp', 'qsm');
        $compactDoc->save($file);
        $this::assertFileExists($file);

        $compactDoc = new XmlCompactDocument('2.1.0');
        $compactDoc->load($file);
        $compactDoc->schemaValidate();

        unlink($file);
        $this::assertFileNotExists($file);
    }

    public function createFromExplodedProvider()
    {
        return [
            ['', 2],
        ];
    }

    /**
     * @dataProvider loadRubricBlockRefsProvider
     * @param string $file
     * @param XmlCompactDocument|null $doc
     * @throws XmlStorageException
     */
    public function testLoadRubricBlockRefs($file, XmlCompactDocument $doc = null)
    {
        if ($doc === null) {
            $src = $file;
            $doc = new XmlCompactDocument();
            $doc->load($src, true);
        }

        // It validates !
        $this::assertInstanceOf(AssessmentTest::class, $doc->getDocumentComponent());

        // Did we retrieve the section as ExtendedAssessmentSection objects?
        $sections = $doc->getDocumentComponent()->getComponentsByClassName('assessmentSection');
        $this::assertCount(1, $sections);
        $this::assertInstanceOf(ExtendedAssessmentSection::class, $sections[0]);

        // Retrieve rubricBlockRefs.
        $rubricBlockRefs = $doc->getDocumentComponent()->getComponentsByClassName('rubricBlockRef');
        $this::assertCount(1, $rubricBlockRefs);
        $rubricBlockRef = $rubricBlockRefs[0];
        $this::assertInstanceOf(RubricBlockRef::class, $rubricBlockRef);
        $this::assertEquals('R01', $rubricBlockRef->getIdentifier());
        $this::assertEquals('./R01.xml', $rubricBlockRef->getHref());
    }

    /**
     * @return array
     */
    public function loadRubricBlockRefsProvider()
    {
        return [
            [self::samplesDir() . 'custom/runtime/rubricblockref.xml'],
        ];
    }

    public function testSaveRubricBlockRefs()
    {
        $src = self::samplesDir() . 'custom/runtime/rubricblockref.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);

        $this::assertFileExists($file);
        $this->testLoadRubricBlockRefs('', $doc);

        unlink($file);
        $this::assertFileNotExists($file);
    }

    public function testExplodeRubricBlocks()
    {
        $src = self::samplesDir() . 'custom/runtime/rubricblockrefs_explosion.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src, true);
        $doc->setExplodeRubricBlocks(true);

        $file = tempnam('/tmp', 'qsm');

        $doc->save($file);

        // Are external rubricBlocks set?
        $pathinfo = pathinfo($file);

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'rubricBlock_RB_S01_1.xml';
        $this::assertFileExists($path);
        unlink($path);
        $this::assertFileNotExists($path);

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'rubricBlock_RB_S01_2.xml';
        $this::assertFileExists($path);
        unlink($path);
        $this::assertFileNotExists($path);

        unlink($file);

        // Check rubricBlockRefs.
        $rubricBlockRefs = $doc->getDocumentComponent()->getComponentsByClassName('rubricBlockRef');
        $this::assertCount(3, $rubricBlockRefs);

        $this::assertEquals('./R01.xml', $rubricBlockRefs[0]->getHref());
        $this::assertEquals('R01', $rubricBlockRefs[0]->getIdentifier());
        $this::assertEquals('./rubricBlock_RB_S01_1.xml', $rubricBlockRefs[1]->getHref());
        $this::assertEquals('RB_S01_1', $rubricBlockRefs[1]->getIdentifier());
        $this::assertEquals('./rubricBlock_RB_S01_2.xml', $rubricBlockRefs[2]->getHref());
        $this::assertEquals('RB_S01_2', $rubricBlockRefs[2]->getIdentifier());
    }

    public function testCreateFromAssessmentTestInvalidAssessmentItemRefResolution()
    {
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage("An error occurred while unreferencing item reference with identifier 'Q01'.");

        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . 'custom/tests/invalidassessmentitemref.xml';
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());
    }

    /**
     * @dataProvider inferVersionAndSchemaValidateProvider
     * @param string $testFile
     * @param string $expectedVersion
     * @throws XmlStorageException
     */
    public function testInferVersionAndSchemaValidate(string $testFile, string $expectedVersion)
    {
        $doc = new XmlCompactDocument();
        $doc->load($testFile, true);
        $this::assertEquals($expectedVersion, $doc->getVersion());
    }

    /**
     * @return array
     */
    public function inferVersionAndSchemaValidateProvider(): array
    {
        $path = self::samplesDir() . 'custom/tests/empty_compact_test/';

        return [
            [$path . 'empty_compact_test_2_1.xml', '2.1.0'],
            [$path . 'empty_compact_test_2_2.xml', '2.2.0'],

            // 2.1 was previously 1.0. Keeping it for BC.
            [$path . 'empty_compact_test_1_0.xml', '2.1.0'],
        ];
    }

    public function testInferVersionWithMissingNamespaceReturnsDefaultVersion()
    {
        $xmlDoc = new XmlCompactDocument();

        $xmlDoc->load(self::samplesDir() . 'custom/tests/empty_compact_test/empty_compact_test_missing_namespace.xml');

        $this::assertEquals('2.1.0', $xmlDoc->getVersion());
    }

    public function testInferVersionWithWrongNamespaceThrowsException()
    {
        $xmlDoc = new XmlCompactDocument();

        $this->expectException(XmlStorageException::class);

        $xmlDoc->load(self::samplesDir() . 'custom/tests/empty_compact_test/empty_compact_test_wrong_namespace.xml', true);
    }

    /**
     * @dataProvider changeVersionProvider
     * @param string $fromVersion
     * @param string $fromFile
     * @param string $toVersion
     * @param string $toFile
     * @throws XmlStorageException
     */
    public function testChangeVersion($fromVersion, $fromFile, $toVersion, $toFile)
    {
        $doc = new XmlCompactDocument($fromVersion);
        $doc->load($fromFile);

        $doc->changeVersion($toVersion);

        $expected = new XmlCompactDocument($toVersion);
        $expected->load($toFile);

        $this::assertEquals($expected->getDomDocument()->documentElement, $doc->getDomDocument()->documentElement);
    }

    /**
     * @return array
     */
    public function changeVersionProvider(): array
    {
        $path = self::samplesDir() . 'custom/tests/empty_compact_test/empty_compact_test_';
        return [
            ['2.1', $path . '2_1.xml', '2.2', $path . '2_2.xml'],
            ['2.2', $path . '2_2.xml', '2.1', $path . '2_1.xml'],
        ];
    }

    public function testChangeVersionWithUnknownVersionThrowsException()
    {
        $wrongVersion = '36.15';
        $patchedWrongVersion = $wrongVersion . '.0';
        $file21 = self::samplesDir() . 'custom/tests/empty_compact_test/empty_compact_test_2_1.xml';

        $doc = new XmlCompactDocument('2.1');
        $doc->load($file21);

        $this->expectException(QtiVersionException::class);
        $this->expectExceptionMessage('QTI Compact is not supported for version "' . $patchedWrongVersion . '".');

        $doc->changeVersion($wrongVersion);
    }
}
