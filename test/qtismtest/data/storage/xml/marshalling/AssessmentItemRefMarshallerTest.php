<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\collections\IdentifierCollection;
use qtism\common\datatypes\QtiDuration;
use qtism\common\enums\BaseType;
use qtism\data\AssessmentItemRef;
use qtism\data\expressions\BaseValue;
use qtism\data\ItemSessionControl;
use qtism\data\rules\BranchRule;
use qtism\data\rules\BranchRuleCollection;
use qtism\data\rules\PreCondition;
use qtism\data\rules\PreConditionCollection;
use qtism\data\state\TemplateDefault;
use qtism\data\state\TemplateDefaultCollection;
use qtism\data\state\VariableMapping;
use qtism\data\state\VariableMappingCollection;
use qtism\data\state\Weight;
use qtism\data\state\WeightCollection;
use qtism\data\TimeLimits;
use qtismtest\QtiSmTestCase;

/**
 * Class AssessmentItemRefMarshallerTest
 */
class AssessmentItemRefMarshallerTest extends QtiSmTestCase
{
    public function testMarshallMinimal()
    {
        $identifier = 'question1';
        $href = '../../question1.xml';

        $component = new AssessmentItemRef($identifier, $href);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);
        $this::assertEquals($href, $element->getAttribute('href'));
        $this::assertEquals($identifier, $element->getAttribute('identifier'));
    }

    public function testMarshallMaximal()
    {
        $identifier = 'question1';
        $href = '../../question1.xml';
        $required = true;
        $fixed = true;

        $preConditions = new PreConditionCollection();
        $preConditions[] = new PreCondition(new BaseValue(BaseType::BOOLEAN, true));
        $preConditions[] = new PreCondition(new BaseValue(BaseType::BOOLEAN, false));

        $branchRules = new BranchRuleCollection();
        $branchRules[] = new BranchRule(new BaseValue(BaseType::INTEGER, 1), 'target1');
        $branchRules[] = new BranchRule(new BaseValue(BaseType::INTEGER, 2), 'target2');

        $itemSessionControl = new ItemSessionControl();

        $timeLimits = new TimeLimits();
        $timeLimits->setMaxTime(new QtiDuration('PT50S')); // 50 seconds.

        $variableMappings = new VariableMappingCollection();
        $variableMappings[] = new VariableMapping('var1', 'var2');
        $variableMappings[] = new VariableMapping('var3', 'var4');

        $weights = new WeightCollection();
        $weights[] = new Weight('weight1', 1.5);

        $templateDefaults = new TemplateDefaultCollection();
        $templateDefaults[] = new TemplateDefault('tpl1', new BaseValue(BaseType::INTEGER, 15));

        $categories = new IdentifierCollection(['cat1', 'cat2']);

        $component = new AssessmentItemRef($identifier, $href, $categories);
        $component->setRequired($required);
        $component->setFixed($fixed);
        $component->setPreConditions($preConditions);
        $component->setBranchRules($branchRules);
        $component->setItemSessionControl($itemSessionControl);
        $component->setTimeLimits($timeLimits);
        $component->setWeights($weights);
        $component->setVariableMappings($variableMappings);
        $component->setTemplateDefaults($templateDefaults);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentItemRef', $element->nodeName);
        $this::assertEquals($identifier, $element->getAttribute('identifier'));
        $this::assertEquals($href, $element->getAttribute('href'));
        $this::assertEquals(implode("\x20", $categories->getArrayCopy()), $element->getAttribute('category'));
        $this::assertEquals('true', $element->getAttribute('required'));
        $this::assertEquals('true', $element->getAttribute('fixed'));

        $weightElts = $element->getElementsByTagName('weight');
        $this::assertEquals(1, $weightElts->length);

        $templateDefaultElts = $element->getElementsByTagName('templateDefault');
        $this::assertEquals(1, $templateDefaultElts->length);

        $variableMappingsElts = $element->getElementsByTagName('variableMapping');
        $this::assertEquals(2, $variableMappingsElts->length);

        $preConditionElts = $element->getElementsByTagName('preCondition');
        $this::assertEquals(2, $preConditionElts->length);

        $branchRuleElts = $element->getElementsByTagName('branchRule');
        $this::assertEquals(2, $branchRuleElts->length);

        $itemSessionControlElts = $element->getElementsByTagName('itemSessionControl');
        $this::assertEquals(1, $itemSessionControlElts->length);

        $timeLimitsElts = $element->getElementsByTagName('timeLimits');
        $this::assertEquals(1, $timeLimitsElts->length);
    }

    public function testUnmarshallMinimal()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="question1" href="../../question1.xml"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(AssessmentItemRef::class, $component);
        $this::assertEquals('../../question1.xml', $component->getHref());
        $this::assertEquals('question1', $component->getIdentifier());
        $this::assertFalse($component->isFixed());
        $this::assertFalse($component->isRequired());
    }

    public function testUnmarshallMaximal()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<assessmentItemRef xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="question1" href="../../question1.xml" category="cat1 cat2" fixed="true" required="true">
				<preCondition>
					<baseValue baseType="boolean">true</baseValue>
				</preCondition>
				<preCondition>
					<baseValue baseType="boolean">false</baseValue>
				</preCondition>
				<branchRule target="target1">
					<baseValue baseType="integer">1</baseValue>
				</branchRule>
				<branchRule target="target2">
					<baseValue baseType="integer">2</baseValue>
				</branchRule>
				<itemSessionControl maxAttempts="1" allowComment="true"/>
				<timeLimits minTime="50"/>
				<variableMapping sourceIdentifier="var1" targetIdentifier="var2"/>
				<variableMapping sourceIdentifier="var3" targetIdentifier="var4"/>
				<weight identifier="weight1" value="1.5"/>
				<templateDefault templateIdentifier="tpl1">
					<baseValue baseType="integer">15</baseValue>
				</templateDefault>
			</assessmentItemRef>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(AssessmentItemRef::class, $component);
        $this::assertEquals('../../question1.xml', $component->getHref());
        $this::assertEquals('cat1 cat2', implode("\x20", $component->getCategories()->getArrayCopy()));
        $this::assertTrue($component->isFixed());
        $this::assertTrue($component->isRequired());

        $this::assertCount(2, $component->getVariableMappings());
        $this::assertCount(1, $component->getWeights());
        $this::assertCount(1, $component->getTemplateDefaults());
        $this::assertCount(2, $component->getPreConditions());
        $this::assertCount(2, $component->getBranchRules());
        $this::assertInstanceOf(TimeLimits::class, $component->getTimeLimits());
        $this::assertInstanceOf(ItemSessionControl::class, $component->getItemSessionControl());
    }
}
