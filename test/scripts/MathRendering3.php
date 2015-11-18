<?php

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;
use qtism\runtime\common\State;
use qtism\runtime\common\TemplateVariable;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\QtiString;

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

$doc = new XmlDocument();
$doc->load(dirname(__FILE__) . '/../samples/rendering/math_3.xml');

$tpl_E = new TemplateVariable('TPL_E', Cardinality::SINGLE, BaseType::STRING, new QtiString('E'));
$tpl_m = new TemplateVariable('TPL_m', Cardinality::SINGLE, BaseType::STRING, new QtiString('m'));
$tpl_c = new TemplateVariable('TPL_c', Cardinality::SINGLE, BaseType::STRING, new QtiString('c'));

$renderer = new XhtmlRenderingEngine();
$renderer->setState(
    new State(
        array(
            $tpl_E,
            $tpl_m,
            $tpl_c
        )
    )
);

$renderer->setPrintedVariablePolicy(XhtmlRenderingEngine::TEMPLATE_ORIENTED);
$rendering = $renderer->render($doc->getDocumentComponent());
$rendering->formatOutput = true;

echo $rendering->saveXML();
