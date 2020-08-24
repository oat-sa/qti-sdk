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

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use InvalidArgumentException;
use qtism\common\utils\Version;
use qtism\data\QtiComponent;
use qtism\data\state\ExternalScored;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\View;
use qtism\data\ViewCollection;

/**
 * Marshalling/Unmarshalling implementation for outcomeDeclaration.
 */
class OutcomeDeclarationMarshaller extends VariableDeclarationMarshaller
{
    /**
     * Marshall an OutcomeDeclaration object into a DOMElement object.
     *
     * @param QtiComponent $component An OutcomeDeclaration object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = parent::marshall($component);
        $version = $this->getVersion();

        // deal with views.
        // !!! If $arrayViews contain all possible views, it means that the treated
        // !!! outcome is relevant to all views, as per QTI 2.1 spec.
        if (Version::compare($version, '2.1.0', '>=') === true && !in_array($component->getViews()->getArrayCopy(), View::asArray())) {
            $arrayViews = [];
            foreach ($component->getViews() as $view) {
                $arrayViews[] = View::getNameByConstant($view);
            }

            if (count($arrayViews) > 0) {
                $this->setDOMElementAttribute($element, 'view', implode("\x20", $arrayViews));
            }
        }

        // deal with interpretation.
        if ($component->getInterpretation() !== '') {
            $this->setDOMElementAttribute($element, 'interpretation', $component->getInterpretation());
        }

        // deal with long interpretation.
        if ($component->getLongInterpretation() !== '') {
            $this->setDOMElementAttribute($element, 'longInterpretation', $component->getLongInterpretation());
        }

        // Deal with normal maximum.
        if ($component->getNormalMaximum() !== false) {
            $this->setDOMElementAttribute($element, 'normalMaximum', $component->getNormalMaximum());
        }

        // Deal with normal minimum.
        if (Version::compare($version, '2.1.0', '>=') === true && $component->getNormalMinimum() !== false) {
            $this->setDOMElementAttribute($element, 'normalMinimum', $component->getNormalMinimum());
        }

        // Deal with mastery value.
        if (Version::compare($version, '2.1.0', '>=') === true && $component->getMasteryValue() !== false) {
            $this->setDOMElementAttribute($element, 'masteryValue', $component->getMasteryValue());
        }

        // Deal with lookup table.
        if ($component->getLookupTable() !== null) {
            $lookupTableMarshaller = $this->getMarshallerFactory()->createMarshaller($component->getLookupTable(), [$component->getBaseType()]);
            $element->appendChild($lookupTableMarshaller->marshall($component->geTLookupTable()));
        }

        if (Version::compare($version, '2.2.0', '>=') === true && $component->isExternallyScored()) {
            $this->setDOMElementAttribute($element, 'externalScored', ExternalScored::getNameByConstant($component->getExternalScored()));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI outcomeDeclaration element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent An OutcomeDeclaration object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        try {
            $version = $this->getVersion();
            $baseComponent = parent::unmarshall($element);
            $object = new OutcomeDeclaration($baseComponent->getIdentifier());
            $object->setBaseType($baseComponent->getBaseType());
            $object->setCardinality($baseComponent->getCardinality());
            $object->setDefaultValue($baseComponent->getDefaultValue());

            // Set external scored attribute
            if (($externalScored = $this->getDOMElementAttributeAs($element, 'externalScored')) != null) {
                $object->setExternalScored(ExternalScored::getConstantByName($externalScored));
            }

            // deal with views.
            if (Version::compare($version, '2.1.0', '>=') === true && ($views = $this->getDOMElementAttributeAs($element, 'view')) != null) {
                $viewCollection = new ViewCollection();
                foreach (explode("\x20", $views) as $viewName) {
                    $viewCollection[] = View::getConstantByName($viewName);
                }

                $object->setViews($viewCollection);
            }

            // deal with interpretation.
            if (($interpretation = $this->getDOMElementAttributeAs($element, 'interpretation')) != null) {
                $object->setInterpretation($interpretation);
            }

            // deal with longInterpretation.
            if (($longInterpretation = $this->getDOMElementAttributeAs($element, 'longInterpretation')) != null) {
                $object->setLongInterpretation($longInterpretation);
            }

            // deal with normalMaximum.
            if (($normalMaximum = $this->getDOMElementAttributeAs($element, 'normalMaximum', 'float')) !== null) {
                $object->setNormalMaximum($normalMaximum);
            }

            // deal with normalMinimum.
            if (Version::compare($version, '2.1.0', '>=') === true && ($normalMinimum = $this->getDOMElementAttributeAs($element, 'normalMinimum', 'float')) !== null) {
                $object->setNormalMinimum($normalMinimum);
            }

            // deal with matseryValue.
            if (Version::compare($version, '2.1.0', '>=') === true && ($masteryValue = $this->getDOMElementAttributeAs($element, 'masteryValue', 'float')) !== null) {
                $object->setMasteryValue($masteryValue);
            }

            if (($externalScored = static::getDOMElementAttributeAs($element, 'externalScored')) !== null) {
                $object->setExternalScored(ExternalScored::getConstantByName($externalScored));
            }

            // deal with lookupTable.
            $interpolationTables = $element->getElementsByTagName('interpolationTable');
            $matchTable = $element->getElementsByTagName('matchTable');

            if ($interpolationTables->length == 1 || $matchTable->length == 1) {
                // we have a lookupTable defined.
                $lookupTable = null;

                if ($interpolationTables->length == 1) {
                    $lookupTable = $interpolationTables->item(0);
                } else {
                    $lookupTable = $matchTable->item(0);
                }

                $lookupTableMarshaller = $this->getMarshallerFactory()->createMarshaller($lookupTable, [$object->getBaseType()]);
                $object->setLookupTable($lookupTableMarshaller->unmarshall($lookupTable));
            }

            return $object;
        } catch (InvalidArgumentException $e) {
            $msg = 'An unexpected error occurred while unmarshalling the outcomeDeclaration.';
            throw new UnmarshallingException($msg, $element, $e);
        }
    }

    public function getExpectedQtiClassName()
    {
        return 'outcomeDeclaration';
    }
}
