<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\rules\BranchRule;
use qtismtest\QtiSmTestCase;

/**
 * Class BranchRuleMarshallerTest
 */
class BranchRuleMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $target = 'target1';

        $component = new BranchRule(new BaseValue(BaseType::BOOLEAN, true), $target);
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('branchRule', $element->nodeName);
        $this->assertEquals('baseValue', $element->getElementsByTagName('baseValue')->item(0)->nodeName);
        $this->assertEquals($target, $element->getAttribute('target'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<branchRule xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" target="target1">
				<baseValue baseType="boolean">true</baseValue>
			</branchRule>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory()->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(BranchRule::class, $component);
        $this->assertEquals('target1', $component->getTarget());
        $this->assertInstanceOf(BaseValue::class, $component->getExpression());
    }
}
