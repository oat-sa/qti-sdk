<?php
namespace qtismtest\runtime\rendering\markup\xhtml;

use qtismtest\QtiSmTestCase;
use qtism\data\content\PrintedVariable;
use qtism\runtime\rendering\markup\xhtml\PrintedVariableRenderer;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

class PrintedVariableRendererTest extends QtiSmTestCase 
{
    public function testRenderNoChildren() {
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
}
