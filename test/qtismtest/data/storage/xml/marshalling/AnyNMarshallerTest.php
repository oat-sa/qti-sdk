<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\AnyN;
use qtismtest\QtiSmTestCase;

/**
 * Class AnyNMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class AnyNMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $subs = new ExpressionCollection();
        $subs[] = new BaseValue(BaseType::BOOLEAN, true);
        $subs[] = new BaseValue(BaseType::BOOLEAN, true);
        $subs[] = new BaseValue(BaseType::BOOLEAN, false);

        $min = 1;
        $max = 2;

        $component = new AnyN($subs, 1, 2);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('anyN', $element->nodeName);
        $this->assertEquals('' . $min, $element->getAttribute('min'));
        $this->assertEquals('' . $max, $element->getAttribute('max'));
        $this->assertEquals(3, $element->getElementsByTagName('baseValue')->length);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<anyN xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" min="1" max="2">
				<baseValue baseType="boolean">true</baseValue>
				<baseValue baseType="boolean">true</baseValue>
				<baseValue baseType="boolean">false</baseValue>
			</anyN>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(AnyN::class, $component);
        $this->assertEquals(1, $component->getMin());
        $this->assertEquals(2, $component->getMax());
        $this->assertEquals(3, count($component->getExpressions()));
    }
}
