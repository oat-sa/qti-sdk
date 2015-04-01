<?php
require_once(dirname(__FILE__) . '/../../../../vendor/autoload.php');
use qtism\data\storage\php\PhpDocument;
use qtism\data\storage\xml\XmlCompactDocument;

$test = $argv[1];

$xmlFile = dirname(__FILE__) . "/../runtime/${test}.xml";
$xmlDoc = new XmlCompactDocument();
$xmlDoc->load($xmlFile);

$phpDoc = new PhpDocument();
$phpDoc->setDocumentComponent($xmlDoc->getDocumentComponent());
$phpDoc->save("${test}.php");