<?php

namespace qtismtest\data\storage\xml;

use DOMDocument;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmTestCase;
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\xhtml\A;
use qtism\data\content\xhtml\text\Div;
use qtism\data\content\xhtml\presentation\Hr;
use qtism\data\content\TextRun;
use qtism\data\content\TemplateBlock;
use qtism\data\content\RubricBlock;
use qtism\data\content\interactions\Prompt;

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
        $this::assertEquals(1, count($feedbacks));

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
        $this::assertEquals(1, count($simpleChoices));

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
        $this::assertEquals(1, count($divs));

        $divContent = $divs[0]->getContent();
        $divText = $divContent[0];
        $this::assertEquals('Hello there & there! I am trying to make <you> "crazy"', $divText->getcontent());
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
            [self::samplesDir() . 'ims/items/3_0/empty_item.xml', '3.0.0'],
        ];
    }

    public function testInferVersionWithMissingNamespaceThrowsException()
    {
        $xmlDoc = new XmlDocument();

        $this->expectException(XmlStorageException::class);

        $xmlDoc->load(self::samplesDir() . 'custom/tests/empty_compact_test/empty_compact_test_missing_namespace.xml');
    }
}
