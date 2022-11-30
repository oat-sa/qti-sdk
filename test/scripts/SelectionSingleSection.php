<?php

declare(strict_types=1);

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\runtime\tests\SessionManager;

require_once(__DIR__ . '/../../vendor/autoload.php');

$iterations = (int)$argv[1];

$doc = new XmlCompactDocument();
$doc->load(__DIR__ . '/../samples/custom/runtime/selection_single_section.xml');

$sectionsDistribution = [];

for ($i = 0; $i < $iterations; $i++) {
    $manager = new SessionManager(new FileSystemFileManager());

    echo "Taking test ${i}...\n";
    $sections = [];

    $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());
    $session->beginTestSession();

    while ($session->getState() === AssessmentTestSessionState::INTERACTING) {
        $sections[] = $session->getRoute()->current()->getAssessmentSection()->getIdentifier();
        $session->moveNext();
    }

    $sections = array_unique($sections);
    foreach ($sections as $section) {
        if (isset($sectionsDistribution[$section]) === false) {
            $sectionsDistribution[$section] = 0;
        }

        $sectionsDistribution[$section]++;
    }

    unset($manager);
    unset($session);
}

ksort($sectionsDistribution);

echo "\nDistribution:\n";

foreach ($sectionsDistribution as $section => $dist) {
    echo "${section}: ${dist}\n";
}
