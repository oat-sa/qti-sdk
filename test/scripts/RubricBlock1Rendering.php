<?php

declare(strict_types=1);

use qtism\data\storage\xml\XmlDocument;
use qtism\data\View;
use qtism\data\ViewCollection;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

require_once(__DIR__ . '/../../vendor/autoload.php');

$doc = new XmlDocument();
$doc->load(__DIR__ . '/../samples/rendering/rubricblock_1.xml');

$renderer = new XhtmlRenderingEngine();

if (isset($argv[1])) {
    if (strpos($argv[1], ',') !== false) {
        $strviews = explode(',', $argv[1]);

        $view = new ViewCollection();
        foreach ($strviews as $v) {
            $view[] = View::getConstantByName(trim($v));
        }
    } else {
        $view = new ViewCollection([View::getConstantByName(trim($argv[1]))]);
    }

    $renderer->setViewPolicy(AbstractMarkupRenderingEngine::CONTEXT_AWARE);
    $renderer->setViews($view);
}

$rendering = $renderer->render($doc->getDocumentComponent());
$rendering->formatOutput = true;

echo $rendering->saveXML();
