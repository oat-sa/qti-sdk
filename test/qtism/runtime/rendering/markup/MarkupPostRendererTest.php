<?php
use qtism\runtime\rendering\markup\MarkupPostRenderer;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class MarkupPostRendererTest extends QtiSmTestCase {
	
    /**
     * @dataProvider xmlDeclarationCleanUpProvider
     */
    public function testXmlDeclarationCleanUp($relativeUri) {
        // Regular file, simple new line after XML declaration.
        $file = self::samplesDir() . $relativeUri;
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->load($file, LIBXML_NONET);
        
        // Check content prior to test.
        $output = $doc->saveXML();
        $this->assertTrue(mb_strpos($output, '<?xml version="1.0" encoding="UTF-8"?>', 0, 'UTF-8') === 0);
        
        // formatOutput + clean up XML declaration.
        $renderer = new MarkupPostRenderer(true, true);
        $output = $renderer->render($doc);
        $this->assertTrue(mb_strpos($output, '<itemBody', 0, 'UTF-8') === 0);
    }
    
    public function xmlDeclarationCleanUpProvider() {
        return array(
            // Regular file, simple new line after XML declaration.
            array('rendering/postrendering/xmldeclaration_cleanup_1.xml'),
            // Regular file, multiple new lines after XML declaration.
            array('rendering/postrendering/xmldeclaration_cleanup_2.xml'),
            // Regular file, no new lines after XML declaration.
            array('rendering/postrendering/xmldeclaration_cleanup_3.xml')                
        );
    }
    
    public function testNoDocumentElement() {
        $this->setExpectedException('qtism\\runtime\\rendering\\RenderingException');
        
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $renderer = new MarkupPostRenderer();
        $output = $renderer->render($doc);
    }
    
    public function testTemplateOrientedFeedback() {
        $file = self::samplesDir() . 'rendering/postrendering/templateoriented_1.xml';
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->load($file, LIBXML_NONET);

        $file = file($file);
        // Check file consistency...
        $this->assertEquals(trim($file[3]), "<!-- qtism-if (\$qtismState['outcome1'] == 'showoutcome1'): -->");
        $this->assertEquals(trim($file[5]), "<!-- qtism-if (\$qtismState['outcome2'] != 'hideoutcome2'): -->");
        $this->assertEquals(trim($file[9]), "<!-- qtism-endif -->");
        $this->assertEquals(trim($file[11]), "<!-- qtism-endif -->");
        $this->assertEquals(trim($file[13]), "<!-- qtism-if (\$qtismState['outcome3'] != 'hideoutcome3'): -->");
        $this->assertEquals(trim($file[15]), "<!-- qtism-endif -->");
        $this->assertEquals(trim($file[18]), "<!-- qtism-if (\$qtismState['outcome4'] == 'showoutcome4'): -->");
        $this->assertEquals(trim($file[20]), "<!-- qtism-endif -->");
        
        // Check output consistency...
        $renderer = new MarkupPostRenderer(true, true, true);
        $output = $renderer->render($doc);
        
        $filename = tempnam('/tmp', 'qsm');
        file_put_contents($filename, $output);
        
        $file = file($filename);
        $this->assertEquals(trim($file[2]), "<?php if (\$qtismState['outcome1'] == 'showoutcome1'): ?>");
        $this->assertEquals(trim($file[4]), "<?php if (\$qtismState['outcome2'] != 'hideoutcome2'): ?>");
        $this->assertEquals(trim($file[8]), "<?php endif; ?>");
        $this->assertEquals(trim($file[10]), "<?php endif; ?>");
        $this->assertEquals(trim($file[12]), "<?php if (\$qtismState['outcome3'] != 'hideoutcome3'): ?>");
        $this->assertEquals(trim($file[14]), "<?php endif; ?>");
        $this->assertEquals(trim($file[17]), "<?php if (\$qtismState['outcome4'] == 'showoutcome4'): ?>");
        $this->assertEquals(trim($file[19]), "<?php endif; ?>");
        
        unlink($filename);
    }
}