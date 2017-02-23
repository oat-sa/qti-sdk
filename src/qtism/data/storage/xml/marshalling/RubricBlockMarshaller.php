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

namespace qtism\data\storage\xml\marshalling;

use qtism\data\content\FlowStaticCollection;
use qtism\data\content\FlowStatic;
use qtism\data\content\Block;
use qtism\data\content\Stylesheet;
use qtism\data\content\StylesheetCollection;
use qtism\data\QtiComponent;
use qtism\data\content\RubricBlock;
use qtism\data\ViewCollection;
use qtism\data\View;
use \DOMElement;
use \DOMText;

/**
 * Marshalling/Unmarshalling implementation for rubrickBlock.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RubricBlockMarshaller extends Marshaller
{
    /**
	 * Marshall a RubricBlock object into a DOMElement object.
	 *
	 * @param \qtism\data\QtiComponent $component A RubricBlock object.
	 * @return \DOMElement The according DOMElement object.
	 */
    protected function marshall(QtiComponent $component)
    {
        $element = static::getDOMCradle()->createElement($component->getQtiClassName());

        $arrayViews = array();
        foreach ($component->getViews() as $view) {
            $key = array_search($view, View::asArray());
            // replace '_' by the space char.
            $arrayViews[] = strtolower(str_replace("\xf2", "\x20", $key));
        }

        if (count($arrayViews) > 0) {
            $this->setDOMElementAttribute($element, 'view', implode("\x20", $arrayViews));
        }

        if ($component->getUse() != '') {
            $this->setDOMElementAttribute($element, 'use', $component->getUse());
        }

        if ($component->hasXmlBase() === true) {
            static::setXmlBase($element, $component->getXmlBase());
        }

        foreach ($component->getContent() as $block) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($block);
            $element->appendChild($marshaller->marshall($block));
        }

        foreach ($component->getStylesheets() as $stylesheet) {
            $stylesheetMarshaller = $this->getMarshallerFactory()->createMarshaller($stylesheet);
            $element->appendChild($stylesheetMarshaller->marshall($stylesheet));
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
	 * Unmarshall a DOMElement object corresponding to a QTI rubrickBlock element.
	 *
	 * @param \DOMElement $element A DOMElement object.
	 * @return \qtism\data\content\RubricBlock A RubricBlock object.
	 * @throws \qtism\data\storage\xml\marshalling\UnmarshallingException If the mandatory attribute 'href' is missing from $element.
	 */
    protected function unmarshall(DOMElement $element)
    {
        // First we retrieve the mandatory views.
        if (($value = $this->getDOMElementAttributeAs($element, 'view', 'string')) !== null) {
            $viewsArray = explode("\x20", $value);
            $viewsCollection = new ViewCollection();
            $ref = View::asArray();

            foreach ($viewsArray as $viewString) {
                $key = strtoupper(str_replace("\xf2", "\x20", $viewString));
                if (array_key_exists($key, $ref)) {
                    $viewsCollection[] = $ref[$key];
                }
            }

            $object = new RubricBlock($viewsCollection);

            if (($value = $this->getDOMElementAttributeAs($element, 'use', 'string')) !== null) {
                $object->setUse($value);
            }

            if (($xmlBase = static::getXmlBase($element)) !== false) {
                $object->setXmlBase($xmlBase);
            }

            $stylesheets = new StylesheetCollection();
            $content = new FlowStaticCollection();

            foreach (self::getChildElementsByTagName($element, 'apipAccessibility', true, true) as $elt) {

                if ($elt instanceof DOMText) {
                    $elt = self::getDOMCradle()->createElement('textRun', $elt->wholeText);
                }

                $marshaller = $this->getMarshallerFactory()->createMarshaller($elt);
                $cpt = $marshaller->unmarshall($elt);

                if ($cpt instanceof Stylesheet) {
                    $stylesheets[] = $cpt;
                } elseif ($cpt instanceof FlowStatic && !in_array($cpt->getQtiClassName(), array('hottext', 'feedbackBlock', 'feedbackInline', 'rubricBlock', 'infoControl'))) {
                    $content[] = $cpt;
                } else {
                    $msg = "The 'rubricBlock' cannot contain '" . $cpt->getQtiClassName() . "' elements.";
                    throw new UnmarshallingException($msg, $element);
                }
            }

            $object->setStylesheets($stylesheets);
            $object->setContent($content);

            $this->fillBodyElement($object, $element);

            return $object;
        } else {
            $msg = "The mandatory attribute 'views' is missing.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
	 * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
	 */
    public function getExpectedQtiClassName()
    {
        return 'rubricBlock';
    }
}
