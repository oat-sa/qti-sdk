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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package 
 */


namespace qtism\data\storage\xml\marshalling;

use qtism\data\content\interactions\TextEntryInteraction;
use qtism\data\QtiComponent;
use \InvalidArgumentException;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for TextEntryInteraction.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TextEntryInteractionMarshaller extends Marshaller {
	
	/**
	 * Marshall a TextEntryInteraction object into a DOMElement object.
	 * 
	 * @param QtiComponent $component A TextEntryInteraction object.
	 * @return DOMElement The according DOMElement object.
	 * @throws MarshallingException
	 */
	protected function marshall(QtiComponent $component) {
        $element = self::getDOMCradle()->createElement('textEntryInteraction');
        
        self::setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());
        
        if ($component->getBase() !== 10) {
            self::setDOMElementAttribute($element, 'base', $component->getBase());
        }
        
        if ($component->hasStringIdentifier() === true) {
            self::setDOMElementAttribute($element, 'stringIdentifier', $component->getStringIdentifier());
        }
        
        if ($component->hasExpectedLength() === true) {
            self::setDOMElementAttribute($element, 'expectedLength', $component->getExpectedLength());
        }
        
        if ($component->hasPatternMask() === true) {
            self::setDOMElementAttribute($element, 'patternMask', $component->getPatternMask());
        }
        
        if ($component->hasPlaceholderText() === true) {
            self::setDOMElementAttribute($element, 'placeholderText', $component->getPlaceholderText());
        }
        
        self::fillElement($element, $component);
        return $element;
	}
	
	/**
	 * Unmarshall a DOMElement object corresponding to a textEntryInteraction element.
	 * 
	 * @param DOMElement $element A DOMElement object.
	 * @return QtiComponent A TextEntryInteraction object.
	 * @throws UnmarshallingException
	 */
	protected function unmarshall(DOMElement $element) {
	    
	    if (($responseIdentifier = self::getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            
            try {
                $component = new TextEntryInteraction($responseIdentifier);
            }
            catch (InvalidArgumentException $e) {
                $msg = "The value '${responseIdentifier}' of the 'responseIdentifier' attribute of the 'textEntryInteraction' element is not a valid identifier.";
                throw new UnmarshallingException($msg, $element, $e);
            }
            
            if (($base = self::getDOMElementAttributeAs($element, 'base', 'integer')) !== null) {
                $component->setBase($base);
            }
            
            if (($stringIdentifier = self::getDOMElementAttributeAs($element, 'stringIdentifier')) !== null) {
                $component->setStringIdentifier($stringIdentifier);
            }
            
            if (($expectedLength = self::getDOMElementAttributeAs($element, 'expectedLength', 'integer')) !== null) {
                $component->setExpectedLength($expectedLength);
            }
            
            if (($patternMask = self::getDOMElementAttributeAs($element, 'patternMask')) !== null) {
                $component->setPatternMask($patternMask);
            }
            
            if (($placeholderText = self::getDOMElementAttributeAs($element, 'placeholderText')) !== null) {
                $component->setPlaceholderText($placeholderText);
            }
            
            self::fillBodyElement($component, $element);
		    return $component;
        }
        else {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the 'textEntryInteraction' element.";
            throw new UnmarshallingException($msg, $element);
        }
	}
	
	public function getExpectedQtiClassName() {
		return 'textEntryInteraction';
	}
}
