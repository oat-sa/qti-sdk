<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\PatternMatch;
use qtismtest\QtiSmTestCase;

/**
 * Class PatternMatchMarshallerTest
 */
class PatternMatchMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $subs = new ExpressionCollection();
        $subs[] = new BaseValue(BaseType::STRING, 'Hello World');

        $pattern = '^Hello World$';

        $component = new PatternMatch($subs, $pattern);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('patternMatch', $element->nodeName);
        $this::assertEquals($pattern, $element->getAttribute('pattern'));
        $this::assertEquals(1, $element->getElementsByTagName('baseValue')->length);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<patternMatch xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" pattern="^Hello World$">
				<baseValue baseType="string">Hello World</baseValue>
			</patternMatch>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(PatternMatch::class, $component);
        $this::assertEquals('^Hello World$', $component->getPattern());
        $this::assertEquals(1, count($component->getExpressions()));
    }
}
