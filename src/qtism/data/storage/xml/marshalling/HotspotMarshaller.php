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
 * Copyright (c) 2013-2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\common\collections\IdentifierCollection;
use qtism\common\utils\Version;
use qtism\data\storage\Utils;
use qtism\data\content\interactions\HotspotChoice;
use qtism\data\content\interactions\AssociableHotspot;
use qtism\common\datatypes\QtiShape;
use qtism\data\ShowHide;
use qtism\data\QtiComponent;
use \DOMElement;
use \InvalidArgumentException;
use \UnexpectedValueException;

/**
 * Marshalling/Unmarshalling implementation for HotspotChoice/AssociableHotspot.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class HotspotMarshaller extends Marshaller
{
    /**
	 * Marshall a HotspotChoice/AssociableHotspot object into a DOMElement object.
	 *
	 * @param \qtism\data\QtiComponent $component A HotspotChoice/AssociableHotspot object.
	 * @return \DOMElement The according DOMElement object.
	 * @throws \qtism\data\storage\xml\marshalling\MarshallingException
	 */
    protected function marshall(QtiComponent $component)
    {
        $version = $this->getVersion();
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());
        self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        self::setDOMElementAttribute($element, 'shape', QtiShape::getNameByConstant($component->getShape()));
        self::setDOMElementAttribute($element, 'coords', $component->getCoords()->__toString());

        if ($component->isFixed() === true) {
            self::setDOMElementAttribute($element, 'fixed', true);
        }

        if (Version::compare($version, '2.1.0', '>=') === true && $component->hasTemplateIdentifier() === true) {
            self::setDOMElementAttribute($element, 'templateIdentifier', $component->getTemplateIdentifier());
        }

        if (Version::compare($version, '2.1.0', '>=') === true && $component->getShowHide() === ShowHide::HIDE) {
            self::setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant($component->getShowHide()));
        }

        if ($component->hasHotspotLabel() === true) {
            self::setDOMElementAttribute($element, 'hotspotLabel', $component->getHotspotLabel());
        }
        
        if ($component instanceof AssociableHotspot) {
            
            if (Version::compare($version, '2.1.0', '<') === true) {
                $matchGroup = $component->getMatchGroup();
                if (count($matchGroup) > 0) {
                    self::setDOMElementAttribute($element, 'matchGroup', implode(' ', $matchGroup->getArrayCopy()));
                }
            }
            
            if (Version::compare($version, '2.1.0', '>=') === true) {
                if ($component->getMatchMin() !== 0) {
                    self::setDOMElementAttribute($element, 'matchMin', $component->getMatchMin());
                }
            }
        }
        
        if ($component instanceof AssociableHotspot) {
            self::setDOMElementAttribute($element, 'matchMax', $component->getMatchMax());
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
	 * Unmarshall a DOMElement object corresponding to a hotspotChoice/associableHotspot element.
	 *
	 * @param \DOMElement $element A DOMElement object.
	 * @return \qtism\data\QtiComponent A HotspotChoice/AssociableHotspot object.
	 * @throws \qtism\data\storage\xml\marshalling\UnmarshallingException
	 */
    protected function unmarshall(DOMElement $element)
    {
        $version = $this->getVersion();
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {

            if (($shape = $this->getDOMElementAttributeAs($element, 'shape')) !== null) {

                if (($coords = $this->getDOMElementAttributeAs($element, 'coords')) !== null) {

                    $shape = QtiShape::getConstantByName($shape);
                    if ($shape === false) {
                        $msg = "The value of the mandatory attribute 'shape' is not a value from the 'shape' enumeration.";
                        throw new UnmarshallingException($msg, $element);
                    }

                    try {
                        $coords = Utils::stringToCoords($coords, $shape);
                    } catch (UnexpectedValueException $e) {
                        $msg = "The coordinates 'coords' of element '" . $element->localName . "' are not valid regarding the shape they are bound to.";
                        throw new UnmarshallingException($msg, $element, $e);
                    } catch (InvalidArgumentException $e) {
                        $msg = "The coordinates 'coords' of element '" . $element->localName . "' could not be converted.";
                        throw new UnmarshallingException($msg, $element, $e);
                    }

                    if ($element->localName === 'hotspotChoice') {
                        $component = new HotspotChoice($identifier, $shape, $coords);
                    } else {
                        if (($matchMax = $this->getDOMElementAttributeAs($element, 'matchMax', 'integer')) !== null) {
                            $component = new AssociableHotspot($identifier, $matchMax, $shape, $coords);
                        } else {
                            $msg = "The mandatory attribute 'matchMax' is missing from element 'associableHotspot'.";
                            throw new UnmarshallingException($msg, $element);
                        }
                    }

                    if (($hotspotLabel = $this->getDOMElementAttributeAs($element, 'hotspotLabel')) !== null) {
                        $component->setHotspotLabel($hotspotLabel);
                    }

                    if (($fixed = $this->getDOMElementAttributeAs($element, 'fixed', 'boolean')) !== null) {
                        $component->setFixed($fixed);
                    }

                    if (($templateIdentifier = $this->getDOMElementAttributeAs($element, 'templateIdentifier')) !== null) {
                        $component->setTemplateIdentifier($templateIdentifier);
                    }

                    if (($showHide = $this->getDOMElementAttributeAs($element, 'showHide')) !== null) {

                        if (($showHide = ShowHide::getConstantByName($showHide)) !== false) {
                            $component->setShowHide($showHide);
                        } else {
                            $msg = "The value of the 'showHide' attribute of element '" . $element->localName . "' is not a value from the 'showHide' enumeration.";
                            throw new UnmarshallingException($msg, $element);
                        }
                    }
                    
                    if ($element->localName === 'associableHotspot') {
                        if (Version::compare($version, '2.1.0', '<') === true) {
                            if (($matchGroup = $this->getDOMElementAttributeAs($element, 'matchGroup')) !== null) {
                                $component->setMatchGroup(new IdentifierCollection(explode("\x20", $matchGroup)));
                            }
                        }
                        
                        if (Version::compare($version, '2.1.0', '>=') === true) {
                            if (($matchMin = $this->getDOMElementAttributeAs($element, 'matchMin', 'integer')) !== null) {
                                $component->setMatchMin($matchMin);
                            }
                        }
                    }

                    $this->fillBodyElement($component, $element);

                    return $component;
                } else {
                    $msg = "The mandatory attribute 'coords' is missing from element '" . $element->localName . "'.";
                    throw new UnmarshallingException($msg, $element);
                }
            } else {
                $msg = "The mandatory attribute 'shape' is missing from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'identifier' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
	 * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
	 */
    public function getExpectedQtiClassName()
    {
        return '';
    }
}
