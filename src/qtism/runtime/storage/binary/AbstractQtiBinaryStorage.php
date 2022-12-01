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
use OutOfBoundsException;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\storage\BinaryStreamAccess;
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
     * @param AssessmentTest $test
     * @param QtiBinaryVersion|null $version
     */
    public function __construct(
        AbstractSessionManager $manager,
        AssessmentTest $test,
        QtiBinaryVersion $version = null
    ) {
        parent::__construct($manager, $test);
        $seeker = new BinaryAssessmentTestSeeker($test);
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
    protected function setSeeker(BinaryAssessmentTestSeeker $seeker): void
    {
        $this->seeker = $seeker;
    }

    /**
     * Get the BinaryAssessmentTestSeeker object used by this implementation.
     *
     * @return BinaryAssessmentTestSeeker An AssessmentTestSeeker object.
     */
    protected function getSeeker(): BinaryAssessmentTestSeeker
    {
        return $this->seeker;
    }

    /**
     * Instantiate a new AssessmentTestSession.
     *
     * @param int $config (optional) The configuration to be taken into account for the instantiated AssessmentTestSession object.
     * @param string $sessionId An session ID. If not provided, a new session ID will be generated and given to the AssessmentTestSession.
     * @return AssessmentTestSession An AssessmentTestSession object.
     * @throws StorageException
     */
    public function instantiate($config = 0, $sessionId = ''): AssessmentTestSession
    {
        // If not provided, generate a session ID.
        if (empty($sessionId)) {
            $sessionId = uniqid('qtism', true);
        }

        try {
            $session = $this->getManager()->createAssessmentTestSession($this->getAssessmentTest(), null, $config);
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
    public function persist(AssessmentTestSession $assessmentTestSession): void
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
            $access->writeTinyInt($route->getPosition());

            // persist time reference.
            $timeReference = $assessmentTestSession->getTimeReference();
            if ($timeReference === null) {
                $access->writeBoolean(false);
            } else {
                $access->writeBoolean(true);
                $access->writeDateTime($timeReference);
            }

            // persist visited testPart identifiers.
            $visitedTestPartIdentifiers = $assessmentTestSession->getVisitedTestPartIdentifiers();
            $access->writeTinyInt(count($visitedTestPartIdentifiers));
            foreach ($visitedTestPartIdentifiers as $visitedTestPartIdentifier) {
                $access->writeString($visitedTestPartIdentifier);
            }

            // persist path.
            $access->writePath($assessmentTestSession->getPath());

            // -- Persist configuration
            $access->writeShort($assessmentTestSession->getConfig());

            // -- Persist the Route of the AssessmentTestSession and the related item sessions.
            $access->writeTinyInt($route->count());
            $itemSessionStore = $assessmentTestSession->getAssessmentItemSessionStore();
            $pendingResponseStore = $assessmentTestSession->getPendingResponseStore();

            // Preserve route position.
            $oldRoutePosition = $route->getPosition();

            $seeker = $this->getSeeker();
            $routeItems = $route->getAllRouteItems();
            foreach ($routeItems as $routeItem) {
                $item = $routeItem->getAssessmentItemRef();
                $occurence = $routeItem->getOccurence();

                // Deal with RouteItem
                $access->writeRouteItem($seeker, $routeItem);

                // Deal with ItemSession related to the previously written RouteItem.
                try {
                    $itemSession = $itemSessionStore->getAssessmentItemSession($item, $occurence);
                    $access->writeBoolean(true);
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
                } catch (OutOfBoundsException $e) {
                    // No assessmentItemSession for this route item.
                    $access->writeBoolean(false);
                }
            }

            // Reset route position
            $route->setPosition($oldRoutePosition);

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
     * @param string $sessionId
     * @return AssessmentTestSession An AssessmentTestSession object.
     * @throws StorageException If the AssessmentTestSession could not be retrieved from persistent binary storage.
     */
    public function retrieve($sessionId): AssessmentTestSession
    {
        try {
            $stream = $this->getRetrievalStream($sessionId);
            $stream->open();
            $access = $this->createBinaryStreamAccess($stream);

            // Read the QTI Binary Storage version.
            $this->version->retrieve($access);

            // Deal with intrinsic values of the Test Session.
            $assessmentTestSessionState = $access->readTinyInt();
            $currentPosition = $access->readTinyInt();

            if ($access->readBoolean() === true) {
                $timeReference = $access->readDateTime();
            } else {
                $timeReference = null;
            }

            $visitedTestPartIdentifiers = [];
            $visitedTestPartIdentifiersCount = $access->readTinyInt();
            for ($i = 0; $i < $visitedTestPartIdentifiersCount; $i++) {
                $visitedTestPartIdentifiers[] = $access->readString();
            }

            $path = $access->readPath();

            // -- Session configuration.
            $config = $access->readShort();

            // Build the route and the item sessions.
            $route = new Route();
            $lastOccurenceUpdate = new SplObjectStorage();
            $itemSessionStore = new AssessmentItemSessionStore();
            $pendingResponseStore = new PendingResponseStore();
            $routeCount = $access->readTinyInt();

            // Create the item session factory that will be used to instantiate
            // new item sessions.

            $seeker = $this->getSeeker();

            for ($i = 0; $i < $routeCount; $i++) {
                $routeItem = $access->readRouteItem($seeker);
                $route->addRouteItemObject($routeItem);

                // An already instantiated session for this route item?
                if ($access->readBoolean() === true) {
                    $itemSession = $access->readAssessmentItemSession($this->getManager(), $seeker, $this->version);

                    // last-update
                    if ($access->readBoolean() === true) {
                        $lastOccurenceUpdate[$routeItem->getAssessmentItemRef()] = $routeItem->getOccurence();
                    }

                    // pending-responses
                    if ($access->readBoolean() === true) {
                        $pendingResponseStore->addPendingResponses($access->readPendingResponses($seeker));
                    }

                    $itemSessionStore->addAssessmentItemSession($itemSession, $routeItem->getOccurence());
                }
            }

            $route->setPosition($currentPosition);
            $manager = $this->getManager();
            $test = $this->getAssessmentTest();
            $assessmentTestSession = $manager->createAssessmentTestSession($test, $route, $config);
            $assessmentTestSession->setAssessmentItemSessionStore($itemSessionStore);
            $assessmentTestSession->setSessionId($sessionId);
            $assessmentTestSession->setState($assessmentTestSessionState);
            $assessmentTestSession->setLastOccurenceUpdate($lastOccurenceUpdate);
            $assessmentTestSession->setPendingResponseStore($pendingResponseStore);
            $assessmentTestSession->setTimeReference($timeReference);
            $assessmentTestSession->setVisitedTestPartIdentifiers($visitedTestPartIdentifiers);
            $assessmentTestSession->setPath($path);

            // Build the test-level global scope, composed of Outcome Variables.
            foreach ($test->getOutcomeDeclarations() as $outcomeDeclaration) {
                $outcomeVariable = OutcomeVariable::createFromDataModel($outcomeDeclaration);
                $access->readVariableValue($outcomeVariable);
                $assessmentTestSession->setVariable($outcomeVariable);
            }

            // Build the duration store.
            $durationStore = new DurationStore();
            $durationCount = $access->readShort();
            for ($i = 0; $i < $durationCount; $i++) {
                $varName = $access->readString();
                $durationVariable = new OutcomeVariable($varName, Cardinality::SINGLE, BaseType::DURATION);
                $access->readVariableValue($durationVariable);
                $durationStore->setVariable($durationVariable);
            }
            $assessmentTestSession->setDurationStore($durationStore);

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
    abstract protected function getRetrievalStream($sessionId): MemoryStream;

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
     * @return BinaryStreamAccess
     */
    abstract protected function createBinaryStreamAccess(IStream $stream): BinaryStreamAccess;
}
