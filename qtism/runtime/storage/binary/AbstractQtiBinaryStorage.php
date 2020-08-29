<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\storage\binary;

use Exception;
use InvalidArgumentException;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\storage\IStream;
use qtism\common\storage\MemoryStream;
use qtism\data\AssessmentTest;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\storage\common\AbstractStorage;
use qtism\runtime\storage\common\AssessmentTestSeeker;
use qtism\runtime\storage\common\StorageException;
use qtism\runtime\tests\AbstractSessionManager;
use qtism\runtime\tests\AssessmentItemSessionStore;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\DurationStore;
use qtism\runtime\tests\PendingResponseStore;
use qtism\runtime\tests\Route;
use RuntimeException;
use SplObjectStorage;

/**
 * An abstract Binary AssessmentTestSession Storage Service implementation.
 */
abstract class AbstractQtiBinaryStorage extends AbstractStorage
{
    /** @var QtiBinaryVersion */
    private $version;

    /**
     * The AssessmentTestSeeker object used by this implementation.
     *
     * @var AssessmentTestSeeker
     */
    private $seeker;

    /**
     * Create a new AbstractQtiBinaryStorage.
     *
     * @param AbstractSessionManager $manager
     * @param BinaryAssessmentTestSeeker $seeker
     * @param QtiBinaryVersion|null $version
     *
     * @throws InvalidArgumentException If $assessmentTest does not implement the Document interface.
     */
    public function __construct(
        AbstractSessionManager $manager,
        BinaryAssessmentTestSeeker $seeker,
        QtiBinaryVersion $version = null
    ) {
        parent::__construct($manager);
        $this->setSeeker($seeker);

        if ($version === null) {
            $version = new QtiBinaryVersion();
        }
        $this->version = $version;
    }

    /**
     * Set the BinaryAssessmentTestSeeker object used by this implementation.
     *
     * @param BinaryAssessmentTestSeeker $seeker An AssessmentTestSeeker object.
     */
    protected function setSeeker(BinaryAssessmentTestSeeker $seeker)
    {
        $this->seeker = $seeker;
    }

    /**
     * Get the BinaryAssessmentTestSeeker object used by this implementation.
     *
     * @return BinaryAssessmentTestSeeker An AssessmentTestSeeker object.
     */
    protected function getSeeker()
    {
        return $this->seeker;
    }

    /**
     * Instantiate a new AssessmentTestSession.
     *
     * @param AssessmentTest $test
     * @param string $sessionId An session ID. If not provided, a new session ID will be generated and given to the AssessmentTestSession.
     * @return AssessmentTestSession An AssessmentTestSession object.
     * @throws StorageException
     */
    public function instantiate(AssessmentTest $test, $sessionId = '')
    {
        // If not provided, generate a session ID.
        if (empty($sessionId)) {
            $sessionId = uniqid('qtism', true);
        }

        try {
            $session = $this->getManager()->createAssessmentTestSession($test);
            $session->setSessionId($sessionId);

            return $session;
        } catch (Exception $e) {
            $msg = 'An error occurred while instantiating the given AssessmentTest.';
            throw new StorageException($msg, StorageException::INSTANTIATION, $e);
        }
    }

    /**
     * Persist an AssessmentTestSession into persistent binary data.
     *
     * @param AssessmentTestSession $assessmentTestSession
     * @throws StorageException
     */
    public function persist(AssessmentTestSession $assessmentTestSession)
    {
        try {
            $stream = new MemoryStream();
            $stream->open();
            $access = $this->createBinaryStreamAccess($stream);

            // Write the QTI Binary Storage version in use to persist the test session.
            $this->version->persist($access);

            // Deal with intrinsic values of the Test Session.
            $access->writeTinyInt($assessmentTestSession->getState());

            // Write the current position in the route.
            $route = $assessmentTestSession->getRoute();
            $access->writeInteger($route->getPosition());

            // Persist the Route of the AssessmentTestSession and the related item sessions.
            $access->writeInteger($route->count());

            // persist whether or not to force branching.
            $access->writeBoolean($assessmentTestSession->mustForceBranching());

            // persist whether or not to force preconditions.
            $access->writeBoolean($assessmentTestSession->mustForcePreconditions());

            // persist whether or not to use path tracking.
            $access->writeBoolean($assessmentTestSession->mustTrackPath());

            // persist whether or not to always allow jumps.
            $access->writeBoolean($assessmentTestSession->mustAlwaysAllowJumps());

            // persist path.
            $access->writePath($assessmentTestSession->getPath());

            $itemSessionStore = $assessmentTestSession->getAssessmentItemSessionStore();
            $pendingResponseStore = $assessmentTestSession->getPendingResponseStore();

            $seeker = $this->getSeeker();

            foreach ($route as $routeItem) {
                $item = $routeItem->getAssessmentItemRef();
                $occurence = $routeItem->getOccurence();

                // Deal with RouteItem
                $access->writeRouteItem($seeker, $routeItem);

                // Deal with ItemSession related to the previously written RouteItem.
                    $itemSession = $itemSessionStore->getAssessmentItemSession($item, $occurence);
                    $access->writeAssessmentItemSession($seeker, $itemSession);

                    // Deal with last occurence update.
                    $access->writeBoolean($assessmentTestSession->isLastOccurenceUpdate($item, $occurence));

                    // Deal with PendingResponses
                    if (($pendingResponses = $pendingResponseStore->getPendingResponses($item, $occurence)) !== false) {
                        $access->writeBoolean(true);
                        $access->writePendingResponses($seeker, $pendingResponses);
                    } else {
                        $access->writeBoolean(false);
                    }
            }

            // Deal with test session configuration.
            // -- AutoForward (not in use anymore, fake it).
            $access->writeBoolean(false);

            // Persist the test-level global scope.
            foreach ($assessmentTestSession->getKeys() as $outcomeIdentifier) {
                $outcomeVariable = $assessmentTestSession->getVariable($outcomeIdentifier);
                $access->writeVariableValue($outcomeVariable);
            }

            $durationStore = $assessmentTestSession->getDurationStore();
            $access->writeShort(count($durationStore));
            foreach ($durationStore->getKeys() as $k) {
                $access->writeString($k);
                $access->writeVariableValue($durationStore->getVariable($k));
            }

            $this->persistStream($assessmentTestSession, $stream);

            $stream->close();
        } catch (Exception $e) {
            $sessionId = $assessmentTestSession->getSessionId();
            $msg = "An error occurred while persisting AssessmentTestSession with ID '${sessionId}': " . $e->getMessage();
            throw new StorageException($msg, StorageException::PERSISTENCE, $e);
        }
    }

    /**
     * Retrieve an AssessmentTestSession object from storage by $sessionId.
     *
     * @param AssessmentTest $test
     * @param string $sessionId
     * @return AssessmentTestSession An AssessmentTestSession object.
     * @throws StorageException If the AssessmentTestSession could not be retrieved from persistent binary storage.
     */
    public function retrieve(AssessmentTest $test, $sessionId)
    {
        try {
            $stream = $this->getRetrievalStream($sessionId);
            $stream->open();
            $access = $this->createBinaryStreamAccess($stream);

            // Read the QTI Binary Storage version.
            $this->version->retrieve($access);

            // Deal with intrinsic values of the Test Session.
            $assessmentTestSessionState = $access->readTinyInt();
            $currentPosition = $this->version->storesPositionAndRouteCountAsInteger()
                ? $access->readInteger()
                : $access->readTinyInt();

            // Build the route and the item sessions.
            $route = new Route();
            $lastOccurenceUpdate = new SplObjectStorage();
            $itemSessionStore = new AssessmentItemSessionStore();
            $pendingResponseStore = new PendingResponseStore();
            $routeCount = $this->version->storesPositionAndRouteCountAsInteger()
                ? $access->readInteger()
                : $access->readTinyInt();

            // Reads configuration.
            $forceBranching = $this->version->storesForceBranchingAndPreconditions()
                ? $access->readBoolean()
                : false;
            $forcePreconditions = $this->version->storesForceBranchingAndPreconditions()
                ? $access->readBoolean()
                : false;
            $mustTrackPath = $this->version->storesTrackPath()
                ? $access->readBoolean()
                : false;
            $mustAlwaysAllowJumps = $this->version->storesAlwaysAllowJumps()
                ? $access->readBoolean()
                : false;
            $path = $this->version->storesTrackPath()
                ? $access->readPath()
                : [];

            // Create the item session factory that will be used to instantiate
            // new item sessions.

            $seeker = $this->getSeeker();

            for ($i = 0; $i < $routeCount; $i++) {
                $routeItem = $access->readRouteItem($seeker, $this->version);
                    $itemSession = $access->readAssessmentItemSession($this->getManager(), $seeker, $this->version);

                    // last-update
                    if ($access->readBoolean() === true) {
                        $lastOccurenceUpdate[$routeItem->getAssessmentItemRef()] = $routeItem->getOccurence();
                    }

                    // pending-responses
                    if ($access->readBoolean() === true) {
                        $pendingResponseStore->addPendingResponses($access->readPendingResponses($seeker));
                    }

                $route->addRouteItemObject($routeItem);
                    $itemSessionStore->addAssessmentItemSession($itemSession, $routeItem->getOccurence());
            }

            $route->setPosition($currentPosition);
            $manager = $this->getManager();
            $assessmentTestSession = $manager->createAssessmentTestSession($test, $route);
            $assessmentTestSession->setAssessmentItemSessionStore($itemSessionStore);
            $assessmentTestSession->setSessionId($sessionId);
            $assessmentTestSession->setState($assessmentTestSessionState);
            $assessmentTestSession->setLastOccurenceUpdate($lastOccurenceUpdate);
            $assessmentTestSession->setPendingResponseStore($pendingResponseStore);
            $assessmentTestSession->setForceBranching($forceBranching);
            $assessmentTestSession->setForcePreconditions($forcePreconditions);
            $assessmentTestSession->setPathTracking($mustTrackPath);
            $assessmentTestSession->setAlwaysAllowJumps($mustAlwaysAllowJumps);
            $assessmentTestSession->setPath($path);

            // Deal with test session configuration.
            // -- AutoForward (not in use anymore, consume it anyway).
            $access->readBoolean();

            // Build the test-level global scope, composed of Outcome Variables.
            foreach ($test->getOutcomeDeclarations() as $outcomeDeclaration) {
                $outcomeVariable = OutcomeVariable::createFromDataModel($outcomeDeclaration);
                $access->readVariableValue($outcomeVariable);
                $assessmentTestSession->setVariable($outcomeVariable);
            }

            // Build the duration store.
            $durationStore = new DurationStore();

            if ($this->version->storesDurations()) {
                $durationCount = $access->readShort();
                for ($i = 0; $i < $durationCount; $i++) {
                    $varName = $access->readString();
                    $durationVariable = new OutcomeVariable($varName, Cardinality::SINGLE, BaseType::DURATION);
                    $access->readVariableValue($durationVariable);
                    $durationStore->setVariable($durationVariable);
                }

                $assessmentTestSession->setDurationStore($durationStore);
            }

            $stream->close();

            return $assessmentTestSession;
        } catch (Exception $e) {
            $msg = 'An error occurred while retrieving AssessmentTestSession. ' . $e->getMessage();
            throw new StorageException($msg, StorageException::RETRIEVAL, $e);
        }
    }

    /**
     * Get the MemoryStream that has to be used to retrieve an AssessmentTestSession.
     *
     * Be careful, the implementation of this method must not open the given $stream.
     *
     * @param string $sessionId A test session identifier.
     * @return MemoryStream A MemoryStream object.
     * @throws RuntimeException If an error occurs.
     */
    abstract protected function getRetrievalStream($sessionId);

    /**
     * Persist A MemoryStream that contains the binary data representing $assessmentTestSession in an appropriate location.
     *
     * Be careful, the implementation of this method must not close the given $stream.
     *
     * @param AssessmentTestSession $assessmentTestSession An AssessmentTestSession object.
     * @param MemoryStream $stream An open MemoryStream object.
     */
    abstract protected function persistStream(AssessmentTestSession $assessmentTestSession, MemoryStream $stream);

    /**
     * Create a suitable BinaryStreamAccess object.
     *
     * @param IStream $stream
     * @return QtiBinaryStreamAccess
     */
    abstract protected function createBinaryStreamAccess(IStream $stream);
}
