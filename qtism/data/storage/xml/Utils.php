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

namespace qtism\data\storage\xml;

use DOMDocument;
use DOMElement;
use qtism\common\utils\Version;
use qtism\data\storage\xml\versions\QtiVersion200;
use qtism\data\storage\xml\versions\QtiVersion210;
use qtism\data\storage\xml\versions\QtiVersion211;
use qtism\data\storage\xml\versions\QtiVersion220;
use qtism\data\storage\xml\versions\QtiVersion221;
use qtism\data\storage\xml\versions\QtiVersion222;
use qtism\data\storage\xml\versions\QtiVersion300;
use qtism\data\storage\xml\versions\ResultVersion21;
use qtism\data\storage\xml\versions\ResultVersion22;
use SplStack;

/**
 * A class providing XML utility methods.
 */
class Utils
{
    /**
     * Get the location of an XML Schema Definition file from a given namespace.
     *
     * This utility method enables you to know what is the location of an XML Schema Definition
     * file to be used to validate a $document for a given target namespace.
     *
     * @param DOMDocument $document A DOMDocument object.
     * @param string $namespaceUri A Namespace URI you want to know the related XSD file location.
     * @return boolean|string False if no location can be found for $namespaceUri, otherwise the location of the XSD file.
     */
    public static function getXsdLocation(DOMDocument $document, $namespaceUri)
    {
        $root = $document->documentElement;
        $location = false;

        if (empty($root) === false) {
            $schemaLocation = $root->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation');
            if (empty($schemaLocation) === false) {
                $parts = preg_split('/\s+/', $schemaLocation);

                // Look at pairs...
                $partsCount = count($parts);
                for ($i = 0; $i < $partsCount; $i += 2) {
                    if (isset($parts[$i + 1]) && $parts[$i] === $namespaceUri) {
                        $location = $parts[$i + 1];
                        break;
                    }
                }
            }
        }

        return $location;
    }

    /**
     * Change the name of $element into $name.
     *
     * @param DOMElement $element A DOMElement object you want to change the name.
     * @param string $name The new name of $element.
     *
     * @return DOMElement
     */
    public static function changeElementName(DOMElement $element, $name)
    {
        $newElement = $element->ownerDocument->createElement($name);

        foreach ($element->childNodes as $child) {
            $child = $element->ownerDocument->importNode($child, true);
            $newElement->appendChild($child);
        }

        foreach ($element->attributes as $attrName => $attrNode) {
            if ($attrNode->namespaceURI === null) {
                $newElement->setAttribute($attrName, $attrNode->value);
            } else {
                $newElement->setAttributeNS($attrNode->$namespaceURI, $attrNode->prefix . ':' . $attrName, $attrNode->value);
            }
        }

        $newElement->ownerDocument->replaceChild($newElement, $element);

        return $newElement;
    }

    /**
     * Anonimize a given DOMElement. By 'anonimize', we mean remove
     * all namespace membership of an element and its child nodes.
     *
     * For instance, <m:math display="inline"><m:mi>x</m:mi></m:math> becomes
     * <math display="inline"><mi>x</mi></math>.
     *
     * @param DOMElement $element The DOMElement to be anonimized.
     * @return DOMElement The anonimized DOMElement copy of $element.
     */
    public static function anonimizeElement(DOMElement $element)
    {
        $stack = new SplStack();
        $traversed = [];
        $children = [];

        $stack->push($element);

        while ($stack->count() > 0) {
            $node = $stack->pop();

            if ($node->nodeType === XML_ELEMENT_NODE && $node->childNodes->length > 0 && in_array($node, $traversed, true) === false) {
                array_push($traversed, $node);
                $stack->push($node);

                for ($i = 0; $i < $node->childNodes->length; $i++) {
                    $stack->push($node->childNodes->item($i));
                }
            } elseif ($node->nodeType === XML_ELEMENT_NODE && $node->childNodes->length > 0 && in_array($node, $traversed, true) === true) {
                // Build hierarchical node copy from the current node. All the attributes
                // of $node must be copied into $newNode.
                $newNode = $node->ownerDocument->createElement($node->localName);

                // Copy all attributes.
                foreach ($node->attributes as $attr) {
                    $newNode->setAttribute($attr->localName, $attr->value);
                }

                for ($i = 0; $i < $node->childNodes->length; $i++) {
                    $newNode->appendChild(array_pop($children));
                }

                array_push($children, $newNode);
            } else {
                array_push($children, $node->cloneNode());
            }
        }

        return $children[0];
    }

    /**
     * Import all the child nodes of DOMElement $from to DOMElement $into.
     *
     * @param DOMElement $from The source DOMElement.
     * @param DOMElement $into The target DOMElement.
     * @param boolean $deep Whether or not to import the whole node hierarchy.
     */
    public static function importChildNodes(DOMElement $from, DOMElement $into, $deep = true)
    {
        for ($i = 0; $i < $from->childNodes->length; $i++) {
            $node = $into->ownerDocument->importNode($from->childNodes->item($i), $deep);
            $into->appendChild($node);
        }
    }

    /**
     * Import (gracefully i.e. by respecting namespaces) the attributes of DOMElement $from to
     * DOMElement $into.
     *
     * @param DOMElement $from The source DOMElement.
     * @param DOMElement $into The target DOMElement.
     */
    public static function importAttributes(DOMElement $from, DOMElement $into)
    {
        for ($i = 0; $i < $from->attributes->length; $i++) {
            $attr = $from->attributes->item($i);

            if ($attr->localName !== 'schemaLocation') {
                if (empty($attr->namespaceURI) === false) {
                    $into->setAttributeNS($attr->namespaceURI, $attr->prefix . ':' . $attr->localName, $attr->value);
                } else {
                    $into->setAttribute($attr->localName, $attr->value);
                }
            }
        }
    }
}
