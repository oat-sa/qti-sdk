<?php

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\AssessmentTest;
use qtism\data\QtiComponent;
use qtism\data\storage\php\PhpDocument;
use qtism\data\storage\php\PhpStorageException;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\storage\binary\LocalQtiBinaryStorage;
use qtism\runtime\storage\common\AbstractStorage;
use qtism\runtime\storage\common\StorageException;
use qtism\runtime\tests\AssessmentItemSessionException;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionException;
use qtism\runtime\tests\SessionManager;

date_default_timezone_set('UTC');

require_once(__DIR__ . '/../../vendor/autoload.php');

/**
 * @param array|null $average
 * @return QtiComponent
 * @throws PhpStorageException
 */
function loadTestDefinition(array &$average = null)
{
    $start = microtime();

    $phpDoc = new PhpDocument();
    $phpDoc->load(__DIR__ . '/../../test/samples/custom/php/linear_40_items.php');

    if ($average !== null) {
        spentTime($start, microtime(), $average);
    }

    return $phpDoc->getDocumentComponent();
}

/**
 * @return SessionManager
 */
function createFactory()
{
    return new SessionManager(new FileSystemFileManager());
}

/**
 * @param SessionManager $factory
 * @param AssessmentTest $test
 * @return LocalQtiBinaryStorage
 */
function createStorage(SessionManager $factory, AssessmentTest $test)
{
    return new LocalQtiBinaryStorage($factory, $test);
}

/**
 * @param $start
 * @param $end
 * @param array|null $registration
 * @return mixed
 */
function spentTime($start, $end, array &$registration = null)
{
    $startTime = explode(' ', $start);
    $endTime = explode(' ', $end);
    $time = ($endTime[0] + $endTime[1]) - ($startTime[0] + $startTime[1]);

    if ($registration !== null) {
        $registration[] = $time;
    }

    return $time;
}

/**
 * @param AssessmentTestSession $session
 * @param $identifier
 * @param array|null $average
 * @throws AssessmentTestSessionException
 */
function attempt(AssessmentTestSession $session, $identifier, array &$average = null)
{
    $start = microtime();

    $session->beginAttempt();
    $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier($identifier))]));

    if ($average !== null) {
        spentTime($start, microtime(), $average);
    }
}

/**
 * @param AbstractStorage $storage
 * @param $sessionId
 * @param array|null $average
 * @return mixed
 * @throws StorageException
 */
function retrieve(AbstractStorage $storage, $sessionId, array &$average = null)
{
    $start = microtime();

    $session = $storage->retrieve($sessionId);

    if ($average !== null) {
        spentTime($start, microtime(), $average);
    }

    return $session;
}

/**
 * @param AbstractStorage $storage
 * @param AssessmentTestSession $session
 * @param null $average
 * @throws StorageException
 */
function persist(AbstractStorage $storage, AssessmentTestSession $session, &$average = null)
{
    $start = microtime();

    $storage->persist($session);

    if ($average !== null) {
        spentTime($start, microtime(), $average);
    }
}

/**
 * @param AssessmentTestSession $session
 * @param array $average
 * @throws AssessmentItemSessionException
 * @throws AssessmentTestSessionException
 * @throws PhpStorageException
 */
function moveNext(AssessmentTestSession $session, array &$average)
{
    $start = microtime();

    $session->moveNext();

    if ($average !== null) {
        spentTime($start, microtime(), $average);
    }
}

/**
 * @param AssessmentTestSession $session
 * @param array|null $average
 */
function neighbourhood(AssessmentTestSession $session, array &$average = null)
{
    $start = microtime();
    $neighbourhood = $session->getPossibleJumps();

    if ($average !== null) {
        spentTime($start, microtime(), $average);
    }
}

$averageAttempt = [];
$effectiveAverageAttempt = [];
$averageRetrieve = [];
$averagePersist = [];
$averageNext = [];
$averageLoad = [];
$averageNeighbourhood = [];

// Beginning of the session + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = $storage->instantiate();
$sessionId = $session->getSessionId();
$session->beginTestSession();
$storage->persist($session);
unset($session);
unset($storage);
unset($test);

$end = microtime();
echo 'Beginning of the session + persistance (' . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceA', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 1 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceB', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 2 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceC', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 3 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceD', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 4 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceE', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 5 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceF', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 6 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceG', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 7 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceH', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 8 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceI', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 9 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceJ', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 10 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceK', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 11 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceL', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 12 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceM', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 13 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceN', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 14 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceO', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 15 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceP', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 16 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceQ', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 17 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceR', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 18 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceS', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 19 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceT', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 20 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceU', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 21 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceV', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 22 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceW', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 23 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceX', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 24 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceY', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 25 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceZ', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 26 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceA', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 27 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceB', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 28 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceC', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 29 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceD', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 30 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceE', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 31 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceF', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 32 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceG', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 33 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceH', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 34 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceI', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 35 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceJ', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 36 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceK', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 37 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceL', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 38 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceM', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 39 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceN', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();

$numberCorrect = $session->numberCorrect();

unset($session);
unset($storage);
unset($test);

echo 'Retrieving session + attempt 40 + persistance (' . spentTime($start, $end, $averageAttempt) . ")\n";

echo 'Average attempt time = ' . (array_sum($averageAttempt) / count($averageAttempt)) . "\n";
echo 'Effective average attempt time = ' . (array_sum($effectiveAverageAttempt) / count($effectiveAverageAttempt)) . "\n";
echo 'Retrieve average time = ' . (array_sum($averageRetrieve) / count($averageRetrieve)) . "\n";
echo 'Persist average time = ' . (array_sum($averagePersist) / count($averagePersist)) . "\n";
echo 'MoveNext average time = ' . (array_sum($averageNext) / count($averageNext)) . "\n";
echo 'Load average time = ' . (array_sum($averageLoad) / count($averageLoad)) . "\n";
echo 'Neighbourhood average time = ' . (array_sum($averageNeighbourhood) / count($averageNeighbourhood)) . "\n";
echo "Number correct = ${numberCorrect}\n";
