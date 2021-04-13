<?php

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

require_once(__DIR__ . '/../../vendor/autoload.php');

$doc = new XmlDocument();
$doc->load(__DIR__ . '/../samples/rendering/rubricblock_3.xml');

$renderer = new XhtmlRenderingEngine();
$separate = false;

if (isset($argv[1]) && strtolower($argv[1]) === 'separate') {
    $separate = true;
}

$renderer->setStylesheetPolicy(($separate === true) ? XhtmlRenderingEngine::STYLESHEET_SEPARATE : XhtmlRenderingEngine::STYLESHEET_INLINE);

$rendering = $renderer->render($doc->getDocumentComponent());
$rendering->formatOutput = true;

echo $rendering->saveXML();

if ($separate === true) {
    echo "\n\nSeparate Stylesheets:\n";
    echo "----------------------\n\n";

    $stylesheets = $renderer->getStylesheets();
    echo $stylesheets->ownerDocument->saveXML($stylesheets) . "\n";
}
