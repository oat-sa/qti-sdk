<?php

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

require_once(__DIR__ . '/../../vendor/autoload.php');

$doc = new XmlDocument();
$doc->load(__DIR__ . '/../samples/rendering/itemwithfullfeedback_1.xml');

$renderer = new XhtmlRenderingEngine();
$renderer->setFeedbackShowHidePolicy(XhtmlRenderingEngine::TEMPLATE_ORIENTED);
$rendering = $renderer->render($doc->getDocumentComponent());
$rendering->formatOutput = true;

echo $rendering->saveXML();
