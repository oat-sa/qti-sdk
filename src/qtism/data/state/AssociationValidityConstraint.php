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
 * Copyright (c) 2013-2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\state;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use \InvalidArgumentException;

/**
 * The AssociationValidityConstraint class.
 * 
 * It represents an identifier association constraint to be applied on a given response variable of Pair/DirectedPair baseType.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssociationValidityConstraint extends QtiComponent
{
    /**
     * The identifier on which the validity constraint applies to.
     * 
     * @var string
     * @qtism-bean-property
     */
    private $identifier;
    
    /**
     * The minimum number of times $identifier may be found in a Response Variable.
     * 
     * @var integer
     * @qtism-bean-property
     */
    private $minConstraint;
    
    /**
     * The maximum number of times $identifier may be found in a Response Variable.
     * 
     * @var integer
     * @qtism-bean-property
     */
    private $maxConstraint;
    
    /**
	 * Create a new AssociationValidityConstraint object.
     * 
     * If the $patternMask attribute is provided, it represent a constraint to be applied on all string 
     * values contained by the variable described in the $responseÏdentifier variable.
	 *
     * @param string $identifier The identifier on which the validity constraint applies to.
     * @param integer $minConstraint The minimum number of times $identifier may be found in a Response Variable.
     * @param integer $maxConstraint The maximum number of times $identifier may be found in a Response Variable.
     * @throws \InvalidArgumentException If one or more of the arguments above are invalid.
	 */
    public function __construct($identifier, $minConstraint, $maxConstraint)
    {
        $this->setIdentifier($identifier);
        $this->setMinConstraint($minConstraint);
        $this->setMaxConstraint($maxConstraint);
    }
    
    /**
     * Set the identifier on which the validity constraint applies to.
     * 
     * @param integer $identifier
     * @throws \InvalidArgumentException If $identifier is an empty string.
     */
    public function setIdentifier($identifier)
    {
        if (is_string($identifier) === false || empty($identifier) === true) {
            throw new InvalidArgumentException(
                "The 'identifier' argument must be a non-empty string."
            );
        }
        
        $this->identifier = $identifier;
    }
    
    /**
     * Get the identifier on which the validity constraint applies to.
     * 
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    /**
     * Set the minimum number of times $identifier may be found in a Response Variable.
     * 
     * @param integer $minConstraint A non negative integer (>= 0) integer value.
     * @throws \InvalidArgumentException If $minConstraint is not a non negative (>= 0) integer value.
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
     * Get the minimum number of times $identifier may be found in a Response Variable.
     * 
     * @return integer A non negative (>= 0) integer value.
     */
    public function getMinConstraint()
    {
        return $this->minConstraint;
    }
    
    /**
     * Set the maximum number of times $identifier may be found in a Response Variable.
     * 
     * Please note that 0 indicates no constraint.
     * 
     * @param integer $maxConstraint An integer value which is greater than the 'minConstraint' in place.
     * @throws \InvalidArgumentException If $maxConstraint is not an integer greater or equal to the 'minConstraint' in place.
     */
    public function setMaxConstraint($maxConstraint)
    {
        if (is_int($maxConstraint) === false) {
            throw new InvalidArgumentException(
                "The 'maxConstraint' argument must be an integer."
            );
        } else if ($maxConstraint < 0) { 
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
     * Get the maximum number of times $identifier may be found in a Response Variable.
     * 
     * Please note that 0 indicates no constraint.
     * 
     * @return integer
     */
    public function getMaxConstraint()
    {
        return $this->maxConstraint;
    }

    /**
	 * @see \qtism\data\QtiComponent::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'associationValidityConstraint';
    }

    /**
	 * @see \qtism\data\QtiComponent::getComponents()
	 */
    public function getComponents()
    {
        return new QtiComponentCollection();
    }
}
