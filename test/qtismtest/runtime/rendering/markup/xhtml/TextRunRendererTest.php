<?php

namespace qtismtest\runtime\rendering\markup\xhtml;

use qtism\data\content\TextRun;
use qtism\runtime\rendering\markup\xhtml\TextRunRenderer;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;
use qtismtest\QtiSmTestCase;

/**
 * Class TextRunRendererTest
 */
class TextRunRendererTest extends QtiSmTestCase
{
    public function testRender()
    {
        $ctx = new XhtmlRenderingEngine();
        $textRun = new TextRun('test text');
        $renderer = new TextRunRenderer();
        $renderer->setRenderingEngine($ctx);

        $xhtml = $renderer->render($textRun);
        $node = $xhtml->firstChild;
        $this::assertEquals('test text', $node->wholeText);
    }
}
