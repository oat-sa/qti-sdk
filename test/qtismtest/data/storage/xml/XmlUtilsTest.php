<?php
namespace qtismtest\data\storage\xml;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\Utils;
use \DOMDocument;

class XmlUtilsTest extends QtiSmTestCase {
    
	/**
	 * @dataProvider validInferQTIVersionProvider
	 */
	public function testInferQTIVersionValid($file, $expectedVersion) {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->load($file);
		$this->assertEquals($expectedVersion, Utils::inferVersion($dom));
	}
	
	public function validInferQTIVersionProvider() {
		return array(
			array(self::samplesDir() . 'ims/items/2_1/associate.xml', '2.1'),
			array(self::samplesDir() . 'ims/items/2_0/associate.xml', '2.0'),
			array(self::samplesDir() . 'ims/tests/arbitrary_collections_of_item_outcomes/arbitrary_collections_of_item_outcomes.xml', '2.1')		
		);
	}
	
	/**
	 * 
	 * @param string $originalXmlString
	 * @param string $expectedXmlString
	 * @dataProvider anonimizeElementProvider
	 */
	public function testAnonimizeElement($originalXmlString, $expectedXmlString) {
	    $elt = $this->createDOMElement($originalXmlString);
	    $newElt = Utils::anonimizeElement($elt);
	    
	    $this->assertEquals($expectedXmlString, $newElt->ownerDocument->saveXML($newElt));
	}
	
	public function anonimizeElementProvider() {
	    return array(
	        array('<m:math xmlns:m="http://www.w3.org/1998/Math/MathML" display="inline"><m:mn>1</m:mn><m:mo>+</m:mo><m:mn>2</m:mn><m:mo>=</m:mo><m:mn>3</m:mn></m:math>',
	               '<math display="inline"><mn>1</mn><mo>+</mo><mn>2</mn><mo>=</mo><mn>3</mn></math>'),
	                    
            array('<math xmlns="http://www.w3.org/1998/Math/MathML" display="inline"><mn>1</mn><mo>+</mo><mn>2</mn><mo>=</mo><mn>3</mn></math>',
                   '<math display="inline"><mn>1</mn><mo>+</mo><mn>2</mn><mo>=</mo><mn>3</mn></math>'),
	                    
	        array('<math xmlns="http://www.w3.org/1998/Math/MathML" display="inline"><mn><![CDATA[1]]></mn><mo>+</mo><mn><![CDATA[2]]></mn><mo>=</mo><mn><![CDATA[3]]></mn></math>',
	               '<math display="inline"><mn><![CDATA[1]]></mn><mo>+</mo><mn><![CDATA[2]]></mn><mo>=</mo><mn><![CDATA[3]]></mn></math>')
	    );
	}
	
	/**
	 * @dataProvider getXsdLocationProvider
	 * 
	 * @param string $file
	 * @param string $namespaceUri
	 * @param boolean|string $expectedLocation
	 */
	public function testGetXsdLocation($file, $namespaceUri, $expectedLocation) {
	    $document = new DOMDocument('1.0', 'UTF-8');
	    $document->load($file);
	    $location = Utils::getXsdLocation($document, $namespaceUri);
	    
	    $this->assertSame($expectedLocation, $location);
	}
	
	public function getXsdLocationProvider() {
	    return array(
	        // Valid.
	        array(
	            self::samplesDir() . 'custom/items/versiondetection_20_singlespace.xml', 
	            'http://www.imsglobal.org/xsd/imsqti_v2p0', 
	            'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd'
	        ),
            array(
                self::samplesDir() . 'custom/items/versiondetection_20_multispace.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd'
            ),
            array(
                self::samplesDir() . 'custom/items/versiondetection_20_singletab.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd'
            ),
            array(
                self::samplesDir() . 'custom/items/versiondetection_20_multitab.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd'
            ),
            array(
                self::samplesDir() . 'custom/items/versiondetection_20_leading.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd'
            ),
            array(
                self::samplesDir() . 'custom/items/versiondetection_20_trailing.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd'
            ),
	                    
	        // Invalid
            array(
                self::samplesDir() . 'custom/items/versiondetection_20_trailing.xml',
                'wrong-ns',
                false
          ),
            array(
                self::samplesDir() . 'custom/items/versiondetection_20_emptyschemalocation.xml',
                'wrong-ns',
                false
          ),
            array(
                self::samplesDir() . 'custom/items/versiondetection_20_noschemalocationattribute.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                false
          ),
	    );
	}
}