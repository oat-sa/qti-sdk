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

use qtism\common\utils\Version;
use qtism\data\QtiComponent;
use qtism\data\state\TemplateDeclaration;
use \DOMElement;
use \InvalidArgumentException;

/**
 * Marshalling/Unmarshalling implementation for templateDeclaration.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TemplateDeclarationMarshaller extends VariableDeclarationMarshaller
{
    /**
	 * Marshall a TemplateDeclaration object into a DOMElement object.
	 *
	 * @param \qtism\data\QtiComponent $component A TemplateDeclaration object.
	 * @return \DOMElement The according DOMElement object.
	 */
    protected function marshall(QtiComponent $component)
    {
        $element = parent::marshall($component);
        $version = $this->getVersion();
        
        if ($component->isParamVariable() === true) {
            $this->setDOMElementAttribute($element, 'paramVariable', true);
        } elseif (Version::compare($version, '2.0.0', '==') === true && $component->isParamVariable() === false) {
            $this->setDOMElementAttribute($element, 'paramVariable', false);
        }

        if ($component->isMathVariable() === true) {
            $this->setDOMElementAttribute($element, 'mathVariable', true);
        } elseif (Version::compare($version, '2.0.0', '==') === true && $component->isMathVariable() === false) {
            $this->setDOMElementAttribute($element, 'mathVariable', false);
        }

        return $element;
    }

    /**
	 * Unmarshall a DOMElement object corresponding to a QTI templateDeclaration element.
	 *
	 * @param \DOMElement $element A DOMElement object.
	 * @return \qtism\data\QtiComponent A TemplateDeclaration object.
	 * @throws \qtism\data\storage\xml\marshalling\UnmarshallingException
	 */
    protected function unmarshall(DOMElement $element)
    {
        try {
            $baseComponent = parent::unmarshall($element);
            $object = new TemplateDeclaration($baseComponent->getIdentifier());
            $object->setBaseType($baseComponent->getBaseType());
            $object->setCardinality($baseComponent->getCardinality());
            $object->setDefaultValue($baseComponent->getDefaultValue());

            $version = $this->getVersion();
            
            if (($paramVariable = $this->getDOMElementAttributeAs($element, 'paramVariable', 'boolean')) !== null) {
                $object->setParamVariable($paramVariable);
            } elseif (Version::compare($version, '2.0.0', '==') === true) {
                $msg = "The mandatory attribute 'paramVariable' is missing from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }

            if (($mathVariable = $this->getDOMElementAttributeAs($element, 'mathVariable', 'boolean')) !== null) {
                $object->setMathVariable($mathVariable);
            } elseif (Version::compare($version, '2.0.0', '==') === true) {
                $msg = "The mandatory attribute 'mathVariable' is missing from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }

            return $object;
        } catch (InvalidArgumentException $e) {
            $msg = "An unexpected error occured while unmarshalling the templateDeclaration.";
            throw new UnmarshallingException($msg, $element, $e);
        }
    }

    /**
	 * @see \qtism\data\storage\xml\marshalling\VariableDeclarationMarshaller::getExpectedQtiClassName()
	 */
    public function getExpectedQtiClassName()
    {
        return 'templateDeclaration';
    }
}
