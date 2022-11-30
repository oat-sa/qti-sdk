<?php

declare(strict_types=1);

namespace qtismtest\data\storage\xml;

use DOMDocument;
use qtism\data\NavigationMode;
use qtism\data\ShowHide;
use qtism\data\storage\LocalFileResolver;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\versions\QtiVersionException;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmTestCase;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\ExtendedTestPart;
use qtism\data\content\RubricBlockRef;
use qtism\data\AssessmentTest;
use ReflectionException;

/**
 * Class XmlCompactAssessmentDocumentTest
 */
class XmlCompactAssessmentDocumentTest extends QtiSmTestCase
{
    /**
     * @param XmlCompactDocument|null $doc
     * @throws XmlStorageException
     */
    public function testLoad(XmlCompactDocument $doc = null): void
    {
        if ($doc === null) {
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
    public function testSave(string $version): void
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
        $this::assertFileDoesNotExist($file);
    }

    public function versionsToTest()
    {
        return [['2.1.0'],['1.0']];
    }

    /**
     * @dataProvider schemaValidateProvider
     * @param string $path
     */
    public function testSchemaValidate($path): void
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load($path, LIBXML_COMPACT | LIBXML_NONET | LIBXML_XINCLUDE);

        $schema = __DIR__ . '/../../../../../src/qtism/data/storage/xml/schemes/qticompact_v2p1.xsd';
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
     * @param bool $filesystem
     * @throws XmlStorageException
     * @throws MarshallingException
     * @throws ReflectionException
     */
    public function testCreateFromXmlAssessmentTestDocument($version, $file, $filesystem): void
    {
        $inputFilesystem = $filesystem ? $this->getFileSystem() : null;
        $outputFilesystem = $filesystem ? $this->getOutputFileSystem() : null;
        $doc = new XmlDocument($version);
        $doc->setFilesystem($inputFilesystem);
        $doc->load($file);

        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, null, $version);
        $compactDoc->setFilesystem($outputFilesystem);

        $newFile = tempnam('/tmp', 'qsm');
        $compactDoc->save($newFile);
        $this::assertFileExists($newFile);

        $compactDoc = new XmlCompactDocument($version);
        $compactDoc->setFilesystem($outputFilesystem);
        $compactDoc->load($newFile, true);

        $this->testLoad($compactDoc);
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
                false,
            ],
            [
                '2.2',
                self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen_2_2.xml',
                false,
            ],
            [
                '2.1',
                'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml',
                true,
            ],
            [
                '2.2',
                'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen_2_2.xml',
                true,
            ],
        ];
    }

    /**
     * @dataProvider createFromWithUnresolvableAssessmentSectionRefProvider
     * @param $file
     * @param $filesystem
     * @throws XmlStorageException
     * @throws ReflectionException
     */
    public function testCreateFromWithUnresolvableAssessmentSectionRef($file, $filesystem): void
    {
        $doc = new XmlDocument('2.1');

        if ($filesystem === true) {
            $doc->setFilesystem($this->getFileSystem());
        }

        $doc->load($file);

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage("An error occurred while unreferencing section reference with identifier 'Sektion_181865064'");

        XmlCompactDocument::createFromXmlAssessmentTestDocument($doc);
    }

    /**
     * @return array
     */
    public function createFromWithUnresolvableAssessmentSectionRefProvider(): array
    {
        return [
            [self::samplesDir() . 'custom/interaction_mix_saschen_assessmentsectionref/interaction_mix_sachsen3.xml', false],
            ['custom/interaction_mix_saschen_assessmentsectionref/interaction_mix_sachsen3.xml', true],
        ];
    }

    /**
     * @dataProvider createFromExplodedProvider
     * @param string $version
     * @param int $sectionCount
     * @throws XmlStorageException
     * @throws MarshallingException
     * @throws ReflectionException
     */
    public function testCreateFromExploded($version, $sectionCount): void
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
        $this::assertFileDoesNotExist($file);
    }

    public function createFromExplodedProvider()
    {
        return [
            ['', 2],
            ['2', 3],
        ];
    }

    /**
     * @dataProvider createFromTestWithShuffledInteractionsProvider
     * @param $file
     * @param $filesystem
     * @throws XmlStorageException
     * @throws ReflectionException
     */
    public function testCreateFromTestWithShuffledInteractions($file, $filesystem): void
    {
        $doc = new XmlDocument('2.1');

        if ($filesystem === true) {
            $doc->setFilesystem($this->getFileSystem());
        }

        $doc->load($file);

        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());
        $compactTest = $compactDoc->getDocumentComponent();

        // Checking Q01 (choiceInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q01');
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this::assertCount(1, $shufflings);
        $this::assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this::assertCount(1, $shufflingGroups);
        $this::assertEquals(['ChoiceA', 'ChoiceB', 'ChoiceC', 'ChoiceD'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());

        // Checking Q02 (orderInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q02');
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this::assertCount(1, $shufflings);
        $this::assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this::assertCount(1, $shufflingGroups);
        $this::assertEquals(['DriverA', 'DriverB', 'DriverC'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());

        // Checking Q03 (associateInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q03');
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this::assertCount(1, $shufflings);
        $this::assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this::assertCount(1, $shufflingGroups);
        $this::assertEquals(['A', 'C', 'D', 'L', 'M', 'P'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());

        // Checking Q04 (matchInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q04');
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this::assertCount(1, $shufflings);
        $this::assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this::assertCount(2, $shufflingGroups);
        $this::assertEquals(['C', 'D', 'L', 'P'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this::assertEquals(['M', 'R', 'T'], $shufflingGroups[1]->getIdentifiers()->getArrayCopy());

        // Checking Q05 (gapMatchInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q05');
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this::assertCount(1, $shufflings);
        $this::assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this::assertCount(1, $shufflingGroups);
        $this::assertEquals(['W', 'Sp', 'Su', 'A'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());

        // Checking Q06 (inlineChoiceInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q06');
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this::assertCount(1, $shufflings);
        $this::assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this::assertCount(1, $shufflingGroups);
        $this::assertEquals(['G', 'L', 'Y'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());

        // Checking Q07 (inlineChoiceInteraction) shufflings with shuffle attribute set to FALSE.
        $itemRef = $compactTest->getComponentByIdentifier('Q07');
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this::assertCount(0, $shufflings);
    }

    /**
     * @return array
     */
    public function createFromTestWithShuffledInteractionsProvider(): array
    {
        return [
            [self::samplesDir() . 'custom/tests/shufflings.xml', false],
            ['custom/tests/shufflings.xml', true],
        ];
    }

    /**
     * @dataProvider loadRubricBlockRefsProvider
     * @param string $file
     * @param bool $filesystem
     * @param XmlCompactDocument|null $doc
     * @throws XmlStorageException
     */
    public function testLoadRubricBlockRefs($file, $filesystem, XmlCompactDocument $doc = null): void
    {
        if ($doc === null) {
            $src = $file;
            $doc = new XmlCompactDocument();

            if ($filesystem === true) {
                $doc->setFilesystem($this->getFileSystem());
            }

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
    public function loadRubricBlockRefsProvider(): array
    {
        return [
            [self::samplesDir() . 'custom/runtime/rubricblockref.xml', false],
            ['custom/runtime/rubricblockref.xml', true],
        ];
    }

    public function testSaveRubricBlockRefs(): void
    {
        $src = self::samplesDir() . 'custom/runtime/rubricblockref.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);

        $this::assertFileExists($file);
        $this->testLoadRubricBlockRefs('', false, $doc);

        unlink($file);
        $this::assertFileDoesNotExist($file);
    }

    public function testExplodeRubricBlocks(): void
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
        $this::assertFileDoesNotExist($path);

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'rubricBlock_RB_S01_2.xml';
        $this::assertFileExists($path);
        unlink($path);
        $this::assertFileDoesNotExist($path);

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

    public function testExplodeTestFeedbacks(): void
    {
        $src = self::samplesDir() . 'custom/runtime/testfeedbackrefs_explosion.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src, true);
        $doc->setExplodeTestFeedbacks(true);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);
        $pathinfo = pathinfo($file);

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_P01_1.xml';
        $this::assertFileExists($path);
        $tfDoc = new XmlDocument();
        $tfDoc->load($path);
        $this::assertEquals('feedback1', $tfDoc->getDocumentComponent()->getIdentifier());

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_P01_2.xml';
        $this::assertFileExists($path);
        $tfDoc = new XmlDocument();
        $tfDoc->load($path);
        $this::assertEquals('feedback2', $tfDoc->getDocumentComponent()->getIdentifier());

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_testfeedbackrefs_explosion_1.xml';
        $this::assertFileExists($path);
        $tfDoc = new XmlDocument();
        $tfDoc->load($path);
        $this::assertEquals('mainfeedback1', $tfDoc->getDocumentComponent()->getIdentifier());

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_testfeedbackrefs_explosion_2.xml';
        $this::assertFileExists($path);
        $tfDoc = new XmlDocument();
        $tfDoc->load($path);
        $this::assertEquals('mainfeedback2', $tfDoc->getDocumentComponent()->getIdentifier());

        $this::assertEquals(0, $doc->getDocumentComponent()->containsComponentWithClassName('testFeedback'));
    }

    public function testModalFeedbackRuleLoad(): void
    {
        $src = self::samplesDir() . 'custom/runtime/modalfeedbackrules.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src, true);

        $test = $doc->getDocumentComponent();
        $itemRefs = $test->getComponentsByClassName('assessmentItemRef', true);
        $this::assertCount(1, $itemRefs);

        $feedbackRules = $itemRefs[0]->getModalFeedbackRules();
        $this::assertCount(2, $feedbackRules);

        $this::assertEquals('LOOKUP', $feedbackRules[0]->getOutcomeIdentifier());
        $this::assertEquals('SHOWME', $feedbackRules[0]->getIdentifier());
        $this::assertEquals(ShowHide::SHOW, $feedbackRules[0]->getShowHide());
        $this::assertEquals('Feedback 1', $feedbackRules[0]->getTitle());

        $this::assertEquals('LOOKUP2', $feedbackRules[1]->getOutcomeIdentifier());
        $this::assertEquals('HIDEME', $feedbackRules[1]->getIdentifier());
        $this::assertEquals(ShowHide::HIDE, $feedbackRules[1]->getShowHide());
        $this::assertFalse($feedbackRules[1]->hasTitle());
    }

    /**
     * @depends testModalFeedbackRuleLoad
     */
    public function testModalFeedbackRuleSave(): void
    {
        $src = self::samplesDir() . 'custom/runtime/modalfeedbackrules.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);

        // Let's load the document as DOMDocument...
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load($file);

        $modalFeedbackRuleElts = $doc->documentElement->getElementsByTagName('modalFeedbackRule');
        $modalFeedbackRule1 = $modalFeedbackRuleElts->item(0);
        $this::assertEquals('LOOKUP', $modalFeedbackRule1->getAttribute('outcomeIdentifier'));
        $this::assertEquals('SHOWME', $modalFeedbackRule1->getAttribute('identifier'));
        $this::assertEquals('show', $modalFeedbackRule1->getAttribute('showHide'));
        $this::assertEquals('Feedback 1', $modalFeedbackRule1->getAttribute('title'));

        $modalFeedbackRule2 = $modalFeedbackRuleElts->item(1);
        $this::assertEquals('LOOKUP2', $modalFeedbackRule2->getAttribute('outcomeIdentifier'));
        $this::assertEquals('HIDEME', $modalFeedbackRule2->getAttribute('identifier'));
        $this::assertEquals('hide', $modalFeedbackRule2->getAttribute('showHide'));
        $this::assertEquals('', $modalFeedbackRule2->getAttribute('title'));

        unlink($file);
    }

    public function testTestFeedbackRefLoad(): void
    {
        $src = self::samplesDir() . 'custom/runtime/test_feedback_refs.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src, true);

        $test = $doc->getDocumentComponent();
        $testFeedbackRefs = $test->getComponentsByClassName('testFeedbackRef');
        $this::assertCount(3, $testFeedbackRefs);
    }

    /**
     * @depends testTestFeedbackRefLoad
     */
    public function testFeedbackRefSave(): void
    {
        $src = self::samplesDir() . 'custom/runtime/test_feedback_refs.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src, true);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load($file);

        $testFeedbackRefElts = $doc->getElementsByTagName('testFeedbackRef');
        $this::assertEquals(3, $testFeedbackRefElts->length);

        $testFeedbackRefElt1 = $testFeedbackRefElts->item(0);
        $this::assertEquals('feedback1', $testFeedbackRefElt1->getAttribute('identifier'));
        $this::assertEquals('atEnd', $testFeedbackRefElt1->getAttribute('access'));
        $this::assertEquals('show', $testFeedbackRefElt1->getAttribute('showHide'));
        $this::assertEquals('showme', $testFeedbackRefElt1->getAttribute('outcomeIdentifier'));
        $this::assertEquals('./TF01.xml', $testFeedbackRefElt1->getAttribute('href'));

        $testFeedbackRefElt2 = $testFeedbackRefElts->item(1);
        $this::assertEquals('feedback2', $testFeedbackRefElt2->getAttribute('identifier'));
        $this::assertEquals('atEnd', $testFeedbackRefElt2->getAttribute('access'));
        $this::assertEquals('show', $testFeedbackRefElt2->getAttribute('showHide'));
        $this::assertEquals('showme', $testFeedbackRefElt2->getAttribute('outcomeIdentifier'));
        $this::assertEquals('./TF02.xml', $testFeedbackRefElt2->getAttribute('href'));

        $testFeedbackRefElt3 = $testFeedbackRefElts->item(2);
        $this::assertEquals('mainfeedback1', $testFeedbackRefElt3->getAttribute('identifier'));
        $this::assertEquals('during', $testFeedbackRefElt3->getAttribute('access'));
        $this::assertEquals('show', $testFeedbackRefElt3->getAttribute('showHide'));
        $this::assertEquals('showme', $testFeedbackRefElt3->getAttribute('outcomeIdentifier'));
        $this::assertEquals('./TFMAIN.xml', $testFeedbackRefElt3->getAttribute('href'));
    }

    public function testCreateFromAssessmentTestEndAttemptIdentifiers(): void
    {
        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . 'custom/test_contains_endattemptinteractions.xml';
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());

        // ExtendedAssessmentItemRefs!
        $assessmentItemRefs = $compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef');
        $this::assertCount(2, $assessmentItemRefs);

        $assessmentItemRef = $assessmentItemRefs[0];
        $endAttemptIdentifiers = $assessmentItemRef->getEndAttemptIdentifiers();
        $this::assertEquals('Q01', $assessmentItemRef->getIdentifier());
        $this::assertCount(1, $endAttemptIdentifiers);
        $this::assertEquals('HINT', $endAttemptIdentifiers[0]);

        $assessmentItemRef = $assessmentItemRefs[1];
        $endAttemptIdentifiers = $assessmentItemRef->getEndAttemptIdentifiers();
        $this::assertCount(2, $endAttemptIdentifiers);
        $this::assertEquals('LOST', $endAttemptIdentifiers[0]);
        $this::assertEquals('LOST2', $endAttemptIdentifiers[1]);
    }

    public function testCreateFromAssessmentTestInvalidAssessmentItemRefResolution(): void
    {
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage("An error occurred while unreferencing item reference with identifier 'Q01'.");
        $this->expectExceptionCode(XmlStorageException::RESOLUTION);

        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . 'custom/tests/invalidassessmentitemref.xml';
        $doc->load($file);
        XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());
    }

    public function testCreateFromAssessmentTestResponseValidityConstraints(): void
    {
        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . 'custom/tests/response_validity_constraints.xml';
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());

        $assessmentItemRefs = $compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef');

        $this::assertCount(1, $assessmentItemRefs[0]->getResponseValidityConstraints());
        $this::assertEquals('RESPONSE', $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getResponseIdentifier());
        $this::assertEquals(0, $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getMinConstraint());
        $this::assertEquals(1, $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getMaxConstraint());
    }

    public function testLoadResponseValidityConstraints(): void
    {
        $doc = new XmlCompactDocument('2.1');
        $file = self::samplesDir() . 'custom/runtime/validate_response/nonlinear_simultaneous.xml';
        $doc->load($file, true);

        $assessmentItemRefs = $doc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef');

        $this::assertCount(1, $assessmentItemRefs[0]->getResponseValidityConstraints());
        $this::assertEquals('RESPONSE', $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getResponseIdentifier());
        $this::assertEquals(0, $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getMinConstraint());
        $this::assertEquals(1, $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getMaxConstraint());

        $this::assertCount(1, $assessmentItemRefs[1]->getResponseValidityConstraints());
        $this::assertEquals('RESPONSE', $assessmentItemRefs[1]->getResponseValidityConstraints()[0]->getResponseIdentifier());
        $this::assertEquals(1, $assessmentItemRefs[1]->getResponseValidityConstraints()[0]->getMinConstraint());
        $this::assertEquals(1, $assessmentItemRefs[1]->getResponseValidityConstraints()[0]->getMaxConstraint());
        $this::assertEquals('[a-z]{1,5}', $assessmentItemRefs[1]->getResponseValidityConstraints()[0]->getPatternMask());

        $this::assertCount(2, $assessmentItemRefs[2]->getResponseValidityConstraints());
        $this::assertEquals('RESPONSE1', $assessmentItemRefs[2]->getResponseValidityConstraints()[0]->getResponseIdentifier());
        $this::assertEquals(0, $assessmentItemRefs[2]->getResponseValidityConstraints()[0]->getMinConstraint());
        $this::assertEquals(1, $assessmentItemRefs[2]->getResponseValidityConstraints()[0]->getMaxConstraint());
        $this::assertEquals('RESPONSE2', $assessmentItemRefs[2]->getResponseValidityConstraints()[1]->getResponseIdentifier());
        $this::assertEquals(1, $assessmentItemRefs[2]->getResponseValidityConstraints()[1]->getMinConstraint());
        $this::assertEquals(1, $assessmentItemRefs[2]->getResponseValidityConstraints()[1]->getMaxConstraint());
        $this::assertEquals('[a-z]{1,5}', $assessmentItemRefs[2]->getResponseValidityConstraints()[1]->getPatternMask());
    }

    /**
     * @depends testLoadResponseValidityConstraints
     */
    public function testLoadAssociationValidityConstraints(): void
    {
        $doc = new XmlCompactDocument('2.1');
        $file = self::samplesDir() . 'custom/runtime/validate_response/association_constraints.xml';
        $doc->load($file, true);

        $assessmentItemRefs = $doc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef');
        $this::assertCount(1, $assessmentItemRefs);

        $associationValidityConstraints = $assessmentItemRefs[0]->getComponentsByClassName('associationValidityConstraint');
        $this::assertCount(2, $associationValidityConstraints);

        $this::assertEquals('H', $associationValidityConstraints[0]->getIdentifier());
        $this::assertEquals(1, $associationValidityConstraints[0]->getMinConstraint());
        $this::assertEquals(1, $associationValidityConstraints[0]->getMaxConstraint());

        $this::assertEquals('O', $associationValidityConstraints[1]->getIdentifier());
        $this::assertEquals(1, $associationValidityConstraints[1]->getMinConstraint());
        $this::assertEquals(1, $associationValidityConstraints[1]->getMaxConstraint());
    }

    public function testLoadAssociationValidityConstraintsInvalidAgainstXsd(): void
    {
        $this->expectException(XmlStorageException::class);

        $doc = new XmlCompactDocument('2.1');
        $file = self::samplesDir() . 'custom/runtime/validate_response/association_constraints_xsd_invalid.xml';
        $doc->load($file, true);
    }

    /**
     * @dataProvider ceateFromAssessmentSectionRefsDataProvider
     * @param string $file
     * @throws XmlStorageException
     * @throws ReflectionException
     */
    public function testCreateFromAssessmentSectionRefs($file): void
    {
        $doc = new XmlDocument();
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc);

        $root = $compactDoc->getDocumentComponent();

        $testParts = $root->getTestParts();
        $this::assertTrue(isset($testParts['T01']));
        $this::assertInstanceOf(ExtendedTestPart::class, $testParts['T01']);

        $this::assertCount(1, $testParts['T01']->getAssessmentSections());
        $this::assertTrue(isset($testParts['T01']->getAssessmentSections()['S00']));

        $mainSection = $testParts['T01']->getAssessmentSections()['S00'];
        $this::assertInstanceOf(ExtendedAssessmentSection::class, $mainSection);
        $sectionParts = $mainSection->getSectionParts();
        $this::assertCount(5, $sectionParts);
        $this::assertSame(
            ['Q01', 'S01', 'Q03', 'S02', 'Q05'],
            $sectionParts->getKeys()
        );

        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $sectionParts['Q01']);
        $this::assertInstanceOf(ExtendedAssessmentSection::class, $sectionParts['S01']);
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $sectionParts['Q03']);
        $this::assertInstanceOf(ExtendedAssessmentSection::class, $sectionParts['S02']);
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $sectionParts['Q05']);

        $section = $sectionParts['S01'];
        $this::assertCount(1, $section->getSectionParts());
        $this::assertTrue(isset($section->getSectionParts()['Q02']));
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $section->getSectionParts()['Q02']);

        $section = $sectionParts['S02'];
        $this::assertCount(1, $section->getSectionParts());
        $this::assertTrue(isset($section->getSectionParts()['Q04']));
        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $section->getSectionParts()['Q04']);
    }

    /**
     * @return array
     */
    public function ceateFromAssessmentSectionRefsDataProvider(): array
    {
        return [
            [self::samplesDir() . 'custom/tests/mixed_assessment_section_refs/test_similar_ids.xml'],
            [self::samplesDir() . 'custom/tests/mixed_assessment_section_refs/test_different_ids.xml'],
        ];
    }

    public function testCreateFromAssessmentTestTitleAndLabels(): void
    {
        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . 'custom/extended_title_label.xml';
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc);

        $assessmentItemRefs = $compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef');
        $this::assertCount(3, $assessmentItemRefs);

        $assessmentItemRef = $assessmentItemRefs[0];
        $this::assertEquals('Q01', $assessmentItemRef->getIdentifier());
        $this::assertEquals('Unattended Luggage', $assessmentItemRef->getTitle());
        $this::assertTrue($assessmentItemRef->hasTitle());
        $this::assertSame('', $assessmentItemRef->getLabel());
        $this::assertFalse($assessmentItemRef->hasLabel());

        $assessmentItemRef = $assessmentItemRefs[1];
        $this::assertEquals('Q02', $assessmentItemRef->getIdentifier());
        $this::assertEquals('Unattended Luggage', $assessmentItemRef->getTitle());
        $this::assertTrue($assessmentItemRef->hasTitle());
        $this::assertEquals('My Label', $assessmentItemRef->getLabel());
        $this::assertTrue($assessmentItemRef->hasLabel());

        $assessmentItemRef = $assessmentItemRefs[2];
        $this::assertEquals('Q03', $assessmentItemRef->getIdentifier());
        $this::assertEquals('Unattended Luggage', $assessmentItemRef->getTitle());
        $this::assertTrue($assessmentItemRef->hasTitle());
        $this::assertSame('', $assessmentItemRef->getLabel());
        $this::assertFalse($assessmentItemRef->hasLabel());
    }

    /**
     * @dataProvider inferVersionAndSchemaValidateProvider
     * @param string $testFile
     * @param string $expectedVersion
     * @throws XmlStorageException
     */
    public function testInferVersionAndSchemaValidate(string $testFile, string $expectedVersion): void
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

    public function testInferVersionWithMissingNamespaceReturnsDefaultVersion(): void
    {
        $xmlDoc = new XmlCompactDocument();

        $xmlDoc->load(self::samplesDir() . 'custom/tests/empty_compact_test/empty_compact_test_missing_namespace.xml');

        $this::assertEquals('2.1.0', $xmlDoc->getVersion());
    }

    public function testInferVersionWithWrongNamespaceThrowsException(): void
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
    public function testChangeVersion($fromVersion, $fromFile, $toVersion, $toFile): void
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

    public function testChangeVersionWithUnknownVersionThrowsException(): void
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
