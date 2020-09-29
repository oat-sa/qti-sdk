<?php

require_once(__DIR__ . '/../../../../qtism/qtism.php');

use qtism\data\storage\php\PhpDocument;
use qtism\data\storage\xml\XmlCompactDocument;

$xmlFile = __DIR__ . '/../runtime/nonlinear_40_items.xml';
$xmlDoc = new XmlCompactDocument();
$xmlDoc->load($xmlFile);

$phpDoc = new PhpDocument();
$phpDoc->setDocumentComponent($xmlDoc->getDocumentComponent());
$phpDoc->save('nonlinear_40_items.php');
