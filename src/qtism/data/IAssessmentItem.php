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
use qtism\common\collections\IdentifierCollection;
use qtism\data\content\ModalFeedbackRuleCollection;
use qtism\data\processing\ResponseProcessing;
use qtism\data\processing\TemplateProcessing;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\ResponseValidityConstraintCollection;
use qtism\data\state\ShufflingCollection;
use qtism\data\state\TemplateDeclarationCollection;

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
    public function isTimeDependent(): bool;

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
    public function isAdaptive(): bool;

    /**
     * Get the response declarations.
     *
     * @return ResponseDeclarationCollection A collection of ResponseDeclaration objects.
     */
    public function getResponseDeclarations(): ResponseDeclarationCollection;

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
    public function getOutcomeDeclarations(): OutcomeDeclarationCollection;

    /**
     * Set the outcome declarations.
     *
     * @param OutcomeDeclarationCollection $outcomeDeclarations A collection of OutcomeDeclaration objects.
     */
    public function setOutcomeDeclarations(OutcomeDeclarationCollection $outcomeDeclarations);

    /**
     * Get the template declarations.
     *
     * @return TemplateDeclarationCollection $templateDeclarations
     */
    public function getTemplateDeclarations(): TemplateDeclarationCollection;

    /**
     * Set the template declarations.
     *
     * @param TemplateDeclarationCollection $templateDeclarations
     */
    public function setTemplateDeclarations(TemplateDeclarationCollection $templateDeclarations);

    /**
     * Get the modal feedback rules.
     *
     * @return ModalFeedbackRuleCollection
     */
    public function getModalFeedbackRules(): ModalFeedbackRuleCollection;

    /**
     * Get the associated ResponseProcessing object.
     *
     * @return ResponseProcessing A ResponseProcessing object or null if no associated response processing.
     */
    public function getResponseProcessing(): ?ResponseProcessing;

    /**
     * Set the associated ResponseProcessing object.
     *
     * @param ResponseProcessing $responseProcessing A ResponseProcessing object or null if no associated response processing.
     */
    public function setResponseProcessing(?ResponseProcessing $responseProcessing = null);

    /**
     * Get the associated TemplateProcessing object.
     *
     * @return TemplateProcessing A TemplateProcessing object or null if no associated template processing.
     */
    public function getTemplateProcessing(): ?TemplateProcessing;

    /**
     * Set the associated TemplateProcessing object.
     *
     * @param TemplateProcessing $templateProcessing A TemplateProcessing object or null if no associated template processing.
     */
    public function setTemplateProcessing(?TemplateProcessing $templateProcessing = null);

    /**
     * Get the response variable identifiers related to endAttemptInteractions in the item content.
     *
     * @return IdentifierCollection
     */
    public function getEndAttemptIdentifiers(): IdentifierCollection;

    /**
     * Get the ShufflingCollection object representing how choices are shuffled in shuffled interactions.
     *
     * @return ShufflingCollection
     */
    public function getShufflings(): ShufflingCollection;

    /**
     * Get the ResponseValidityConstraintCollection object.
     *
     * The ResponseValidityConstraint objects returned describes how the responses provided to make
     * an attempt on the item should be validated.
     *
     * @return ResponseValidityConstraintCollection
     */
    public function getResponseValidityConstraints(): ResponseValidityConstraintCollection;

    /**
     * Set the title.
     *
     * Set the title of the assessmentItem.
     *
     * @param string $title
     * @throws InvalidArgumentException
     */
    public function setTitle($title);

    /**
     * Get the title.
     *
     * Get the title of the assessmentItem.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Set the label.
     *
     * Set the label of the assessmentItem
     *
     * @param $label
     * @throws InvalidArgumentException
     */
    public function setLabel($label);

    /**
     * Get the label.
     *
     * Get the label of the assessmentItem.
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Has a label.
     *
     * Whether or not the assessmentItem has a label.
     *
     * @return bool
     */
    public function hasLabel(): bool;
}
