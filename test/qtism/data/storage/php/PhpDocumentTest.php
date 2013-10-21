<?php

use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\php\PhpDocument;
use qtism\data\storage\xml\XmlDocument;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class PhpDocumentTest extends QtiSmTestCase {
	
    public function testSimpleLoad() {
        
        $doc = new PhpDocument();
        $doc->load(self::samplesDir() . 'custom/php/php_storage_simple.php');
        
        $assessmentTest = $doc->getDocumentComponent();
        $this->assertInstanceOf('qtism\\data\\AssessmentTest', $assessmentTest);
        
        $this->assertEquals('php_storage_simple', $assessmentTest->getIdentifier());
    }
    
     public function testSimpleSave() {

        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/php/php_storage_simple.xml');
        $phpDoc = new PhpDocument($doc->getDocumentComponent());
        
        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);
        unset($file);
    }
    
    /**
     *
     * @dataProvider loadTestSamplesDataProvider
     * @param string $testUri
     */
    public function testLoadTestSamples($testUri) {
        // Basic XML -> PHP transormation + save + load
        $xmlDoc = new XmlDocument('2.1');
        $xmlDoc->load($testUri);
    
        $phpDoc = new PhpDocument();
        $phpDoc->setDocumentComponent($xmlDoc->getDocumentComponent());
    
        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);
    
        $phpDoc = new PhpDocument();
        $phpDoc->load($file);
    
        $this->assertInstanceOf('qtism\\data\\AssessmentTest', $phpDoc->getDocumentComponent());
        $this->assertEquals($file, $phpDoc->getUrl());
    
        unset($file);
    }
    
    public function testLoadInteractionMixSaschsen() {
        $xmlDoc = new XmlDocument('2.1');
        $xmlDoc->load(self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml');
    
        $phpDoc = new PhpDocument();
        $phpDoc->setDocumentComponent($xmlDoc->getDocumentComponent());
    
        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);
    
        $phpDoc = new PhpDocument();
        $phpDoc->load($file);
    
        $this->assertEquals('InteractionMixSachsen_1901710679', $phpDoc->getDocumentComponent()->getIdentifier());
    }
    
    public function loadTestSamplesDataProvider() {
        return array(
                        array(self::samplesDir() . 'ims/tests/arbitrary_collections_of_item_outcomes/arbitrary_collections_of_item_outcomes.xml'),
                        array(self::samplesDir() . 'ims/tests/arbitrary_weighting_of_item_outcomes/arbitrary_weighting_of_item_outcomes.xml'),
                        array(self::samplesDir() . 'ims/tests/basic_statistics_as_outcomes/basic_statistics_as_outcomes.xml'),
                        array(self::samplesDir() . 'ims/tests/branching_based_on_the_response_to_an_assessmentitem/branching_based_on_the_response_to_an_assessmentitem.xml'),
                        array(self::samplesDir() . 'ims/tests/controlling_the_duration_of_an_item_attempt/controlling_the_duration_of_an_item_attempt.xml'),
                        array(self::samplesDir() . 'ims/tests/controlling_item_feedback_in_relation_to_the_test/controlling_item_feedback_in_relation_to_the_test.xml'),
                        array(self::samplesDir() . 'ims/tests/early_termination_of_test_based_on_accumulated_item_outcomes/early_termination_of_test_based_on_accumulated_item_outcomes.xml'),
                        array(self::samplesDir() . 'ims/tests/feedback_examples_test/feedback_examples_test.xml'),
                        array(self::samplesDir() . 'ims/tests/golden_required_items_and_sections/golden_required_items_and_sections.xml'),
                        array(self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml'),
                        array(self::samplesDir() . 'ims/tests/items_arranged_into_sections_within_tests/items_arranged_into_sections_within_tests.xml'),
                        array(self::samplesDir() . 'ims/tests/mapping_item_outcomes_prior_to_aggregation/mapping_item_outcomes_prior_to_aggregation.xml'),
                        array(self::samplesDir() . 'ims/tests/randomizing_the_order_of_items_and_sections/randomizing_the_order_of_items_and_sections.xml'),
                        array(self::samplesDir() . 'ims/tests/sets_of_items_with_leading_material/sets_of_items_with_leading_material.xml'),
                        array(self::samplesDir() . 'ims/tests/simple_feedback_test/simple_feedback_test.xml'),
                        array(self::samplesDir() . 'ims/tests/specifiying_the_number_of_allowed_attempts/specifiying_the_number_of_allowed_attempts.xml')
        );
    }
}