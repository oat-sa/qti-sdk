<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data;

use InvalidArgumentException;
use qtism\data\processing\ResponseProcessing;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\ResponseValidityConstraintCollection;

/**
 * Any clas that claims to represent An AssessmentItem must implement this interface.
 */
interface IAssessmentItem extends QtiIdentifiable
{
    /**
     * Set whether the item is time dependent or not.
     *
     * @param bool $timeDependent Time dependent or not.
     * @throws InvalidArgumentException If $timeDependent is not a boolean value.
     */
    public function setTimeDependent($timeDependent);

    /**
     * Whether the item is time dependent.
     *
     * @return bool
     */
    public function isTimeDependent();

    /**
     * Set whether the item is adaptive.
     *
     * @param bool $adaptive Adaptive or not.
     * @throws InvalidArgumentException If $adaptive is not a boolean value.
     */
    public function setAdaptive($adaptive);

    /**
     * Whether the item is adaptive.
     *
     * @return bool
     */
    public function isAdaptive();

    /**
     * Get the response declarations.
     *
     * @return ResponseDeclarationCollection A collection of ResponseDeclaration objects.
     */
    public function getResponseDeclarations();

    /**
     * Set the response declarations.
     *
     * @param ResponseDeclarationCollection $responseDeclarations A collection of ResponseDeclaration objects
     */
    public function setResponseDeclarations(ResponseDeclarationCollection $responseDeclarations);

    /**
     * Get the outcome declarations.
     *
     * @return OutcomeDeclarationCollection A collection of OutcomeDeclaration objects.
     */
    public function getOutcomeDeclarations();

    /**
     * Set the outcome declarations.
     *
     * @param OutcomeDeclarationCollection $outcomeDeclarations A collection of OutcomeDeclaration objects.
     */
    public function setOutcomeDeclarations(OutcomeDeclarationCollection $outcomeDeclarations);

    /**
     * Get the associated ResponseProcessing object.
     *
     * @return ResponseProcessing A ResponseProcessing object or null if no associated response processing.
     */
    public function getResponseProcessing();

    /**
     * Set the associated ResponseProcessing object.
     *
     * @param ResponseProcessing $responseProcessing A ResponseProcessing object or null if no associated response processing.
     */
    public function setResponseProcessing(ResponseProcessing $responseProcessing = null);

    /**
     * Get the ResponseValidityConstraintCollection object.
     *
     * The ResponseValidityConstraint objects returned describes how the responses provided to make
     * an attempt on the item should be validated.
     *
     * @return ResponseValidityConstraintCollection
     */
    public function getResponseValidityConstraints(): ResponseValidityConstraintCollection;
}
