<?php

declare(strict_types=1);

namespace qtismtest\data\state;

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\qtipl\ConditionRenderingOptions;
use qtism\runtime\rendering\qtipl\QtiPLRenderer;
use qtismtest\QtiSmTestCase;

/**
 * Class QtiPLTest
 */
class QtiPLTest extends QtiSmTestCase
{
    public function testPreCondition(): void
    {
        $renderer = new QtiPLRenderer(ConditionRenderingOptions::getDefault());
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/rulesforQtiPL.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            "preCondition(variable[identifier=\"Q02.RESPONSE\"]() == 'C')",
            $renderer->render($test->getComponentByIdentifier('Q01')->getPreConditions()[0])
        );
    }

    public function testBranchRule(): void
    {
        $renderer = new QtiPLRenderer(ConditionRenderingOptions::getDefault());
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/rulesforQtiPL.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'branchRule[target="Q1"](correct[identifier="Q1"]() == true)',
            $renderer->render($test->getComponentByIdentifier('Q2')->getBranchRules()[0])
        );
    }

    public function testXInclude(): void
    {
        $renderer = new QtiPLRenderer(ConditionRenderingOptions::getDefault());
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/xinclude/xinclude_ns_in_tag.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'include()',
            $renderer->render($test->getComponentsByClassName('include')[0])
        );
    }

    public function testResponseRules(): void
    {
        $renderer = new QtiPLRenderer(ConditionRenderingOptions::getDefault());
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/set_outcome_values_with_sum.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'if (isNull(variable[identifier="response-X"]())) {
    setOutcomeValue[identifier="score-X"](0);
} elseif (variable[identifier="response-X"]() == correct[identifier="response-X"]()) {
    setOutcomeValue[identifier="score-X"](variable[identifier="maxscore-X"]());
} else {
    setOutcomeValue[identifier="score-X"](0);
}',
            $renderer->render($test->getComponentsByClassName('responseCondition')[0])
        );

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/responseruleforQtiPL1.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'if (isNull(variable[identifier="response-X"]())) {
    setOutcomeValue[identifier="score-X"](0);
}',
            $renderer->render($test->getComponentsByClassName('responseCondition')[0])
        );

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/responseruleforQtiPL2.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'if (isNull(variable[identifier="response-X"]())) {
    setOutcomeValue[identifier="score-X"](0);
} elseif (variable[identifier="response-X"]() == correct[identifier="response-X"]()) {
    setOutcomeValue[identifier="score-X"](variable[identifier="maxscore-X"]());
} elseif (variable[identifier="response-Y"]() == correct[identifier="response-Y"]()) {
    setOutcomeValue[identifier="score-Y"](variable[identifier="maxscore-Y"]());
} else {
    setOutcomeValue[identifier="score-X"](0);
}',
            $renderer->render($test->getComponentsByClassName('responseCondition')[0])
        );

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/responseruleforQtiPL3.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'if (isNull(variable[identifier="response-X"]())) {
    setOutcomeValue[identifier="score-X"](0);
} elseif (variable[identifier="response-X"]() == correct[identifier="response-X"]()) {
    lookupOutcomeValue[identifier="score-X"](true == true);
} elseif (variable[identifier="response-Y"]() == correct[identifier="response-Y"]()) {
    exitResponse();
} else {
    setOutcomeValue[identifier="score-X"](0);
}',
            $renderer->render($test->getComponentsByClassName('responseCondition')[0])
        );
    }

    public function testTemplateRules(): void
    {
        $renderer = new QtiPLRenderer(ConditionRenderingOptions::getDefault());
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/templateruleforQtiPL1.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'if (true) {
    exitTemplate();
} elseif (false) {
    setTemplateValue[identifier="Q01"]("Template");
} elseif (true) {
    setCorrectResponse[identifier="Q01"](true);
} else {
    templateConstraint(3);
}',
            $renderer->render($test->getComponentsByClassName('templateCondition')[0])
        );

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/templateruleforQtiPL2.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'if (true) {
    setDefaultValue[identifier="Q01"](true);
}',
            $renderer->render($test->getComponentsByClassName('templateCondition')[0])
        );
    }

    public function testOutcomeRules(): void
    {
        $renderer = new QtiPLRenderer(ConditionRenderingOptions::getDefault());
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/rulesforQtiPL.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'if (true) {
    exitTest();
} elseif (false) {
    exitTest();
} elseif (true) {
    exitTest();
} else {
    setOutcomeValue[identifier="Q01"](false);
}',
            $renderer->render($test->getComponentsByClassName('outcomeCondition')[0])
        );

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingexpressions.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'if (true) {
    exitTest();
}',
            $renderer->render($test->getComponentsByClassName('outcomeCondition')[0])
        );
    }

    public function testParametrableIndentation(): void
    {
        $renderer = new QtiPLRenderer(new ConditionRenderingOptions(8));
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/rulesforQtiPL.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'if (true) {
        exitTest();
} elseif (false) {
        exitTest();
} elseif (true) {
        exitTest();
} else {
        setOutcomeValue[identifier="Q01"](false);
}',
            $renderer->render($test->getComponentsByClassName('outcomeCondition')[0])
        );

        $renderer = new QtiPLRenderer(new ConditionRenderingOptions(-8));
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingexpressions.xml');
        $test = $doc->getDocumentComponent();

        $this::assertEquals(
            'if (true) {
exitTest();
}',
            $renderer->render($test->getComponentsByClassName('outcomeCondition')[0])
        );
    }
}
