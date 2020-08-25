<?php

namespace qtismtest\runtime\rendering\markup\xhtml;

use DOMElement;
use qtism\data\content\enums\AriaLive;
use qtism\data\content\enums\AriaOrientation;
use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Abbr;
use qtism\data\content\xhtml\text\Br;
use qtism\data\content\xhtml\text\Span;
use qtism\runtime\rendering\markup\xhtml\BodyElementRenderer;
use qtism\runtime\rendering\markup\xhtml\TextRunRenderer;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;
use qtism\runtime\rendering\RenderingException;
use qtismtest\QtiSmTestCase;

/**
 * Class BodyElementRendererTest
 *
 * @package qtismtest\runtime\rendering\markup\xhtml
 */
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

        /** @var DOMElement $element */
        $element = $abbrRenderer->render($abbr)->firstChild;

        $this->assertEquals('abbr', $element->nodeName);
        $this->assertEquals('my-abbr', $element->getAttribute('id'));
        $this->assertEquals('qti-bodyElement qti-abbr qti qti-abbr', $element->getAttribute('class'));
        $this->assertEquals('abbreviation...', $element->firstChild->wholeText);

        // no aria-* attributes should be found...
        $this->assertSame('', $element->getAttribute('aria-orientation'));
        $this->assertSame('', $element->getAttribute('aria-live'));
        $this->assertSame('', $element->getAttribute('aria-level'));
        $this->assertSame('', $element->getAttribute('aria-owns'));
        $this->assertSame('', $element->getAttribute('aria-labelledby'));
        $this->assertSame('', $element->getAttribute('aria-flowto'));
        $this->assertSame('', $element->getAttribute('aria-label'));
        $this->assertSame('', $element->getAttribute('aria-describedby'));
        $this->assertSame('', $element->getAttribute('aria-controls'));
        $this->assertSame('', $element->getAttribute('aria-hidden'));
    }

    public function testRenderFullAria()
    {
        $ctx = new XhtmlRenderingEngine();

        $span = new Span('myspan');
        $span->setAriaOrientation(AriaOrientation::HORIZONTAL);
        $span->setAriaLive(AriaLive::POLITE);
        $span->setAriaLevel(5);
        $span->setAriaOwns('IDREF1');
        $span->setAriaLabelledBy('IDREF2');
        $span->setAriaFlowTo('IDREF3');
        $span->setAriaLabel('my label');
        $span->setAriaDescribedBy('IDREF4');
        $span->setAriaControls('IDREF5');
        $span->setAriaHidden(true);

        $renderer = new BodyElementRenderer();
        $renderer->setRenderingEngine($ctx);

        /** @var DOMElement $element */
        $element = $renderer->render($span)->firstChild;

        $this->assertEquals('span', $element->nodeName);
        $this->assertEquals('horizontal', $element->getAttribute('aria-orientation'));
        $this->assertEquals('polite', $element->getAttribute('aria-live'));
        $this->assertEquals('5', $element->getAttribute('aria-level'));
        $this->assertEquals('IDREF1', $element->getAttribute('aria-owns'));
        $this->assertEquals('IDREF2', $element->getAttribute('aria-labelledby'));
        $this->assertEquals('IDREF3', $element->getAttribute('aria-flowto'));
        $this->assertEquals('my label', $element->getAttribute('aria-label'));
        $this->assertEquals('IDREF4', $element->getAttribute('aria-describedby'));
        $this->assertEquals('IDREF5', $element->getAttribute('aria-controls'));
        $this->assertEquals('true', $element->getAttribute('aria-hidden'));
    }
}
