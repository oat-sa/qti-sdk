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
            $this->assertEquals('2.1.0', $doc->getVersion());

            $file = self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml';
            $doc->load($file);
        }

        $doc->schemaValidate();

        $testParts = $doc->getDocumentComponent()->getTestParts();
        $this->assertEquals(1, count($testParts));
        $assessmentSections = $testParts['testpartID']->getAssessmentSections();
        $this->assertEquals(1, count($assessmentSections));
        $assessmentSection = $assessmentSections['Sektion_181865064'];
        $this->assertInstanceOf(ExtendedAssessmentSection::class, $assessmentSection);

        $assessmentItemRefs = $assessmentSections['Sektion_181865064']->getSectionParts();

        $itemCount = 0;
        foreach ($assessmentItemRefs as $k => $ref) {
            $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $assessmentItemRefs[$k]);
            $this->assertTrue($assessmentItemRefs[$k]->hasResponseProcessing());
            $this->assertFalse($assessmentItemRefs[$k]->isTimeDependent());
            $this->assertFalse($assessmentItemRefs[$k]->isAdaptive());
            $itemCount++;
        }
        $this->assertEquals(13, $itemCount); // contains 13 assessmentItemRef elements.

        // Pick up 3 for a test...
        $assessmentItemRef = $assessmentItemRefs['Choicemultiple_871212949'];
        $this->assertEquals('Choicemultiple_871212949', $assessmentItemRef->getIdentifier());
        $responseDeclarations = $assessmentItemRef->getResponseDeclarations();
        $this->assertEquals(1, count($responseDeclarations));
        $this->assertEquals('RESPONSE_27966883', $responseDeclarations['RESPONSE_27966883']->getIdentifier());
        $outcomeDeclarations = $assessmentItemRef->getOutcomeDeclarations();
        $this->assertEquals(10, count($outcomeDeclarations));
        $this->assertEquals('MAXSCORE', $outcomeDeclarations['MAXSCORE']->getIdentifier());
    }

    public function testSave()
    {
        // Version 1.0 for XmlCompactDocuments was in use by legacy code. Let's make it BC.
        $doc = new XmlCompactDocument('1.0');
        $file = self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml';
        $doc->load($file);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);
        $this->assertTrue(file_exists($file));

        $doc = new XmlCompactDocument('2.1');
        $doc->load($file);

        // retest content...
        $this->testLoad($doc);

        unlink($file);
        $this->assertFalse(file_exists($file));
    }

    /**
     * @dataProvider schemaValidateProvider
     * @param string $path
     */
    public function testSchemaValidate(string $path)
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load($path, LIBXML_COMPACT | LIBXML_NONET | LIBXML_XINCLUDE);

        $schema = __DIR__ . '/../../../../../qtism/data/storage/xml/schemes/qticompact_v2p1.xsd';
        $this->assertTrue($doc->schemaValidate($schema));
    }

    /**
     * @return array
     */
    public function schemaValidateProvider(): array
    {
        return [
            [self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml'],
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
    public function testcreateFromXmlAssessmentTestDocument($version, $file, $expectedFile)
    {
        $doc = new XmlDocument($version);
        $doc->load($file);

        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, null, null, $version);

        $newFile = tempnam('/tmp', 'qsm');
        $compactDoc->save($newFile);
        $this->assertTrue(file_exists($newFile));

        $compactDoc = new XmlCompactDocument($version);
        $compactDoc->load($newFile, true);

        $expectedDoc = new XmlCompactDocument($version);
        $expectedDoc->load($expectedFile, true);
        $this->assertEquals($expectedDoc->saveToString(), $compactDoc->saveToString());
        
        unlink($newFile);
        $this->assertFileNotExists($newFile);
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
                self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml'
            ],
            [
                '2.2',
                self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen_2_2.xml',
                self::samplesDir() . 'custom/interaction_mix_sachsen_compact_2_2.xml'
            ],
        ];
    }

    /**
     * @param XmlCompactDocument|null $compactDoc
     * @throws XmlStorageException
     * @throws MarshallingException
     */
    public function testCreateFormExploded(XmlCompactDocument $compactDoc = null)
    {
        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . 'custom/interaction_mix_saschen_assessmentsectionref/interaction_mix_sachsen.xml';
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());

        $this->assertInstanceOf(XmlCompactDocument::class, $compactDoc);
        $this->assertEquals('InteractionMixSachsen_1901710679', $compactDoc->getDocumentComponent()->getIdentifier());
        $this->assertEquals('Interaction Mix (Sachsen)', $compactDoc->getDocumentComponent()->getTitle());

        $outcomeDeclarations = $compactDoc->getDocumentComponent()->getOutcomeDeclarations();
        $this->assertEquals(2, count($outcomeDeclarations));
        $this->assertEquals('SCORE', $outcomeDeclarations['SCORE']->getIdentifier());

        $testParts = $compactDoc->getDocumentComponent()->getTestParts();
        $this->assertEquals(1, count($testParts));
        $this->assertEquals('testpartID', $testParts['testpartID']->getIdentifier());
        $this->assertEquals(NavigationMode::NONLINEAR, $testParts['testpartID']->getNavigationMode());

        $assessmentSections1stLvl = $testParts['testpartID']->getAssessmentSections();
        $this->assertEquals(1, count($assessmentSections1stLvl));
        $this->assertEquals('Container_45665458', $assessmentSections1stLvl['Container_45665458']->getIdentifier());

        $assessmentSections2ndLvl = $assessmentSections1stLvl['Container_45665458']->getSectionParts();
        $this->assertEquals(1, count($assessmentSections2ndLvl));
        $this->assertInstanceOf(ExtendedAssessmentSection::class, $assessmentSections2ndLvl['Sektion_181865064']);
        $this->assertEquals(0, count($assessmentSections2ndLvl['Sektion_181865064']->getRubricBlockRefs()));
        $this->assertEquals('Sektion_181865064', $assessmentSections2ndLvl['Sektion_181865064']->getIdentifier());

        $assessmentItemRefs = $assessmentSections2ndLvl['Sektion_181865064']->getSectionParts();
        $this->assertEquals(13, count($assessmentItemRefs));

        // Pick up 4 for a test...
        $assessmentItemRef = $assessmentItemRefs['Hotspot_278940407'];
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $assessmentItemRef);
        $this->assertEquals('Hotspot_278940407', $assessmentItemRef->getIdentifier());
        $responseDeclarations = $assessmentItemRef->getResponseDeclarations();
        $this->assertEquals(1, count($responseDeclarations));
        $this->assertEquals('RESPONSE', $responseDeclarations['RESPONSE']->getIdentifier());
        $outcomeDeclarations = $assessmentItemRef->getOutcomeDeclarations();
        $this->assertEquals(5, count($outcomeDeclarations));
        $this->assertEquals('FEEDBACKBASIC', $outcomeDeclarations['FEEDBACKBASIC']->getIdentifier());

        $file = tempnam('/tmp', 'qsm');
        $compactDoc->save($file);
        $this->assertTrue(file_exists($file));

        $compactDoc = new XmlCompactDocument('2.1');
        $compactDoc->load($file);
        $compactDoc->schemaValidate();

        unlink($file);
        $this->assertFalse(file_exists($file));
    }

    /**
     * @param XmlCompactDocument|null $doc
     * @throws XmlStorageException
     */
    public function testLoadRubricBlockRefs(XmlCompactDocument $doc = null)
    {
        if (empty($doc)) {
            $src = self::samplesDir() . 'custom/runtime/rubricblockref.xml';
            $doc = new XmlCompactDocument();
            $doc->load($src, true);
        }

        // It validates !
        $this->assertInstanceOf(AssessmentTest::class, $doc->getDocumentComponent());

        // Did we retrieve the section as ExtendedAssessmentSection objects?
        $sections = $doc->getDocumentComponent()->getComponentsByClassName('assessmentSection');
        $this->assertEquals(1, count($sections));
        $this->assertInstanceOf(ExtendedAssessmentSection::class, $sections[0]);

        // Retrieve rubricBlockRefs.
        $rubricBlockRefs = $doc->getDocumentComponent()->getComponentsByClassName('rubricBlockRef');
        $this->assertEquals(1, count($rubricBlockRefs));
        $rubricBlockRef = $rubricBlockRefs[0];
        $this->assertInstanceOf(RubricBlockRef::class, $rubricBlockRef);
        $this->assertEquals('R01', $rubricBlockRef->getIdentifier());
        $this->assertEquals('./R01.xml', $rubricBlockRef->getHref());
    }

    public function testSaveRubricBlockRefs()
    {
        $src = self::samplesDir() . 'custom/runtime/rubricblockref.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);

        $this->assertTrue(file_exists($file));
        $this->testLoadRubricBlockRefs($doc);

        unlink($file);
        $this->assertFalse(file_exists($file));
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
        $this->assertTrue(file_exists($path));
        unlink($path);
        $this->assertFalse(file_exists($path));

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'rubricBlock_RB_S01_2.xml';
        $this->assertTrue(file_exists($path));
        unlink($path);
        $this->assertFalse(file_exists($path));

        unlink($file);

        // Check rubricBlockRefs.
        $rubricBlockRefs = $doc->getDocumentComponent()->getComponentsByClassName('rubricBlockRef');
        $this->assertEquals(3, count($rubricBlockRefs));

        $this->assertEquals('./R01.xml', $rubricBlockRefs[0]->getHref());
        $this->assertEquals('R01', $rubricBlockRefs[0]->getIdentifier());
        $this->assertEquals('./rubricBlock_RB_S01_1.xml', $rubricBlockRefs[1]->getHref());
        $this->assertEquals('RB_S01_1', $rubricBlockRefs[1]->getIdentifier());
        $this->assertEquals('./rubricBlock_RB_S01_2.xml', $rubricBlockRefs[2]->getHref());
        $this->assertEquals('RB_S01_2', $rubricBlockRefs[2]->getIdentifier());
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
        $this->assertEquals($expectedVersion, $doc->getVersion());
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

    public function testInferVersionWithMissingNamespaceThrowsException()
    {
        $xmlDoc = new XmlCompactDocument();

        $this->expectException(XmlStorageException::class);

        $xmlDoc->load(self::samplesDir() . 'custom/tests/empty_compact_test/empty_compact_test_missing_namespace.xml');
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

        $this->assertEquals($expected->getDomDocument()->documentElement, $doc->getDomDocument()->documentElement);
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
