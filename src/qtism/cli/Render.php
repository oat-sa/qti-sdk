<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\cli;

use cli\Arguments;
use DOMXPath;
use qtism\common\utils\Exception as ExceptionUtils;
use qtism\data\storage\xml\Utils as XmlUtils;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;
use qtism\runtime\rendering\markup\goldilocks\GoldilocksRenderingEngine;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

/**
 * Render CLI Module.
 *
 * This CLI Module enables you to render QTI XML files in various flavours
 * e.g. XHTML or Goldilocks.
 */
class Render extends Cli
{
    /**
     * @see \qtism\cli\Cli::setupArguments()
     */
    protected function setupArguments()
    {
        $arguments = new Arguments(['strict' => false]);

        // -- Options
        // Flavour option.
        $arguments->addOption(
            ['flavour'],
            [
                'default' => 'xhtml',
                'description' => 'Rendering flavour.',
            ]
        );

        // Source option.
        $arguments->addOption(
            ['source'],
            [
                'description' => 'QTI XML source to be rendered.',
            ]
        );

        // XMLBase option.
        $arguments->addOption(
            ['xmlbase'],
            [
                'default' => 'process',
                'description' => 'xml:base behaviour.',
            ]
        );

        // -- Flags
        // Document flag.
        $arguments->addFlag(
            ['document', 'd'],
            'Embed the rendering into a document.'
        );

        // Format flag.
        $arguments->addFlag(
            ['format', 'f'],
            'Format the rendering output with indentation.'
        );

        // Novalidate flag.
        $arguments->addFlag(
            ['novalidate', 'n'],
            'Do not validate QTI XML source.'
        );

        // CSS Class Hierarchy flag.
        $arguments->addFlag(
            ['csshierarchy', 'c'],
            'Full qti- CSS class hierarchy.'
        );

        return $arguments;
    }

    /**
     * @see \qtism\cli\Cli::checkArguments()
     */
    protected function checkArguments()
    {
        $arguments = $this->getArguments();

        // Check 'source' argument.
        if (($source = $arguments['source']) === null) {
            $this->missingArgument('source');
        } elseif (is_readable($source) === false) {
            if (file_exists($source) === false) {
                $msg = "The QTI file '${source}' does not exist.";
            } else {
                $msg = "The QTI file '${source}' cannot be read. Check permissions.";
            }

            $this->fail($msg);
        }

        // Check 'flavour' argument.
        if (empty($arguments['flavour']) == true) {
            $arguments['flavour'] = 'xhtml';
        }

        $knownFlavours = [
            'xhtml',
            'goldilocks',
        ];

        if (in_array(strtolower($arguments['flavour']), $knownFlavours) === false) {
            $msg = "Unknown --flavour value'" . $arguments['flavour'] . "'. Available flavours are " . implode(', ', $knownFlavours) . '.';
            $this->fail($msg);
        }

        // Check 'xmlbase' argument.
        if (empty($arguments['xmlbase']) == true) {
            $arguments['xmlbase'] = 'process';
        }

        $knownXmlBase = [
            'process',
            'keep',
            'ignore',
        ];

        if (in_array(strtolower($arguments['xmlbase']), $knownXmlBase) === false) {
            $msg = "Unknown --xmlbase value '" . $arguments['xmlbase'] . "'. Available values are " . implode(', ', $knownXmlBase) . '.';
            $this->fail($msg);
        }
    }

    /**
     * Renders a QTI XML File into another flavour.
     *
     * This implementations considers that all necessary checks about
     * arguments and their values were performed in \qtism\cli\Render::checkArguments().
     *
     * @see \qtism\cli\Cli::run()
     * @see \qtism\cli\Render::checkArguments()
     */
    protected function run()
    {
        $engine = $this->instantiateEngine();
        $arguments = $this->getArguments();

        // Load XML Document.
        $source = $arguments['source'];
        $doc = new XmlDocument();
        $validate = !($arguments['novalidate'] === true);

        try {
            $doc->load($source, $validate);

            $renderingData = '';

            switch (strtolower($arguments['flavour'])) {
                case 'goldilocks':
                    $renderingData = $this->runGoldilocks($doc, $engine);
                    break;

                case 'xhtml':
                    $renderingData = $this->runXhtml($doc, $engine);
                    break;
            }

            // Add final new line?
            $nl = false;
            if ($arguments['document'] !== true && $arguments['format'] !== true) {
                $nl = true;
            }

            $this->out($renderingData, $nl);
            $this->success('QTI XML file successfully rendered.');
        } catch (XmlStorageException $e) {
            switch ($e->getCode()) {
                case XmlStorageException::READ:
                    $msg = "An error occurred while reading QTI file '${source}'.\nThe system returned the following error:\n";
                    $msg .= ExceptionUtils::formatMessage($e);
                    $this->fail($msg);
                    break;

                case XmlStorageException::XSD_VALIDATION:
                    $msg = "The QTI file '${source}' is invalid against XML Schema.\nThe system returned the following error:\n";
                    $msg .= ExceptionUtils::formatMessage($e);
                    $this->fail($msg);
                    break;

                case XmlStorageException::VERSION:
                    $msg = "The QTI version of file '${source}' could not be detected.";
                    $this->fail($msg);
                    break;

                default:
                    $msg = "An fatal error occurred while reading QTI file '${source}'.";
                    $this->fail($msg);
                    break;
            }
        }
    }

    /**
     * Run the rendering behaviour related to the "Goldilocks" flavour.
     *
     * @param XmlDocument $doc the QTI XML document to be rendered.
     * @param GoldilocksRenderingEngine $renderer An instance of GoldilocksRenderingEngine
     * @return string The rendered data as a string.
     */
    private function runGoldilocks(XmlDocument $doc, GoldilocksRenderingEngine $renderer)
    {
        $arguments = $this->getArguments();
        $profile = $arguments['flavour'];

        $xml = $renderer->render($doc->getDocumentComponent());

        $header = '';
        $footer = '';
        $indent = '';
        $nl = '';

        if ($arguments['format'] === true) {
            $xml->formatOutput = true;
            $indent .= "\x20\x20";
            $nl .= "\n";
        }

        if ($arguments['document'] === true) {
            $header .= "<!doctype html>\n";
        }

        $xpath = new DOMXPath($xml);
        $assessmentItemElts = $xpath->query("//div[contains(@class, 'qti-assessmentItem')]");

        if ($assessmentItemElts->length > 0 && $arguments['document'] === true) {
            $rootComponent = $doc->getDocumentComponent();
            $htmlAttributes = [];

            // Take the content of <assessmentItem> and put it into <html>.
            $attributes = $assessmentItemElts->item(0)->attributes;
            foreach ($attributes as $name => $attr) {
                $htmlAttributes[$name] = $name . '="' . XmlUtils::escapeXmlSpecialChars($attr->value, true) . '"';
            }

            while ($attributes->length > 0) {
                $assessmentItemElts->item(0)->removeAttribute($attributes->item(0)->name);
            }

            $header .= '<html ' . implode(' ', $htmlAttributes) . ">${nl}";
            $header .= "${indent}<head>${nl}";
            $header .= "${indent}${indent}<meta charset=\"utf-8\">${nl}";
            $header .= "${indent}${indent}<title>" . XmlUtils::escapeXmlSpecialChars($rootComponent->getTitle()) . "</title>${nl}";
            $header .= "${indent}${indent}" . $renderer->getStylesheets()->ownerDocument->saveXML($renderer->getStylesheets());
            $header .= "${indent}</head>${nl}";

            $itemBodyElts = $xpath->query("//div[contains(@class, 'qti-itemBody')]");
            if ($itemBodyElts->length > 0) {
                $body = $xml->saveXml($itemBodyElts->item(0));
                $body = substr($body, strlen('<div>'));
                $body = substr($body, 0, strlen('</div>') * -1);
                $body = "<body ${body}</body>${nl}";
            } else {
                $body = $xml->saveXml($xml->documentElement) . (string)${nl};
            }

            if ($arguments['document'] === true) {
                $footer = "</html>\n";
            }
        } else {
            $body = $xml->saveXml($xml->documentElement) . (string)${nl};
        }

        // Indent body...
        $indentBody = '';

        if ($arguments['document'] === null) {
            $indent = '';
        }

        foreach (preg_split('/\n|\r/u', $body, -1, PREG_SPLIT_NO_EMPTY) as $bodyLine) {
            // do stuff with $line
            $indentBody .= "${indent}${bodyLine}${nl}";
        }

        $body = $indentBody;

        return "{$header}{$body}{$footer}";
    }

    /**
     * Run the rendering behaviour related to the "XHTML" flavour.
     *
     * @param XmlDocument $doc The QTI XML document to be rendered.
     * @param XhtmlRenderingEngine $renderer
     * @return string The raw rendering data.
     */
    private function runXhtml(XmlDocument $doc, XhtmlRenderingEngine $renderer)
    {
        $arguments = $this->getArguments();
        $profile = $arguments['flavour'];

        $xml = $renderer->render($doc->getDocumentComponent());

        $header = '';
        $footer = '';
        $indent = '';
        $nl = '';

        if ($arguments['format'] === true) {
            $xml->formatOutput = true;
            $indent .= "\x20\x20";
            $nl .= "\n";
        }

        if ($arguments['document'] === true) {
            $rootComponent = $doc->getDocumentComponent();

            $title = '';
            if ($rootComponent->getQtiClassName() === 'assessmentItem') {
                $title = XmlUtils::escapeXmlSpecialChars($rootComponent->getTitle());
            }

            $header .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
            $header .= "<html>${nl}";
            $header .= "${indent}<head>${nl}";
            $header .= "${indent}${indent}<meta charset=\"utf-8\">${nl}";

            if (empty($title) !== false) {
                $header .= "${indent}${indent}<title>" . $title . "</title>${nl}";
            }

            $header .= "${indent}${indent}" . $renderer->getStylesheets()->ownerDocument->saveXML($renderer->getStylesheets());
            $header .= "${indent}</head>${nl}";
            $header .= "${indent}<body>${nl}";

            $footer = "${indent}</body>${nl}";
            $footer .= "</html>\n";
        }

        $body = $xml->saveXml($xml->documentElement) . (string)${nl};

        // Indent body...
        $indentBody = '';

        if ($arguments['document'] === null) {
            $indent = '';
        }

        foreach (preg_split('/\n|\r/u', $body, -1, PREG_SPLIT_NO_EMPTY) as $bodyLine) {
            // do stuff with $line
            $indentBody .= "${indent}${indent}${bodyLine}${nl}";
        }

        $body = $indentBody;

        return "{$header}{$indentBody}{$footer}";
    }

    /**
     * Instantiate an appropriate Rendering Engine.
     *
     * The instantiated Rendering Engine implementation will depend on the "flavour"
     * CLI argument.
     *
     * @return AbstractMarkupRenderingEngine
     */
    private function instantiateEngine()
    {
        $arguments = $this->getArguments();
        $engine = null;
        switch (strtolower($arguments['flavour'])) {
            case 'goldilocks':
                $engine = new GoldilocksRenderingEngine();
                break;

            case 'xhtml':
                $engine = new XhtmlRenderingEngine();
                break;
        }

        if ($arguments['xmlbase'] === 'process') {
            $engine->setXmlBasePolicy(AbstractMarkupRenderingEngine::XMLBASE_PROCESS);
        } elseif ($arguments['xmlbase'] === 'keep') {
            $engine->setXmlBasePolicy(AbstractMarkupRenderingEngine::XMLBASE_KEEP);
        } elseif ($arguments['xmlbase'] === 'ignore') {
            $engine->setXmlBasePolicy(AbstractMarkupRenderingEngine::XMLBASE_IGNORE);
        }

        if ($arguments['document'] === true) {
            $engine->setStylesheetPolicy(AbstractMarkupRenderingEngine::STYLESHEET_SEPARATE);
        }

        if ($arguments['csshierarchy'] === true) {
            $engine->setCssClassPolicy(AbstractMarkupRenderingEngine::CSSCLASS_ABSTRACT);
        }

        return $engine;
    }
}
