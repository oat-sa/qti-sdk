<?php

declare(strict_types=1);

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

require_once(__DIR__ . '/../../vendor/autoload.php');

$doc = new XmlDocument();
$doc->load(__DIR__ . '/../samples/rendering/choiceinteraction_1.xml');

$renderer = new XhtmlRenderingEngine();
$renderer->setChoiceShowHidePolicy(AbstractMarkupRenderingEngine::TEMPLATE_ORIENTED);
$renderer->setShufflingPolicy(AbstractMarkupRenderingEngine::TEMPLATE_ORIENTED);

$rendering = $renderer->render($doc->getDocumentComponent());
$rendering->formatOutput = true;

echo $rendering->saveXML();
