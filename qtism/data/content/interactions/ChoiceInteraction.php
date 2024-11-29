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

namespace qtism\data\content\interactions;

use InvalidArgumentException;
use qtism\data\QtiComponentCollection;
use qtism\data\state\ResponseValidityConstraint;

/**
 * From IMS QTI:
 *
 * The choice interaction presents a set of choices to the candidate. The candidate's task
 * is to select one or more of the choices, up to a maximum of maxChoices. The interaction
 * is always initialized with no choices selected.
 *
 * The choiceInteraction must be bound to a response variable with a baseType of identifier
 * and single or multiple cardinality.
 */
class ChoiceInteraction extends BlockInteraction
{
    /**
     * From IMS QTI:
     *
     * If the shuffle attribute is true then the delivery engine must randomize the order in which
     * the choices are initially presented, subject to the value of the fixed attribute of each choice.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $shuffle = false;

    /**
     * From IMS QTI:
     *
     * The maximum number of choices that the candidate is allowed to select. If maxChoices is 0
     * then there is no restriction. If maxChoices is greater than 1 (or 0) then the interaction
     * must be bound to a response with multiple cardinality.
     *
     * @var int
     * @qtism-bean-property
     */
    private $maxChoices = 1;

    /**
     * From IMS QTI:
     *
     * The minimum number of choices that the candidate is required to select to form a valid response.
     * If minChoices is 0 then the candidate is not required to select any choices. minChoices must be
     * less than or equal to the limit imposed by maxChoices.
     *
     * @var int
     * @qtism-bean-property
     */
    private $minChoices = 0;

    /**
     * From IMS QTI:
     *
     * The orientation attribute provides a hint to rendering systems that the choices have an
     * inherent vertical or horizontal interpretation.
     *
     * @var int
     * @qtism-bean-property
     */
    private $orientation = Orientation::VERTICAL;

    /**
     * From IMS QTI:
     *
     * An ordered list of the choices that are displayed to the user. The order is the order of
     * the choices presented to the user unless shuffle is true.
     *
     * @var SimpleChoiceCollection
     * @qtism-bean-property
     */
    private $simpleChoices;

    /**
     * Create a new ChoiceInteraction object.
     *
     * @param string $responseIdentifier The identifier of the associated response variable.
     * @param SimpleChoiceCollection $simpleChoices An ordered list of choices (minimum one choice) that are displayed to the user.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement (string256).
     * @throws InvalidArgumentException If any of the arguments is invalid.
     */
    public function __construct($responseIdentifier, SimpleChoiceCollection $simpleChoices, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($responseIdentifier, $id, $class, $lang, $label);
        $this->setSimpleChoices($simpleChoices);
        $this->setMaxChoices(1);
        $this->setMinChoices(0);
        $this->setOrientation(Orientation::VERTICAL);
    }

    /**
     * Set the ordered list of choices that are displayed to the user.
     *
     * @param SimpleChoiceCollection $simpleChoices A collection of SimpleChoice objects.
     * @throws InvalidArgumentException If $simpleChoices is empty.
     */
    public function setSimpleChoices(SimpleChoiceCollection $simpleChoices)
    {
        if (count($simpleChoices) > 0) {
            $this->simpleChoices = $simpleChoices;
        } else {
            $msg = 'A ChoiceInteraction object must be composed of at lease one SimpleChoice object, none given.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the ordered list of choices that are displayed to the user.
     *
     * @return SimpleChoiceCollection A collection of at least one SimpleChoice object.
     */
    public function getSimpleChoices()
    {
        return $this->simpleChoices;
    }

    /**
     * Set whether the delivery engine must randomize the order in which the choices
     * are initialiiy presented.
     *
     * @param bool $shuffle
     * @throws InvalidArgumentException If $shuffle is not a boolean value.
     */
    public function setShuffle($shuffle)
    {
        if (is_bool($shuffle)) {
            $this->shuffle = $shuffle;
        } else {
            $msg = "The 'shuffle' argument must be a boolean value, '" . gettype($shuffle) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Know whether the delivery engine must randomize the order in which the choices
     * are initially presented.
     *
     * @return bool
     */
    public function mustShuffle()
    {
        return $this->shuffle;
    }

    /**
     * Set the maximum number of choices that the candidate is allowed to select.
     *
     * @param int $maxChoices A positive (>= 0) integer.
     * @throws InvalidArgumentException If $maxChoices is not a positive integer.
     */
    public function setMaxChoices($maxChoices)
    {
        if (is_int($maxChoices) && $maxChoices >= 0) {
            $this->maxChoices = $maxChoices;
        } else {
            $msg = "The 'maxChoices' argument must be a positive (>= 0) integer, '" . gettype($maxChoices) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the maximum number of choices that the candidate is allowed to select.
     *
     * @return int A strictly positive (> 0) integer.
     */
    public function getMaxChoices()
    {
        return $this->maxChoices;
    }

    /**
     * Set the minimum number of choices that the candidate is required to select.
     *
     * A value of 0 means the candidate is not required to select any choices.
     *
     * @param int $minChoices A positive (>= 0) integer.
     * @throws InvalidArgumentException If $minChoices is not a positive (>= 0) integer.
     */
    public function setMinChoices($minChoices)
    {
        if (is_int($minChoices) && $minChoices >= 0) {
            $this->minChoices = $minChoices;
        } else {
            $msg = "The 'minChoices' argument must be a positive (>= 0) integer, '" . gettype($minChoices) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the minimum number of choices that the candidate is required to select.
     *
     * @return int A positive (> 0) integer.
     */
    public function getMinChoices()
    {
        return $this->minChoices;
    }

    /**
     * Set the orientation of the choices.
     *
     * @param int $orientation A value from the Orientation enumeration.
     * @throws InvalidArgumentException If $orientation is not a value from the Orientation enumeration.
     */
    public function setOrientation($orientation)
    {
        if (in_array($orientation, Orientation::asArray(), true)) {
            $this->orientation = $orientation;
        } else {
            $msg = "The 'orientation' argument must be a value from the Orientation enumeration, '" . gettype($orientation) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the orientation of the choices.
     *
     * @return int A value from the Orientation enumeration.
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    public function getResponseValidityConstraint(): ResponseValidityConstraint
    {
        return new ResponseValidityConstraint(
            $this->getResponseIdentifier(),
            $this->getMinChoices(),
            $this->getMaxChoices()
        );
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'choiceInteraction';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents()
    {
        $parentComponents = parent::getComponents();

        return new QtiComponentCollection(array_merge($parentComponents->getArrayCopy(), $this->getSimpleChoices()->getArrayCopy()));
    }
}
