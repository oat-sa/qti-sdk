<?php

namespace qtismtest\data\storage\xml;

use DOMDocument;
use InvalidArgumentException;
use LogicException;
use qtism\data\AssessmentItemRef;
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\xhtml\A;
use qtism\data\content\xhtml\text\Div;
use qtism\data\content\xhtml\presentation\Hr;
use qtism\data\content\TextRun;
use qtism\data\content\TemplateBlock;
use qtism\data\content\RubricBlock;
use qtism\data\content\interactions\Prompt;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmTestCase;

/**
 * Class XmlDocumentTest
 */
class XmlDocumentTest extends QtiSmTestCase
{
    public function testRubricBlockRuptureNoValidation()
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
        $this::assertEquals('This div and its inner text are perfectly valid from both XSD and paper spec point of views.', trim($divContent[0]->getContent()));

        $a = $content[7];
        $this::assertInstanceOf(A::class, $a);
        $aContent = $a->getContent();
        $this::assertEquals('Go to somewhere...', $aContent[0]->getContent());
    }

    public function testRubricBlockRuptureValidation()
    {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/rubricblock_other_content_than_block.xml';

        // We use here XSD validation.
        $valid = false;
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(__DIR__ . '/../../../../../qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd');
        $this::assertTrue($valid, 'Even if the content of the rubricBlock is invalid from the paper spec point of view, it is XSD valid. See rupture points.');

        $doc->load($file);
        $this::assertTrue(true);
    }

    public function testTemplateBlockRuptureNoValidation()
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
        $this::assertEquals('This div and its inner text are perfectly valid from both XSD and paper spec point of views.', trim($divContent[0]->getContent()));

        $a = $content[7];
        $this::assertInstanceOf(A::class, $a);
        $aContent = $a->getContent();
        $this::assertEquals('Go to somewhere...', $aContent[0]->getContent());
    }

    public function testTemplateBlockRuptureValidation()
    {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/templateblock_other_content_than_block.xml';
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(__DIR__ . '/../../../../../qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd');
        $this::assertTrue($valid, 'Even if the content of the templateBlock is invalid from the paper spec point of view, it is XSD valid. See rupture points.');

        $doc->load($file);
        $this::assertTrue(true);
    }

    public function testFeedbackBlockRuptureNoValidation()
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
        $this::assertEquals('This div and its inner text are perfectly valid from both XSD and paper spec point of views.', trim($divContent[0]->getContent()));

        $a = $content[7];
        $this::assertInstanceOf(A::class, $a);
        $aContent = $a->getContent();
        $this::assertEquals('Go to somewhere...', $aContent[0]->getContent());
    }

    public function testFeedbackBlockRuptureValidation()
    {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/feedbackblock_other_content_than_block.xml';
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(__DIR__ . '/../../../../../qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd');
        $this::assertTrue($valid, 'Even if the content of the feedbackBlock is invalid from the paper spec point of view, it is XSD valid. See rupture points.');

        $doc->load($file);
        $this::assertTrue(true);
    }

    public function testPromptRuptureNoValidation()
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

    public function testPromptRuptureValidation()
    {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/prompt_other_content_than_inlinestatic.xml';
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(__DIR__ . '/../../../../../qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd');
        $this::assertTrue($valid, 'Even if the content of the prompt is invalid from the paper spec point of view, it is XSD valid. See rupture points.');

        $doc->load($file);
        $this::assertTrue(true);
    }

    public function testAmps()
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

    public function testWrongVersion()
    {
        $this->expectException(InvalidArgumentException::class);
        $doc = new XMLDocument('2.2.1012');
    }

    public function testLoadFromString()
    {
        $doc = new XmlDocument('2.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');

        $component = $doc->getDocumentComponent();
        $this::assertInstanceOf(AssessmentItemRef::class, $component);
        $this::assertEquals('Q01', $component->getIdentifier());
        $this::assertEquals('./Q01.xml', $component->getHref());
    }

    public function testLoadFromEmptyString()
    {
        $doc = new XmlDocument('2.1');

        $expectedMsg = 'Cannot load QTI from an empty string.';
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);

        $doc->loadFromString('');
    }

    public function testLoadFromMalformedString()
    {
        $doc = new XmlDocument('2.1');

        $this->expectException(XmlStorageException::class);

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

    public function testSerialization()
    {
        $doc = new XmlDocument('2.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');

        $this->assertEquals(
            $doc->saveToString(),
            unserialize(serialize($doc))->saveToString()
        );
    }

    public function testLoadNoVersion()
    {
        $doc = new XmlDocument('2.1');

        $doc->load(self::samplesDir() . 'invalid/noversion.xml');
        $this::assertEquals('2.1.0', $doc->getVersion());
    }

    public function testVersionDoesNotChangeLoadFromString()
    {
        $doc = new XmlDocument('2.1.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');
        // Version always returned as MAJOR.MINOR.PATCH
        $this::assertEquals('2.1.1', $doc->getVersion());
    }

    public function testInvalidAgainstXMLSchema()
    {
        $xsdLocation = realpath(__DIR__ . '/../../../../../qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd');
        $expectedMsg = "The document could not be validated with XML Schema '$xsdLocation':\n";
        $expectedMsg .= "Error: Element '{http://www.imsglobal.org/xsd/imsqti_v2p1}responseDeclaration', attribute 'foo': The attribute 'foo' is not allowed. at 9:0.";
        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage($expectedMsg);

        $uri = self::samplesDir() . 'invalid/xsdinvalid.xml';
        $doc = new XmlDocument('2.1.0');
        $doc->load($uri, true);
    }

    public function testSchemaValidateUnknownFile()
    {
        $doc = new XmlDocument();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Schema 'blub' cannot be read. Does this file exist? Is it readable?");

        $doc->schemaValidate('blub');
    }

    public function testIncludeAssessmentSectionRefsNoComponent()
    {
        $doc = new XmlDocument();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot resolve assessmentSectionRefs before loading any file.');
        $doc->includeAssessmentSectionRefs();
    }

    /**
     * @dataProvider saveNoComponentProvider
     * @param string $file
     * @throws XmlStorageException
     * @throws MarshallingException
     */
    public function testSaveNoComponent($file)
    {
        $doc = new XmlDocument();

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage('The document cannot be saved. No document component object to be saved.');

        $doc->save($file);
    }

    /**
     * @return array
     */
    public function saveNoComponentProvider()
    {
        return [
            ['path.xml'],
        ];
    }


    /**
     * @dataProvider validInferQTIVersionProvider
     * @param string $file
     * @param string $expectedVersion
     * @throws XmlStorageException
     */
    public function testInferQTIVersionValid($file, $expectedVersion)
    {
        $dom = new XmlDocument();
        $dom->load($file);
        $this::assertEquals($expectedVersion, $dom->getVersion());
    }

    /**
     * @return array
     */
    public function validInferQTIVersionProvider()
    {
        return [
            [self::samplesDir() . 'ims/items/2_2/choice_multiple.xml', '2.2.0'],
            [self::samplesDir() . 'ims/items/2_1_1/likert.xml', '2.1.1'],
            [self::samplesDir() . 'ims/items/2_1/associate.xml', '2.1.0'],
            [self::samplesDir() . 'ims/items/2_0/associate.xml', '2.0.0'],
            [self::samplesDir() . 'ims/tests/arbitrary_collections_of_item_outcomes/arbitrary_collections_of_item_outcomes.xml', '2.1.0'],
            [self::samplesDir() . 'ims/items/2_2_1/choice.xml', '2.2.1'],
            [self::samplesDir() . 'ims/items/2_2_2/choice.xml', '2.2.2'],
            [self::samplesDir() . 'ims/items/2_2_3/choice.xml', '2.2.3'],
            [self::samplesDir() . 'ims/items/2_2_4/choice.xml', '2.2.4'],
            [self::samplesDir() . 'ims/items/3_0/empty_item.xml', '3.0.0'],
        ];
    }

    public function testInferVersionWithMissingNamespaceReturnsDefaultVersion()
    {
        $xmlDoc = new XmlDocument();

        $xmlDoc->load(self::samplesDir() . 'ims/tests/empty_tests/empty_test_missing_namespace.xml');

        $this::assertEquals('2.1.0', $xmlDoc->getVersion());
    }

    public function testInferVersionWithWrongNamespaceThrowsException()
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
    public function testChangeVersion($fromVersion, $fromFile, $toVersion, $toFile)
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

    public function testChangeVersionWithUnknownVersionThrowsException()
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
