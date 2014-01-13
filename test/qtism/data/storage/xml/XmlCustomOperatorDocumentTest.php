<?php

use qtism\common\enums\BaseType;
use qtism\data\storage\xml\XmlDocument;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class XmlCustomOperatorDocumentTest extends QtiSmTestCase {
	
    public function testReadNoLax($url = '') {
        $doc = new XmlDocument();
        $url = (empty($url) === true) ? (self::samplesDir() . 'custom/operators/custom_operator_1.xml') : $url;
        $doc->load($url, true);
        $customOperator = $doc->getDocumentComponent();
        
        $this->assertInstanceOf('qtism\\data\\expressions\\operators\\CustomOperator', $customOperator);
        $this->assertEquals('com.taotesting.qtism.customOperator1', $customOperator->getClass());
        $this->assertEquals('http://qtism.taotesting.com/xsd/customOperator1.xsd', $customOperator->getDefinition());
        
        $xml = $customOperator->getXml();
        $this->assertEquals('false', $xml->documentElement->getAttributeNS('http://qtism.taotesting.com', 'debug'));
        $this->assertEquals('default', $xml->documentElement->getAttributeNS('http://qtism.taotesting.com', 'syntax'));
        
        $expressions = $customOperator->getExpressions();
        $this->assertEquals(1, count($expressions));
        $this->assertInstanceOf('qtism\\data\\expressions\\BaseValue', $expressions[0]);
        $this->assertEquals(BaseType::STRING, $expressions[0]->getBaseType());
        $this->assertEquals('Param1Data', $expressions[0]->getValue());
    }
    
    public function testWriteNoLax() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/operators/custom_operator_1.xml');
        
        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);
        $this->testReadNoLax($file);
        
        unlink($file);
    }
    
    public function testReadQTIOnly($url = '') {
        $doc = new XmlDocument();
        $url = (empty($url) === true) ? (self::samplesDir() . 'custom/operators/custom_operator_2.xml') : $url;
        $doc->load($url, true);
        $customOperator = $doc->getDocumentComponent();
        
        $this->assertInstanceOf('qtism\\data\\expressions\\operators\\CustomOperator', $customOperator);
        
        $expressions = $customOperator->getExpressions();
        $this->assertEquals(1, count($expressions));
        $this->assertInstanceOf('qtism\\data\\expressions\\BaseValue', $expressions[0]);
        $this->assertEquals(BaseType::STRING, $expressions[0]->getBaseType());
        $this->assertEquals('Param1Data', $expressions[0]->getValue());
    }
    
    public function testWriteQTIOnly() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/operators/custom_operator_2.xml');
        
        $file = tempnam('/tmp', 'qsm');
        $doc->save($file);
        $this->testReadQTIOnly($file);
        
        unlink($file);
    }
    
    public function testReadFullLax($url = '') {
//         $doc = new XmlDocument();
//         $url = (empty($url) === true) ? (self::samplesDir() . 'custom/operators/custom_operator_3.xml') : $url;
//         $doc->load($url, true);
//         $customOperator = $doc->getDocumentComponent();
        
//         $this->assertInstanceOf('qtism\\data\\expressions\\operators\\CustomOperator', $customOperator);
//         $this->assertEquals('com.taotesting.qtism.customOperator1', $customOperator->getClass());
//         $this->assertEquals('http://qtism.taotesting.com/xsd/customOperator1.xsd', $customOperator->getDefinition());
        
//         $xml = $customOperator->getXml();
//         $this->assertEquals('false', $xml->documentElement->getAttributeNS('http://qtism.taotesting.com', 'debug'));
//         $this->assertEquals('default', $xml->documentElement->getAttributeNS('http://qtism.taotesting.com', 'syntax'));
        
//         $expressions = $customOperator->getExpressions();
//         $this->assertEquals(1, count($expressions));
//         $this->assertInstanceOf('qtism\\data\\expressions\\BaseValue', $expressions[0]);
//         $this->assertEquals(BaseType::STRING, $expressions[0]->getBaseType());
//         $this->assertEquals('Param1Data', $expressions[0]->getValue());
    }
}