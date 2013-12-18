<?php

use qtism\runtime\rendering\AbstractRenderingEngine;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

require_once(dirname(__FILE__) . '/../../qtism/qtism.php');

$doc = new XmlDocument();
$doc->load('../samples/rendering/xmlbase_2.xml');

$renderer = new XhtmlRenderingEngine();

if (empty($argv[1]) === false) {
    switch (strtolower($argv[1])) {
        case 'ignore':
            $renderer->setXmlBasePolicy(AbstractRenderingEngine::XMLBASE_IGNORE);
            break;

        case 'keep':
            $renderer->setXmlBasePolicy(AbstractRenderingEngine::XMLBASE_KEEP);
            break;

        case 'process':
            $renderer->setXmlBasePolicy(AbstractRenderingEngine::XMLBASE_PROCESS);
            break;
    }
}

$rendering = $renderer->render($doc->getDocumentComponent());
$rendering->formatOutput = true;

echo $rendering->saveXML();