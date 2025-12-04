<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\rules\BranchRule;
use qtismtest\QtiSmTestCase;

class BranchRuleMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $target = 'target1';

        $component = new BranchRule(new BaseValue(BaseType::BOOLEAN, true), $target);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('branchRule', $element->nodeName);
        $this::assertEquals('baseValue', $element->getElementsByTagName('baseValue')->item(0)->nodeName);
        $this::assertEquals($target, $element->getAttribute('target'));
    }

    public function testUnmarshall(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<testPart xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="testPart1">
				<branchRule target="target1">
					<baseValue baseType="boolean">true</baseValue>
				</branchRule>
			</testPart>
			'
        );
        $element = $dom->getElementsByTagName('branchRule')->item(0);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(BranchRule::class, $component);
        $this::assertEquals('target1', $component->getTarget());
        $this::assertInstanceOf(BaseValue::class, $component->getExpression());
        $this::assertEquals('testPart1', $component->getParentIdentifier());
    }
}
