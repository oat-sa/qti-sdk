<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Equal;
use qtism\data\expressions\operators\ToleranceMode;
use qtismtest\QtiSmTestCase;

/**
 * Class EqualMarshallerTest
 */
class EqualMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $subs = new ExpressionCollection();
        $subs[] = new BaseValue(BaseType::INTEGER, 1);
        $subs[] = new BaseValue(BaseType::INTEGER, 2);

        $toleranceMode = ToleranceMode::EXACT;
        $includeLowerBound = false;
        $includeUpperBound = true;

        $component = new Equal($subs);
        $component->setToleranceMode($toleranceMode);
        $component->setIncludeLowerBound($includeLowerBound);
        $component->setIncludeUpperBound($includeUpperBound);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('equal', $element->nodeName);
        $this->assertEquals('exact', $element->getAttribute('toleranceMode'));
        $this->assertEquals('false', $element->getAttribute('includeLowerBound'));
        $this->assertEquals('', $element->getAttribute('includeUpperBound'));
        $this->assertEquals(2, $element->getElementsByTagName('baseValue')->length);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<equal xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" includeLowerBound="false" includeUpperBound="true" toleranceMode="exact">
				<baseValue baseType="integer">1</baseValue>
				<baseValue baseType="integer">2</baseValue>
			</equal>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Equal::class, $component);
        $this->assertIsBool($component->doesIncludeLowerBound());
        $this->assertIsBool($component->doesIncludeUpperBound());
        $this->assertFalse($component->doesIncludeLowerBound());
        $this->assertTrue($component->doesIncludeUpperBound());
        $this->assertEquals(2, count($component->getExpressions()));
    }
}
