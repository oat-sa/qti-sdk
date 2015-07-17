<?php
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
 * Copyright (c) 2013-2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\data\state;

use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use \InvalidArgumentException;
use \OutOfBoundsException;

/**
 * The Shuffling class represents sets of shuffled/to be shuffled identifiers 
 * 
 * A Shuffling object can represent how the choices of an interaction are
 * shuffled at instantiation time. It is composed by one or two Shuffling Groups
 * composed of QTI Identifiers, representing in which order choices of an interaction
 * are to be displayed to candidates. Shuffling objects are usually bound
 * to AssessmentItemSession objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Shuffling extends QtiComponent 
{
    /**
     * The identifier of the interaction the Shuffling reflects.
     * 
     * @var string
     * @qtism-bean-property
     */
    private $responseIdentifier;
    
    /**
     * 1 to 2 ShufflingGroup objects representing shuffled identifiers. 
     * 
     * @var \qtism\data\state\ShufflingGroupCollection
     * @qtism-bean-property
     */
    private $shufflingGroups;
    
    /**
     * Create a new Shuffling object.
     * 
     * @param \qtism\data\state\ShufflingGroupCollection $shufflingGroups
     * @throws \InvalidArgumentException If $shufflingGroups does not contain 1 to 2 ShufflingGroup objects.
     */
    public function __construct($responseIdentifier, ShufflingGroupCollection $shufflingGroups) 
    {
        $this->setResponseIdentifier($responseIdentifier);
        $this->setShufflingGroups($shufflingGroups);
    }
    
    /**
     * Get the identifier of the interaction the Shuffling reflects.
     * 
     * @param string $responseIdentifier
     */
    public function setResponseIdentifier($responseIdentifier)
    {
        $this->responseIdentifier = $responseIdentifier;
    }
    
    /**
     * Set the identifier of the interaction the Shuffling reflects.
     * 
     * @return string
     */
    public function getResponseIdentifier()
    {
        return $this->responseIdentifier;
    }
    
    /**
     * Set the ShufflingGroups.
     * 
     * @param \qtism\data\state\ShufflingGroupCollection $shufflingGroups
     * @throws \InvalidArgumentException If $shufflingGroups does not contain 1 to 2 ShufflingGroup objects.
     */
    public function setShufflingGroups(ShufflingGroupCollection $shufflingGroups) 
    {
        if (count($shufflingGroups) === 0) {
            $msg = "A Shuffling object must composed of at least 1 ShufflingGroup object. None given";
            throw new InvalidArgumentException($msg);
        } elseif (($c = count($shufflingGroups)) > 2) {
            $msg = "A Shuffling object must composed of at most 2 ShufflingGroup objects. ${c} given.";
            throw new InvalidArgumentException($msg);
        } else {
            $this->shufflingGroups = $shufflingGroups;
        }
    }
    
    /**
     * Get the ShufflingGroups.
     * 
     * @return \qtism\data\state\ShufflingGroupCollection
     */
    public function getShufflingGroups() 
    {
        return $this->shufflingGroups;
    }
    
    /**
     * Shuffles the identifiers of the ShufflingGroups.
     * 
     * Calling this method will create a new deep copy of this Shuffling object, with identifiers of the Shuffling
     * Groups shuffled again.
     * 
     * @return \qtism\data\state\Shuffling
     */
    public function shuffle() 
    {
        $shuffling = clone $this;
        $groups = $shuffling->getShufflingGroups();
        
        for ($i = 0; $i < count($groups); $i++) {
            $identifiers = $groups[$i]->getIdentifiers();
            $fixedIdentifiers = $groups[$i]->getFixedIdentifiers()->getArrayCopy();
            $shufflableIndexes = array();
            
            // Find shuffblable indexes.
            for ($n = 0; $n < count($identifiers); $n++) {
                if (in_array($identifiers[$n], $fixedIdentifiers) === false) {
                    $shufflableIndexes[] = $n;
                }
            }
            
            // Shuffle the new group.
            $n = count($shufflableIndexes) - 1;
            for ($j = $n; $j > 0; $j--) {
                $k = mt_rand(0, $n);
                
                $tmp1 = $identifiers[$shufflableIndexes[$j]];
                $tmp2 = $identifiers[$shufflableIndexes[$k]];
                
                $identifiers[$shufflableIndexes[$j]] = $tmp2;
                $identifiers[$shufflableIndexes[$k]] = $tmp1;
            }
        }
        
        return $shuffling;
    }
    
    /**
     * Retrieve an identifier by $index.
     * 
     * You can reach identifiers in all the ShufflingGroup objects composing the Shuffling object.
     * For instance, if the Shuffling object is composed of 2 ShufflingGroup objects containing
     * respectively ['id1', 'id2', 'id3'] and ['id4', 'id5', 'id6'], then 'id2' is at index 1 and
     * 'id5' is at index 4.
     * 
     * @param integer $index
     * @throws OutOfBoundsException
     * @return string
     */
    public function getIdentifierAt($index) {
        $i = 0;
        
        foreach ($this->getShufflingGroups() as $shufflingGroup) {
            foreach ($shufflingGroup->getIdentifiers() as $identifier) {
                if ($i === $index) {
                    return $identifier;
                }
                $i++;
            }
        }
        
        throw new OutOfBoundsException("No identifier at index ${index}.");
    }
    
    /**
     * Clone the Shuffling object.
     */
    public function __clone() 
    {
        $groups = $this->getShufflingGroups();
        $newGroups = new ShufflingGroupCollection();
        
        for ($i = 0; $i < count($groups); $i++) {
            $newGroups[] = clone $groups[$i];
        }
        
        $this->setShufflingGroups($newGroups);
    }
    
    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'shuffling';
    }
    
    /**
     * @see \qtism\data\QtiComponent::getComponents()
     */
    public function getComponents()
    {
        return new QtiComponentCollection($this->getShufflingGroups()->getArrayCopy());
    }
}
