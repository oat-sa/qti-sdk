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
 * Copyright (c) 2013-2024 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data;

use InvalidArgumentException;
use qtism\common\utils\Format;

/**
 * The TestFeedback class.
 */
class TestFeedbackRef extends QtiComponent
{
    /**
     * From IMS QTI:
     *
     * Test feedback is shown to the candidate either directly following outcome processing
     * (during the test) or at the end of the testPart or assessmentTest as appropriate
     * (referred to as atEnd).
     *
     * The value of an outcome variable is used in conjunction with the showHide and
     * identifier attributes to determine whether or not the feedback is actually
     * shown in a similar way to feedbackElement (Item Model).
     *
     * @var int
     * @qtism-bean-property
     */
    private $access = TestFeedbackAccess::DURING;

    /**
     * The QTI Identifier of the outcome variable bound to this feedback.
     *
     * @var string
     * @qtism-bean-property
     */
    private $outcomeIdentifier;

    /**
     * From IMS QTI:
     *
     * The showHide attribute determines how the visibility of the feedbackElement is controlled.
     * If set to show then the feedback is hidden by default and shown only if the associated
     * outcome variable matches, or contains, the value of the identifier attribute. If set
     * to hide then the feedback is shown by default and hidden if the associated outcome
     * variable matches, or contains, the value of the identifier attribute.
     *
     * @var int
     * @qtism-bean-property
     */
    private $showHide = ShowHide::SHOW;

    /**
     * The QTI identifier of the TestFeedback.
     *
     * @var string
     * @qtism-bean-property
     */
    private $identifier;

    /**
     * A URI referencing the actual real TestFeedback QTI class.
     *
     * @var string
     * @qtism-bean-property
     */
    private $href;

    /**
     * Create a new TestFeedbackRef object.
     *
     * @param string $identifier An identifier.
     * @param string $outcomeIdentifier An identifier.
     * @param int $access A value from the TestFeedbackAccess enumeration.
     * @param int $showHide A value from the ShowHide enumeration.
     * @param string $href A URI.
     * @throws InvalidArgumentException If one of the arguments is invalid.
     */
    public function __construct($identifier, $outcomeIdentifier, $access, $showHide, $href)
    {
        $this->setIdentifier($identifier);
        $this->setOutcomeIdentifier($outcomeIdentifier);
        $this->setHref($href);
        $this->setAccess($access);
        $this->setShowHide($showHide);
    }

    /**
     * Get how the feedback is shown to the candidate.
     *
     * * TestFeedbackAccess::DURING = At outcome processing time.
     * * TestFeedbackAccess::AT_END = At the end of the TestPart or AssessmentTest.
     *
     * @return int A value of the TestFeedbackAccess enumeration.
     */
    public function getAccess(): int
    {
        return $this->access;
    }

    /**
     * Set how the feedback is shown to the candidate.
     *
     * * TestFeedbackAccess::DURING = At outcome processing time.
     * * TestFeedbackAccess:AT_END = At the end of the TestPart or AssessmentTest.
     *
     * @param int $access A value of the TestFeedbackAccess enumeration.
     * @throws InvalidArgumentException If $access is not a value from the TestFeedbackAccess enumeration.
     */
    public function setAccess($access): void
    {
        if (in_array($access, TestFeedbackAccess::asArray(), true)) {
            $this->access = $access;
        } else {
            $msg = "'{$access}' is not a value from the TestFeedbackAccess enumeration.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the QTI Identifier of the outcome variable bound to this TestFeedback.
     *
     * @return string A QTI Identifier.
     */
    public function getOutcomeIdentifier(): string
    {
        return $this->outcomeIdentifier;
    }

    /**
     * Set the QTI Identifier of the outcome variable bound to this TestFeedback.
     *
     * @param string $outcomeIdentifier A QTI Identifier.
     * @throws InvalidArgumentException If $outcomeIdentifier is not a valid QTI Identifier.
     */
    public function setOutcomeIdentifier($outcomeIdentifier): void
    {
        if (Format::isIdentifier((string)$outcomeIdentifier)) {
            $this->outcomeIdentifier = $outcomeIdentifier;
        } else {
            $msg = "'{$outcomeIdentifier}' is not a valid QTI Identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the QTI identifier of this TestFeedback.
     *
     * @return string A QTI identifier.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Set the QTI identifier of this TestFeedback.
     *
     * @param string $identifier A QTI Identifier.
     * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
     */
    public function setIdentifier($identifier): void
    {
        if (Format::isIdentifier((string)$identifier, false)) {
            $this->identifier = $identifier;
        } else {
            $msg = "'{$identifier}' is not a valid QTI Identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get how the feedback should be displayed.
     *
     * @return int A value from the ShowHide enumeration.
     */
    public function getShowHide(): int
    {
        return $this->showHide;
    }

    /**
     * Set how the feedback should be displayed.
     *
     * @param bool $showHide A value from the ShowHide enumeration.
     * @throws InvalidArgumentException If $showHide is not a value from the ShowHide enumeration.
     */
    public function setShowHide($showHide): void
    {
        if (in_array($showHide, ShowHide::asArray(), true)) {
            $this->showHide = $showHide;
        } else {
            $msg = "'{$showHide}' is not a value from the ShowHide enumeration.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Set the hyper-text reference to the actual content of the TestFeedback.
     *
     * @return string
     */
    public function getHref(): string
    {
        return $this->href;
    }

    /**
     * Get the hyper-text reference to the actual content of the TestFeedback.
     *
     * @param string $href
     */
    public function setHref($href): void
    {
        if (Format::isUri($href) === true) {
            $this->href = $href;
        } else {
            $msg = "'{$href}' is not a valid URI.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'testFeedbackRef';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection();
    }
}
