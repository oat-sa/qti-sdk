<?php
namespace qtismtest\runtime\rendering\markup\goldilocks;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\goldilocks\GoldilocksRenderingEngine;

class GoldilocksRenderingEngineTest extends QtiSmTestCase {
	
    /**
     * @dataProvider testRenderingProvider
     */
    public function testRendering($file, $expectedFile, $renderingMode, $xmlBasePolicy, $stylesheetPolicy, $cssClassPolicy) 
    {
        $engine = new GoldilocksRenderingEngine();
        $engine->setChoiceShowHidePolicy($renderingMode);
        $engine->setFeedbackShowHidePolicy($renderingMode);
        $engine->setViewPolicy($renderingMode);
        $engine->setPrintedVariablePolicy($renderingMode);
        $engine->setXmlBasePolicy($xmlBasePolicy);
        $engine->setStylesheetPolicy($stylesheetPolicy);
        $engine->setCssClassPolicy($cssClassPolicy);
        
        $doc = new XmlDocument();
        $doc->load($file);
        
        $rendered = $engine->render($doc->getDocumentComponent());
        $strRendered = $rendered->saveXML($rendered->documentElement);
        $strExpected = rtrim(file_get_contents($expectedFile));
        $this->assertEquals($strExpected, $strRendered);
    }
    
    public function testRenderingProvider()
    {
        return array(
            // choiceInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/choice.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/choiceInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // choiceInteraction-1
            array(
                self::samplesDir() . 'ims/items/2_2/choice_multiple.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/choiceInteraction-1.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // choiceInteraction-2
            array(
                self::samplesDir() . 'ims/items/2_2/choice_fixed.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/choiceInteraction-2.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // choiceInteraction-3
            array(
                self::samplesDir() . 'ims/items/2_2/choice_multiple_rtl.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/choiceInteraction-3.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // associateInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/associate.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/associateInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
            // extendedTextInteraction-0
            array(
                self::samplesDir() . 'ims/items/2_2/extended_text.xml',
                self::samplesDir() . 'rendering/goldilocks/rendered/extendedTextInteraction-0.html',
                GoldilocksRenderingEngine::CONTEXT_STATIC,
                GoldilocksRenderingEngine::XMLBASE_IGNORE,
                GoldilocksRenderingEngine::STYLESHEET_INLINE,
                GoldilocksRenderingEngine::CSSCLASS_CONCRETE
            ),
        );
    }
    
    /*public function testGenerate() {
        $renderingMode = GoldilocksRenderingEngine::CONTEXT_STATIC;
        $xmlBasePolicy = GoldilocksRenderingEngine::XMLBASE_IGNORE;
        $stylesheetPolicy = GoldilocksRenderingEngine::STYLESHEET_INLINE;
        $cssClassPolicy = GoldilocksRenderingEngine::CSSCLASS_CONCRETE;
        
        $engine = new GoldilocksRenderingEngine();
        $engine->setChoiceShowHidePolicy($renderingMode);
        $engine->setFeedbackShowHidePolicy($renderingMode);
        $engine->setViewPolicy($renderingMode);
        $engine->setPrintedVariablePolicy($renderingMode);
        $engine->setXmlBasePolicy($xmlBasePolicy);
        $engine->setStylesheetPolicy($stylesheetPolicy);
        $engine->setCssClassPolicy($cssClassPolicy);
        
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_2/extended_text_rubric.xml');
        
        $rendered = $engine->render($doc->getDocumentComponent());
        $strRendered = $rendered->saveXML($rendered->documentElement);
        file_put_contents(self::samplesDir() . 'rendering/goldilocks/rendered/extendedText-1.html', $strRendered . "\n");
    }*/
}
