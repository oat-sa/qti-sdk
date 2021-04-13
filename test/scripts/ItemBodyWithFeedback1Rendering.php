<?php

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\State;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

require_once(__DIR__ . '/../../vendor/autoload.php');

$doc = new XmlDocument();
$doc->load(__DIR__ . '/../samples/rendering/itembodywithfeedback_1.xml');

$outcome1 = new OutcomeVariable('outcome1', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier(''));
$outcome2 = new OutcomeVariable('outcome2', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier(''));

$renderer = new XhtmlRenderingEngine();

if (isset($argv[1]) && $argv[1] === 'CONTEXT_AWARE') {
    $renderer->setFeedbackShowHidePolicy(AbstractMarkupRenderingEngine::CONTEXT_AWARE);

    if (isset($argv[2])) {
        $outcome1->setValue(new QtiIdentifier($argv[2]));
    }

    if (isset($argv[3])) {
        $outcome2->setValue(new QtiIdentifier($argv[3]));
    }
}

$renderer->setState(new State([$outcome1, $outcome2]));
$rendering = $renderer->render($doc->getDocumentComponent());
$rendering->formatOutput = true;

echo $rendering->saveXML();
