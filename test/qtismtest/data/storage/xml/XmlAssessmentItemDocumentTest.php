<?php

namespace qtismtest\data\storage\xml;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\AssessmentItem;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmTestCase;

/**
 * Class XmlAssessmentItemDocumentTest
 */
class XmlAssessmentItemDocumentTest extends QtiSmTestCase
{
    /**
     * @dataProvider validFileProvider
     * @param string $uri
     * @param string $expectedVersion
     * @param bool $validate
     * @throws XmlStorageException
     */
    public function testLoad(string $uri, string $expectedVersion, bool $validate = false): void
    {
        $doc = new XmlDocument();
        $doc->load($uri, $validate);
        self::assertEquals($expectedVersion, $doc->getVersion());

        self::assertInstanceOf(AssessmentItem::class, $doc->getDocumentComponent());
    }

    /**
     * @dataProvider validFileProvider
     * @param string $uri
     * @param string $expectedVersion
     * @throws XmlStorageException
     */
    public function testLoadFromString($uri, $expectedVersion)
    {
        $doc = new XmlDocument($expectedVersion);
        $doc->loadFromString(file_get_contents($uri));
        $this::assertEquals($expectedVersion, $doc->getVersion());

        $assessmentItem = $doc->getDocumentComponent();
        $this::assertInstanceOf(AssessmentItem::class, $assessmentItem);
    }

    /**
     * @dataProvider validFileProvider
     * @param string $uri
     * @param string $expectedVersion
     * @throws XmlStorageException
     * @throws MarshallingException
     */
    public function testWrite(string $uri, string $expectedVersion): void
    {
        $doc = new XmlDocument();
        $doc->load($uri);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);
        self::assertFileExists($file);

        $this->testLoad($file, $expectedVersion, true);

        unlink($file);
        // Nobody else touched it?
        self::assertFileNotExists($file);
    }

    /**
     * @dataProvider validFileProvider
     * @param string $uri
     * @param string $expectedVersion
     * @throws XmlStorageException
     * @throws MarshallingException
     */
    public function testSaveToString($uri, $expectedVersion)
    {
        $doc = new XmlDocument();
        $doc->load($uri);
        $this::assertEquals($expectedVersion, $doc->getVersion());

        $assessmentItem = $doc->getDocumentComponent();
        $this::assertInstanceOf(AssessmentItem::class, $assessmentItem);

        $file = tempnam('/tmp', 'qsm');
        file_put_contents($file, $doc->saveToString());

        $this::assertTrue(file_exists($file));
        //$this->testLoadFromString($file, $expectedVersion);

        unlink($file);
        // Nobody else touched it?
        $this::assertFileNotExists($file);
    }

    public function testLoad224()
    {
        $file = self::samplesDir() . 'ims/items/2_2_4/choice.xml';
        $doc = new XmlDocument();
        $doc->load($file);

        $this::assertEquals('2.2.4', $doc->getVersion());

        $this::assertEquals($doc->saveToString(), file_get_contents(self::samplesDir() . 'ims/items/2_2_4/choice.xml'));
    }

    public function testLoad223()
    {
        $file = self::samplesDir() . 'ims/items/2_2_3/choice.xml';
        $doc = new XmlDocument();
        $doc->load($file);

        $this::assertEquals('2.2.3', $doc->getVersion());

        $this::assertEquals($doc->saveToString(), file_get_contents(self::samplesDir() . 'ims/items/2_2_3/choice.xml'));
    }

    public function testLoad222()
    {
        $file = self::samplesDir() . 'ims/items/2_2_2/choice.xml';
        $doc = new XmlDocument();
        $doc->load($file, true);

        $this::assertEquals('2.2.2', $doc->getVersion());

        $this::assertEquals($doc->saveToString(), file_get_contents(self::samplesDir() . 'ims/items/2_2_2/choice.xml'));
    }

    public function testLoad221()
    {
        $file = self::samplesDir() . 'ims/items/2_2_1/choice_aria.xml';
        $doc = new XmlDocument();
        $doc->load($file, true);

        $this::assertEquals('2.2.1', $doc->getVersion());
        $this::assertEquals($doc->saveToString(), file_get_contents(self::samplesDir() . 'ims/items/2_2_1/choice_aria.xml'));
    }

    public function testLoad22()
    {
        $file = self::samplesDir() . 'ims/items/2_2/associate.xml';
        $doc = new XmlDocument();
        $doc->load($file, true);

        $this::assertEquals('2.2.0', $doc->getVersion());
    }

    public function testLoad22NoSchemaLocation()
    {
        $file = self::samplesDir() . 'custom/items/2_2/no_schema_location.xml';
        $doc = new XmlDocument();
        $doc->load($file, true);

        $this::assertEquals('2.2.0', $doc->getVersion());
    }

    public function testLoad211()
    {
        $file = self::samplesDir() . 'ims/items/2_1_1/associate.xml';
        $doc = new XmlDocument();
        $doc->load($file, true);

        $this::assertEquals('2.1.1', $doc->getVersion());
    }

    public function testLoad21()
    {
        $file = self::samplesDir() . 'ims/items/2_1/associate.xml';
        $doc = new XmlDocument();
        $doc->load($file, true);

        $this::assertEquals('2.1.0', $doc->getVersion());
    }

    public function testLoad21NoSchemaLocation()
    {
        $file = self::samplesDir() . 'custom/items/2_1/no_schema_location.xml';
        $doc = new XmlDocument();
        $doc->load($file, true);

        $this::assertEquals('2.1.0', $doc->getVersion());
    }

    public function testLoad20()
    {
        $file = self::samplesDir() . 'ims/items/2_0/associate.xml';
        $doc = new XmlDocument();
        $doc->load($file, true);

        $this::assertEquals('2.0.0', $doc->getVersion());
    }

    /**
     * @param string $uri
     * @throws XmlStorageException
     */
    public function testLoadTemplate($uri = '')
    {
        $file = (empty($uri)) ? self::samplesDir() . 'ims/items/2_1/template.xml' : $uri;

        $doc = new XmlDocument();
        $doc->load($file, true);

        $item = $doc->getDocumentComponent();

        // Look for all template declarations.
        $templateDeclarations = $item->getTemplateDeclarations();
        $this::assertCount(4, $templateDeclarations);

        $this::assertEquals('PEOPLE', $templateDeclarations['PEOPLE']->getIdentifier());
        $this::assertEquals(Cardinality::SINGLE, $templateDeclarations['PEOPLE']->getCardinality());
        $this::assertEquals(BaseType::STRING, $templateDeclarations['PEOPLE']->getBaseType());
        $this::assertFalse($templateDeclarations['PEOPLE']->isMathVariable());
        $this::assertFalse($templateDeclarations['PEOPLE']->isParamVariable());

        $this::assertEquals('A', $templateDeclarations['A']->getIdentifier());
        $this::assertEquals(Cardinality::SINGLE, $templateDeclarations['A']->getCardinality());
        $this::assertEquals(BaseType::INTEGER, $templateDeclarations['A']->getBaseType());
        $this::assertFalse($templateDeclarations['A']->isMathVariable());
        $this::assertFalse($templateDeclarations['A']->isParamVariable());

        $this::assertEquals('B', $templateDeclarations['B']->getIdentifier());
        $this::assertEquals(Cardinality::SINGLE, $templateDeclarations['B']->getCardinality());
        $this::assertEquals(BaseType::INTEGER, $templateDeclarations['B']->getBaseType());
        $this::assertFalse($templateDeclarations['B']->isMathVariable());
        $this::assertFalse($templateDeclarations['B']->isParamVariable());

        $this::assertEquals('MIN', $templateDeclarations['MIN']->getIdentifier());
        $this::assertEquals(Cardinality::SINGLE, $templateDeclarations['MIN']->getCardinality());
        $this::assertEquals(BaseType::INTEGER, $templateDeclarations['MIN']->getBaseType());
        $this::assertFalse($templateDeclarations['MIN']->isMathVariable());
        $this::assertFalse($templateDeclarations['MIN']->isParamVariable());
    }

    public function testWriteTemplate()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_1/template.xml');

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);
        unset($doc);

        $this->testLoadTemplate($file);

        unlink($file);
        $this::assertFileNotExists($file);
    }

    /**
     * @param string $url
     * @throws XmlStorageException
     */
    public function testLoadPCIItem($url = '')
    {
        $doc = new XmlDocument();
        $doc->load((empty($url)) ? self::samplesDir() . 'custom/interactions/custom_interaction_pci.xml' : $url, true);
        $item = $doc->getDocumentComponent();

        $this::assertInstanceOf(AssessmentItem::class, $item);
        $this::assertEquals('SimpleExample', $item->getIdentifier());
        $this::assertEquals('Example', $item->getTitle());
        $this::assertFalse($item->isAdaptive());
        $this::assertFalse($item->isTimeDependent());

        // responseDeclaration
        $responseDeclarations = $item->getComponentsByClassName('responseDeclaration');
        $this::assertCount(1, $responseDeclarations);
        $this::assertEquals(BaseType::POINT, $responseDeclarations[0]->getBaseType());
        $this::assertEquals(Cardinality::SINGLE, $responseDeclarations[0]->getCardinality());
        $this::assertEquals('RESPONSE', $responseDeclarations[0]->getIdentifier());

        // templateDeclarations
        $templateDeclarations = $item->getComponentsByClassName('templateDeclaration');
        $this::assertCount(2, $templateDeclarations);
        $this::assertEquals(BaseType::INTEGER, $templateDeclarations[0]->getBaseType());
        $this::assertEquals(Cardinality::SINGLE, $templateDeclarations[0]->getCardinality());
        $this::assertEquals('X', $templateDeclarations[0]->getIdentifier());
        $this::assertEquals(BaseType::INTEGER, $templateDeclarations[1]->getBaseType());
        $this::assertEquals(Cardinality::SINGLE, $templateDeclarations[1]->getCardinality());
        $this::assertEquals('Y', $templateDeclarations[1]->getIdentifier());

        // customInteraction
        $customInteractions = $item->getComponentsByClassName('customInteraction');
        $this::assertCount(1, $customInteractions);

        $customInteraction = $customInteractions[0];
        $this::assertEquals('RESPONSE', $customInteraction->getResponseIdentifier());
        $this::assertEquals('graph1', $customInteraction->getId());

        // xml content
        $customInteractionElt = $customInteraction->getXml()->documentElement;
        $this::assertEquals('RESPONSE', $customInteractionElt->getAttribute('responseIdentifier'));
        $this::assertEquals('graph1', $customInteractionElt->getAttribute('id'));

        $pci = 'http://www.imsglobal.org/xsd/portableCustomInteraction';
        // -- pci:portableCustomInteraction
        $portableCustomInteractionElts = $customInteractionElt->getElementsByTagNameNS($pci, 'portableCustomInteraction');
        $this::assertEquals(1, $portableCustomInteractionElts->length);
        $this::assertEquals('IW30MX6U48JF9120GJS', $portableCustomInteractionElts->item(0)->getAttribute('customInteractionTypeIdentifier'));

        // --pci:templateVariableMapping
        $templateVariableMappingElts = $customInteractionElt->getElementsByTagNameNS($pci, 'templateVariableMapping');
        $this::assertEquals(2, $templateVariableMappingElts->length);
        $this::assertEquals('X', $templateVariableMappingElts->item(0)->getAttribute('templateIdentifier'));
        $this::assertEquals('areaX', $templateVariableMappingElts->item(0)->getAttribute('configurationProperty'));
        $this::assertEquals('Y', $templateVariableMappingElts->item(1)->getAttribute('templateIdentifier'));
        $this::assertEquals('areaY', $templateVariableMappingElts->item(1)->getAttribute('configurationProperty'));

        // --pci:instance
        $instanceElts = $customInteractionElt->getElementsByTagNameNS($pci, 'instance');
        $this::assertEquals(1, $instanceElts->length);

        // --xhtml:script
        $xhtml = 'http://www.w3.org/1999/xhtml';
        $scriptElts = $instanceElts->item(0)->getElementsByTagNameNS($xhtml, 'script');
        $this::assertEquals(2, $scriptElts->length);
        $this::assertEquals('text/javascript', $scriptElts->item(0)->getAttribute('type'));
        $this::assertEquals('js/graph.js', $scriptElts->item(0)->getAttribute('src'));
        $this::assertEquals('text/javascript', $scriptElts->item(1)->getAttribute('type'));
        $this::assertEquals(7, mb_strpos($scriptElts->item(1)->nodeValue, 'qtiCustomInteractionContext.setConfiguration(', 0, 'UTF-8'));

        // --xhtml:div
        $divElts = $instanceElts->item(0)->getElementsByTagNameNS($xhtml, 'div');
        $this::assertEquals(1, $divElts->length);
        $this::assertEquals('graph1_box', $divElts->item(0)->getAttribute('id'));
        $this::assertEquals('graph', $divElts->item(0)->getAttribute('class'));
        $this::assertEquals('width:500px; height:500px;', $divElts->item(0)->getAttribute('style'));
    }

    public function testWritePCIItem()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/interactions/custom_interaction_pci.xml');

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);

        $this->testLoadPCIItem($file);
        unlink($file);
    }

    /**
     * @return array
     */
    public function validFileProvider()
    {
        return [
            // -- 2.2.4
            [self::decorateUri('essay.xml', '2.2.4'), '2.2.4'],
            [self::decorateUri('choice.xml', '2.2.4'), '2.2.4'],
            [self::decorateUri('uploadinteraction-with-single-mime-type.xml', '2.2.4'), '2.2.4'],

            // -- 2.2.3
            [self::decorateUri('essay.xml', '2.2.3'), '2.2.3'],
            [self::decorateUri('choice.xml', '2.2.3'), '2.2.3'],
            [self::decorateUri('uploadinteraction-with-single-mime-type.xml', '2.2.3'), '2.2.3'],

            // -- 2.2.2
            [self::decorateUri('essay.xml', '2.2.2'), '2.2.2'],
            [self::decorateUri('choice.xml', '2.2.2'), '2.2.2'],
            [self::decorateUri('uploadinteraction-with-single-mime-type.xml', '2.2.2'), '2.2.2'],

            // -- 2.2.1
            [self::decorateUri('choice_aria.xml', '2.2.1'), '2.2.1'],
            [self::decorateUri('choice.xml', '2.2.1'), '2.2.1'],
            [self::decorateUri('graphic_order.xml', '2.2.1'), '2.2.1'],

            // -- 2.2.0
            [self::decorateUri('adaptive_template.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('adaptive.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('associate.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('choice_fixed.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('choice_multiple.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('choice.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('extended_text_rubric.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('extended_text.xml', '2.2.0'), '2.2.0'],
            // Removed because the 2.2.0 XSD is now looking correctly at the feedback->id atomicity!
            //[self::decorateUri('feedbackblock_adaptive.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('feedbackblock_solution_random.xml', '2.2.0'), '2.2.0'],
            //[self::decorateUri('feedbackblock_templateblock.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('feedbackInline.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('gap_match.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('graphic_associate.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('graphic_gap_match.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('graphic_order.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('hotspot.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('hottext.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('inline_choice.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('likert.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('match.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('math.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('mc_calc3.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('mc_calc5.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('mc_stat2.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('modalFeedback.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('multi-input.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('nested_object.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('order.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('orkney1.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('orkney2.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('position_object.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('slider.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('template.xml', '2.2.0'), '2.2.0'],
            [self::decorateUri('text_entry.xml', '2.2.0'), '2.2.0'],

            // -- 2.1.1
            [self::decorateUri('adaptive.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('adaptive_template.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('mc_stat2.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('mc_calc3.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('mc_calc5.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('associate.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('choice_fixed.xml', '2.1.1'), '2.1.1'],
            // @todo C10 is invalid identifier? Double check! (Actually it seems the example is broken... we'll see).
            //array(self::decorateUri('choice_multiple_chocolade.xml', '2.1.1'), '2.1.1'),
            [self::decorateUri('modalFeedback.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('feedbackInline.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('choice_multiple.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('choice.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('extended_text_rubric.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('extended_text.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('gap_match.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('graphic_associate.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('graphic_gap_match.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('graphic_order.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('hotspot.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('hottext.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('inline_choice.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('match.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('multi-input.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('order.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('position_object.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('select_point.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('slider.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('text_entry.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('template.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('math.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('feedbackblock_solution_random.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('feedbackblock_adaptive.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('orkney1.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('orkney2.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('nested_object.xml', '2.1.1'), '2.1.1'],
            [self::decorateUri('likert.xml', '2.1.1'), '2.1.1'],
            //[self::decorateUri('feedbackblock_templateblock.xml', '2.1.1'), '2.1.1'],

            // -- 2.1.0
            [self::decorateUri('adaptive.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('adaptive_template.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('mc_stat2.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('mc_calc3.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('mc_calc5.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('associate.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('choice_fixed.xml', '2.1.0'), '2.1.0'],
            // @todo C10 is invalid identifier? Double check! (Actually it seems the example is fucked up... we'll see).
            //[self::decorateUri('choice_multiple_chocolade.xml', '2.1'), '2.1'],
            [self::decorateUri('modalFeedback.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('feedbackInline.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('choice_multiple.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('choice.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('extended_text_rubric.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('extended_text.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('gap_match.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('graphic_associate.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('graphic_gap_match.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('graphic_order.xml', '2.1'), '2.1.0'],
            [self::decorateUri('hotspot.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('hottext.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('inline_choice.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('match.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('multi-input.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('order.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('position_object.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('select_point.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('slider.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('text_entry.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('template.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('math.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('feedbackblock_solution_random.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('feedbackblock_adaptive.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('orkney1.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('orkney2.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('nested_object.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('likert.xml', '2.1.0'), '2.1.0'],
            [self::decorateUri('feedbackblock_templateblock.xml', '2.1'), '2.1.0'],

            // -- 2.0.0
            [self::decorateUri('associate.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('associate_lang.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('adaptive.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('choice_multiple.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('choice.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('drawing.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('extended_text.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('feedback.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('gap_match.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('graphic_associate.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('graphic_gap_match.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('graphic_order.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('hint.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('hotspot.xml', '2.0.0'), '2.0.0'],
            //array(self::decorateUri('hottext.xml', '2.0')),
            [self::decorateUri('inline_choice.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('likert.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('match.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('nested_object.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('order_partial_scoring.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('order.xml', '2.0.0'), '2.0.0'],
            [self::decorateUri('orkney1.xml', '2.0'), '2.0.0'],
            //array(self::decorateUri('position_object.xml', '2.0.0'), '2.0.0'),
            [self::decorateUri('select_point.xml', '2.0'), '2.0.0'],
            [self::decorateUri('slider.xml', '2.0'), '2.0.0'],
            [self::decorateUri('template_image.xml', '2.0'), '2.0.0'],
            [self::decorateUri('template.xml', '2.0'), '2.0.0'],
            [self::decorateUri('text_entry.xml', '2.0'), '2.0.0'],
            [self::decorateUri('upload_composite.xml', '2.0'), '2.0.0'],
            [self::decorateUri('upload.xml', '2.0'), '2.0.0'],

            // Other miscellaneous items...
            [self::samplesDir() . 'custom/items/custom_operator_item.xml', '2.1.0'],
            [self::samplesDir() . 'custom/items/rich_gap_text.xml', '2.2.0'],
            [self::samplesDir() . 'custom/items/infocontrol.xml', '2.1.0'],
            [self::samplesDir() . 'custom/items/2_2/biditorture1.xml', '2.2.0'],
            [self::samplesDir() . 'custom/items/2_2/media_video_html5.xml', '2.2.0'],
        ];
    }

    public function testRetrievePromptFromGraphicGapMatch()
    {
        $doc = new XmlDocument();
        $doc->load(self::decorateUri('graphic_gap_match.xml', '2.2'));

        $prompts = $doc->getDocumentComponent()->getComponentsByClassName('prompt');
        $this::assertCount(1, $prompts);
    }

    /**
     * @throws XmlStorageException
     */
    public function testValidMultipleMimeTypesInUploadInteraction(): void
    {
        $version = '2.2.4';
        $uri = self::decorateUri('uploadinteraction-with-multiple-mime-types.xml', $version);

        $doc = new XmlDocument();
        $doc->load($uri, true);

        $this::assertEquals($version, $doc->getVersion());
    }

    /**
     * @dataProvider invalidVersionForMultipleMimeTypesInUploadInteraction
     * @param string $version
     * @param string $path
     * @throws XmlStorageException
     */
    public function testInvalidMultipleMimeTypesInUploadInteraction(string $version, string $path): void
    {
        $uri = self::decorateUri('uploadinteraction-with-multiple-mime-types.xml', $version);
        $doc = new XmlDocument();

        $this->expectException(XmlStorageException::class);
        $this->expectExceptionMessage(
            sprintf("The document could not be validated with XML Schema '%s':\nError: Element '{http://www.imsglobal.org/xsd/imsqti_v2p2}uploadInteraction', attribute 'type': [facet 'pattern'] The value 'application/pdf image/jpeg image/png' is not accepted by the pattern '%s'. at 10:0.",
                realpath(__DIR__ . '/../../../../../src/qtism/data/storage/xml/schemes/' . $path . '.xsd'),
                '[\p{IsBasicLatin}-[()<>@,;:\\\\"/\[\]?=]]+/[\p{IsBasicLatin}-[()<>@,;:\\\\"/\[\]?=]]+'
            )
        );
        $doc->load($uri, true);
    }

    public function invalidVersionForMultipleMimeTypesInUploadInteraction()
    {
        return [
            ['2.2.3', 'qtiv2p2p3/imsqti_v2p2p3'],
            ['2.2.2', 'qtiv2p2p2/imsqti_v2p2p2'],
        ];
    }

    /**
     * @param $uri
     * @param string $version
     * @return string
     */
    private static function decorateUri($uri, $version = '2.1')
    {
        if ($version === '2.1' || $version === '2.1.0') {
            return self::samplesDir() . 'ims/items/2_1/' . $uri;
        } elseif ($version === '2.1.1') {
            return self::samplesDir() . 'ims/items/2_1_1/' . $uri;
        } elseif ($version === '2.2' || $version === '2.2.0') {
            return self::samplesDir() . 'ims/items/2_2/' . $uri;
        } elseif ($version === '2.2.1') {
            return self::samplesDir() . 'ims/items/2_2_1/' . $uri;
        } elseif ($version === '2.2.2') {
            return self::samplesDir() . 'ims/items/2_2_2/' . $uri;
        } elseif ($version === '2.2.3') {
            return self::samplesDir() . 'ims/items/2_2_3/' . $uri;
        } elseif ($version === '2.2.4') {
            return self::samplesDir() . 'ims/items/2_2_4/' . $uri;
        } elseif ($version === '3.0.0') {
            return self::samplesDir() . 'ims/items/3_0/' . $uri;
        } else {
            return self::samplesDir() . 'ims/items/2_0/' . $uri;
        }
    }
}
