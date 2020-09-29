<?php

namespace qtismtest\runtime\rendering\markup\xhtml;

use qtism\common\datatypes\QtiFloat;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\content\PrintedVariable;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\State;
use qtism\runtime\rendering\markup\xhtml\PrintedVariableRenderer;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;
use qtismtest\QtiSmTestCase;

/**
 * Class PrintedVariableRendererTest
 */
class PrintedVariableRendererTest extends QtiSmTestCase
{
    public function testRenderContextStatic()
    {
        $ctx = new XhtmlRenderingEngine();
        $pv = new PrintedVariable('OUTCOME1', 'my-id');
        $pv->setFormat('%d');
        $pv->setPowerForm(true);
        $pv->setIndex(1);
        $pv->setBase(10);
        $pv->setDelimiter(',');
        $pv->setField('field');
        $pv->setMappingIndicator('=');

        $renderer = new PrintedVariableRenderer();
        $renderer->setRenderingEngine($ctx);

        $element = $renderer->render($pv)->firstChild;

        $this->assertEquals('span', $element->nodeName);
        $this->assertEquals('my-id', $element->getAttribute('id'));
        $this->assertEquals('qti-printedVariable', $element->getAttribute('class'));
        $this->assertEquals('%d', $element->getAttribute('data-format'));
        $this->assertEquals('true', $element->getAttribute('data-power-form'));
        $this->assertEquals('1', $element->getAttribute('data-index'));
        $this->assertEquals('10', $element->getAttribute('data-base'));
        $this->assertEquals(',', $element->getAttribute('data-delimiter'));
        $this->assertEquals('field', $element->getAttribute('data-field'));
        $this->assertEquals('=', $element->getAttribute('data-mapping-indicator'));
    }

    public function testRenderContextAware()
    {
        $ctx = new XhtmlRenderingEngine();
        $ctx->setPrintedVariablePolicy(XhtmlRenderingEngine::CONTEXT_AWARE);

        $var = new OutcomeVariable('OUTCOME1', Cardinality::SINGLE, BaseType::FLOAT);
        $var->setValue(new QtiFloat(1.337));
        $state = new State([$var]);
        $ctx->setState($state);

        $pv = new PrintedVariable('OUTCOME1', 'my-id');
        $pv->setFormat('%d');

        $renderer = new PrintedVariableRenderer();
        $renderer->setRenderingEngine($ctx);

        $element = $renderer->render($pv)->firstChild;

        $this->assertEquals('span', $element->nodeName);
        $this->assertEquals('my-id', $element->getAttribute('id'));
        $this->assertEquals('OUTCOME1', $element->getAttribute('data-identifier'));
        $this->assertEquals('%d', $element->getAttribute('data-format'));
        $this->assertEquals('false', $element->getAttribute('data-power-form'));
        $this->assertEquals('10', $element->getAttribute('data-base'));
        $this->assertEquals(';', $element->getAttribute('data-delimiter'));
        $this->assertEquals('=', $element->getAttribute('data-mapping-indicator'));
        $this->assertEquals('qti-printedVariable', $element->getAttribute('class'));
    }
}
