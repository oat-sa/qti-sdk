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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtism\runtime\storage\serializable;

use Exception;
use qtism\common\datatypes\files\FileManagerException;
use qtism\data\AssessmentTest;
use qtism\data\QtiIdentifiable;
use qtism\runtime\storage\common\AbstractStorage;
use qtism\runtime\storage\common\AssessmentTestSeeker;
use qtism\runtime\storage\common\StorageException;
use qtism\runtime\storage\driver\exception\DriverDeletionException;
use qtism\runtime\storage\driver\StorageDriverInterface;
use qtism\runtime\tests\AbstractSessionManager;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentItemSessionStore;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\Route;
use qtism\runtime\tests\RouteItem;

class QtiSerializableStorage extends AbstractStorage
{
    private SerializerInterface $serializer;
    private StorageDriverInterface $storageDriver;

    private AssessmentTestSeeker $seeker;

    public function __construct(
        AbstractSessionManager $manager,
        AssessmentTest $test,
        SerializerInterface $serializer,
        StorageDriverInterface $storageDriver
    ) {
        $this->seeker = new AssessmentTestSeeker($test, [
            'assessmentItemRef',
            'assessmentSection',
            'testPart',
            'outcomeDeclaration',
            'responseDeclaration',
            'templateDeclaration',
            'branchRule',
            'preCondition',
            'itemSessionControl',
        ]);
        $this->serializer = $serializer;
        $this->storageDriver = $storageDriver;
        parent::__construct($manager, $test);
    }


    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function persist(AssessmentTestSession $assessmentTestSession)
    {
        $serializedSession = $this->serializer->encode($assessmentTestSession);
        $this->storageDriver->write($assessmentTestSession->getSessionId(), $serializedSession);
    }

    /**
     * @inheritDoc
     */
    public function retrieve($sessionId)
    {
        $serializedSession = $this->storageDriver->read($sessionId);
        if (!$serializedSession) {
            throw new StorageException('', StorageException::RETRIEVAL);
        }
        $decodedSession = $this->serializer->decode($serializedSession);
        $route = $decodedSession->getRoute();
        $oldAssessmentItemSessionStore = $decodedSession->getAssessmentItemSessionStore();
        $newAssessmentItemSessionStore = new AssessmentItemSessionStore();
        $newLastOccurrenceUpdate = new \SplObjectStorage();
        $newRoute = new Route();
        $newRoute->setPosition($route->getPosition());
        $route->rewind();
        while ($route->valid()) {
            $currentPosition = $route->getPosition();
            $copyRouteItem = $route->getRouteItemAt($currentPosition);
            if (
                !isset($originalAssessmentItemRef)
                || (
                    $originalAssessmentItemRef instanceof QtiIdentifiable
                    && $copyRouteItem->getAssessmentItemRef()->getIdentifier() !== $originalAssessmentItemRef->getIdentifier()
                )
            ) {
                $originalAssessmentItemRef = $this->seeker->seekComponent('assessmentItemRef', $currentPosition);
            }

            $routeItem = new RouteItem(
                $originalAssessmentItemRef,
                $copyRouteItem->getAssessmentSection(),
                $copyRouteItem->getTestPart(),
                $this->getAssessmentTest()
            );
            $routeItem->setOccurence($copyRouteItem->getOccurence());
            $routeItem->setBranchRules($copyRouteItem->getBranchRules());
            $routeItem->setPreConditions($copyRouteItem->getPreConditions());
            $newRoute->addRouteItemObject($routeItem);

            if ($copyRouteItem->getOccurence() === 0) {
                $assessmentItemSessions = $oldAssessmentItemSessionStore->getAssessmentItemSessions(
                    $copyRouteItem->getAssessmentItemRef()
                );
                /** @var AssessmentItemSession $assessmentItemSession */
                foreach ($assessmentItemSessions as $occurrence => $assessmentItemSession) {
                    $assessmentItemSession->setAssessmentItem($originalAssessmentItemRef);

                    $newAssessmentItemSessionStore->addAssessmentItemSession(
                        $assessmentItemSession,
                        $occurrence
                    );
                    $lastOccurrenceUpdate = $decodedSession->whichLastOccurenceUpdate(
                        $copyRouteItem->getAssessmentItemRef()
                    );
                    if ($lastOccurrenceUpdate !== false) {
                        $newLastOccurrenceUpdate[$originalAssessmentItemRef] = $lastOccurrenceUpdate;
                    }
                }
            }

            $route->next();
        }
        $decodedSession->setLastOccurenceUpdate($newLastOccurrenceUpdate);
        $decodedSession->setRoute($newRoute);
        $decodedSession->setAssessmentItemSessionStore($newAssessmentItemSessionStore);


        return $decodedSession;
    }

    /**
     * @inheritDoc
     */
    public function exists($sessionId): bool
    {
        return $this->storageDriver->exists($sessionId);
    }

    /**
     * @inheritDoc
     */
    public function delete(AssessmentTestSession $assessmentTestSession): bool
    {
        $fileManager = $this->getManager()->getFileManager();
        foreach ($assessmentTestSession->getFiles() as $file) {
            try {
                $fileManager->delete($file);
            } catch (FileManagerException $e) {
                throw new StorageException(
                    "An unexpected error occurred while deleting file '" . $file->getIdentifier() . "' bound to Assessment Test Session '" . $assessmentTestSession->getSessionId() . "'.",
                    StorageException::DELETION,
                    $e
                );
            }
        }

        try {
            $this->storageDriver->delete($assessmentTestSession->getSessionId());
            return true;
        } catch (DriverDeletionException) {
            return false;
        }
    }
}
