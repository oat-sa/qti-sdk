<?php

require_once(__DIR__ . '/../../../../vendor/autoload.php');

use qtism\data\storage\php\PhpDocument;
use qtism\data\storage\xml\XmlCompactDocument;

$test = $argv[1];

$xmlFile = __DIR__ . "/../runtime/${test}.xml";
$xmlDoc = new XmlCompactDocument();
$xmlDoc->load($xmlFile);

$phpDoc = new PhpDocument();
$phpDoc->setDocumentComponent($xmlDoc->getDocumentComponent());
$phpDoc->save("${test}.php");
