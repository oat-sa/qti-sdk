<?php
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

function loadTestDefinition() {
    $phpDoc = new PhpDocument();
    $phpDoc->load(dirname(__FILE__) . '/../../test/samples/custom/php/linear_35_items.php');
    
    return $phpDoc->getDocumentComponent();
}

function createFactory(AssessmentTest $assessmentTest) {
    return new AssessmentTestSessionFactory($assessmentTest);
}

function createStorage(AssessmentTestSessionFactory $factory) {
    return new TemporaryQtiBinaryStorage($factory);
}

function spentTime($start, $end) {
    $startTime = explode(' ', $start);
    $endTime = explode(' ', $end);
    $time = ($endTime[0] + $endTime[1]) - ($startTime[0] + $startTime[1]);
    return $time;
}

// Beginning of the session + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->instantiate();
$sessionId = $session->getSessionId();
$session->beginTestSession();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Beginning of the session + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 1 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 2 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceC')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 3 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceD')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 4 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceE')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 5 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceF')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 6 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceG')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 7 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceH')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 8 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceI')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 9 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceJ')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 10 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceK')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 11 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceL')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 12 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceM')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 13 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceN')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 14 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceO')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 15 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceP')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 16 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceQ')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 17 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceR')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 18 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceS')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 19 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceT')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 20 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceU')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 21 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceV')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 22 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceW')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 23 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceX')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 24 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceY')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 25 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceZ')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 26 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 27 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 28 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceC')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 29 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceD')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 30 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceE')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 31 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceF')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 32 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceG')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 33 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceH')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 34 + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attempt + persistance.
$start = microtime();

$storage = createStorage(createFactory(loadTestDefinition()));
$session = $storage->retrieve($sessionId);
$session->beginAttempt();
$session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceI')))));
$session->moveNext();
$storage->persist($session);
unset($session);
unset($storage);

$end = microtime();
echo "Retrieving session + attempt 35 + persistance (" . spentTime($start, $end) . ")\n";