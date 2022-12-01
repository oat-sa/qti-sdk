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
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille <camille@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\data\QtiComponentCollection;
use qtism\data\state\CorrectResponse;

/**
 * Class ResultResponseVariable
 *
 * The Item result information related to a 'Response Variable'.
 */
class ResultResponseVariable extends ItemVariable
{
    /**
     * When a response variable is bound to an interaction that supports the shuffling of choices,
     * the sequence of choices experienced by the candidate will vary between test instances.
     * When shuffling is in effect, the sequence of choices should be reported as a sequence of choice identifiers using this attribute.
     *
     * Multiplicity [0,1]
     *
     * @var QtiIdentifier
     */
    protected $choiceSequence = null;

    /**
     * The correct response may be output as part of the report if desired.
     * Systems are not limited to reporting correct responses declared in responseDeclarations.
     * For example, a correct response may be set by a templateRule or may simply have been suppressed
     * from the declaration passed to the delivery engine e.g. for security.
     *
     * Multiplicity [0,1]
     *
     * @var CorrectResponse
     */
    protected $correctResponse = null;

    /**
     * The response given by the candidate.
     *
     * Multiplicity [1]
     *
     * @var CandidateResponse
     */
    protected $candidateResponse;

    /**
     * ResultResponseVariable constructor.
     *
     * @param QtiIdentifier $identifier
     * @param $cardinality
     * @param CandidateResponse $candidateResponse
     * @param null $baseType
     * @param CorrectResponse|null $correctResponse
     * @param QtiIdentifier|null $choiceSequence
     */
    public function __construct(
        QtiIdentifier $identifier,
        $cardinality,
        CandidateResponse $candidateResponse,
        $baseType = null,
        CorrectResponse $correctResponse = null,
        QtiIdentifier $choiceSequence = null
    ) {
        parent::__construct($identifier, $cardinality, $baseType);
        $this->setCandidateResponse($candidateResponse);
        $this->setCorrectResponse($correctResponse);
        $this->setChoiceSequence($choiceSequence);
    }

    /**
     * Returns the QTI class name as per QTI 2.1 specification.
     *
     * @return string A QTI class name.
     */
    public function getQtiClassName(): string
    {
        return 'responseVariable';
    }

    /**
     * Get the direct child components of this one.
     *
     * @return QtiComponentCollection A collection of QtiComponent objects.
     */
    public function getComponents(): QtiComponentCollection
    {
        $components = [$this->getCandidateResponse()];
        if ($this->hasCorrectResponse()) {
            $components = array_merge($components, $this->getCorrectResponse());
        }
        return new QtiComponentCollection($components);
    }

    /**
     * Get the candidate response
     *
     * @return CandidateResponse
     */
    public function getCandidateResponse(): CandidateResponse
    {
        return $this->candidateResponse;
    }

    /**
     * Set the candidate response
     *
     * @param CandidateResponse $candidateResponse
     * @return $this
     */
    public function setCandidateResponse(CandidateResponse $candidateResponse)
    {
        $this->candidateResponse = $candidateResponse;
        return $this;
    }

    /**
     * Get the correct response
     *
     * @return CorrectResponse|null
     */
    public function getCorrectResponse(): ?CorrectResponse
    {
        return $this->correctResponse;
    }

    /**
     * Set the correct response
     *
     * @param CorrectResponse $correctResponse
     * @return $this
     */
    public function setCorrectResponse(CorrectResponse $correctResponse = null)
    {
        $this->correctResponse = $correctResponse;
        return $this;
    }

    /**
     * Check if the correctResponse is set
     *
     * @return bool
     */
    public function hasCorrectResponse(): bool
    {
        return $this->correctResponse !== null;
    }

    /**
     * Get the choice sequence
     *
     * @return QtiIdentifier|null
     */
    public function getChoiceSequence(): ?QtiIdentifier
    {
        return $this->choiceSequence;
    }

    /**
     * Set the choice sequence
     *
     * @param QtiIdentifier $choiceSequence
     * @return $this
     */
    public function setChoiceSequence(QtiIdentifier $choiceSequence = null)
    {
        $this->choiceSequence = $choiceSequence;
        return $this;
    }

    /**
     * Check if the choice sequence is set
     *
     * @return bool
     */
    public function hasChoiceSequence(): bool
    {
        return $this->choiceSequence !== null;
    }
}
