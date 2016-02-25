<?php
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\runtime\tests\SessionManager;
use qtism\runtime\tests\AssessmentTestSessionState;

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

$iterations = intval($argv[1]);

$doc = new XmlCompactDocument();
$doc->load(dirname(__FILE__) . '/../samples/custom/runtime/selection_single_section.xml');

$sectionsDistribution = array();

for ($i = 0; $i < $iterations; $i++) {
    $manager = new SessionManager();
    var_dump(gc_enabled());
    echo "Taking test ${i}...\n";
    echo memory_get_usage(), "\n";
    $sections = array();

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
    gc_collect_cycles();
}

ksort($sectionsDistribution);

echo "\nDistribution:\n";

foreach ($sectionsDistribution as $section => $dist) {
    echo "${section}: ${dist}\n";
}
