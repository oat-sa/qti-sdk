<?php

declare(strict_types=1);

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
 * @license GPLv2
 */

namespace qtism\runtime\tests;

use InvalidArgumentException;

/**
 * The Jump class represents the a possible location in an AssessmentTestSession
 * a candidate can "jump" to. Indeed, when the NONLINEAR navigation mode is in force,
 * the candidate has the ability to "jump" to a given RouteItem
 */
class Jump
{
    /**
     * The position in the route the jump
     * leads to.
     *
     * @var int
     */
    private $position;

    /**
     * The AssessmentItemRef the candidate can jump to.
     *
     * @var RouteItem
     */
    private $target;

    /**
     * The AssessmentItemSession related to the $assessmentItemRef.$occurence
     * the Jump targets.
     *
     * @var AssessmentItemSession
     */
    private $itemSession;

    /**
     * Create a new Jump object.
     *
     * @param int $position The position in the assessment test session's route the jump leads to.
     * @param RouteItem $target The RouteItem to go when following the jump.
     * @param AssessmentItemSession $itemSession The AssessmentItemSession related to the RouteItem.
     * @throws InvalidArgumentException If $occurence is not an integer value or $itemSessionState is not a value from the AssessmentItemSessionState enumeration.
     */
    public function __construct($position, RouteItem $target, AssessmentItemSession $itemSession)
    {
        $this->setPosition($position);
        $this->setTarget($target);
        $this->setItemSession($itemSession);
    }

    /**
     * Set the position in the assessment test session's route the
     * jump leads to.
     *
     * @param int $position
     */
    protected function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * Get the position in the assessment test session's route the
     * jump leads to.
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Set the RouteItem the candidate can jump to.
     *
     * @param RouteItem $target A RouteItem object.
     */
    protected function setTarget(RouteItem $target): void
    {
        $this->target = $target;
    }

    /**
     * Get the RouteItem the candidate can jump to.
     *
     * @return RouteItem A RouteItem object.
     */
    public function getTarget(): RouteItem
    {
        return $this->target;
    }

    /**
     * Set the AssessmentItemSession related to AssessmentItemRef.occurence.
     *
     * @param AssessmentItemSession $itemSession An AssessmentItemSession object.
     * @throws InvalidArgumentException If $itemSessionState is not a value from the AssessmentItemSessionState enumeration.
     */
    protected function setItemSession(AssessmentItemSession $itemSession): void
    {
        $this->itemSession = $itemSession;
    }

    /**
     * Get the AssessmentItemSession related to AssessmentItemRef.occurence.
     *
     * @return AssessmentItemSession An AssessmentItemSession object.
     */
    public function getItemSession(): AssessmentItemSession
    {
        return $this->itemSession;
    }
}
