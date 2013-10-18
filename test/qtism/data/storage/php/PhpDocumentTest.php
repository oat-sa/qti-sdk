<?php

use qtism\data\storage\php\PhpDocument;

use qtism\data\storage\xml\XmlCompactAssessmentTestDocument;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class PhpDocumentTest extends QtiSmTestCase {
	
    public function testSimpleLoad() {
        
        $doc = new PhpDocument();
        $doc->load(self::samplesDir() . 'custom/php/php_storage_simple.php');
        
        $assessmentTest = $doc->getDocumentComponent();
        $this->assertInstanceOf('qtism\\data\\AssessmentTest', $assessmentTest);
    }
    
     public function testSimpleSave() {

        $doc = new XmlCompactAssessmentTestDocument();
        $doc->load(self::samplesDir() . 'custom/php/php_storage_simple.xml');
        $phpDoc = new PhpDocument($doc->getXmlDocument()->getDocumentComponent());
        
        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);
        unset($file);
    }
}