<?php

namespace qtismtest\data\storage\xml;

use DOMDocument;
use InvalidArgumentException;
use LogicException;
use qtism\data\AssessmentItem;
use qtism\data\AssessmentItemRef;
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\RubricBlock;
use qtism\data\content\TemplateBlock;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\A;
use qtism\data\content\xhtml\presentation\Hr;
use qtism\data\content\xhtml\text\Div;
use qtism\data\BranchRuleTargetException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmTestCase;

/**
 * Class XmlDocumentTest
 */
class XmlDocumentTest extends QtiSmTestCase
{
    public function testRubricBlockRuptureNoValidation(): void
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/paper_vs_xsd/rubricblock_other_content_than_block.xml');

        $search = $doc->getDocumentComponent()->getComponentsByClassName('rubricBlock');
        $rubricBlock = $search[0];
        $this::assertInstanceOf(RubricBlock::class, $rubricBlock);

        $content = $rubricBlock->getContent();
        $text = $content[0];
        $this::assertEquals('Hello there', substr(trim($text->getContent()), 0, 11));

        $hr = $content[2];
        $this::assertInstanceOf(Hr::class, $hr);

        $div = $content[4];
        $this::assertInstanceOf(Div::class, $div);
        $divContent = $div->getContent();
        $this::assertEquals(
            'This div and its inner text are perfectly valid from both XSD and paper spec point of views.',
            trim($divContent[0]->getContent())
        );

        $a = $content[7];
        $this::assertInstanceOf(A::class, $a);
        $aContent = $a->getContent();
        $this::assertEquals('Go to somewhere...', $aContent[0]->getContent());
    }

    public function testRubricBlockRuptureValidation(): void
    {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/rubricblock_other_content_than_block.xml';

        // We use here XSD validation.
        $valid = false;
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(
            __DIR__ . '/../../../../../src/qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd'
        );
        $this::assertTrue(
            $valid,
            'Even if the content of the rubricBlock is invalid from the paper spec point of view, it is XSD valid.'
        );

        $doc->load($file);
        $this::assertTrue(true);
    }

    public function testTemplateBlockRuptureNoValidation(): void
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/paper_vs_xsd/templateblock_other_content_than_block.xml');

        // Check the content...
        $search = $doc->getDocumentComponent()->getComponentsByClassName('templateBlock');
        $templateBlock = $search[0];
        $this::assertInstanceOf(TemplateBlock::class, $templateBlock);

        $content = $templateBlock->getContent();
        $this::assertEquals('Hello there', substr(trim($content[0]->getContent()), 0, 11));

        $hr = $content[2];
        $this::assertInstanceOf(Hr::class, $hr);

        $div = $content[4];
        $this::assertInstanceOf(Div::class, $div);
        $divContent = $div->getContent();
        $this::assertEquals(
            'This div and its inner text are perfectly valid from both XSD and paper spec point of views.',
            trim($divContent[0]->getContent())
        );

        $a = $content[7];
        $this::assertInstanceOf(A::class, $a);
        $aContent = $a->getContent();
        $this::assertEquals('Go to somewhere...', $aContent[0]->getContent());
    }

    public function testTemplateBlockRuptureValidation(): void
    {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/templateblock_other_content_than_block.xml';
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(
            __DIR__ . '/../../../../../src/qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd'
        );
        $this::assertTrue(
            $valid,
            'Even if the content of the templateBlock is invalid from the paper spec point of view, it is XSD valid.'
        );

        $doc->load($file);
        $this::assertTrue(true);
    }

    public function testFeedbackBlockRuptureNoValidation(): void
    {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/feedbackblock_other_content_than_block.xml';
        $doc->load($file);

        // Let's check the content of this...
        $test = $doc->getDocumentComponent();
        $feedbacks = $test->getComponentsByClassName('feedbackBlock');
        $this::assertCount(1, $feedbacks);

        $feedback = $feedbacks[0];
        $content = $feedback->getContent();
        $text = $content[0];
        $this::assertInstanceOf(TextRun::class, $text);
        $this::assertEquals('Hello there', substr(trim($text->getContent()), 0, 11));

        $hr = $content[2];
        $this::assertInstanceOf(Hr::class, $hr);

        $div = $content[4];
        $this::assertInstanceOf(Div::class, $div);
        $divContent = $div->getContent();
        $this::assertEquals(
            'This div and its inner text are perfectly valid from both XSD and paper spec point of views.',
            trim($divContent[0]->getContent())
        );

        $a = $content[7];
        $this::assertInstanceOf(A::class, $a);
        $aContent = $a->getContent();
        $this::assertEquals('Go to somewhere...', $aContent[0]->getContent());
    }

    public function testFeedbackBlockRuptureValidation(): void
    {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/feedbackblock_other_content_than_block.xml';
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(
            __DIR__ . '/../../../../../src/qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd'
        );
        $this::assertTrue(
            $valid,
            'Even if the content of the feedbackBlock is invalid from the paper spec point of view, it is XSD valid.'
        );

        $doc->load($file);
        $this::assertTrue(true);
    }

    public function testPromptRuptureNoValidation(): void
    {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/prompt_other_content_than_inlinestatic.xml';
        $doc->load($file);

        $search = $doc->getDocumentComponent()->getComponentsByClassName('prompt');
        $prompt = $search[0];
        $this::assertInstanceOf(Prompt::class, $prompt);

        $promptContent = $prompt->getContent();
        $this::assertEquals('Hell ', $promptContent[0]->getContent());
        $div = $promptContent[1];
        $divContent = $div->getContent();
        $this::assertEquals('YEAH!', $divContent[0]->getContent());

        $search = $doc->getDocumentComponent()->getComponentsByClassName('choiceInteraction');
        $choiceInteraction = $search[0];
        $this::assertInstanceOf(ChoiceInteraction::class, $choiceInteraction);

        $simpleChoices = $choiceInteraction->getSimpleChoices();
        $this::assertCount(1, $simpleChoices);

        $simpleChoiceContent = $simpleChoices[0]->getContent();
        $this::assertEquals('Resistance is futile!', $simpleChoiceContent[0]->getContent());
    }

    public function testPromptRuptureValidation(): void
    {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/prompt_other_content_than_inlinestatic.xml';
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(
            __DIR__ . '/../../../../../src/qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd'
        );
        $this::assertTrue(
            $valid,
            'Even if the content of the prompt is invalid from the paper spec point of view, it is XSD valid.'
        );

        $doc->load($file);
        $this::assertTrue(true);
    }

    public function testAmps(): void
    {
        $file = self::samplesDir() . 'custom/amps.xml';
        $doc = new XmlDocument();
        $doc->load($file);

        $root = $doc->getDocumentComponent();
        $divs = $root->getComponentsByClassName('div');
        $this::assertCount(1, $divs);

        $divContent = $divs[0]->getContent();
        $divText = $divContent[0];
        $this::assertEquals('Hello there & there! I am trying to make <you> "crazy"', $divText->getcontent());
    }

    public function testWrongVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new XMLDocument('2.2.1012');
    }

    public function testLoadFromString(): void
    {
        $doc = new XmlDocument('2.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');

        $component = $doc->getDocumentComponent();
        $this::assertInstanceOf(AssessmentItemRef::class, $component);
        $this::assertEquals('Q01', $component->getIdentifier());
        $this::assertEquals('./Q01.xml', $component->getHref());
    }

    public function testLoadFromEmptyString(): void
    {
        $doc = new XmlDocument('2.1');

        $expectedMsg = 'Cannot load QTI from an empty string.';
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);
        $this->expectExceptionCode(XmlStorageException::READ);

        $doc->loadFromString('');
    }

    public function testLoadFromMalformedString(): void
    {
        $doc = new XmlDocument('2.1');

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionCode(XmlStorageException::READ);

        // libxml library on Travis produces another error message.
        // Don't know how to find the version though.
        $libMxl2_9_10_Message = 'Premature end of data in tag assessmentItem line 1';
        $libMxl2_9_other_Message = 'EndTag\: \\\'<\\/\\\' not found';

        $expectedMsg = '/^An internal error occurred while parsing QTI-XML:' . "\n"
            . 'Fatal Error: (' . $libMxl2_9_10_Message . '|' . $libMxl2_9_other_Message . ') at 1\\:17\\.$/';
        $assertionMethod = method_exists($this, 'expectExceptionMessageMatches')
            ? 'expectExceptionMessageMatches'
            : 'expectExceptionMessageRegExp';
        $this->$assertionMethod($expectedMsg);

        $doc->loadFromString('<assessmentItem>');
    }

    public function testSerialization(): void
    {
        $doc = new XmlDocument('2.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');

        $this->assertEquals(
            $doc->saveToString(),
            unserialize(serialize($doc))->saveToString()
        );
    }

    public function testOlderSerializedDataDeserialization(): void
    {
        if (PHP_VERSION_ID >= 80100) {
            $this->markTestSkipped('DOM objects serialization is impossible in PHP 8.1 or higher.');
        }

        $unserializedDoc = unserialize(file_get_contents(self::samplesDir(). 'serialized/xmldocument'));
        $this->assertInstanceOf(XmlDocument::class, $unserializedDoc);

        $doc = new XmlDocument('2.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');

        $this->assertEquals(
            $doc->saveToString(),
            $unserializedDoc->saveToString()
        );
    }

    public function testLoadNoVersion(): void
    {
        $doc = new XmlDocument('2.1');

        $doc->load(self::samplesDir() . 'invalid/noversion.xml');
        $this::assertEquals('2.1.0', $doc->getVersion());
    }

    public function testLoadFromNonExistingFile(): void
    {
        $doc = new XmlDocument('2.1');
        // This path does not resolve anything.
        $path = self::samplesDir() . 'invalid/unknown.xml';

        $expectedMsg = "Cannot load QTI file at path '{$path}'. It does not exist or is not readable.";
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);
        $this->expectExceptionCode(XmlStorageException::RESOLUTION);

        $doc->load($path);
    }

    public function testLoadFromStringNotSupportedElement20(): void
    {
        // Will throw an error because assessmentItemRef is not supported in QTI 2.0.
        $doc = new XmlDocument('2.0');
        $expectedMsg = "'assessmentItemRef' components are not supported in QTI version '2.0.0'.";

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);
        $this->expectExceptionCode(XmlStorageException::VERSION);
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');
    }

    public function testSaveNoMarshaller20(): void
    {
        $doc = new XMLDocument('2.1.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');
        $doc->setVersion('2.0');

        $expectedMsg = "'assessmentItemRef' components are not supported in QTI version '2.0.0'.";
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);

        $doc->saveToString();
    }

    public function testVersionDoesNotChangeLoadFromString(): void
    {
        $doc = new XmlDocument('2.1.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');
        // Version always returned as MAJOR.MINOR.PATCH
        $this::assertEquals('2.1.1', $doc->getVersion());
    }

    public function testSaveUnknownLocation(): void
    {
        $qtiPath = '/unknown/location/qti.xml';

        $doc = new XmlDocument('2.1.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');

        $expectedMsg = sprintf(
            'An error occurred while saving QTI-XML file at \'%s\'. Maybe the save location is not reachable?',
            $qtiPath
        );
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);
        $this->expectExceptionCode(XmlStorageException::WRITE);

        $doc->save($qtiPath);
    }

    public function testSaveWrongLocationFileSystem(): void
    {
        $doc = new XmlDocument('2.1.1');
        $doc->setFilesystem($this->getOutputFileSystem());
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');

        $expectedMsg = 'An error occurred while saving QTI-XML file';
        $this->expectException(XmlStorageException::class);

        $doc->save('../../../../../../../../unknown/location.xml');
    }

    public function testUnknownClassWhileSavingBecauseOfVersion1(): void
    {
        $doc = new XmlDocument('2.1.1');
        $doc->loadFromString('
            <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float">
                <matchTable>
                    <matchTableEntry sourceValue="1" targetValue="2.5"/>
                </matchTable>
            </outcomeDeclaration>');

        // This should fail because in QTI 2.0.0, <matchTable> does not exist.
        $doc->setVersion('2.0.0');

        $expectedMsg = "'matchTable' components are not supported in QTI version '2.0.0'";
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);
        $this->expectExceptionCode(XmlStorageException::VERSION);
        $doc->saveToString();
    }

    public function testUnknownClassWhileLoadingBecauseOfVersion1(): void
    {
        $expectedMsg = "'matchTable' components are not supported in QTI version '2.0.0'";
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);
        $this->expectExceptionCode(XmlStorageException::VERSION);

        // This will fail because no <matchTable> element is defined in the 2.0.0 QTI Information Model.
        $doc = new XmlDocument('2.0.0');
        $doc->loadFromString('
            <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float">
                <matchTable>
                    <matchTableEntry sourceValue="1" targetValue="2.5"/>
                </matchTable>
            </outcomeDeclaration>');
    }

    public function testUnknownClassWhileSavingBecauseOfVersion2(): void
    {
        $doc = new XmlDocument('2.1.1');
        $doc->loadFromString('
            <sum>
                <subtract>
                    <mathConstant name="pi"/>
                    <mathConstant name="pi"/>
                </subtract>
            </sum>');

        // This should fail because in QTI 2.0.0, <mathConstant does not exist>.
        $doc->setVersion('2.0.0');

        $expectedMsg = "'mathConstant' components are not supported in QTI version '2.0.0'";
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);
        $this->expectExceptionCode(XmlStorageException::VERSION);
        $doc->saveToString();
    }

    public function testUnknownClassWhileLoadingBecauseOfVersion2(): void
    {
        $expectedMsg = "'mathConstant' components are not supported in QTI version '2.0.0'";
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);
        $this->expectExceptionCode(XmlStorageException::VERSION);

        $doc = new XmlDocument('2.0.0');
        $doc->loadFromString('
            <sum>
                <subtract>
                    <mathConstant name="pi"/>
                    <mathConstant name="pi"/>
                </subtract>
            </sum>');
    }

    public function testUnknownClassWhileSavingBecauseOfVersion3(): void
    {
        $doc = new XmlDocument('2.2.0');
        $doc->loadFromString('
            <div>
                <bdo dir="rtl">I am reversed!</bdo>
            </div>');

        // This should fail because in QTI 2.2.0 because <bdo> does not exist.
        $doc->setVersion('2.1.0');

        $expectedMsg = "'bdo' components are not supported in QTI version '2.1.0'";
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);
        $this->expectExceptionCode(XmlStorageException::VERSION);
        $doc->saveToString();
    }

    public function testUnknownClassWhileLoadingBecauseOfVersion3(): void
    {
        $expectedMsg = "'bdo' components are not supported in QTI version '2.0.0'";
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);
        $this->expectExceptionCode(XmlStorageException::VERSION);

        $doc = new XmlDocument('2.0.0');
        $doc->loadFromString('
            <div>
                <bdo dir="rtl">I am reversed!</bdo>
            </div>');
    }

    public function testInvalidAgainstXMLSchema(): void
    {
        $xsdLocation = realpath(
            __DIR__ . '/../../../../../src/qtism/data/storage/xml/versions/../schemes/qtiv2p1/imsqti_v2p1.xsd'
        );
        $expectedMsgParts = [
            "The document could not be validated with XML Schema '$xsdLocation':\n",
            'Error: Element \'{http://www.imsglobal.org/xsd/imsqti_v2p1}responseDeclaration\', attribute \'foo\': ',
            'The attribute \'foo\' is not allowed. at 9:0.',
        ];
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage(implode('', $expectedMsgParts));
        $this->expectExceptionCode(XmlStorageException::XSD_VALIDATION);

        $uri = self::samplesDir() . 'invalid/xsdinvalid.xml';
        $doc = new XmlDocument('2.1.0');
        $doc->load($uri, true);
    }
    
    /**
     * Test loading a document with valid branch rule targets
     */
    public function testValidBranchRuleTargets(): void
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/branch_rules/test_with_valid_branch_rules.xml', true);
        
        // If we get here, the document loaded and validated successfully
        $this::assertTrue(true);
    }
    
    /**
     * Test loading a document with special branch rule targets (EXIT_TEST, etc.)
     */
    public function testSpecialTargetBranchRules(): void
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/branch_rules/test_with_special_targets.xml', true);
        
        // If we get here, the document loaded and validated successfully
        $this::assertTrue(true);
    }
    
    /**
     * Test loading a document with an invalid branch rule target 
     * should throw a BranchRuleTargetException
     */
    public function testInvalidBranchRuleTarget(): void
    {
        $doc = new XmlDocument();
        
        $this->expectException(BranchRuleTargetException::class);
        $this->expectExceptionMessage('BranchRule target "nonexistent_item" does not exist in the document');
        
        $doc->load(self::samplesDir() . 'custom/branch_rules/test_with_invalid_target_branch_rule.xml', true);
    }
    
    /**
     * Test loading a document with multiple branch rules where one has an invalid target
     * should throw a BranchRuleTargetException
     */
    public function testMultipleBranchRulesWithOneInvalid(): void
    {
        $doc = new XmlDocument();
        
        $this->expectException(BranchRuleTargetException::class);
        $this->expectExceptionMessage('BranchRule target "nonexistent_item" does not exist in the document');
        
        $doc->load(self::samplesDir() . 'custom/branch_rules/test_with_multiple_branch_rules.xml', true);
    }
    
    /**
     * Test loading a document with a missing branch rule target
     * should throw an XmlStorageException due to XML Schema validation failure
     */
    public function testMissingBranchRuleTarget(): void
    {
        $doc = new XmlDocument();
        
        $this->expectException(XmlStorageException::class);
        
        $doc->load(self::samplesDir() . 'custom/branch_rules/test_with_missing_target_branch_rule.xml', true);
    }
    
    /**
     * Test loading a document with multiple invalid branch rule targets
     * should throw a BranchRuleTargetException with multiple error messages
     */
    public function testMultipleInvalidBranchRuleTargets(): void
    {
        $doc = new XmlDocument();
        
        $this->expectException(BranchRuleTargetException::class);
        $this->expectExceptionMessage('BranchRule target "nonexistent_item1" does not exist in the document; BranchRule target "nonexistent_item2" does not exist in the document');
        
        $doc->load(self::samplesDir() . 'custom/branch_rules/test_with_multiple_invalid_targets.xml', true);
    }

    public function testXIncludeNoComponent(): void
    {
        $doc = new XmlDocument();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot include fragments via XInclude before loading any file.');
        $doc->xInclude();
    }

    public function testResolveTemplateLocationNoComponent(): void
    {
        $doc = new XmlDocument();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot resolve template location before loading any file.');
        $doc->resolveTemplateLocation();
    }

    public function testIncludeAssessmentSectionRefsNoComponent(): void
    {
        $doc = new XmlDocument();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot resolve assessmentSectionRefs before loading any file.');
        $doc->includeAssessmentSectionRefs();
    }

    /**
     * @dataProvider saveNoComponentProvider
     * @param string $file
     * @param bool $filesystem
     * @throws XmlStorageException
     * @throws MarshallingException
     */
    public function testSaveNoComponent($file, $filesystem): void
    {
        $doc = new XmlDocument();

        if ($filesystem === true) {
            $doc->setFilesystem($this->getFileSystem());
        }

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage('The document cannot be saved. No document component object to be saved.');

        $doc->save($file);
    }

    /**
     * @return array
     */
    public function saveNoComponentProvider(): array
    {
        return [
            ['path.xml', true],
            ['path.xml', false],
        ];
    }

    public function testLoadFromFileSystemNoValidation(): void
    {
        $fileSystem = $this->getFileSystem();
        $doc = new XmlDocument();
        $doc->setFilesystem($fileSystem);
        $doc->load('ims/items/2_1/choice.xml');

        $this::assertInstanceOf(AssessmentItem::class, $doc->getDocumentComponent());
    }

    public function testLoadFromFileSystemPositiveValidation(): void
    {
        $fileSystem = $this->getFileSystem();
        $doc = new XmlDocument();
        $doc->setFilesystem($fileSystem);
        $doc->load('ims/items/2_1/choice.xml', true);

        $this::assertInstanceOf(AssessmentItem::class, $doc->getDocumentComponent());
    }

    public function testLoadFromFileSystemNegativeValidation(): void
    {
        $fileSystem = $this->getFileSystem();
        $doc = new XmlDocument();
        $doc->setFilesystem($fileSystem);

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage('The document could not be validated with XML Schema');

        $doc->load('invalid/xsdinvalid.xml', true);
    }

    public function testLoadFromFileSystemNotExistingFile(): void
    {
        $fileSystem = $this->getFileSystem();
        $doc = new XmlDocument();
        $doc->setFilesystem($fileSystem);
        $path = 'invalid/unknown.xml';

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage("Cannot load QTI file at path '{$path}'. It does not exist or is not readable.");

        $doc->load($path);
    }

    public function testLoadFromFileSystemEmptyFile(): void
    {
        $fileSystem = $this->getFileSystem();
        $doc = new XmlDocument();
        $doc->setFilesystem($fileSystem);
        $path = 'invalid/empty.xml';

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage('Cannot load QTI from an empty string.');

        $doc->load($path);
    }

    public function testSaveFileSystem(): void
    {
        $filesystem = $this->getFileSystem();
        $doc = new XmlDocument();
        $doc->setFilesystem($filesystem);
        $path = 'ims/items/2_1/choice.xml';

        $doc->load($path);

        $strXml = $doc->saveToString();
        $outputFilesystem = $this->getOutputFileSystem();

        $doc->setFilesystem($outputFilesystem);
        $doc->save('XmlDocumentTest/choice-test-save.xml');

        $this::assertSame($strXml, $outputFilesystem->read('XmlDocumentTest/choice-test-save.xml'));
    }

    /**
     * @dataProvider validInferQTIVersionProvider
     * @param string $file
     * @param string $expectedVersion
     * @throws XmlStorageException
     */
    public function testInferQTIVersionValid($file, $expectedVersion): void
    {
        $dom = new XmlDocument();
        $dom->load($file);
        $this::assertEquals($expectedVersion, $dom->getVersion());
    }

    /**
     * @return array
     */
    public function validInferQTIVersionProvider(): array
    {
        return [
            [self::samplesDir() . 'ims/items/2_2/choice_multiple.xml', '2.2.0'],
            [self::samplesDir() . 'ims/items/2_1_1/likert.xml', '2.1.1'],
            [self::samplesDir() . 'ims/items/2_1/associate.xml', '2.1.0'],
            [self::samplesDir() . 'ims/items/2_0/associate.xml', '2.0.0'],
            [
                self::samplesDir()
                . 'ims/tests/arbitrary_collections_of_item_outcomes/arbitrary_collections_of_item_outcomes.xml',
                '2.1.0',
            ],
            [self::samplesDir() . 'ims/items/2_2_1/choice.xml', '2.2.1'],
            [self::samplesDir() . 'ims/items/2_2_2/choice.xml', '2.2.2'],
            [self::samplesDir() . 'ims/items/2_2_3/choice.xml', '2.2.3'],
            [self::samplesDir() . 'ims/items/2_2_4/choice.xml', '2.2.4'],
            [self::samplesDir() . 'ims/items/3_0/empty_item.xml', '3.0.0'],
        ];
    }

    public function testInferVersionWithMissingNamespaceReturnsDefaultVersion(): void
    {
        $xmlDoc = new XmlDocument();

        $xmlDoc->load(self::samplesDir() . 'ims/tests/empty_tests/empty_test_missing_namespace.xml');

        $this::assertEquals('2.1.0', $xmlDoc->getVersion());
    }

    public function testInferVersionWithWrongNamespaceThrowsException(): void
    {
        $xmlDoc = new XmlDocument();

        $this->expectException(XmlStorageException::class);

        $xmlDoc->load(self::samplesDir() . 'ims/tests/empty_tests/empty_test_wrong_namespace.xml', true);
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
        $doc = new XmlDocument($fromVersion);
        $doc->load($fromFile);

        $doc->changeVersion($toVersion);

        $expected = new XmlDocument($toVersion);
        $expected->load($toFile);

        $this::assertEquals($expected->getDomDocument()->documentElement, $doc->getDomDocument()->documentElement);
    }

    /**
     * @return array
     */
    public function changeVersionProvider(): array
    {
        $path = self::samplesDir() . 'ims/tests/empty_tests/empty_test_';
        return [
            ['2.1', $path . 'v2p1.xml', '2.2', $path . 'v2p2.xml'],
            ['2.2', $path . 'v2p2.xml', '2.1', $path . 'v2p1.xml'],
            ['2.1', $path . 'v2p1.xml', '2.1.1', $path . 'v2p1p1.xml'],
            ['2.2', $path . 'v2p2.xml', '2.2.1', $path . 'v2p2p1.xml'],
            ['2.2', $path . 'v2p2.xml', '2.2.2', $path . 'v2p2p2.xml'],
            ['2.2', $path . 'v2p2.xml', '2.2.3', $path . 'v2p2p3.xml'],
            ['2.2', $path . 'v2p2.xml', '2.2.4', $path . 'v2p2p4.xml'],
        ];
    }

    public function testChangeVersionWithUnknownVersionThrowsException(): void
    {
        $wrongVersion = '36.15';
        $patchedWrongVersion = $wrongVersion . '.0';
        $file21 = self::samplesDir() . 'custom/tests/empty_compact_test/empty_compact_test_2_1.xml';

        $doc = new XmlDocument('2.1');
        $doc->load($file21);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('QTI version "' . $patchedWrongVersion . '" is not supported.');

        $doc->changeVersion($wrongVersion);
    }
}
