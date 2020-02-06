<?php

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\qtipl\ConditionRenderingOptions;
use qtism\runtime\rendering\qtipl\QtiPLRenderer;
use qtismtest\QtiSmTestCase;

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

$renderer = new QtiPLRenderer(ConditionRenderingOptions::getDefault());
$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/tests/branchingexpressions.xml');
$dom = $doc->getDomDocument();
$branchrules = $dom->getElementsByTagName("branchRule");
$test = $doc->getDocumentComponent();
$i = 1;

foreach ($branchrules as $branchrule) {
    $newdoc = new DOMDocument();
    $cloned = $branchrule->cloneNode(true);
    $newdoc->appendChild($newdoc->importNode($cloned, true));
    echo $newdoc->saveHTML();
    echo "------------\n";
    echo $renderer->render($test->getComponentByIdentifier('Q' . $i)->getBranchRules()[0]->getExpression())
        . "\n\n";
    $i++;
}

$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/tests/coverageforQtiPL.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$branchrules = $dom->getElementsByTagName("branchRule");

$i = 1;

foreach ($branchrules as $branchrule) {
    $newdoc = new DOMDocument();
    $cloned = $branchrule->cloneNode(true);
    $newdoc->appendChild($newdoc->importNode($cloned, true));
    echo $newdoc->saveHTML();
    echo "------------\n";
    echo $renderer->render($test->getComponentByIdentifier('Q' . $i)->getBranchRules()[0]->getExpression())
        . "\n\n";
    $i++;
}

$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/tests/rulesforQtiPL.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$precondition = $dom->getElementsByTagName("preCondition");
$newdoc = new DOMDocument();
$cloned = $precondition[0]->cloneNode(true);
$newdoc->appendChild($newdoc->importNode($cloned, true));
echo $newdoc->saveHTML();
echo "------------\n";
echo $renderer->render($test->getComponentByIdentifier("Q01")->getPreConditions()[0]) . "\n\n";

$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/tests/rulesforQtiPL.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$branchRule = $dom->getElementsByTagName("branchRule");
$newdoc = new DOMDocument();
$cloned = $branchRule[0]->cloneNode(true);
$newdoc->appendChild($newdoc->importNode($cloned, true));
echo $newdoc->saveHTML();
echo "------------\n";
echo $renderer->render($test->getComponentByIdentifier("Q2")->getBranchRules()[0]) . "\n\n";

$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/items/xinclude/xinclude_ns_in_tag.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$xinclude = $dom->getElementsByTagName("include");
$newdoc = new DOMDocument();
$cloned = $xinclude[0]->cloneNode(true);
$newdoc->appendChild($newdoc->importNode($cloned, true));
echo $newdoc->saveHTML();
echo "------------\n";
echo $renderer->render($test->getComponentsByClassName("include")[0]) . "\n\n";

$renderer = new QtiPLRenderer(ConditionRenderingOptions::getDefault());
$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/items/set_outcome_values_with_sum.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$responseCondition = $dom->getElementsByTagName("responseCondition");
$newdoc = new DOMDocument();
$cloned = $responseCondition[0]->cloneNode(true);
$newdoc->appendChild($newdoc->importNode($cloned, true));
echo $newdoc->saveHTML();
echo "------------\n";
echo $renderer->render($test->getComponentsByClassName("responseCondition")[0]) . "\n\n";

$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/items/responseruleforQtiPL1.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$responseCondition = $dom->getElementsByTagName("responseCondition");
$newdoc = new DOMDocument();
$cloned = $responseCondition[0]->cloneNode(true);
$newdoc->appendChild($newdoc->importNode($cloned, true));
echo $newdoc->saveHTML();
echo "------------\n";
echo $renderer->render($test->getComponentsByClassName("responseCondition")[0]) . "\n\n";

$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/items/responseruleforQtiPL2.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$responseCondition = $dom->getElementsByTagName("responseCondition");
$newdoc = new DOMDocument();
$cloned = $responseCondition[0]->cloneNode(true);
$newdoc->appendChild($newdoc->importNode($cloned, true));
echo $newdoc->saveHTML();
echo "------------\n";
echo $renderer->render($test->getComponentsByClassName("responseCondition")[0]) . "\n\n";

$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/items/responseruleforQtiPL3.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$responseCondition = $dom->getElementsByTagName("responseCondition");
$newdoc = new DOMDocument();
$cloned = $responseCondition[0]->cloneNode(true);
$newdoc->appendChild($newdoc->importNode($cloned, true));
echo $newdoc->saveHTML();
echo "------------\n";
echo $renderer->render($test->getComponentsByClassName("responseCondition")[0]) . "\n\n";

$renderer = new QtiPLRenderer(ConditionRenderingOptions::getDefault());
$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/items/templateruleforQtiPL1.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$templateCondition = $dom->getElementsByTagName("templateCondition");
$newdoc = new DOMDocument();
$cloned = $templateCondition[0]->cloneNode(true);
$newdoc->appendChild($newdoc->importNode($cloned, true));
echo $newdoc->saveHTML();
echo "------------\n";
echo $renderer->render($test->getComponentsByClassName("templateCondition")[0]) . "\n\n";

$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/items/templateruleforQtiPL2.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$templateCondition = $dom->getElementsByTagName("templateCondition");
$newdoc = new DOMDocument();
$cloned = $templateCondition[0]->cloneNode(true);
$newdoc->appendChild($newdoc->importNode($cloned, true));
echo $newdoc->saveHTML();
echo "------------\n";
echo $renderer->render($test->getComponentsByClassName("templateCondition")[0]) . "\n\n";

$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/tests/rulesforQtiPL.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$outcomeCondition = $dom->getElementsByTagName("outcomeCondition");
$newdoc = new DOMDocument();
$cloned = $outcomeCondition[0]->cloneNode(true);
$newdoc->appendChild($newdoc->importNode($cloned, true));
echo $newdoc->saveHTML();
echo "------------\n";
echo $renderer->render($test->getComponentsByClassName("outcomeCondition")[0]) . "\n\n";

$doc = new XmlDocument();
$doc->load(QtiSmTestCase::samplesDir() . 'custom/tests/branchingexpressions.xml');
$test = $doc->getDocumentComponent();
$dom = $doc->getDomDocument();
$outcomeCondition = $dom->getElementsByTagName("outcomeCondition");
$newdoc = new DOMDocument();
$cloned = $outcomeCondition[0]->cloneNode(true);
$newdoc->appendChild($newdoc->importNode($cloned, true));
echo $newdoc->saveHTML();
echo "------------\n";
echo $renderer->render($test->getComponentsByClassName("outcomeCondition")[0]) . "\n\n";
