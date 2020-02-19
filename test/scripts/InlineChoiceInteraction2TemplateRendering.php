<?php

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

$doc = new XmlDocument();
$doc->load(dirname(__FILE__) . '/../samples/rendering/inlinechoiceinteraction_2.xml');

$renderer = new XhtmlRenderingEngine();
$renderer->setChoiceShowHidePolicy(XhtmlRenderingEngine::TEMPLATE_ORIENTED);

if (isset($argv[1]) && $argv[1] === 'shuffle') {
    $renderer->setShufflingPolicy(XhtmlRenderingEngine::TEMPLATE_ORIENTED);
}

$rendering = $renderer->render($doc->getDocumentComponent());
$rendering->formatOutput = true;

echo $rendering->saveXML();
