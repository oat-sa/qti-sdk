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

use InvalidArgumentException;
use oat\dtms\DateTime;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\runtime\common\VariableCollection;

/**
 * Class ItemResult
 *
 * The result of an item session is reported with an itemResult.
 * A report may contain multiple results for the same instance of an item representing multiple attempts,
 * progression through an adaptive item or even more detailed tracking. In these cases, each item result must have a different datestamp.
 */
class ItemResult extends QtiComponent
{
    /**
     * The identifier of the item for which this is a result. For item results that are reported as part of a test result
     * this is the identifier used to refer to the item in the test (see assessmentItemRef).
     * For item results that are reported on their own, this can be any suitable identifier for the item.
     * Where possible, the value should match the identifier attribute on the associated assessmentItem.
     *
     * Multiplicity [1]
     *
     * @var QtiIdentifier
     */
    protected $identifier;

    /**
     * For item results that are reported as part of a test, this attribute must be used to indicate the position of the item
     * within the specific instance of the test. The first item of the first part of the test is defined to have sequence index 1.
     *
     * Multiplicity [0,1]
     *
     * @var QtiInteger
     */
    protected $sequenceIndex;

    /**
     * The date stamp of when this result was recorded.
     *
     * Multiplicity [1]
     *
     * @var DateTime
     */
    protected $datestamp;

    /**
     * The session status is used to interpret the values of the item variables. See the sessionStatus vocabulary.
     *
     * Multiplicity [1]
     *
     * @var SessionStatus = Enumerated value set of: {
     *  - final    The value to use when the item variables represent the values at the end of an attempt after response processing has taken place.
     *              In other words, after the outcome values have been updated to reflect the values of the response variables.
     *  - initial    The value to use for sessions in the initial state, as described above. This value can only be used to describe sessions
     *              for which the response variable numAttempts is 0. The values of the variables are set according to the rules
     *              defined in the appropriate declarations (see responseDeclaration, outcomeDeclaration and templateDeclaration).
     *  - pendingResponseProcessing    The value to use when the item variables represent the values of the response variables after submission
     *                                  but before response processing has taken place. Again, the outcomes are those assigned at the end of the previous attempt
     *                                  as they are awaiting response processing.
     *  - pendingSubmission    The value to use when the item variables represent a snapshot of the current values during an attempt
     *                          (in other words, while interacting or suspended). The values of the response variables represent work in progress
     *                          that has not yet been submitted for response processing by the candidate. The values of the outcome variables represent
     *                          the values assigned during response processing at the end of the previous attempt or, in the case of the first attempt,
     *                          the default values given in the variable declarations.
     * }
     */
    protected $sessionStatus;

    /**
     * During the item session the delivery engine keeps track of the current values assigned to all itemVariables.
     * The values include the values of the built-in variables numAttempts, duration and completionStatus.
     * Each value is represented in the report by an instance of itemVariable.
     *
     * Multiplicity [0,*]
     *
     * @var VariableCollection
     */
    protected $itemVariables;

    /**
     * An optional comment supplied by the candidate (see the allowComment in the ASI documentation [QTI, 15a]).
     *
     * Multiplicity [0,1]
     *
     * @var QtiString
     */
    protected $candidateComment;

    /**
     * ItemResult constructor.
     *
     * @param QtiIdentifier $identifier
     * @param DateTime $datestamp
     * @param integer $sessionStatus
     * @param ItemVariableCollection|null $itemVariables
     * @param QtiString|null $candidateComment
     * @param QtiInteger|null $sequenceIndex
     */
    public function __construct(
        QtiIdentifier $identifier,
        DateTime $datestamp,
        $sessionStatus,
        ItemVariableCollection $itemVariables = null,
        QtiString $candidateComment = null,
        QtiInteger $sequenceIndex = null
    ) {
        $this->setIdentifier($identifier);
        $this->setDatestamp($datestamp);
        $this->setSessionStatus($sessionStatus);
        $this->setItemVariables($itemVariables);
        $this->setCandidateComment($candidateComment);
        $this->setSequenceIndex($sequenceIndex);
    }

    /**
     * Returns the QTI class name as per QTI 2.1 specification.
     *
     * @return string A QTI class name.
     */
    public function getQtiClassName()
    {
        return 'itemResult';
    }

    /**
     * Get the direct child components of this one.
     *
     * @return QtiComponentCollection A collection of QtiComponent objects.
     */
    public function getComponents()
    {
        if ($this->hasItemVariables()) {
            $components = $this->getItemVariables()->toArray();
        } else {
            $components = [];
        }
        return new QtiComponentCollection($components);
    }

    /**
     * Get the Qti identifier of itemResult
     *
     * @return QtiIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set the identifier to itemResult component
     *
     * @param QtiIdentifier $identifier
     * @return $this
     */
    public function setIdentifier(QtiIdentifier $identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * The date stamp of when this result was recorded.
     *
     * @return DateTime
     */
    public function getDatestamp()
    {
        return $this->datestamp;
    }

    /**
     * Set the datestamp that must be a Datetime object
     *
     * @param DateTime $datestamp
     * @return $this
     */
    public function setDatestamp(DateTime $datestamp)
    {
        $this->datestamp = $datestamp;
        return $this;
    }

    /**
     * Get all test variables. Can be outcome, response, candidate or tempalte variable
     *
     * @return ItemVariableCollection
     */
    public function getItemVariables()
    {
        return $this->itemVariables;
    }

    /**
     * Set all test variables
     *
     * @param ItemVariableCollection $itemVariables
     * @return $this
     */
    public function setItemVariables(ItemVariableCollection $itemVariables = null)
    {
        $this->itemVariables = $itemVariables;
        return $this;
    }

    /**
     * Check if the item result has item variables
     *
     * @return bool
     */
    public function hasItemVariables()
    {
        return !is_null($this->itemVariables);
    }

    /**
     * Get the sequence of the item e.g. the position of the item in a test
     *
     * @return QtiInteger
     */
    public function getSequenceIndex()
    {
        return $this->sequenceIndex;
    }

    /**
     * Set the sequence of the item or null if not set
     *
     * @param QtiInteger|null $sequenceIndex
     * @return $this
     */
    public function setSequenceIndex(QtiInteger $sequenceIndex = null)
    {
        $this->sequenceIndex = $sequenceIndex;
        return $this;
    }

    /**
     * Check if the sequence index is set
     *
     * @return bool
     */
    public function hasSequenceIndex()
    {
        return !is_null($this->sequenceIndex);
    }

    /**
     * Get the session status of itemResult.
     *
     * @return SessionStatus
     */
    public function getSessionStatus()
    {
        return $this->sessionStatus;
    }

    /**
     * Set the session status of itemResult.
     *
     * @param $sessionStatus
     * @return $this
     *
     * @throws InvalidArgumentException If the sessionStatus is not a valid sessionStatus
     */
    public function setSessionStatus($sessionStatus)
    {
        $sessionStatus = (int)$sessionStatus;
        if (!in_array($sessionStatus, SessionStatus::asArray(), true)) {
            $msg = sprintf('Invalid session status. Should be one of "%s"', implode('", "', SessionStatus::asArray()));
            throw new InvalidArgumentException($msg);
        }
        $this->sessionStatus = $sessionStatus;
        return $this;
    }

    /**
     * Get the optional candidate comment or null if not set
     *
     * @return string
     */
    public function getCandidateComment()
    {
        return $this->candidateComment;
    }

    /**
     * Set the candidate comment
     *
     * @param QtiString $candidateComment
     * @return $this
     */
    public function setCandidateComment(QtiString $candidateComment = null)
    {
        $this->candidateComment = $candidateComment;
        return $this;
    }

    /**
     * Check if the candidate comment is set
     *
     * @return bool
     */
    public function hasCandidateComment()
    {
        return !is_null($this->candidateComment);
    }
}
