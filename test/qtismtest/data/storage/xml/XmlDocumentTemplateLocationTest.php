<?php
namespace qtismtest\data\storage\xml;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;

class XmlDocumentTemplateLocationTest extends QtiSmTestCase {
	
    public function testCorrectlyFormed() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/template_location/template_location_item.xml', true);
         
        $responseProcessings = $doc->getDocumentComponent()->getComponentsByClassName('responseProcessing');
        $this->assertEquals(1, count($responseProcessings));
        $this->assertEquals('template_location_rp.xml', $responseProcessings[0]->getTemplateLocation());
         
        $doc->resolveTemplateLocation(true);
        
        $responseProcessings = $doc->getDocumentComponent()->getComponentsByClassName('responseProcessing');
        $this->assertEquals(1, count($responseProcessings));
        $this->assertEquals('http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct', $responseProcessings[0]->getTemplate());
    }
    
    public function testNotLoaded() {
        $doc = new XmlDocument();
        
        $this->setExpectedException('\\LogicException', 'Cannot resolve template location before loading any file.');
        $doc->resolveTemplateLocation();
    }
    
    public function testWrongTarget() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/template_location/template_location_item_wrong_target.xml', true);
        
        $this->setExpectedException('qtism\\data\\storage\\xml\\XmlStorageException');
        $doc->resolveTemplateLocation();
    }
    
    public function testInvalidTargetNoValidation() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/template_location/template_location_item_invalid_target.xml', true);
        
        $this->setExpectedException('qtism\\data\\storage\\xml\\XmlStorageException', "'responseProcessingZ' components are not supported in QTI version '2.1.0'.", XmlStorageException::VERSION);
        $doc->resolveTemplateLocation();
    }
    
    public function testInvalidTargetValidation() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/template_location/template_location_item_invalid_target.xml', true);
        
        $this->setExpectedException('qtism\\data\\storage\\xml\\XmlStorageException', null, XmlStorageException::XSD_VALIDATION);
        $doc->resolveTemplateLocation(true);
    }
}
