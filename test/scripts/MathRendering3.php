<?php

use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\common\State;
use qtism\runtime\common\TemplateVariable;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

require_once(__DIR__ . '/../../vendor/autoload.php');

$doc = new XmlDocument();
$doc->load(__DIR__ . '/../samples/rendering/math_3.xml');

$tpl_E = new TemplateVariable('TPL_E', Cardinality::SINGLE, BaseType::STRING, new QtiString('E'));
$tpl_m = new TemplateVariable('TPL_m', Cardinality::SINGLE, BaseType::STRING, new QtiString('m'));
$tpl_c = new TemplateVariable('TPL_c', Cardinality::SINGLE, BaseType::STRING, new QtiString('c'));

$renderer = new XhtmlRenderingEngine();
$renderer->setState(
    new State(
        [
            $tpl_E,
            $tpl_m,
            $tpl_c,
        ]
    )
);

$renderer->setPrintedVariablePolicy(XhtmlRenderingEngine::TEMPLATE_ORIENTED);
$rendering = $renderer->render($doc->getDocumentComponent());
$rendering->formatOutput = true;

echo $rendering->saveXML();
