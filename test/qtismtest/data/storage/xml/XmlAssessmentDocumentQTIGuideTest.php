<?php

namespace qtismtest\data\storage\xml;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\NavigationMode;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtism\data\SubmissionMode;
use qtismtest\QtiSmTestCase;
use qtism\data\expressions\TestVariables;
use qtism\data\expressions\operators\Sum;
use qtism\data\rules\SetOutcomeValue;
use qtism\data\processing\OutcomeProcessing;
use qtism\data\AssessmentItemRef;
use qtism\data\SectionPartCollection;
use qtism\data\AssessmentSection;
use qtism\data\TestPart;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtism\data\state\DefaultValue;
use qtism\data\AssessmentTest;

/**
 * Class XmlAssessmentDocumentQTIGuideTest
 */
class XmlAssessmentDocumentQTIGuideTest extends QtiSmTestCase
{
    /**
     * @dataProvider qtiImplementationGuideAssessmentTestFiles
     *
     * @param string $uri The URI describing the file to load.
     * @throws XmlStorageException
     */
    public function testLoadNoSchemaValidate($uri)
    {
        $doc = new XmlDocument('2.1');
        $doc->load($uri);
        $this::assertInstanceOf(XmlDocument::class, $doc);
        $this::assertInstanceOf(AssessmentTest::class, $doc->getDocumentComponent());
    }

    /**
     * @dataProvider qtiImplementationGuideAssessmentTestFiles
     *
     * @param string $uri The URI describing the file to load.
     * @throws XmlStorageException
     */
    public function testLoadFromStringNoSchemaValidate($uri)
    {
        $doc = new XmlDocument('2.1');
        $doc->loadFromString(file_get_contents($uri));
        $this::assertInstanceOf(XmlDocument::class, $doc);
        $this::assertInstanceOf(AssessmentTest::class, $doc->getDocumentComponent());
    }

    /**
     * @dataProvider qtiImplementationGuideAssessmentTestFiles
     *
     * @param string $uri The URI describing the file to load.
     * @throws XmlStorageException
     * @throws MarshallingException
     */
    public function testLoadSaveSchemaValidate($uri)
    {
        $doc = new XmlDocument('2.1');
        $doc->load($uri);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);

        $doc = new XmlDocument('2.1');
        try {
            $doc->load($file, true); // validate on load.
            $this::assertTrue(true);
            unlink($file);
        } catch (XmlStorageException $e) {
            $this::assertTrue(false, $e->getMessage());
            unlink($file);
        }
    }

    /**
     * @dataProvider qtiImplementationGuideAssessmentTestFiles
     *
     * @param string $uri The URI describing the file to load.
     * @throws XmlStorageException
     * @throws MarshallingException
     */
    public function testLoadSaveToStringSchemaValidate($uri)
    {
        $doc = new XmlDocument('2.1');
        $doc->load($uri);

        $file = tempnam('/tmp', 'qsm');
        $str = $doc->saveToString();
        file_put_contents($file, $str);

        $doc = new XmlDocument('2.1');
        try {
            $doc->load($file, true); // validate on load.
            $this::assertTrue(true);
            unlink($file);
        } catch (XmlStorageException $e) {
            $this::assertTrue(false, $e->getMessage());
            unlink($file);
        }
    }

    /**
     * @return array
     */
    public function qtiImplementationGuideAssessmentTestFiles()
    {
        return [
            [self::decorateUri('interaction_mix_sachsen/interaction_mix_sachsen.xml')],
            [self::decorateUri('simple_feedback_test/simple_feedback_test.xml')],
            [self::decorateUri('feedback_examples_test/feedback_examples_test.xml')],
            [self::decorateUri('sets_of_items_with_leading_material/sets_of_items_with_leading_material.xml')],
            [self::decorateUri('arbitrary_collections_of_item_outcomes/arbitrary_collections_of_item_outcomes.xml')],
            [self::decorateUri('categories_of_item/categories_of_item.xml')],
            [self::decorateUri('arbitrary_weighting_of_item_outcomes/arbitrary_weighting_of_item_outcomes.xml')],
            [self::decorateUri('specifiying_the_number_of_allowed_attempts/specifiying_the_number_of_allowed_attempts.xml')],
            [self::decorateUri('controlling_item_feedback_in_relation_to_the_test/controlling_item_feedback_in_relation_to_the_test.xml')],
            [self::decorateUri('controlling_the_duration_of_an_item_attempt/controlling_the_duration_of_an_item_attempt.xml')],
            [self::decorateUri('early_termination_of_test_based_on_accumulated_item_outcomes/early_termination_of_test_based_on_accumulated_item_outcomes.xml')],
            [self::decorateUri('golden_required_items_and_sections/golden_required_items_and_sections.xml')],
            [self::decorateUri('branching_based_on_the_response_to_an_assessmentitem/branching_based_on_the_response_to_an_assessmentitem.xml')],
            [self::decorateUri('items_arranged_into_sections_within_tests/items_arranged_into_sections_within_tests.xml')],
            [self::decorateUri('randomizing_the_order_of_items_and_sections/randomizing_the_order_of_items_and_sections.xml')],
            [self::decorateUri('basic_statistics_as_outcomes/basic_statistics_as_outcomes.xml')],
            [self::decorateUri('mapping_item_outcomes_prior_to_aggregation/mapping_item_outcomes_prior_to_aggregation.xml')],
        ];
    }

    /**
     * @param null $assessmentTest
     * @throws XmlStorageException
     */
    public function testLoadInteractionMixSachsen($assessmentTest = null)
    {
        if (empty($assessmentTest)) {
            $doc = new XmlDocument('2.1');
            $doc->load(self::decorateUri('interaction_mix_sachsen/interaction_mix_sachsen.xml'));
            $assessmentTest = $doc;
        }

        $assessmentTest->schemaValidate();

        $this::assertInstanceOf(AssessmentTest::class, $assessmentTest->getDocumentComponent());
        $this::assertEquals('InteractionMixSachsen_1901710679', $assessmentTest->getDocumentComponent()->getIdentifier());
        $this::assertEquals('Interaction Mix (Sachsen)', $assessmentTest->getDocumentComponent()->getTitle());

        // -- OutcomeDeclarations
        $outcomeDeclarations = $assessmentTest->getDocumentComponent()->getOutcomeDeclarations();
        $this::assertCount(2, $outcomeDeclarations);

        $outcomeDeclaration = $outcomeDeclarations['SCORE'];
        $this::assertEquals('SCORE', $outcomeDeclaration->getIdentifier());
        $this::assertEquals(Cardinality::SINGLE, $outcomeDeclaration->getCardinality());
        $this::assertEquals(BaseType::FLOAT, $outcomeDeclaration->getBaseType());
        $defaultValue = $outcomeDeclaration->getDefaultValue();
        $this::assertInstanceOf(DefaultValue::class, $defaultValue);
        $values = $defaultValue->getValues();
        $this::assertInstanceOf(ValueCollection::class, $values);
        $this::assertCount(1, $values);
        $value = $values[0];
        $this::assertInstanceOf(Value::class, $value);
        $this::assertIsFloat($value->getValue());
        $this::assertEquals(0.0, $value->getValue());

        $outcomeDeclaration = $outcomeDeclarations['MAXSCORE'];
        $this::assertEquals('MAXSCORE', $outcomeDeclaration->getIdentifier());
        $this::assertEquals(Cardinality::SINGLE, $outcomeDeclaration->getCardinality());
        $this::assertEquals(BaseType::FLOAT, $outcomeDeclaration->getBaseType());
        $defaultValue = $outcomeDeclaration->getDefaultValue();
        $this::assertInstanceOf(DefaultValue::class, $defaultValue);
        $values = $defaultValue->getValues();
        $this::assertInstanceOf(ValueCollection::class, $values);
        $this::assertCount(1, $values);
        $value = $values[0];
        $this::assertInstanceOf(Value::class, $value);
        $this::assertIsFloat($value->getValue());
        $this::assertEquals(18.0, $value->getValue());

        // -- TestParts
        $testParts = $assessmentTest->getDocumentComponent()->getTestParts();
        $this::assertCount(1, $testParts);
        $testPart = $testParts['testpartID'];
        $this::assertInstanceOf(TestPart::class, $testPart);
        $this::assertEquals('testpartID', $testPart->getIdentifier());
        $this::assertEquals(NavigationMode::NONLINEAR, $testPart->getNavigationMode());
        $this::assertEquals(SubmissionMode::INDIVIDUAL, $testPart->getSubmissionMode());

        // -- AssessmentSections
        $assessmentSections = $testPart->getAssessmentSections();
        $this::assertCount(1, $assessmentSections);
        $assessmentSection = $assessmentSections['Sektion_181865064'];
        $this::assertInstanceOf(AssessmentSection::class, $assessmentSection);
        $this::assertEquals('Sektion_181865064', $assessmentSection->getIdentifier());
        $this::assertFalse($assessmentSection->isFixed());
        $this::assertFalse($assessmentSection->isVisible());
        $this::assertEquals('Sektion', $assessmentSection->getTitle());

        // -- AssessmentItemRefs
        $assessmentItemRefs = $assessmentSection->getSectionParts();
        $this::assertInstanceOf(SectionPartCollection::class, $assessmentItemRefs);
        $this::assertCount(13, $assessmentItemRefs);

        $expectedItems = [
            ['Choicetruefalse_176040516', 'Choicetruefalse_176040516.xml'],
            ['Choicesingle_853928446', 'Choicesingle_853928446.xml'],
            ['Choicemultiple_2014410822', 'Choicemultiple_2014410822.xml'],
            ['Choicemultiple_871212949', 'Choicemultiple_871212949.xml'],
            ['Hotspot_278940407', 'Hotspot_278940407.xml'],
            ['Order_913967682', 'Order_913967682.xml'],
            ['Matchsingle_143114773', 'Matchsingle_143114773.xml'],
            ['Matchmultiple_1038910213', 'Matchmultiple_1038910213.xml'],
            ['TextEntry_883368511', 'TextEntry_883368511.xml'],
            ['TextEntrynumeric_2040297025', 'TextEntrynumeric_2040297025.xml'],
            ['TextEntrynumeric_770468849', 'TextEntrynumeric_770468849.xml'],
            ['TextEntrysubset_806481421', 'TextEntrysubset_806481421.xml'],
            ['Hottext_801974120', 'Hottext_801974120.xml'],
        ];

        for ($i = 0, $iMax = count($assessmentItemRefs); $i < $iMax; $i++) {
            [$id, $file] = $expectedItems[$i];

            $this::assertInstanceOf(AssessmentItemRef::class, $assessmentItemRefs[$id]);
            $this::assertEquals($id, $assessmentItemRefs[$id]->getIdentifier());
            $this::assertEquals($file, $assessmentItemRefs[$id]->getHref());
            $this::assertFalse($assessmentItemRefs[$id]->isFixed());
        }

        // OutcomeProcessing
        $outcomeProcessing = $assessmentTest->getDocumentComponent()->getOutcomeProcessing();
        $this::assertInstanceOf(OutcomeProcessing::class, $outcomeProcessing);
        $this::assertCount(1, $outcomeProcessing->getOutcomeRules());

        $outcomeRules = $outcomeProcessing->getOutcomeRules();
        $setOutcomeValue = $outcomeRules[0];
        $this::assertInstanceOf(SetOutcomeValue::class, $setOutcomeValue);
        $this::assertEquals('SCORE', $setOutcomeValue->getIdentifier());
        $sum = $setOutcomeValue->getExpression();
        $this::assertInstanceOf(Sum::class, $sum);

        $expressions = $sum->getExpressions();
        $testVariables = $expressions[0];
        $this::assertInstanceOf(TestVariables::class, $testVariables);
        $this::assertEquals('SCORE', $testVariables->getVariableIdentifier());
    }

    public function testWriteInteractionMixSachsen()
    {
        $doc = new XmlDocument('2.1');
        $doc->load(self::decorateUri('interaction_mix_sachsen/interaction_mix_sachsen.xml'), true);

        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);
        $this::assertFileExists($file);

        $doc = new XmlDocument('2.1');
        $doc->load($file);
        $this->testLoadInteractionMixSachsen($doc);

        // correctly namespaced ?
        $dom = $doc->getDomDocument();
        $assessmentTestElt = $dom->documentElement;
        $this::assertEquals('assessmentTest', $assessmentTestElt->nodeName);
        $this::assertEquals('http://www.imsglobal.org/xsd/imsqti_v2p1', $assessmentTestElt->namespaceURI);

        // childrend in namespace ?
        $outcomeDeclarationElts = $dom->documentElement->getElementsByTagName('outcomeDeclaration');
        $this::assertEquals(2, $outcomeDeclarationElts->length);
        $outcomeDeclarationElt = $outcomeDeclarationElts->item(0);
        $this::assertEquals('http://www.imsglobal.org/xsd/imsqti_v2p1', $outcomeDeclarationElt->namespaceURI);

        unlink($file);
        $this::assertFileDoesNotExist($file);
    }

    /**
     * @param $uri
     * @return string
     */
    private static function decorateUri($uri)
    {
        return self::samplesDir() . 'ims/tests/' . $uri;
    }
}
