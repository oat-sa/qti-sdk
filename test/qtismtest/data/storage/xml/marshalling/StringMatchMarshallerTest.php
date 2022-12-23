<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\StringMatch;
use qtismtest\QtiSmTestCase;

/**
 * Class StringMatchMarshallerTest
 */
class StringMatchMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $subs = new ExpressionCollection();
        $subs[] = new BaseValue(BaseType::STRING, 'hell');
        $subs[] = new BaseValue(BaseType::STRING, 'hello');

        $component = new StringMatch($subs, false);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('stringMatch', $element->nodeName);
        $this::assertEquals('false', $element->getAttribute('caseSensitive'));
        $this::assertEquals('false', $element->getAttribute('substring'));
        $this::assertEquals(2, $element->getElementsByTagName('baseValue')->length);
    }

    public function testUnmarshall(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<stringMatch xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" caseSensitive="true">
				<baseValue baseType="string">hell</baseValue>
				<baseValue baseType="string">hello</baseValue>
			</stringMatch>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(StringMatch::class, $component);
        $this::assertIsBool($component->isCaseSensitive());
        $this::assertTrue($component->isCaseSensitive());
        $this::assertIsBool($component->mustSubstring());
        $this::assertFalse($component->mustSubstring());
    }
}
