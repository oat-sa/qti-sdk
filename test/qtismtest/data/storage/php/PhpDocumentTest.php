<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\data\storage\php;

use qtism\common\beans\BeanException;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\storage\MemoryStreamException;
use qtism\common\storage\StreamAccessException;
use qtism\data\content\enums\AriaLive;
use qtism\data\content\enums\AriaOrientation;
use qtism\data\content\xhtml\text\Span;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Equal;
use qtism\data\expressions\operators\ToleranceMode;
use qtism\data\NavigationMode;
use qtism\data\storage\php\marshalling\PhpMarshallingException;
use qtism\data\storage\php\PhpDocument;
use qtism\data\storage\php\PhpStorageException;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtism\data\SubmissionMode;
use qtismtest\QtiSmTestCase;
use qtism\data\AssessmentItem;
use qtism\data\expressions\operators\CustomOperator;
use qtism\data\content\RubricBlock;
use qtism\data\content\xhtml\ObjectElement;
use qtism\data\content\ItemBody;
use qtism\data\AssessmentTest;
use qtism\data\rules\Selection;
use qtism\data\processing\ResponseProcessing;
use qtism\data\AssessmentItemRef;
use ReflectionException;

/**
 * Class PhpDocumentTest
 */
class PhpDocumentTest extends QtiSmTestCase
{
    /**
     * @param string $path
     * @throws PhpStorageException
     */
    public function testSimpleLoad($path = '')
    {
        $doc = new PhpDocument();
        if (empty($path)) {
            $doc->load(self::samplesDir() . 'custom/php/php_storage_simple.php');
        } else {
            $doc->load($path);
        }

        $assessmentTest = $doc->getDocumentComponent();
        $this::assertInstanceOf(AssessmentTest::class, $assessmentTest);
        $this::assertEquals('php_storage_simple', $assessmentTest->getIdentifier());
        $this::assertEquals('PHP Storage Simple', $assessmentTest->getTitle());

        $testParts = $assessmentTest->getTestParts();
        $this::assertCount(1, $testParts);
        $this::assertTrue(isset($testParts['P01']));
        $this::assertEquals('P01', $testParts['P01']->getIdentifier());
        $this::assertEquals(NavigationMode::LINEAR, $testParts['P01']->getNavigationMode());
        $this::assertEquals(SubmissionMode::INDIVIDUAL, $testParts['P01']->getSubmissionMode());

        $assessmentSections = $testParts['P01']->getAssessmentSections();
        $this::assertCount(1, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S01']));
        $this::assertEquals('S01', $assessmentSections['S01']->getIdentifier());
        $this::assertEquals('Section1', $assessmentSections['S01']->getTitle());
        $this::assertTrue($assessmentSections['S01']->isVisible());

        $assessmentItemRefs = $assessmentSections['S01']->getSectionParts();
        $this::assertCount(3, $assessmentItemRefs);
        $this::assertInstanceOf(AssessmentItemRef::class, $assessmentItemRefs['Q01']);
        $this::assertInstanceOf(AssessmentItemRef::class, $assessmentItemRefs['Q02']);
        $this::assertInstanceOf(AssessmentItemRef::class, $assessmentItemRefs['Q03']);

        $this::assertEquals('Q01', $assessmentItemRefs['Q01']->getIdentifier());
        $this::assertEquals('./Q01.xml', $assessmentItemRefs['Q01']->getHref());
        $this::assertFalse(false, $assessmentItemRefs['Q01']->isTimeDependent());
        $this::assertEquals(['mathematics', 'chemistry'], $assessmentItemRefs['Q01']->getCategories()->getArrayCopy());
        $variableMappings = $assessmentItemRefs['Q01']->getVariableMappings();
        $this::assertCount(1, $variableMappings);
        $this::assertEquals('scoring', $variableMappings[0]->getSource());
        $this::assertEquals('SCORE', $variableMappings[0]->getTarget());
        $weights = $assessmentItemRefs['Q01']->getWeights();
        $this::assertCount(1, $weights);
        $this::assertEquals('W01', $weights['W01']->getIdentifier());
        $this::assertEquals(2.0, $weights['W01']->getValue());
        $responseDeclarations = $assessmentItemRefs['Q01']->getResponseDeclarations();
        $this::assertCount(1, $responseDeclarations);
        $this::assertEquals('RESPONSE', $responseDeclarations['RESPONSE']->getIdentifier());
        $this::assertEquals(Cardinality::SINGLE, $responseDeclarations['RESPONSE']->getCardinality());
        $this::assertEquals(BaseType::IDENTIFIER, $responseDeclarations['RESPONSE']->getBaseType());
        $values = $responseDeclarations['RESPONSE']->getCorrectResponse()->getValues();
        $this::assertCount(1, $values);
        $this::assertEquals('ChoiceA', $values[0]->getValue());
        $outcomeDeclarations = $assessmentItemRefs['Q01']->getOutcomeDeclarations();
        $this::assertCount(1, $outcomeDeclarations);
        $this::assertEquals('scoring', $outcomeDeclarations['scoring']->getIdentifier());
        $this::assertEquals(Cardinality::SINGLE, $outcomeDeclarations['scoring']->getCardinality());
        $this::assertEquals(BaseType::FLOAT, $outcomeDeclarations['scoring']->getBaseType());
        $values = $outcomeDeclarations['scoring']->getDefaultValue()->getValues();
        $this::assertEquals(0.0, $values[0]->getValue());
        $responseProcessing = $assessmentItemRefs['Q01']->getResponseProcessing();
        $this::assertInstanceOf(ResponseProcessing::class, $responseProcessing);
        $this::assertFalse($responseProcessing->hasTemplateLocation());
        $this::assertFalse($responseProcessing->hasTemplate());
        $responseRules = $responseProcessing->getResponseRules();
        $this::assertCount(1, $responseRules);
    }

    public function testSimpleSave()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/php/php_storage_simple.xml');
        $phpDoc = new PhpDocument('2.1', $doc->getDocumentComponent());
        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        $this->testSimpleLoad($file);

        unlink($file);
    }

    public function testCustomOperatorOne()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/operators/custom_operator_1.xml');
        $phpDoc = new PhpDocument('2.1', $doc->getDocumentComponent());

        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        $phpDoc = new PhpDocument();
        $phpDoc->load($file);

        $customOperator = $phpDoc->getDocumentComponent();
        $xml = $customOperator->getXml();
        $this::assertInstanceOf(CustomOperator::class, $customOperator);
        $this::assertEquals('com.taotesting.qtism.customOperator1', $customOperator->getClass());
        $this::assertEquals('http://qtism.taotesting.com/xsd/customOperator1.xsd', $customOperator->getDefinition());
        $this::assertEquals('false', $xml->documentElement->getAttributeNS('http://qtism.taotesting.com', 'debug'));
        $this::assertEquals('default', $xml->documentElement->getAttributeNS('http://qtism.taotesting.com', 'syntax'));
        $this::assertEquals('<customOperator xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:qtism="http://qtism.taotesting.com" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd" class="com.taotesting.qtism.customOperator1" definition="http://qtism.taotesting.com/xsd/customOperator1.xsd" qtism:debug="false" qtism:syntax="default">
    <baseValue baseType="string"><![CDATA[Param1Data]]></baseValue>
</customOperator>', $xml->saveXML($xml->documentElement));

        unlink($file);
    }

    public function testCustomOperatorTwo()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/operators/custom_operator_2.xml');
        $phpDoc = new PhpDocument('2.1', $doc->getDocumentComponent());

        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        $phpDoc = new PhpDocument();
        $phpDoc->load($file);

        $customOperator = $phpDoc->getDocumentComponent();
        $xml = $customOperator->getXml();
        $this::assertInstanceOf(CustomOperator::class, $customOperator);
        $this::assertEquals('<customOperator xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd">
    <baseValue baseType="string"><![CDATA[Param1Data]]></baseValue>
</customOperator>', $xml->saveXML($xml->documentElement));

        unlink($file);
    }

    public function testCustomSelection()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/selection/custom_selection.xml');
        $test = $doc->getDocumentComponent();
        $phpDoc = new PhpDocument('2.1', $test);

        $selection = $test->getComponentsByClassName('selection')[0];
        $this::assertInstanceOf(Selection::class, $selection);

        $domSelection = $selection->getXml();
        $this::assertNotNull($domSelection);

        $this::assertEquals(1, $domSelection->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveItemSelection')->length);
        $this::assertEquals(1, $domSelection->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveEngineRef')->length);

        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        // Do we have the same result after opening it again?
        $phpDoc = new PhpDocument();
        $phpDoc->load($file);

        $test = $phpDoc->getDocumentComponent();

        $selection = $test->getComponentsByClassName('selection')[0];
        $this::assertInstanceOf(Selection::class, $selection);

        $domSelection = $selection->getXml();
        $this::assertNotNull($domSelection);

        $this::assertEquals(1, $domSelection->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveItemSelection')->length);
        $this::assertEquals(1, $domSelection->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveEngineRef')->length);

        unlink($file);
    }

    /**
     * @dataProvider loadTestSamplesDataProvider
     * @param string $testUri
     * @param string $rootType The expected fully qualified class name of the document component.
     * @throws PhpStorageException
     * @throws XmlStorageException
     * @throws ReflectionException
     * @throws BeanException
     * @throws MemoryStreamException
     * @throws StreamAccessException
     * @throws PhpMarshallingException
     */
    public function testLoadTestSamples($testUri, $rootType)
    {
        // Basic XML -> PHP transormation + save + load
        $xmlDoc = new XmlDocument('2.1');
        $xmlDoc->load($testUri);

        $phpDoc = new PhpDocument();
        $phpDoc->setDocumentComponent($xmlDoc->getDocumentComponent());

        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        $phpDoc = new PhpDocument();
        $phpDoc->load($file);

        $this::assertInstanceOf($rootType, $phpDoc->getDocumentComponent());
        $this::assertEquals($file, $phpDoc->getUrl());

        unlink($file);
        $this::assertFileNotExists($file);
    }

    public function testLoadInteractionMixSaschsen()
    {
        $xmlDoc = new XmlDocument('2.1');
        $xmlDoc->load(self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml');

        $phpDoc = new PhpDocument();
        $phpDoc->setDocumentComponent($xmlDoc->getDocumentComponent());

        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        $phpDoc = new PhpDocument();
        $phpDoc->load($file);

        $this::assertEquals('InteractionMixSachsen_1901710679', $phpDoc->getDocumentComponent()->getIdentifier());
        unlink($file);
        $this::assertFileNotExists($file);
    }

    /**
     * @return array
     */
    public function loadTestSamplesDataProvider()
    {
        return [
            [self::samplesDir() . 'ims/tests/arbitrary_collections_of_item_outcomes/arbitrary_collections_of_item_outcomes.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/arbitrary_weighting_of_item_outcomes/arbitrary_weighting_of_item_outcomes.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/basic_statistics_as_outcomes/basic_statistics_as_outcomes.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/branching_based_on_the_response_to_an_assessmentitem/branching_based_on_the_response_to_an_assessmentitem.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/controlling_the_duration_of_an_item_attempt/controlling_the_duration_of_an_item_attempt.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/controlling_item_feedback_in_relation_to_the_test/controlling_item_feedback_in_relation_to_the_test.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/early_termination_of_test_based_on_accumulated_item_outcomes/early_termination_of_test_based_on_accumulated_item_outcomes.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/feedback_examples_test/feedback_examples_test.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/golden_required_items_and_sections/golden_required_items_and_sections.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/items_arranged_into_sections_within_tests/items_arranged_into_sections_within_tests.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/mapping_item_outcomes_prior_to_aggregation/mapping_item_outcomes_prior_to_aggregation.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/randomizing_the_order_of_items_and_sections/randomizing_the_order_of_items_and_sections.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/sets_of_items_with_leading_material/sets_of_items_with_leading_material.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/simple_feedback_test/simple_feedback_test.xml', AssessmentTest::class],
            [self::samplesDir() . 'ims/tests/specifiying_the_number_of_allowed_attempts/specifiying_the_number_of_allowed_attempts.xml', AssessmentTest::class],
            [self::samplesDir() . 'rendering/various_content.xml', RubricBlock::class],
            [self::samplesDir() . 'rendering/associateinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/choiceinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/choiceinteraction_2.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/drawinginteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/drawinginteraction_2.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/endattemptinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/extendedtextinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/gapmatchinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/graphicgapmatchinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/graphicorderinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/hotspotinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/hottextinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/inlinechoiceinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/itembodywithfeedback_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/matchinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/mediainteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/mediainteraction_2.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/mediainteraction_3.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/orderinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/selectpointinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/positionobjectinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/sliderinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/textentryinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/uploadinteraction_1.xml', ItemBody::class],
            [self::samplesDir() . 'rendering/itemfeedback_1.xml', AssessmentItem::class],
            [self::samplesDir() . 'rendering/empty_object.xml', ObjectElement::class],
            [self::samplesDir() . 'rendering/empty_rubricblock.xml', RubricBlock::class],
            [self::samplesDir() . 'rendering/rubricblock_1.xml', RubricBlock::class],
            [self::samplesDir() . 'rendering/rubricblock_2.xml', RubricBlock::class],
            [self::samplesDir() . 'rendering/rubricblock_3.xml', RubricBlock::class],
            [self::samplesDir() . 'rendering/math_1.xml', AssessmentItem::class],
            [self::samplesDir() . 'rendering/math_2.xml', AssessmentItem::class],
            [self::samplesDir() . 'rendering/math_3.xml', AssessmentItem::class],
            [self::samplesDir() . 'rendering/math_4.xml', RubricBlock::class],
            [self::samplesDir() . 'custom/operators/custom_operator_1.xml', CustomOperator::class],
            [self::samplesDir() . 'custom/operators/custom_operator_2.xml', CustomOperator::class],
            [self::samplesDir() . 'custom/operators/custom_operator_3.xml', CustomOperator::class],
            [self::samplesDir() . 'custom/operators/custom_operator_nested_1.xml', CustomOperator::class],
            [self::samplesDir() . 'custom/interactions/custom_interaction_pci.xml', AssessmentItem::class],
        ];
    }

    public function testSaveComponentWithArrayBeanProperty()
    {
        $equal = new Equal(
            new ExpressionCollection(
                [
                    new BaseValue(BaseType::FLOAT, 2.22),
                    new BaseValue(BaseType::FLOAT, 2.22),
                ]
            ),
            ToleranceMode::RELATIVE,
            [5, 5]
        );

        $file = tempnam('/tmp', 'qsm');
        $phpDoc = new PhpDocument('2.1', $equal);
        $phpDoc->save($file);

        $phpDoc2 = new PhpDocument('2.1');
        $phpDoc2->load($file);

        $this::assertInstanceOf(Equal::class, $phpDoc2->getDocumentComponent());
        $this::assertEquals(ToleranceMode::RELATIVE, $phpDoc2->getDocumentComponent()->getToleranceMode());
        $this::assertEquals([5, 5], $phpDoc2->getDocumentComponent()->getTolerance());

        unlink($file);
    }

    public function testSaveError()
    {
        $phpDoc = new PhpDocument();

        $this->expectException(PhpStorageException::class);
        $this->expectExceptionMessage("File located at '/root/root.php' could not be written.");

        $phpDoc->save('/root/root.php');
    }

    public function testLoadError()
    {
        $phpDoc = new PhpDocument();

        $this->expectException(PhpStorageException::class);
        $this->expectExceptionMessage("The PHP document located at '/root/root.php' is not readable or does not exist.");

        $phpDoc->load('/root/root.php');
    }

    /**
     * @throws BeanException
     * @throws MemoryStreamException
     * @throws PhpMarshallingException
     * @throws PhpStorageException
     * @throws ReflectionException
     * @throws StreamAccessException
     */
    public function testBodyElement()
    {
        $span = new Span('myid', 'myclass');
        $span->setAriaControls('IDREF1 IDREF2');
        $span->setAriaDescribedBy('IDREF3');
        $span->setAriaFlowTo('IDREF4');
        $span->setAriaLabel('My Label');
        $span->setAriaLabelledBy('IDREF5');
        $span->setAriaLevel(5);
        $span->setAriaLive(AriaLive::ASSERTIVE);
        $span->setAriaOrientation(AriaOrientation::VERTICAL);
        $span->setAriaOwns('IDREF6');
        $span->setAriaHidden(true);

        $file = tempnam('/tmp', 'qsm');
        $phpDoc = new PhpDocument('2.2', $span);
        $phpDoc->save($file);

        $phpDoc2 = new PhpDocument('2.2');
        $phpDoc2->load($file);
        unlink($file);

        /** @var Span $span */
        $span = $phpDoc2->getDocumentComponent();

        $this::assertEquals('myid', $span->getId());
        $this::assertEquals('myclass', $span->getClass());
        $this::assertEquals('IDREF1 IDREF2', $span->getAriaControls());
        $this::assertEquals('IDREF3', $span->getAriaDescribedBy());
        $this::assertEquals('IDREF4', $span->getAriaFlowTo());
        $this::assertEquals('My Label', $span->getAriaLabel());
        $this::assertEquals('IDREF5', $span->getAriaLabelledBy());
        $this::assertEquals('5', $span->getAriaLevel());
        $this::assertEquals(AriaLive::ASSERTIVE, $span->getAriaLive());
        $this::assertEquals(AriaOrientation::VERTICAL, $span->getAriaOrientation());
        $this::assertEquals('IDREF6', $span->getAriaOwns());
        $this::assertTrue($span->getAriaHidden());
    }
}
