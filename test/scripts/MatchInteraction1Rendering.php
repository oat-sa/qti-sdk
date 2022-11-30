<?php

declare(strict_types=1);

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

require_once(__DIR__ . '/../../vendor/autoload.php');

$doc = new XmlDocument();
$doc->load(__DIR__ . '/../samples/rendering/matchinteraction_1.xml');

$renderer = new XhtmlRenderingEngine();
if (isset($argv[1]) && $argv[1] === 'shuffle') {
    $renderer->setShufflingPolicy(XhtmlRenderingEngine::CONTEXT_AWARE);
}

$rendering = $renderer->render($doc->getDocumentComponent());
$rendering->formatOutput = true;

echo $rendering->saveXML();
