<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Substring;
use qtismtest\QtiSmTestCase;

/**
 * Class SubstringMarshallerTest
 */
class SubstringMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $expr = [new BaseValue(BaseType::STRING, 'Hell'), new BaseValue(BaseType::STRING, 'Shell')];
        $component = new Substring(new ExpressionCollection($expr), false);
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('substring', $element->nodeName);
        $this->assertEquals('false', $element->getAttribute('caseSensitive'));

        $sub = $element->getElementsByTagName('baseValue')->item(0);
        $this->assertEquals('Hell', $sub->nodeValue);
        $this->assertEquals('string', $sub->getAttribute('baseType'));

        $sub = $element->getElementsByTagName('baseValue')->item(1);
        $this->assertEquals('Shell', $sub->nodeValue);
        $this->assertEquals('string', $sub->getAttribute('baseType'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<substring xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<baseValue baseType="string">Hell</baseValue>
				<baseValue baseType="string">Shell</baseValue>
			</substring>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory()->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Substring::class, $component);
        $this->assertTrue($component->isCaseSensitive());

        $sub = $component->getExpressions();
        $sub = $sub[0];
        $this->assertInstanceOf(BaseValue::class, $sub);
        $this->assertEquals(BaseType::STRING, $sub->getBaseType());
        $this->assertEquals('Hell', $sub->getValue());

        $sub = $component->getExpressions();
        $sub = $sub[1];
        $this->assertInstanceOf(BaseValue::class, $sub);
        $this->assertEquals(BaseType::STRING, $sub->getBaseType());
        $this->assertEquals('Shell', $sub->getValue());
    }
}
