<?php

namespace qtismtest\data\storage\xml;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\storage\xml\Utils;
use qtismtest\QtiSmTestCase;

/**
 * Class XmlUtilsTest
 */
class XmlUtilsTest extends QtiSmTestCase
{
    /**
     * @param string $originalXmlString
     * @param string $expectedXmlString
     * @dataProvider anonimizeElementProvider
     */
    public function testAnonimizeElement($originalXmlString, $expectedXmlString)
    {
        $elt = $this->createDOMElement($originalXmlString);
        $newElt = Utils::anonimizeElement($elt);

        $this::assertEquals($expectedXmlString, $newElt->ownerDocument->saveXML($newElt));
    }

    /**
     * @return array
     */
    public function anonimizeElementProvider()
    {
        return [
            [
                '<m:math xmlns:m="http://www.w3.org/1998/Math/MathML" display="inline"><m:mn>1</m:mn><m:mo>+</m:mo><m:mn>2</m:mn><m:mo>=</m:mo><m:mn>3</m:mn></m:math>',
                '<math display="inline"><mn>1</mn><mo>+</mo><mn>2</mn><mo>=</mo><mn>3</mn></math>',
            ],

            [
                '<math xmlns="http://www.w3.org/1998/Math/MathML" display="inline"><mn>1</mn><mo>+</mo><mn>2</mn><mo>=</mo><mn>3</mn></math>',
                '<math display="inline"><mn>1</mn><mo>+</mo><mn>2</mn><mo>=</mo><mn>3</mn></math>',
            ],

            [
                '<math xmlns="http://www.w3.org/1998/Math/MathML" display="inline"><mn><![CDATA[1]]></mn><mo>+</mo><mn><![CDATA[2]]></mn><mo>=</mo><mn><![CDATA[3]]></mn></math>',
                '<math display="inline"><mn><![CDATA[1]]></mn><mo>+</mo><mn><![CDATA[2]]></mn><mo>=</mo><mn><![CDATA[3]]></mn></math>',
            ],
        ];
    }

    /**
     * @dataProvider getXsdLocationProvider
     *
     * @param string $file
     * @param string $namespaceUri
     * @param bool|string $expectedLocation
     */
    public function testGetXsdLocation($file, $namespaceUri, $expectedLocation)
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->load($file);
        $location = Utils::getXsdLocation($document, $namespaceUri);

        $this::assertSame($expectedLocation, $location);
    }

    /**
     * @return array
     */
    public function getXsdLocationProvider()
    {
        return [
            // Valid.
            [
                self::samplesDir() . 'custom/items/versiondetection_20_singlespace.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd',
            ],
            [
                self::samplesDir() . 'custom/items/versiondetection_20_multispace.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd',
            ],
            [
                self::samplesDir() . 'custom/items/versiondetection_20_singletab.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd',
            ],
            [
                self::samplesDir() . 'custom/items/versiondetection_20_multitab.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd',
            ],
            [
                self::samplesDir() . 'custom/items/versiondetection_20_leading.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd',
            ],
            [
                self::samplesDir() . 'custom/items/versiondetection_20_trailing.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd',
            ],
            [
                self::samplesDir() . 'ims/items/2_1_1/associate.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p1',
                'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1p1.xsd',
            ],
            [
                self::samplesDir() . 'ims/items/2_1/associate.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p1',
                'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd',
            ],

            // Invalid
            [
                self::samplesDir() . 'custom/items/versiondetection_20_trailing.xml',
                'wrong-ns',
                false,
            ],
            [
                self::samplesDir() . 'custom/items/versiondetection_20_emptyschemalocation.xml',
                'wrong-ns',
                false,
            ],
            [
                self::samplesDir() . 'custom/items/versiondetection_20_noschemalocationattribute.xml',
                'http://www.imsglobal.org/xsd/imsqti_v2p0',
                false,
            ],
        ];
    }

    /**
     * @dataProvider getDOMElementAttributeAsProvider
     * @param DOMElement $element
     * @param string $attribute
     * @param string $datatype
     * @param mixed $expected
     */
    public function testGetDOMElementAttributeAs(DOMElement $element, $attribute, $datatype, $expected)
    {
        $result = Utils::getDOMElementAttributeAs($element, $attribute, $datatype);
        $this::assertSame($expected, $result);
    }

    /**
     * @return array
     */
    public function getDOMElementAttributeAsProvider()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<foo string="str&amp;str" integer="1" float="1.1" double="1.1" boolean="true" baseType="duration" wrongEnumValue="blah"/>');
        $elt = $dom->documentElement;

        return [
            [$elt, 'string', 'string', 'str&str'],
            [$elt, 'integer', 'integer', 1],
            [$elt, 'float', 'float', 1.1],
            [$elt, 'double', 'double', 1.1],
            [$elt, 'boolean', 'boolean', true],
            [$elt, 'not-existing', '', null],
            [$elt, 'baseType', BaseType::class, BaseType::DURATION],
            [$elt, 'wrongEnumValue', BaseType::class, 'blah'],
            [$elt, 'cardinality', Cardinality::class, null],
        ];
    }

    /**
     * @dataProvider setDOMElementAttributeProvider
     * @param string $attribute
     * @param string $value
     * @param mixed $expected
     */
    public function testSetDOMElementAttribute($attribute, $value, $expected)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->createElement('foo');
        $dom->appendChild($element);

        Utils::setDOMElementAttribute($element, $attribute, $value);
        $result = $dom->saveXML($element);

        $this::assertSame($expected, $result);
    }

    /**
     * @return array
     */
    public function setDOMElementAttributeProvider()
    {
        return [
            ['string', 'str&str', '<foo string="str&amp;str"/>'],
            ['integer', 1, '<foo integer="1"/>'],
            ['float', 1.1, '<foo float="1.1"/>'],
            ['double', 1.1, '<foo double="1.1"/>'],
            ['boolean', true, '<foo boolean="true"/>'],
            ['not-existing',  null, '<foo not-existing=""/>'],
        ];
    }
}
