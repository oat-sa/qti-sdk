<?php
use qtism\runtime\storage\common\AbstractStorage;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlDocument;
use qtism\common\datatypes\Identifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\data\AssessmentTest;
use qtism\runtime\tests\AssessmentTestSessionFactory;
use qtism\runtime\storage\binary\TemporaryQtiBinaryStorage;
use qtism\data\storage\php\PhpDocument;

require_once(dirname(__FILE__) . '/../../qtism/qtism.php');

date_default_timezone_set('UTC');

function loadTestDefinition(array &$average = null) {
    $start = microtime();
    
    $phpDoc = new PhpDocument();
    $phpDoc->load(dirname(__FILE__) . '/../../test/samples/custom/php/linear_4_items.php');
    
    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }
    
    return $phpDoc->getDocumentComponent();
}

function createFactory(AssessmentTest $assessmentTest) {
    return new AssessmentTestSessionFactory($assessmentTest);
}

function createStorage(AssessmentTestSessionFactory $factory) {
    return new TemporaryQtiBinaryStorage($factory);
}

function spentTime($start, $end, array &$registration = null) {
    $startTime = explode(' ', $start);
    $endTime = explode(' ', $end);
    $time = ($endTime[0] + $endTime[1]) - ($startTime[0] + $startTime[1]);
    
    if (!is_null($registration)) {
        $registration[] = $time;
    }
    
    return $time;
}

function attempt(AssessmentTestSession $session, $identifier, array &$average = null) {
    $start = microtime();
    
    $session->beginAttempt();
    $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier($identifier)))));
    
    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }
}

function retrieve(AbstractStorage $storage, $sessionId, array &$average = null) {
    $start = microtime();
    
    $session = $storage->retrieve($sessionId);
    
    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }
    
    return $session;
}

function persist(AbstractStorage $storage, AssessmentTestSession $session, array &$average = null) {
    $start = microtime();
    
    $storage->persist($session);
    
    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }
}

function moveNext(AssessmentTestSession $session, array &$average) {
    $start = microtime();
    
    $session->moveNext();
    
    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }
}

function neighbourhood(AssessmentTestSession $session, array &$average = null) {
    $start = microtime();
    $neighbourhood = $session->getPossibleJumps();

    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }
}

$averageAttempt = array();
$effectiveAverageAttempt = array();
$averageRetrieve = array();
$averagePersist = array();
$averageNext = array();
$averageLoad = array();
$averageNeighbourhood = array();

// Beginning of the session + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition($averageLoad)));
$session = $storage->instantiate();
$sessionId = $session->getSessionId();
$session->beginTestSession();
$storage->persist($session);
$end = microtime();
unset($session);
unset($storage);
echo "Beginning of the session + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attemp + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition($averageLoad)));
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceA', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);

echo "Retrieving session + attempt 1 + persistance (" . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attemp + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition($averageLoad)));
$session = retrieve($storage, $sessionId, $averageRetrieve);
attempt($session, 'ChoiceB', $effectiveAverageAttempt);
neighbourhood($session, $averageNeighbourhood);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);

echo "Retrieving session + attempt 2 + persistance (" . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attemp + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition($averageLoad)));
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceC', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
echo "Retrieving session + attempt 3 + persistance (" . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attemp + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition($averageLoad)));
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceD', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
echo "Retrieving session + attempt 4 + persistance (" . spentTime($start, $end, $averageAttempt) . ")\n\n";

echo "Average attempt time = " . (array_sum($averageAttempt) / count($averageAttempt)) . "\n";
echo "Effective average attempt time = " . (array_sum($effectiveAverageAttempt) / count($effectiveAverageAttempt)) . "\n";
echo "Retrieve average time = " . (array_sum($averageRetrieve) / count($averageRetrieve)) . "\n";
echo "Persist average time = " . (array_sum($averagePersist) / count($averagePersist)) . "\n";
echo "MoveNext average time = " . (array_sum($averageNext) / count($averageNext)) . "\n";
echo "Load average time = " . (array_sum($averageLoad) / count($averageLoad)) . "\n";
echo "Neighbourhood average time = " . (array_sum($averageNeighbourhood) / count($averageNeighbourhood)) . "\n";