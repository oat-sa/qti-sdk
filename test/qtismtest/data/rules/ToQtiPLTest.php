<?php

namespace qtismtest\data\state;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\XmlDocument;

class ToQtiPLTest extends QtiSmTestCase
{
    public function testPreCondition()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/rulesforQtiPL.xml');
        $test = $doc->getDocumentComponent();

        $this->assertEquals("preCondition(variable[identifier=\"Q02.RESPONSE\"]() == 'C')",
            $test->getComponentByIdentifier("Q01")->getPreConditions()[0]->toQtiPL());
    }

    public function testBranchRule()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/rulesforQtiPL.xml');
        $test = $doc->getDocumentComponent();

        $this->assertEquals("branchRule[target=\"Q1\"](correct[identifier=\"Q1\"]() == true)",
            $test->getComponentByIdentifier("Q2")->getBranchRules()[0]->toQtiPL());
    }

    public function testXInclude()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/xinclude/xinclude_ns_in_tag.xml');
        $test = $doc->getDocumentComponent();

        $this->assertEquals("include()",
            $test->getComponentsByClassName("include")[0]->toQtiPL());
    }

    public function testResponseRules()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/set_outcome_values_with_sum.xml');
        $test = $doc->getDocumentComponent();

        $this->assertEquals(
            "if (isNull(variable[identifier=\"response-X\"]())) {
	setOutcomeValue[identifier=\"score-X\"](0);
} elseif (variable[identifier=\"response-X\"]() == correct[identifier=\"response-X\"]()) {
	setOutcomeValue[identifier=\"score-X\"](variable[identifier=\"maxscore-X\"]());
} else {
	setOutcomeValue[identifier=\"score-X\"](0);
}",
            $test->getComponentsByClassName("responseCondition")[0]->toQtiPL());

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/responseruleforQtiPL1.xml');
        $test = $doc->getDocumentComponent();

        $this->assertEquals(
            "if (isNull(variable[identifier=\"response-X\"]())) {
	setOutcomeValue[identifier=\"score-X\"](0);
}",
            $test->getComponentsByClassName("responseCondition")[0]->toQtiPL());

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/responseruleforQtiPL2.xml');
        $test = $doc->getDocumentComponent();

        $this->assertEquals(
            "if (isNull(variable[identifier=\"response-X\"]())) {
	setOutcomeValue[identifier=\"score-X\"](0);
} elseif (variable[identifier=\"response-X\"]() == correct[identifier=\"response-X\"]()) {
	setOutcomeValue[identifier=\"score-X\"](variable[identifier=\"maxscore-X\"]());
} elseif (variable[identifier=\"response-Y\"]() == correct[identifier=\"response-Y\"]()) {
	setOutcomeValue[identifier=\"score-Y\"](variable[identifier=\"maxscore-Y\"]());
} else {
	setOutcomeValue[identifier=\"score-X\"](0);
}",
            $test->getComponentsByClassName("responseCondition")[0]->toQtiPL());

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/responseruleforQtiPL3.xml');
        $test = $doc->getDocumentComponent();

        $this->assertEquals(
            "if (isNull(variable[identifier=\"response-X\"]())) {
	setOutcomeValue[identifier=\"score-X\"](0);
} elseif (variable[identifier=\"response-X\"]() == correct[identifier=\"response-X\"]()) {
	lookupOutcomeValue[identifier=\"score-X\"](true == true);
} elseif (variable[identifier=\"response-Y\"]() == correct[identifier=\"response-Y\"]()) {
	exitResponse();
} else {
	setOutcomeValue[identifier=\"score-X\"](0);
}",
            $test->getComponentsByClassName("responseCondition")[0]->toQtiPL());
    }

    public function testTemplateRules()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/templateruleforQtiPL1.xml');
        $test = $doc->getDocumentComponent();

        $this->assertEquals(
            "if (true) {
	exitTemplate();
} elseif (false) {
	setTemplateValue[identifier=\"Q01\"](\"Template\");
} elseif (true) {
	setCorrectResponse[identifier=\"Q01\"](true);
} else {
	templateConstraint(3);
}",
            $test->getComponentsByClassName("templateCondition")[0]->toQtiPL());

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/templateruleforQtiPL2.xml');
        $test = $doc->getDocumentComponent();

        $this->assertEquals(
            "if (true) {
	setDefaultValue[identifier=\"Q01\"](true);
}",
            $test->getComponentsByClassName("templateCondition")[0]->toQtiPL());
    }

    public function testOutcomeRules()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/rulesforQtiPL.xml');
        $test = $doc->getDocumentComponent();

        $this->assertEquals(
            "if (true) {
	exitTest();
} elseif (false) {
	exitTest();
} elseif (true) {
	exitTest();
} else {
	setOutcomeValue[identifier=\"Q01\"](false);
}",
            $test->getComponentsByClassName("outcomeCondition")[0]->toQtiPL());

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingexpressions.xml');
        $test = $doc->getDocumentComponent();

        $this->assertEquals(
            "if (true) {
	exitTest();
}",
            $test->getComponentsByClassName("outcomeCondition")[0]->toQtiPL());
    }
}