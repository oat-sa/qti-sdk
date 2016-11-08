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
use qtism\data\content\TextOrVariableCollection;
use qtism\data\ShowHide;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use \DOMElement;
use \DOMCharacterData;
use \InvalidArgumentException;

/**
 * The Marshaller implementation for GapChoice(gapText/gapImg) elements of the content model.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class GapChoiceMarshaller extends ContentMarshaller
{
    /**
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::unmarshallChildrenKnown()
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        $version = $this->getVersion();
        
        if (($identifier = self::getDOMElementAttributeAs($element, 'identifier')) !== null) {

            if (($matchMax = self::getDOMElementAttributeAs($element, 'matchMax', 'integer')) !== null) {

                $fqClass = $this->lookupClass($element);

                if ($element->localName === 'gapImg') {
                    if (count($children) === 1) {
                        $component = new $fqClass($identifier, $matchMax, $children[0]);
                    } else {
                        $msg = "A 'gapImg' element must contain a single 'object' element, " . count($children) . " given.";
                        throw new UnmarshallingException($msg, $element);
                    }
                } else {
                    $component = new $fqClass($identifier, $matchMax);
                }

                if (Version::compare($version, '2.1.0', '>=') === true && ($matchMin = self::getDOMElementAttributeAs($element, 'matchMin', 'integer')) !== null) {
                    $component->setMatchMin($matchMin);
                }

                if (($fixed = self::getDOMElementAttributeAs($element, 'fixed', 'boolean')) !== null) {
                    $component->setFixed($fixed);
                }

                if (Version::compare($version, '2.1.0', '>=') === true && ($templateIdentifier = self::getDOMElementAttributeAs($element, 'templateIdentifier')) !== null) {
                    $component->setTemplateIdentifier($templateIdentifier);
                }

                if (Version::compare($version, '2.1.0', '>=') === true && ($showHide = self::getDOMElementAttributeAs($element, 'showHide')) !== null) {
                    $component->setShowHide(ShowHide::getConstantByName($showHide));
                }

                if ($element->localName === 'gapText') {
                   
                    if (Version::compare($version, '2.1.0', '<') === true && $children->exclusivelyContainsComponentsWithClassName('textRun') === false) {
                        $msg = "A 'gapText' element must only contain text. Children elements found.";
                        throw new UnmarshallingException($msg, $element);
                    }
                    
                    try {
                        $component->setContent(new TextOrVariableCollection($children->getArrayCopy()));
                    } catch (InvalidArgumentException $e) {
                        $msg = "Invalid content in 'gapText' element.";
                        throw new UnmarshallingException($msg, $element, $e);
                    }
                } else {
                    if (($objectLabel = self::getDOMElementAttributeAs($element, 'objectLabel')) !== null) {
                        $component->setObjectLabel($objectLabel);
                    }
                }
                
                if (Version::compare($version, '2.1.0', '<') === true && ($matchGroup = self::getDOMElementAttributeAs($element, 'matchGroup')) !== null) {
                    $component->setMatchGroup(new IdentifierCollection(explode("\x20", $matchGroup)));
                }

                $this->fillBodyElement($component, $element);

                return $component;
            } else {
                $msg = "The mandatory 'matchMax' attribute is missing from the 'simpleChoice' element.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory 'identifier' attribute is missing from the 'simpleChoice' element.";
            throw new UnmarshallingException($msg, $element);
        }

    }

    /**
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::marshallChildrenKnown()
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $version = $this->getVersion();
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());
        $this->fillElement($element, $component);

        self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        self::setDOMElementAttribute($element, 'matchMax', $component->getMatchMax());

        if (Version::compare($version, '2.1.0', '>=') === true && $component->getMatchMin() !== 0) {
            self::setDOMElementAttribute($element, 'matchMin', $component->getMatchMin());
        }

        if ($component->isFixed() === true) {
            self::setDOMElementAttribute($element, 'fixed', true);
        }

        if (Version::compare($version, '2.1.0', '>=') === true && $component->hasTemplateIdentifier() === true) {
            self::setDOMElementAttribute($element, 'templateIdentifier', $component->getTemplateIdentifier());
        }

        if (Version::compare($version, '2.1.0', '>=') === true && $component->getShowHide() !== ShowHide::SHOW) {
            self::setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant(ShowHide::HIDE));
        }

        if ($element->localName === 'gapImg' && $component->hasObjectLabel() === true) {
            self::setDOMElementAttribute($element, 'objectLabel', $component->getObjectLabel());
        }

        foreach ($elements as $e) {
            if ($element->localName === 'gapImg') {
                $element->appendChild($e);
            }
            else {
                // 'gapText'...   
                if (Version::compare($version, '2.1.0', '>=') || (Version::compare($version, '2.1.0', '<') && $e instanceof DOMCharacterData)) {
                    // In QTI 2.0, only plain text is allowed...
                    $element->appendChild($e);
                }
                
            }
        }
        
        if (Version::compare($version, '2.1.0', '<') === true) {
            $matchGroup = $component->getMatchGroup();
            if (count($matchGroup) > 0) {
                self::setDOMElementAttribute($element, 'matchGroup', implode(' ', $matchGroup->getArrayCopy()));
            }
        }

        return $element;
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\ContentMarshaller::setLookupClasses()
     */
    protected function setLookupClasses()
    {
        $this->lookupClasses = array("qtism\\data\\content\\interactions");
    }
}
