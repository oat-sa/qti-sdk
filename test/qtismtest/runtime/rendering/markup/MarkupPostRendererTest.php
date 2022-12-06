<?php

namespace qtismtest\runtime\rendering\markup;

use DOMDocument;
use qtism\runtime\rendering\markup\MarkupPostRenderer;
use qtismtest\QtiSmTestCase;
use qtism\runtime\rendering\RenderingException;

/**
 * Class MarkupPostRendererTest
 */
class MarkupPostRendererTest extends QtiSmTestCase
{
    /**
     * @dataProvider xmlDeclarationCleanUpProvider
     * @param string $relativeUri
     * @throws RenderingException
     */
    public function testXmlDeclarationCleanUp($relativeUri): void
    {
        // Regular file, simple new line after XML declaration.
        $file = self::samplesDir() . $relativeUri;
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load($file, LIBXML_NONET);

        // Check content prior to test.
        $output = $doc->saveXML();
        $this::assertSame(0, mb_strpos($output, '<?xml version="1.0" encoding="UTF-8"?>', 0, 'UTF-8'));

        // formatOutput + clean up XML declaration.
        $renderer = new MarkupPostRenderer(true, true);
        $output = $renderer->render($doc);
        $this::assertSame(0, mb_strpos($output, '<itemBody', 0, 'UTF-8'));
    }

    /**
     * @return array
     */
    public function xmlDeclarationCleanUpProvider(): array
    {
        return [
            // Regular file, simple new line after XML declaration.
            ['rendering/postrendering/xmldeclaration_cleanup_1.xml'],
            // Regular file, multiple new lines after XML declaration.
            ['rendering/postrendering/xmldeclaration_cleanup_2.xml'],
            // Regular file, no new lines after XML declaration.
            ['rendering/postrendering/xmldeclaration_cleanup_3.xml'],
        ];
    }

    public function testNoDocumentElement(): void
    {
        $this->expectException(RenderingException::class);

        $doc = new DOMDocument('1.0', 'UTF-8');
        $renderer = new MarkupPostRenderer();
        $output = $renderer->render($doc);
    }

    public function testTemplateOrientedFeedback(): void
    {
        $file = self::samplesDir() . 'rendering/postrendering/templateoriented_1.xml';
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load($file, LIBXML_NONET);

        $file = file($file);
        // Check file consistency...
        $this::assertEquals("<!-- qtism-if ((\$qtismId = \$qtismState['outcome1']) !== null && \$qtismId instanceof qtism\\common\\datatypes\\QtiIdentifier && \$qtismId->getValue() == 'showoutcome1'): -->", trim($file[3]));
        $this::assertEquals("<!-- qtism-if ((\$qtismId = \$qtismState['outcome2']) !== null && \$qtismId instanceof qtism\\common\\datatypes\\QtiIdentifier && \$qtismId->getValue() != 'hideoutcome2'): -->", trim($file[5]));
        $this::assertEquals('<!-- qtism-endif -->', trim($file[9]));
        $this::assertEquals('<!-- qtism-endif -->', trim($file[11]));
        $this::assertEquals("<!-- qtism-if ((\$qtismId = \$qtismState['outcome3']) !== null && \$qtismId instanceof qtism\\common\\datatypes\\QtiIdentifier && \$qtismId->getValue() != 'hideoutcome3'): -->", trim($file[13]));
        $this::assertEquals('<!-- qtism-endif -->', trim($file[15]));
        $this::assertEquals('<div><!-- qtism-printVariable($qtismState, "outcome5", "hello int %i!", false, 10, -1, ";", "", "=") --></div>', trim($file[17]));
        $this::assertEquals("<!-- qtism-if ((\$qtismId = \$qtismState['outcome4']) !== null && \$qtismId instanceof qtism\\common\\datatypes\\QtiIdentifier && \$qtismId->getValue() == 'showoutcome4'): -->", trim($file[19]));
        $this::assertEquals('<!-- qtism-endif -->', trim($file[21]));

        // Check output consistency...
        $renderer = new MarkupPostRenderer(true, true, true);
        $output = $renderer->render($doc);

        $filename = tempnam('/tmp', 'qsm');
        file_put_contents($filename, $output);

        $file = file($filename);
        $this::assertEquals("<?php if ((\$qtismId = \$qtismState['outcome1']) !== null && \$qtismId instanceof qtism\\common\\datatypes\\QtiIdentifier && \$qtismId->getValue() == 'showoutcome1'): ?>", trim($file[2]));
        $this::assertEquals("<?php if ((\$qtismId = \$qtismState['outcome2']) !== null && \$qtismId instanceof qtism\\common\\datatypes\\QtiIdentifier && \$qtismId->getValue() != 'hideoutcome2'): ?>", trim($file[4]));
        $this::assertEquals('<?php endif; ?>', trim($file[8]));
        $this::assertEquals('<?php endif; ?>', trim($file[10]));
        $this::assertEquals("<?php if ((\$qtismId = \$qtismState['outcome3']) !== null && \$qtismId instanceof qtism\\common\\datatypes\\QtiIdentifier && \$qtismId->getValue() != 'hideoutcome3'): ?>", trim($file[12]));
        $this::assertEquals('<?php endif; ?>', trim($file[14]));
        $this::assertEquals("<div><?php echo qtism\\runtime\\rendering\\markup\\Utils::printVariable(\$qtismState, \"outcome5\", \"hello int %i!\", false, 10, -1, \";\", \"\", \"=\"); ?></div>", trim($file[16]));
        $this::assertEquals("<?php if ((\$qtismId = \$qtismState['outcome4']) !== null && \$qtismId instanceof qtism\\common\\datatypes\\QtiIdentifier && \$qtismId->getValue() == 'showoutcome4'): ?>", trim($file[18]));
        $this::assertEquals('<?php endif; ?>', trim($file[20]));

        unlink($filename);
    }

    public function testTemplateOrientedInclude(): void
    {
        $file = self::samplesDir() . 'rendering/postrendering/templateoriented_2.xml';
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load($file, LIBXML_NONET);

        // Check output consistency...
        $renderer = new MarkupPostRenderer(true, true, true);
        $output = $renderer->render($doc);

        $filename = tempnam('/tmp', 'qsm');
        file_put_contents($filename, $output);

        $file = file($filename);
        $this::assertEquals('<?php include(__DIR__ . "/0-" . $qtismState->getShuffledChoiceIdentifierAt(0, 0) . ".phtml"); ?>', trim($file[4]));
        $this::assertEquals('<?php include(__DIR__ . "/0-" . $qtismState->getShuffledChoiceIdentifierAt(0, 2) . ".phtml"); ?>', trim($file[6]));

        $fragments = $renderer->getFragments();
        $this::assertEquals('0-red.phtml', $fragments[0]['path']);
        $this::assertEquals('0-black.phtml', $fragments[1]['path']);

        unlink($filename);
    }
}
