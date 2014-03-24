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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * @subpackage 
 *
 */
namespace qtism\runtime\pci\json;

use qtism\common\enums\BaseType;

use qtism\runtime\common\RecordContainer;
use qtism\common\datatypes\Duration;
use qtism\common\datatypes\DirectedPair;
use qtism\common\datatypes\Pair;
use qtism\common\datatypes\String;
use qtism\common\datatypes\Uri;
use qtism\common\datatypes\IntOrIdentifier;
use qtism\common\datatypes\Identifier;
use qtism\common\datatypes\Integer;
use qtism\common\datatypes\Float;
use qtism\common\datatypes\Boolean;
use qtism\common\datatypes\Point;
use qtism\common\datatypes\Scalar;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\State;
use qtism\common\datatypes\QtiDatatype;
use \InvalidArgumentException;

/**
 * This class aims at providing the necessary behaviours to
 * marshall QtiDataType objects into their JSON representation.
 * 
 * The JSONified data respects the structure formulated by the IMS Global
 * Portable Custom Interaction Version 1.0 Candidate Final specification.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @see http://www.imsglobal.org/assessment/pciv1p0cf/imsPCIv1p0cf.html#_Toc353965343
 */
class Marshaller {
    
    /**
     * Create a new JSON Marshaller object.
     * 
     */
    public function __construct() {
        
    }
    
    /**
     * Marshall some QTI data into JSON.
     * 
     * @param State|QtiDatatype|null $data The data to be marshalled into JSON.
     * @throws InvalidArgumentException If $data has not a compliant type.
     * @throws MarshallingException If an error occurs while marshalling $data into JSON.
     */
    public function marshall($data) {
        
        if (is_null($data) === true) {
            $json = array('base' => $data);
        }
        else if ($data instanceof State) {
            
            $json = array();
            
            foreach ($data as $variable) {
                $json[$variable->getIdentifier()] = $this->marshallUnit($variable->getValue());
            }
        }
        else if ($data instanceof QtiDatatype) {
            $json = $this->marshallUnit($data);
        }
        else {
            $className = get_class($this);
            $msg = "The '${className}::marshall' method only takes State, QtiDatatype and null values as arguments, '";
            
            if (is_object($data) === true) {
                $msg .= get_class($data);
            }
            else {
                $msg .= gettype($data); 
            }
            
            $msg .= "' given.";
            throw new InvalidArgumentException($msg);
        }
        
        return json_encode($json);
    }
    
    protected function marshallUnit($unit) {
        if (is_null($unit) === true) {
            $json = null;
        }
        else if ($unit instanceof Scalar) {
            $json = $this->marshallScalar($unit);
        }
        else if ($unit instanceof MultipleContainer) {
            $json = array();
            $strBaseType = BaseType::getNameByConstant($unit->getBaseType());
            $json['list'] = array($strBaseType => array());
        
            foreach ($unit as $u) {
                $data = $this->marshallUnit($u);
                $json['list'][$strBaseType][] = $data['base'][$strBaseType];
            }
        }
        else if ($unit instanceof RecordContainer) {
            // todo
        }
        else {
            $json = $this->marshallComplex($unit);
        }
        
        return $json;
    }
    
    /**
     * Marshall a single scalar data into a PHP datatype (that can be transformed easilly in JSON
     * later on).
     * 
     * @param null|QtiDatatype $scalar A scalar to be transformed into a PHP datatype for later JSON encoding.
     * @return array|scalar|null
     */
    protected function marshallScalar($scalar) {
        if (is_null($scalar) === true) {
            return $scalar;
        }
        else if ($scalar instanceof QtiDatatype) {
            if ($scalar instanceof Boolean) {
                return array('base' => array('boolean' => $scalar->getValue()));
            }
            else if ($scalar instanceof Integer) {
                return array('base' => array('integer' => $scalar->getValue()));
            }
            else if ($scalar instanceof Float) {
                return array('base' => array('float' => $scalar->getValue()));
            }
            else if ($scalar instanceof Identifier) {
                return array('base' => array('identifier' => $scalar->getValue()));
            }
            else if ($scalar instanceof Uri) {
                return array('base' => array('uri' => $scalar->getValue()));
            }
            else if ($scalar instanceof String) {
                return array('base' => array('string' => $scalar->getValue()));
            }
            else if ($scalar instanceof IntOrIdentifier) {
                return array('base' => array('intOrIdentifier' => $scalar->getValue()));
            }
        }
        else {
            // throw exception.
        }
    }
    
    protected function marshallComplex(QtiDatatype $complex) {
        if (is_null($complex) === true) {
            return $complex;
        }
        else if ($complex instanceof Point) {
            return array('base' => array('point' => array($complex->getX(), $complex->getY())));
        }
        else if ($complex instanceof DirectedPair) {
            return array('base' => array('directedPair' => array($complex->getFirst(), $complex->getSecond())));
        }
        else if ($complex instanceof Pair) {
            return array('base' => array('pair' => array($complex->getFirst(), $complex->getSecond())));
        }
        else if ($complex instanceof Duration) {
            return array('base' => array('duration' => $complex->__toString()));
        }
        else {
            // throw exception
        }
    }
}