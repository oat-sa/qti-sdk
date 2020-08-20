<?php

namespace qtismtest\data\storage\php;

use qtism\data\content\enums\AriaLive;
use qtism\data\content\enums\AriaOrientation;
use qtism\data\content\xhtml\text\Span;
use qtism\data\storage\php\PhpDocument;
use qtism\data\storage\php\PhpStorageException;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlDocument;
use qtismtest\QtiSmTestCase;
use qtism\data\AssessmentItem;
use qtism\data\expressions\operators\CustomOperator;
use qtism\data\content\RubricBlock;
use qtism\data\content\xhtml\QtiObject;
use qtism\data\content\ItemBody;
use qtism\data\AssessmentTest;

class PhpDocumentTest extends QtiSmTestCase
{
    public function testSimpleLoad()
    {
        $doc = new PhpDocument();
        $doc->load(self::samplesDir() . 'custom/php/php_storage_simple.php');

        $assessmentTest = $doc->getDocumentComponent();
        $this->assertInstanceOf(AssessmentTest::class, $assessmentTest);

        $this->assertEquals('php_storage_simple', $assessmentTest->getIdentifier());
    }

    public function testSimpleLoadFromString()
    {
        $doc = new PhpDocument();
        $doc->loadFromString(file_get_contents(self::samplesDir() . 'custom/php/php_storage_simple.php'));

        $assessmentTest = $doc->getDocumentComponent();
        $this->assertInstanceOf(AssessmentTest::class, $assessmentTest);

        $this->assertEquals('php_storage_simple', $assessmentTest->getIdentifier());
    }

    public function testSimpleSave()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/php/php_storage_simple.xml');
        $phpDoc = new PhpDocument('2.1', $doc->getDocumentComponent());
        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);

        unlink($file);
    }

    public function testSimpleSaveToString()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/php/php_storage_simple.xml');
        $phpDoc = new PhpDocument('2.1', $doc->getDocumentComponent());
        $phpStr = $phpDoc->saveToString();

        $phpDoc->loadFromString($phpStr);
        $this->assertEquals('php_storage_simple', $phpDoc->getDocumentComponent()->getIdentifier());
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
        $this->assertInstanceOf(CustomOperator::class, $customOperator);
        $this->assertEquals('com.taotesting.qtism.customOperator1', $customOperator->getClass());
        $this->assertEquals('http://qtism.taotesting.com/xsd/customOperator1.xsd', $customOperator->getDefinition());
        $this->assertEquals('false', $xml->documentElement->getAttributeNS('http://qtism.taotesting.com', 'debug'));
        $this->assertEquals('default', $xml->documentElement->getAttributeNS('http://qtism.taotesting.com', 'syntax'));
        $this->assertEquals('<customOperator xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:qtism="http://qtism.taotesting.com" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd" class="com.taotesting.qtism.customOperator1" definition="http://qtism.taotesting.com/xsd/customOperator1.xsd" qtism:debug="false" qtism:syntax="default">
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
        $this->assertInstanceOf(CustomOperator::class, $customOperator);
        $this->assertEquals('<customOperator xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd">
    <baseValue baseType="string"><![CDATA[Param1Data]]></baseValue>
</customOperator>', $xml->saveXML($xml->documentElement));

        unlink($file);
    }

    /**
     *
     * @dataProvider loadTestSamplesDataProvider
     * @param string $testUri
     * @param string $rootType The expected fully qualified class name of the document component.
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

        $this->assertInstanceOf($rootType, $phpDoc->getDocumentComponent());
        $this->assertEquals($file, $phpDoc->getUrl());

        unlink($file);
        $this->assertFalse(file_exists($file));
    }

    /**
     *
     * @dataProvider loadTestSamplesDataProvider
     * @param string $testUri
     * @param string $rootType The expected fully qualified class name of the document component.
     */
    public function testLoadTestSamplesFromString($testUri, $rootType)
    {
        // Basic XML -> PHP transormation + saveTotring + loadFromString
        $xmlDoc = new XmlDocument('2.1');
        $xmlDoc->loadFromString(file_get_contents($testUri));

        $phpDoc = new PhpDocument();
        $phpDoc->setDocumentComponent($xmlDoc->getDocumentComponent());

        $file = tempnam('/tmp', 'qsm');
        file_put_contents($file, $phpDoc->saveToString());

        $phpDoc = new PhpDocument();
        $phpDoc->loadFromString(file_get_contents($file));

        $this->assertInstanceOf($rootType, $phpDoc->getDocumentComponent());
        $this->assertNull($phpDoc->getUrl());

        unlink($file);
        $this->assertFalse(file_exists($file));
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

        $this->assertEquals('InteractionMixSachsen_1901710679', $phpDoc->getDocumentComponent()->getIdentifier());
        unlink($file);
        $this->assertFalse(file_exists($file));
    }

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
            [self::samplesDir() . 'rendering/empty_object.xml', QtiObject::class],
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

    public function testLoadBadData()
    {
        $this->expectException(PhpStorageException::class);

        $phpDoc = new PhpDocument();
        $phpDoc->load(self::samplesDir() . 'custom/php/baddata.php');
    }

    public function testLoadFromStringBadData()
    {
        $this->expectException(PhpStorageException::class);

        $phpDoc = new PhpDocument();
        $phpDoc->loadFromString('<?php $zorglub = "zorg";');
    }

    public function testLoadNoData()
    {
        $this->expectException(PhpStorageException::class);

        $phpDoc = new PhpDocument();
        $phpDoc->load('somewhere/in/antoine.php');
    }

    public function testCleanOutput()
    {
        $this->expectException(PhpStorageException::class);

        // Make sure that no output is present after this invalid data load.
        $phpDoc = new PhpDocument();
        $phpDoc->load(self::samplesDir() . 'custom/php/baddata2.php');
    }

    public function testCleanOutputFromString()
    {
        $this->expectException(PhpStorageException::class);

        // Make sure that no output is present after this invalid data load.
        $phpDoc = new PhpDocument();
        $phpDoc->loadFromString('<?php echo "FALZOUILLE";');
    }

    public function testCleanOutputFromString2()
    {
        $this->expectException(PhpStorageException::class);

        // Make sure that no output is present after this invalid data load.
        $phpDoc = new PhpDocument();
        $phpDoc->loadFromString('FALZOUILLE');
    }

    /**
     * @throws PhpStorageException
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

        $this->assertEquals('myid', $span->getId());
        $this->assertEquals('myclass', $span->getClass());
        $this->assertEquals('IDREF1 IDREF2', $span->getAriaControls());
        $this->assertEquals('IDREF3', $span->getAriaDescribedBy());
        $this->assertEquals('IDREF4', $span->getAriaFlowTo());
        $this->assertEquals('My Label', $span->getAriaLabel());
        $this->assertEquals('IDREF5', $span->getAriaLabelledBy());
        $this->assertEquals('5', $span->getAriaLevel());
        $this->assertEquals(AriaLive::ASSERTIVE, $span->getAriaLive());
        $this->assertEquals(AriaOrientation::VERTICAL, $span->getAriaOrientation());
        $this->assertEquals('IDREF6', $span->getAriaOwns());
        $this->assertTrue($span->getAriaHidden());
    }
}
