<?php
namespace qtismtest\data\storage\xml;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\Utils;
use \DOMDocument;

class XmlUtilsTest extends QtiSmTestCase
{    
	/**
	 * @dataProvider validInferQTIVersionProvider
	 */
	public function testInferQTIVersionValid($file, $expectedVersion)
    {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->load($file);
		$this->assertEquals($expectedVersion, Utils::inferVersion($dom));
	}
	
	public function validInferQTIVersionProvider()
    {
		return array(
		    array(self::samplesDir() . 'ims/items/2_2/choice_multiple.xml', '2.2.0'),
		    array(self::samplesDir() . 'ims/items/2_1_1/likert.xml', '2.1.1'),
			array(self::samplesDir() . 'ims/items/2_1/associate.xml', '2.1.0'),
			array(self::samplesDir() . 'ims/items/2_0/associate.xml', '2.0.0'),
			array(self::samplesDir() . 'ims/tests/arbitrary_collections_of_item_outcomes/arbitrary_collections_of_item_outcomes.xml', '2.1.0')		
		);
	}
	
	/**
	 * 
	 * @param string $originalXmlString
	 * @param string $expectedXmlString
	 * @dataProvider anonimizeElementProvider
	 */
	public function testAnonimizeElement($originalXmlString, $expectedXmlString)
    {
	    $elt = $this->createDOMElement($originalXmlString);
	    $newElt = Utils::anonimizeElement($elt);
	    
	    $this->assertEquals($expectedXmlString, $newElt->ownerDocument->saveXML($newElt));
	}
	
	public function anonimizeElementProvider() 
    {
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
	public function testGetXsdLocation($file, $namespaceUri, $expectedLocation)
    {
	    $document = new DOMDocument('1.0', 'UTF-8');
	    $document->load($file);
	    $location = Utils::getXsdLocation($document, $namespaceUri);
	    
	    $this->assertSame($expectedLocation, $location);
	}
	
	public function getXsdLocationProvider()
    {
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
	        array(
	            self::samplesDir() . 'ims/items/2_1_1/associate.xml',
	            'http://www.imsglobal.org/xsd/imsqti_v2p1',
	            'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1p1.xsd'                
	        ),
            array(
                self::samplesDir() . 'ims/items/2_1/associate.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p1',
                'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd'
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
    
    /**
     * @dataProvider getSchemaLocationProvider
     */
    public function testGetSchemaLocation($version, $expected)
    {
        $location = Utils::getSchemaLocation($version);
        $this->assertEquals($expected, $location);
    }
    
    public function getSchemaLocationProvider()
    {
        $baseDir = dirname(__FILE__) . '/../../../../../src/qtism/data/storage/xml/schemes';
        return array(
            array('2.0', realpath("${baseDir}/imsqti_v2p0.xsd")),
            array('2.0.0', realpath("${baseDir}/imsqti_v2p0.xsd")),
            array('2.1', realpath("${baseDir}/qtiv2p1/imsqti_v2p1.xsd")),
            array('2.1.0', realpath("${baseDir}/qtiv2p1/imsqti_v2p1.xsd")),
            array('2.1.0', realpath("${baseDir}/qtiv2p1/imsqti_v2p1.xsd")),
            array('2.1.1', realpath("${baseDir}/qtiv2p1p1/imsqti_v2p1p1.xsd")),
            array('2.2', realpath("${baseDir}/qtiv2p2/imsqti_v2p2.xsd")),
            array('2.2.0', realpath("${baseDir}/qtiv2p2/imsqti_v2p2.xsd")),
            array('2.2.1', realpath("${baseDir}/qtiv2p2p1/imsqti_v2p2p1.xsd")),
        );
    }
    
    public function testChangeNamespaceElementName()
    {
        $foo = $this->createDOMElement('<foo xmlns:bar="http://baz" bar:attr="foo"/>');
        $foo = Utils::changeElementName($foo, 'bar');
        $this->assertEquals('bar', $foo->tagName);
        $this->assertEquals('foo', $foo->getAttributeNS('http://baz', 'attr'));
    }
    
    /**
     * @dataProvider escapeXmlSpecialCharsProvider
     */
    public function testEscapeXmlSpecialChars($str, $isAttribute, $expected)
    {
        $this->assertEquals($expected, Utils::escapeXmlSpecialChars($str, $isAttribute));
    }
    
    public function escapeXmlSpecialCharsProvider()
    {
        return array(
            array("'\"&<>", false, "&apos;&quot;&amp;&lt;&gt;"),
            array("<blah>", false, "&lt;blah&gt;"),
            array("blah", false, "blah"),
            array('&"', true, "&amp;&quot;"),
            array('blah & "cool" & \'cool\'', true, "blah &amp; &quot;cool&quot; &amp; 'cool'")
        );
    }
    
    /**
     * @dataProvider webComponentFriendlyAttributeNameProvider
     */
    public function testWebComponentFriendlyAttributeName($qtiName, $expected)
    {
        $this->assertEquals($expected, Utils::webComponentFriendlyAttributeName($qtiName));
    }
    
    public function webComponentFriendlyAttributeNameProvider()
    {
        return [
            ['minChoices', 'min-choices'],
            ['identifier', 'identifier']
        ];
    }
    
    /**
     * @dataProvider webComponentFriendlyClassNameProvider
     */
    public function testWebComponentFriendlyClassName($qtiName, $expected)
    {
        $this->assertEquals($expected, Utils::webComponentFriendlyClassName($qtiName));
    }
    
    public function webComponentFriendlyClassNameProvider()
    {
        return [
            ['choiceInteraction', 'qti-choice-interaction'],
            ['simpleChoice', 'qti-simple-choice'],
            ['prompt', 'qti-prompt']
        ];
    }
    
    /**
     * @dataProvider qtiFriendlyNameProvider
     */
    public function testQtiFriendlyName($wcName, $expected)
    {
        $this->assertEquals($expected, Utils::qtiFriendlyName($wcName));
    }
    
    public function qtiFriendlyNameProvider()
    {
        return [
            ['qti-choice-interaction', 'choiceInteraction'],
            ['qti-prompt', 'prompt'],
            ['min-choices', 'minChoices']
        ];
    }
}
