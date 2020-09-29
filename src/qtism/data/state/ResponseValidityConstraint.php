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

namespace qtism\data\state;

use InvalidArgumentException;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The ResponseValidityConstraint class represent a cardinality constraint to be applied on a given response variable.
 */
class ResponseValidityConstraint extends QtiComponent
{
    /**
     * The identifier of the response the validity constraint applies to.
     *
     * @var string
     * @qtism-bean-property
     */
    private $responseIdentifier;

    /**
     * The minimum cardinality the value to be set to the response must have.
     *
     * @var int
     * @qtism-bean-property
     */
    private $minConstraint;

    /**
     * The maximum cardinality the value to be set the response must have.
     *
     * @var int
     * @qtism-bean-property
     */
    private $maxConstraint;

    /**
     * An XML Schema regular expression representing a constraint to be applied on all string values contained by the variable described in the $responseIdentifier.
     *
     * @var string
     * @qtism-bean-property
     */
    private $patternMask;

    /**
     * The collection of nested AssociationValidityConstraints objects.
     */
    private $associationValidityConstraints;

    /**
     * Create a new ResponseValidityConstraint object.
     *
     * If the $patternMask attribute is provided, it represent a constraint to be applied on all string
     * values contained by the variable described in the $responseÏdentifier variable.
     *
     * @param string $responseIdentifier The identifier of the response the validity constraint applies to.
     * @param int $minConstraint The minimum cardinality the value to be set to the response must have.
     * @param int $maxConstraint The maximum cardinality the value to be set the response must have.
     * @param string $patternMask (optional) A XML Schema regular expression.
     * @throws InvalidArgumentException If one or more of the arguments above are invalid.
     */
    public function __construct($responseIdentifier, $minConstraint, $maxConstraint, $patternMask = '')
    {
        $this->setResponseIdentifier($responseIdentifier);
        $this->setMinConstraint($minConstraint);
        $this->setMaxConstraint($maxConstraint);
        $this->setPatternMask($patternMask);
        $this->setAssociationValidityConstraints(new AssociationValidityConstraintCollection());
    }

    /**
     * Set the identifier of the response the validity constraint applies to.
     *
     * @param int $responseIdentifier
     * @throws InvalidArgumentException If $responseIdentifier is not a non-empty string.
     */
    public function setResponseIdentifier($responseIdentifier)
    {
        if (is_string($responseIdentifier) === false || empty($responseIdentifier)) {
            throw new InvalidArgumentException(
                "The 'responseIdentifier' argument must be a non-empty string."
            );
        }

        $this->responseIdentifier = $responseIdentifier;
    }

    /**
     * Get the identifier of the response the validity constraint applies to.
     *
     * @return string
     */
    public function getResponseIdentifier()
    {
        return $this->responseIdentifier;
    }

    /**
     * Set the minimum cardinality the value to be set to the response must have.
     *
     * @param int $minConstraint A non negative integer (>= 0) integer value.
     * @throws InvalidArgumentException If $minConstraint is not a non negative (>= 0) integer value.
     */
    public function setMinConstraint($minConstraint)
    {
        if (is_int($minConstraint) === false || $minConstraint < 0) {
            throw new InvalidArgumentException(
                "The 'minConstraint' argument must be a non negative (>= 0) integer."
            );
        }

        $this->minConstraint = $minConstraint;
    }

    /**
     * Get the minimum cardinality the value to be set to the response must have.
     *
     * @return int A non negative (>= 0) integer value.
     */
    public function getMinConstraint()
    {
        return $this->minConstraint;
    }

    /**
     * Set the maximum cardinality the value to be set the response must have.
     *
     * Please note that 0 indicates no constraint.
     *
     * @param int $maxConstraint An integer value which is greater than the 'minConstraint' in place.
     * @throws InvalidArgumentException If $maxConstraint is not an integer greater or equal to the 'minConstraint' in place.
     */
    public function setMaxConstraint($maxConstraint)
    {
        if (is_int($maxConstraint) === false) {
            throw new InvalidArgumentException(
                "The 'maxConstraint' argument must be an integer."
            );
        } elseif ($maxConstraint < 0) {
            throw new InvalidArgumentException(
                "The 'maxConstraint' argument must be a non negative (>= 0) integer."
            );
        } elseif ($maxConstraint !== 0 && $maxConstraint < ($minConstraint = $this->getMinConstraint())) {
            throw new InvalidArgumentException(
                "The 'maxConstraint' argument must be greather or equal to than the 'minConstraint' in place."
            );
        }

        $this->maxConstraint = $maxConstraint;
    }

    /**
     * Get the maximum cardinality the value to be set the response must have.
     *
     * Please note that 0 indicates no constraint.
     *
     * @return int
     */
    public function getMaxConstraint()
    {
        return $this->maxConstraint;
    }

    /**
     * Set the PatternMask for the ResponseValidityConstraint.
     *
     * Set the XML Schema regular expression representing a constraint to be applied on all string
     * values contained by the variable described in the $responseÏdentifier variable. Providing an empty
     * string as the $patternMask means there is no constraint to be applied.
     *
     * @param string $patternMask An XML Schema regular expression.
     */
    public function setPatternMask($patternMask)
    {
        if (is_string($patternMask) === false) {
            throw new InvalidArgumentException(
                "The 'patternMask' argument must be a string, '" . gettype($patternMask) . "' given."
            );
        }

        $this->patternMask = $patternMask;
    }

    /**
     * Get the PatternMask for the ResponseValidityConstraint.
     *
     * Get the XML Schema regular expression representing a constraint to be applied on all string
     * values contained by the variable described in the $responseIdentifier variable. The method
     * returns an empty string when there is no constraint to be applied.
     *
     * @return string an XML Schema regulax expression.
     */
    public function getPatternMask()
    {
        return $this->patternMask;
    }

    /**
     * Set the collection of nested AssociationValidityConstraints objects.
     *
     * @param AssociationValidityConstraintCollection $associationValidityConstraints
     */
    public function setAssociationValidityConstraints(AssociationValidityConstraintCollection $associationValidityConstraints)
    {
        $this->associationValidityConstraints = $associationValidityConstraints;
    }

    /**
     * Get the collection of nested AssociationValidityConstraints objects.
     *
     * @return AssociationValidityConstraintCollection
     */
    public function getAssociationValidityConstraints()
    {
        return $this->associationValidityConstraints;
    }

    /**
     * Attach a given $associationValidityConstraint object.
     *
     * @param AssociationValidityConstraint $associationValidityConstraint
     */
    public function addAssociationValidityConstraint(AssociationValidityConstraint $associationValidityConstraint)
    {
        $this->getAssociationValidityConstraints()->attach($associationValidityConstraint);
    }

    /**
     * Remove a given $associationValidityConstraint object.
     *
     * If $associationValidityConstraint is not part of the ResponseValidityConstraint, nothing happens.
     *
     * @param AssociationValidityConstraint $associationValidityConstraint
     */
    public function removeAssociationValidityConstraint(AssociationValidityConstraint $associationValidityConstraint)
    {
        $this->getAssociationValidityConstraints()->remove($associationValidityConstraint);
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'responseValidityConstraint';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents()
    {
        return new QtiComponentCollection(
            $this->getAssociationValidityConstraints()->getArrayCopy()
        );
    }
}
