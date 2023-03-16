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

use qtism\data\AssessmentTest;
use qtism\data\QtiIdentifiable;
use qtism\runtime\storage\common\AssessmentTestSeeker;
use qtism\runtime\tests\AbstractSessionManager;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentItemSessionStore;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\Route;
use qtism\runtime\tests\RouteItem;

class PhpSerializer implements SerializerInterface
{
    private AbstractSessionManager $manager;
    private AssessmentTest $assessmentTest;
    private AssessmentTestSeeker $seeker;

    public function __construct(
        AbstractSessionManager $manager,
        AssessmentTest $assessmentTest,
        AssessmentTestSeeker $seeker
    ) {
        $this->manager = $manager;
        $this->assessmentTest = $assessmentTest;
        $this->seeker = $seeker;
    }

    public function serialize(AssessmentTestSession $assessmentTestSession): string
    {
        return serialize($assessmentTestSession);
    }

    public function deserialize(string $serializedAssessmentTestSession): AssessmentTestSession
    {
        $result = unserialize($serializedAssessmentTestSession);

        if ($result instanceof AssessmentTestSession) {
            return $this->restoreOriginalReferences($result);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Unexpected serialized value provided [%s], expected [%s]',
                is_object($result) ? get_class($result) : gettype($result),
                AssessmentTestSession::class
            )
        );
    }

    private function restoreOriginalReferences(AssessmentTestSession $decodedSession): AssessmentTestSession
    {
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
                $this->assessmentTest
            );
            $routeItem->setOccurence($copyRouteItem->getOccurence());
            $routeItem->setBranchRules($copyRouteItem->getBranchRules());
            $routeItem->setPreConditions($copyRouteItem->getPreConditions());
            $newRoute->addRouteItemObject($routeItem);

            $oldAssessmentItemRef = $copyRouteItem->getAssessmentItemRef();
            if (
                $copyRouteItem->getOccurence() === 0
                && $oldAssessmentItemSessionStore->hasAssessmentItemSession($oldAssessmentItemRef)
            ) {
                $assessmentItemSessions = $oldAssessmentItemSessionStore->getAssessmentItemSessions(
                    $oldAssessmentItemRef
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
        $decodedSession->setSessionManager($this->manager);

        return $decodedSession;
    }
}
