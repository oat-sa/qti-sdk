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
 * The ResponseValidityConstraint class represent a cardinality constraint to be applied on a given response variable.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseValidityConstraint extends QtiComponent
{
    /**
     * The identifier of the response the validity constraint applies to.
     * 
     * @var string
     */
    private $responseIdentifier;
    
    /**
     * The minimum cardinality the value to be set to the response must have.
     * 
     * @var integer
     */
    private $minConstraint;
    
    /**
     * The maximum cardinality the value to be set the response must have.
     */
    private $maxConstraint;
    
    /**
	 * Create a new ResponseValidityConstraint object.
	 *
     * @param string $responseIdentifier The identifier of the response the validity constraint applies to.
     * @param integer $minConstraint The minimum cardinality the value to be set to the response must have.
     * @param integer $maxConstraint The maximum cardinality the value to be set the response must have.
     * @throws \InvalidArgumentException If one or more of the arguments above are invalid.
	 */
    public function __construct($responseIdentifier, $minConstraint, $maxConstraint)
    {
        $this->setResponseIdentifier($responseIdentifier);
        $this->setMinConstraint($minConstraint);
        $this->setMaxConstraint($maxConstraint);
    }
    
    /**
     * Set the identifier of the response the validity constraint applies to.
     * 
     * @param integer $responseIdentifier
     * @throws \InvalidArgumentException If $responseIdentifier is not a non-empty string.
     */
    public function setResponseIdentifier($responseIdentifier)
    {
        if (is_string($responseIdentifier) === false || empty($responseIdentifier) === true) {
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
        return $this->getResponseIdentifier;
    }
    
    /**
     * Set the minimum cardinality the value to be set to the response must have.
     * 
     * @param integer $minConstraint A non negative integer (>= 0) integer value.
     * @throws \InvalidArgumentException If $minConstraint is not a non negative (>= 0) integer value.
     */
    public function setMinConstraint($minConstraint)
    {
        if (is_int($minConstraint) === false || $minConstraint < 0) {
            throw new InvalidArgumentException(
                "The 'minConstraint' argument must be non negative (>= 0) integer."
            );
        }
        
        $this->minConstraint = $minConstraint;
    }
    
    /**
     * Get the minimum cardinality the value to be set to the response must have.
     * 
     * @return integer A non negative (>= 0) integer value.
     */
    public function getMinConstraint()
    {
        return $this->minConstraint;
    }
    
    /**
     * Set the maximum cardinality the value to be set the response must have.
     * 
     * @param integer $maxConstraint An integer value which is greater than the 'minConstraint' in place.
     * @throws \InvalidArgumentException If $maxConstraint is not an integer greater than the 'minConstraint' in place.
     */
    public function setMaxConstraint($maxConstraint)
    {
        if (is_int($maxConstraint) === false) {
            throw new InvalidArgumentException(
                "The 'maxConstraint' argument must be an integer."
            );
        } elseif ($maxConstraint <= ($maxConstraint = $this->getMinConstraint())) {
            throw new InvalidArgumentException(
                "The 'maxConstraint' argument must be greather than the 'minConstraint' in place.";
            );
        }
        
        $this->maxConstraint = $maxConstraint;
    }
    
    /**
     * Get the maximum cardinality the value to be set the response must have.
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
        return 'responseValidityConstraint';
    }

    /**
	 * @see \qtism\data\QtiComponent::getComponents()
	 */
    public function getComponents()
    {
        return new QtiComponentCollection();
    }
}
