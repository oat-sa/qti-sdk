<?php

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
 *
 * @package qtismtest\data\storage\xml
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
        $this->assertEquals($itemCount, 13); // contains 13 assessmentItemRef elements.

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
        $doc = new XmlCompactDocument('2.1.0');
        $file = self::samplesDir() . 'custom/interaction_mix_sachsen_compact.xml';
        $doc->load($file);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);
        $this->assertTrue(file_exists($file));

        $doc = new XmlCompactDocument('2.1.0');
        $doc->load($file);

        // retest content...
        $this->testLoad($doc);

        unlink($file);
        $this->assertFalse(file_exists($file));
    }

    /**
     * @dataProvider testSchemaValidateProvider
     * @param string $path
     */
    public function testSchemaValidate($path)
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load($path, LIBXML_COMPACT | LIBXML_NONET | LIBXML_XINCLUDE);

        $schema = __DIR__ . '/../../../../../src/qtism/data/storage/xml/schemes/qticompact_v2p1.xsd';
        $this->assertTrue($doc->schemaValidate($schema));
    }

    /**
     * @return array
     */
    public function testSchemaValidateProvider(): array
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
     * @dataProvider createFromProvider
     * @param string $file
     * @param bool $filesystem
     * @param string $version
     * @throws XmlStorageException
     * @throws MarshallingException
     * @throws ReflectionException
     */
    public function testCreateFrom($file, $filesystem, $version = '2.1')
    {
        $inputFilesystem = $filesystem ? $this->getFileSystem() : null;
        $outputFilesystem = $filesystem ? $this->getOutputFileSystem() : null;
        $doc = new XmlDocument($version);

        $doc->setFilesystem($inputFilesystem);
        $doc->load($file);

        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, null, $version);

        $file = tempnam('/tmp', 'qsm');
        $compactDoc->setFilesystem($outputFilesystem);

        $compactDoc->save($file);

        $compactDoc = new XmlCompactDocument($version);
        $compactDoc->setFilesystem($outputFilesystem);

        $compactDoc->load($file);
        $this->testLoad($compactDoc);
    }

    /**
     * @return array
     */
    public function createFromProvider()
    {
        return [
            [self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml', false],
            ['ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml', true],
            [self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen_2_2.xml', false, '2.2'],
            ['ims/tests/interaction_mix_sachsen/interaction_mix_sachsen_2_2.xml', true, '2.2'],
        ];
    }

    /**
     * @dataProvider createFromWithUnresolvableAssessmentSectionRefProvider
     * @param $file
     * @param $filesystem
     * @throws XmlStorageException
     * @throws ReflectionException
     */
    public function testCreateFromWithUnresolvableAssessmentSectionRef($file, $filesystem)
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
    public function createFromWithUnresolvableAssessmentSectionRefProvider()
    {
        return [
            [self::samplesDir() . 'custom/interaction_mix_saschen_assessmentsectionref/interaction_mix_sachsen3.xml', false],
            ['custom/interaction_mix_saschen_assessmentsectionref/interaction_mix_sachsen3.xml', true],
        ];
    }

    /**
     * @param string $v
     * @param int $sectionCount
     * @throws XmlStorageException
     * @throws MarshallingException
     * @throws ReflectionException
     */
    public function testCreateFromExploded($v = '', $sectionCount = 2)
    {
        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . "custom/interaction_mix_saschen_assessmentsectionref/interaction_mix_sachsen${v}.xml";
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

        // Globally, we should have only one testPart, 2 sections, 13 items
        $this->assertEquals(1, count($compactDoc->getDocumentComponent()->getComponentsByClassName('testPart')));
        $this->assertEquals($sectionCount, count($compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentSection')));
        $this->assertEquals(13, count($compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef')));
        // And no more assessmentSectionRef, as they have been resolved!
        $this->assertEquals(0, count($compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentSectionRef')));

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

        $compactDoc = new XmlCompactDocument('2.1.0');
        $compactDoc->load($file);
        $compactDoc->schemaValidate();

        unlink($file);
        $this->assertFalse(file_exists($file));
    }

    public function testCreateFromExploded2()
    {
        $this->testCreateFromExploded('2', 3);
    }

    /**
     * @dataProvider createFromTestWithShuffledInteractionsProvider
     * @param $file
     * @param $filesystem
     * @throws XmlStorageException
     * @throws ReflectionException
     */
    public function testCreateFromTestWithShuffledInteractions($file, $filesystem)
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
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(['ChoiceA', 'ChoiceB', 'ChoiceC', 'ChoiceD'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());

        // Checking Q02 (orderInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q02');
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(['DriverA', 'DriverB', 'DriverC'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());

        // Checking Q03 (associateInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q03');
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(['A', 'C', 'D', 'L', 'M', 'P'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());

        // Checking Q04 (matchInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q04');
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(2, count($shufflingGroups));
        $this->assertEquals(['C', 'D', 'L', 'P'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this->assertEquals(['M', 'R', 'T'], $shufflingGroups[1]->getIdentifiers()->getArrayCopy());

        // Checking Q05 (gapMatchInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q05');
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(['W', 'Sp', 'Su', 'A'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());

        // Checking Q06 (inlineChoiceInteraction) shufflings...
        $itemRef = $compactTest->getComponentByIdentifier('Q06');
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(1, count($shufflings));
        $this->assertEquals('RESPONSE', $shufflings[0]->getResponseIdentifier());

        $shufflingGroups = $shufflings[0]->getShufflingGroups();
        $this->assertEquals(1, count($shufflingGroups));
        $this->assertEquals(['G', 'L', 'Y'], $shufflingGroups[0]->getIdentifiers()->getArrayCopy());

        // Checking Q07 (inlineChoiceInteraction) shufflings with shuffle attribute set to FALSE.
        $itemRef = $compactTest->getComponentByIdentifier('Q07');
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $itemRef);

        $shufflings = $itemRef->getShufflings();
        $this->assertEquals(0, count($shufflings));
    }

    /**
     * @return array
     */
    public function createFromTestWithShuffledInteractionsProvider()
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
    public function testLoadRubricBlockRefs($file, $filesystem, XmlCompactDocument $doc = null)
    {
        if (empty($doc)) {
            $src = $file;
            $doc = new XmlCompactDocument();

            if ($filesystem === true) {
                $doc->setFilesystem($this->getFileSystem());
            }

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

    /**
     * @return array
     */
    public function loadRubricBlockRefsProvider()
    {
        return [
            [self::samplesDir() . 'custom/runtime/rubricblockref.xml', false],
            ['custom/runtime/rubricblockref.xml', true],
        ];
    }

    public function testSaveRubricBlockRefs()
    {
        $src = self::samplesDir() . 'custom/runtime/rubricblockref.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);

        $this->assertTrue(file_exists($file));
        $this->testLoadRubricBlockRefs('', false, $doc);

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

    public function testExplodeTestFeedbacks()
    {
        $src = self::samplesDir() . 'custom/runtime/testfeedbackrefs_explosion.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src, true);
        $doc->setExplodeTestFeedbacks(true);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);
        $pathinfo = pathinfo($file);

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_P01_1.xml';
        $this->assertTrue(file_exists($path));
        $tfDoc = new XmlDocument();
        $tfDoc->load($path);
        $this->assertEquals('feedback1', $tfDoc->getDocumentComponent()->getIdentifier());

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_P01_2.xml';
        $this->assertTrue(file_exists($path));
        $tfDoc = new XmlDocument();
        $tfDoc->load($path);
        $this->assertEquals('feedback2', $tfDoc->getDocumentComponent()->getIdentifier());

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_testfeedbackrefs_explosion_1.xml';
        $this->assertTrue(file_exists($path));
        $tfDoc = new XmlDocument();
        $tfDoc->load($path);
        $this->assertEquals('mainfeedback1', $tfDoc->getDocumentComponent()->getIdentifier());

        $path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'testFeedback_TF_testfeedbackrefs_explosion_2.xml';
        $this->assertTrue(file_exists($path));
        $tfDoc = new XmlDocument();
        $tfDoc->load($path);
        $this->assertEquals('mainfeedback2', $tfDoc->getDocumentComponent()->getIdentifier());

        $this->assertEquals(0, $doc->getDocumentComponent()->containsComponentWithClassName('testFeedback'));
    }

    public function testModalFeedbackRuleLoad()
    {
        $src = self::samplesDir() . 'custom/runtime/modalfeedbackrules.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src, true);

        $test = $doc->getDocumentComponent();
        $itemRefs = $test->getComponentsByClassName('assessmentItemRef', true);
        $this->assertEquals(1, count($itemRefs));

        $feedbackRules = $itemRefs[0]->getModalFeedbackRules();
        $this->assertEquals(2, count($feedbackRules));

        $this->assertEquals('LOOKUP', $feedbackRules[0]->getOutcomeIdentifier());
        $this->assertEquals('SHOWME', $feedbackRules[0]->getIdentifier());
        $this->assertEquals(ShowHide::SHOW, $feedbackRules[0]->getShowHide());
        $this->assertEquals('Feedback 1', $feedbackRules[0]->getTitle());

        $this->assertEquals('LOOKUP2', $feedbackRules[1]->getOutcomeIdentifier());
        $this->assertEquals('HIDEME', $feedbackRules[1]->getIdentifier());
        $this->assertEquals(ShowHide::HIDE, $feedbackRules[1]->getShowHide());
        $this->assertFalse($feedbackRules[1]->hasTitle());
    }

    /**
     * @depends testModalFeedbackRuleLoad
     */
    public function testModalFeedbackRuleSave()
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
        $this->assertEquals('LOOKUP', $modalFeedbackRule1->getAttribute('outcomeIdentifier'));
        $this->assertEquals('SHOWME', $modalFeedbackRule1->getAttribute('identifier'));
        $this->assertEquals('show', $modalFeedbackRule1->getAttribute('showHide'));
        $this->assertEquals('Feedback 1', $modalFeedbackRule1->getAttribute('title'));

        $modalFeedbackRule2 = $modalFeedbackRuleElts->item(1);
        $this->assertEquals('LOOKUP2', $modalFeedbackRule2->getAttribute('outcomeIdentifier'));
        $this->assertEquals('HIDEME', $modalFeedbackRule2->getAttribute('identifier'));
        $this->assertEquals('hide', $modalFeedbackRule2->getAttribute('showHide'));
        $this->assertEquals('', $modalFeedbackRule2->getAttribute('title'));

        unlink($file);
    }

    public function testTestFeedbackRefLoad()
    {
        $src = self::samplesDir() . 'custom/runtime/test_feedback_refs.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src, true);

        $test = $doc->getDocumentComponent();
        $testFeedbackRefs = $test->getComponentsByClassName('testFeedbackRef');
        $this->assertEquals(3, count($testFeedbackRefs));
    }

    /**
     * @depends testTestFeedbackRefLoad
     */
    public function testFeedbackRefSave()
    {
        $src = self::samplesDir() . 'custom/runtime/test_feedback_refs.xml';
        $doc = new XmlCompactDocument();
        $doc->load($src, true);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load($file);

        $testFeedbackRefElts = $doc->getElementsByTagName('testFeedbackRef');
        $this->assertEquals(3, $testFeedbackRefElts->length);

        $testFeedbackRefElt1 = $testFeedbackRefElts->item(0);
        $this->assertEquals('feedback1', $testFeedbackRefElt1->getAttribute('identifier'));
        $this->assertEquals('atEnd', $testFeedbackRefElt1->getAttribute('access'));
        $this->assertEquals('show', $testFeedbackRefElt1->getAttribute('showHide'));
        $this->assertEquals('showme', $testFeedbackRefElt1->getAttribute('outcomeIdentifier'));
        $this->assertEquals('./TF01.xml', $testFeedbackRefElt1->getAttribute('href'));

        $testFeedbackRefElt2 = $testFeedbackRefElts->item(1);
        $this->assertEquals('feedback2', $testFeedbackRefElt2->getAttribute('identifier'));
        $this->assertEquals('atEnd', $testFeedbackRefElt2->getAttribute('access'));
        $this->assertEquals('show', $testFeedbackRefElt2->getAttribute('showHide'));
        $this->assertEquals('showme', $testFeedbackRefElt2->getAttribute('outcomeIdentifier'));
        $this->assertEquals('./TF02.xml', $testFeedbackRefElt2->getAttribute('href'));

        $testFeedbackRefElt3 = $testFeedbackRefElts->item(2);
        $this->assertEquals('mainfeedback1', $testFeedbackRefElt3->getAttribute('identifier'));
        $this->assertEquals('during', $testFeedbackRefElt3->getAttribute('access'));
        $this->assertEquals('show', $testFeedbackRefElt3->getAttribute('showHide'));
        $this->assertEquals('showme', $testFeedbackRefElt3->getAttribute('outcomeIdentifier'));
        $this->assertEquals('./TFMAIN.xml', $testFeedbackRefElt3->getAttribute('href'));
    }

    public function testCreateFromAssessmentTestEndAttemptIdentifiers()
    {
        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . 'custom/test_contains_endattemptinteractions.xml';
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());

        // ExtendedAssessmentItemRefs!
        $assessmentItemRefs = $compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef');
        $this->assertEquals(2, count($assessmentItemRefs));

        $assessmentItemRef = $assessmentItemRefs[0];
        $endAttemptIdentifiers = $assessmentItemRef->getEndAttemptIdentifiers();
        $this->assertEquals('Q01', $assessmentItemRef->getIdentifier());
        $this->assertEquals(1, count($endAttemptIdentifiers));
        $this->assertEquals('HINT', $endAttemptIdentifiers[0]);

        $assessmentItemRef = $assessmentItemRefs[1];
        $endAttemptIdentifiers = $assessmentItemRef->getEndAttemptIdentifiers();
        $this->assertEquals(2, count($endAttemptIdentifiers));
        $this->assertEquals('LOST', $endAttemptIdentifiers[0]);
        $this->assertEquals('LOST2', $endAttemptIdentifiers[1]);
    }

    public function testCreateFromAssessmentTestInvalidAssessmentItemRefResolution()
    {
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage("An error occurred while unreferencing item reference with identifier 'Q01'.");
        $this->expectExceptionCode(
            XmlStorageException::RESOLUTION
        );

        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . 'custom/tests/invalidassessmentitemref.xml';
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());
    }

    public function testCreateFromAssessmentTestResponseValidityConstraints()
    {
        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . 'custom/tests/response_validity_constraints.xml';
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc, new LocalFileResolver());

        $assessmentItemRefs = $compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef');

        $this->assertEquals(1, count($assessmentItemRefs[0]->getResponseValidityConstraints()));
        $this->assertEquals('RESPONSE', $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getResponseIdentifier());
        $this->assertEquals(0, $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getMinConstraint());
        $this->assertEquals(1, $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getMaxConstraint());
    }

    public function testLoadResponseValidityConstraints()
    {
        $doc = new XmlCompactDocument('2.1');
        $file = self::samplesDir() . 'custom/runtime/validate_response/nonlinear_simultaneous.xml';
        $doc->load($file, true);

        $assessmentItemRefs = $doc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef');

        $this->assertEquals(1, count($assessmentItemRefs[0]->getResponseValidityConstraints()));
        $this->assertEquals('RESPONSE', $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getResponseIdentifier());
        $this->assertEquals(0, $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getMinConstraint());
        $this->assertEquals(1, $assessmentItemRefs[0]->getResponseValidityConstraints()[0]->getMaxConstraint());

        $this->assertEquals(1, count($assessmentItemRefs[1]->getResponseValidityConstraints()));
        $this->assertEquals('RESPONSE', $assessmentItemRefs[1]->getResponseValidityConstraints()[0]->getResponseIdentifier());
        $this->assertEquals(1, $assessmentItemRefs[1]->getResponseValidityConstraints()[0]->getMinConstraint());
        $this->assertEquals(1, $assessmentItemRefs[1]->getResponseValidityConstraints()[0]->getMaxConstraint());
        $this->assertEquals('[a-z]{1,5}', $assessmentItemRefs[1]->getResponseValidityConstraints()[0]->getPatternMask());

        $this->assertEquals(2, count($assessmentItemRefs[2]->getResponseValidityConstraints()));
        $this->assertEquals('RESPONSE1', $assessmentItemRefs[2]->getResponseValidityConstraints()[0]->getResponseIdentifier());
        $this->assertEquals(0, $assessmentItemRefs[2]->getResponseValidityConstraints()[0]->getMinConstraint());
        $this->assertEquals(1, $assessmentItemRefs[2]->getResponseValidityConstraints()[0]->getMaxConstraint());
        $this->assertEquals('RESPONSE2', $assessmentItemRefs[2]->getResponseValidityConstraints()[1]->getResponseIdentifier());
        $this->assertEquals(1, $assessmentItemRefs[2]->getResponseValidityConstraints()[1]->getMinConstraint());
        $this->assertEquals(1, $assessmentItemRefs[2]->getResponseValidityConstraints()[1]->getMaxConstraint());
        $this->assertEquals('[a-z]{1,5}', $assessmentItemRefs[2]->getResponseValidityConstraints()[1]->getPatternMask());
    }

    /**
     * @depends testLoadResponseValidityConstraints
     */
    public function testLoadAssociationValidityConstraints()
    {
        $doc = new XmlCompactDocument('2.1');
        $file = self::samplesDir() . 'custom/runtime/validate_response/association_constraints.xml';
        $doc->load($file, true);

        $assessmentItemRefs = $doc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef');
        $this->assertEquals(1, count($assessmentItemRefs));

        $associationValidityConstraints = $assessmentItemRefs[0]->getComponentsByClassName('associationValidityConstraint');
        $this->assertEquals(2, count($associationValidityConstraints));

        $this->assertEquals('H', $associationValidityConstraints[0]->getIdentifier());
        $this->assertEquals(1, $associationValidityConstraints[0]->getMinConstraint());
        $this->assertEquals(1, $associationValidityConstraints[0]->getMaxConstraint());

        $this->assertEquals('O', $associationValidityConstraints[1]->getIdentifier());
        $this->assertEquals(1, $associationValidityConstraints[1]->getMinConstraint());
        $this->assertEquals(1, $associationValidityConstraints[1]->getMaxConstraint());
    }

    public function testLoadAssociationValidityConstraintsInvalidAgainstXsd()
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
    public function testCreateFromAssessmentSectionRefs($file)
    {
        $doc = new XmlDocument();
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc);

        $root = $compactDoc->getDocumentComponent();

        $testParts = $root->getTestParts();
        $this->assertTrue(isset($testParts['T01']));
        $this->assertInstanceOf(ExtendedTestPart::class, $testParts['T01']);

        $this->assertCount(1, $testParts['T01']->getAssessmentSections());
        $this->assertTrue(isset($testParts['T01']->getAssessmentSections()['S00']));

        $mainSection = $testParts['T01']->getAssessmentSections()['S00'];
        $this->assertInstanceOf(ExtendedAssessmentSection::class, $mainSection);
        $sectionParts = $mainSection->getSectionParts();
        $this->assertCount(5, $sectionParts);
        $this->assertSame(
            ['Q01', 'S01', 'Q03', 'S02', 'Q05'],
            $sectionParts->getKeys()
        );

        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $sectionParts['Q01']);
        $this->assertInstanceOf(ExtendedAssessmentSection::class, $sectionParts['S01']);
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $sectionParts['Q03']);
        $this->assertInstanceOf(ExtendedAssessmentSection::class, $sectionParts['S02']);
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $sectionParts['Q05']);

        $section = $sectionParts['S01'];
        $this->assertCount(1, $section->getSectionParts());
        $this->assertTrue(isset($section->getSectionParts()['Q02']));
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $section->getSectionParts()['Q02']);

        $section = $sectionParts['S02'];
        $this->assertCount(1, $section->getSectionParts());
        $this->assertTrue(isset($section->getSectionParts()['Q04']));
        $this->assertInstanceOf(ExtendedAssessmentItemRef::class, $section->getSectionParts()['Q04']);
    }

    /**
     * @return array
     */
    public function ceateFromAssessmentSectionRefsDataProvider()
    {
        return [
            [self::samplesDir() . 'custom/tests/mixed_assessment_section_refs/test_similar_ids.xml'],
            [self::samplesDir() . 'custom/tests/mixed_assessment_section_refs/test_different_ids.xml'],
        ];
    }

    public function testCreateFromAssessmentTestTitleAndLabels()
    {
        $doc = new XmlDocument('2.1');
        $file = self::samplesDir() . 'custom/extended_title_label.xml';
        $doc->load($file);
        $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc);

        $assessmentItemRefs = $compactDoc->getDocumentComponent()->getComponentsByClassName('assessmentItemRef');
        $this->assertEquals(3, count($assessmentItemRefs));

        $assessmentItemRef = $assessmentItemRefs[0];
        $this->assertEquals('Q01', $assessmentItemRef->getIdentifier());
        $this->assertEquals('Unattended Luggage', $assessmentItemRef->getTitle());
        $this->assertTrue($assessmentItemRef->hasTitle());
        $this->assertSame('', $assessmentItemRef->getLabel());
        $this->assertFalse($assessmentItemRef->hasLabel());

        $assessmentItemRef = $assessmentItemRefs[1];
        $this->assertEquals('Q02', $assessmentItemRef->getIdentifier());
        $this->assertEquals('Unattended Luggage', $assessmentItemRef->getTitle());
        $this->assertTrue($assessmentItemRef->hasTitle());
        $this->assertEquals('My Label', $assessmentItemRef->getLabel());
        $this->assertTrue($assessmentItemRef->hasLabel());

        $assessmentItemRef = $assessmentItemRefs[2];
        $this->assertEquals('Q03', $assessmentItemRef->getIdentifier());
        $this->assertEquals('Unattended Luggage', $assessmentItemRef->getTitle());
        $this->assertTrue($assessmentItemRef->hasTitle());
        $this->assertSame('', $assessmentItemRef->getLabel());
        $this->assertFalse($assessmentItemRef->hasLabel());
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

        $xmlDoc->load(self::samplesDir() . 'custom/tests/empty_compact_test/empty_compact_test_missing_namespace.xml');

        $this->assertEquals('2.1.0', $xmlDoc->getVersion());
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
