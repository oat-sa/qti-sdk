<?php

namespace qtismtest\runtime\rendering\markup\xhtml;

use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Abbr;
use qtism\data\content\xhtml\text\Br;
use qtism\runtime\rendering\markup\xhtml\BodyElementRenderer;
use qtism\runtime\rendering\markup\xhtml\TextRunRenderer;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;
use qtismtest\QtiSmTestCase;

class BodyElementRendererTest extends QtiSmTestCase
{
    public function testRenderNoChildren()
    {
        $ctx = new XhtmlRenderingEngine();
        $ctx->setCssClassPolicy(XhtmlRenderingEngine::CSSCLASS_ABSTRACT);
        $br = new Br('my-br', 'break down', 'en-US', 'QTISM generated.');

        $renderer = new BodyElementRenderer();
        $renderer->setRenderingEngine($ctx);

        $element = $renderer->render($br)->firstChild;

        $this->assertEquals('br', $element->nodeName);
        $this->assertEquals('my-br', $element->getAttribute('id'));
        $this->assertEquals('qti-bodyElement qti-br break down', $element->getAttribute('class'));
        $this->assertEquals('en-US', $element->getAttribute('lang'));
        $this->assertEquals('', $element->getAttribute('label'));
    }

    public function testRenderChildren()
    {
        $ctx = new XhtmlRenderingEngine();
        $ctx->setCssClassPolicy(XhtmlRenderingEngine::CSSCLASS_ABSTRACT);

        $abbr = new Abbr('my-abbr', 'qti qti-abbr');
        $abbrRenderer = new BodyElementRenderer();
        $abbrRenderer->setRenderingEngine($ctx);

        $textRun = new TextRun('abbreviation...');
        $textRunRenderer = new TextRunRenderer();
        $textRunRenderer->setRenderingEngine($ctx);
        $renderedTextRun = $textRunRenderer->render($textRun);
        $ctx->storeRendering($textRun, $renderedTextRun);

        $abbr->setContent(new InlineCollection([$textRun]));

        $element = $abbrRenderer->render($abbr)->firstChild;

        $this->assertEquals('abbr', $element->nodeName);
        $this->assertEquals('my-abbr', $element->getAttribute('id'));
        $this->assertEquals('qti-bodyElement qti-abbr qti qti-abbr', $element->getAttribute('class'));
        $this->assertEquals('abbreviation...', $element->firstChild->wholeText);
    }
}
