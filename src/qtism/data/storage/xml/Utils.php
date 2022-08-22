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
use InvalidArgumentException;
use LibXMLError;
use qtism\common\enums\Enumeration;
use SimpleXMLElement;
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
     * @return bool|string False if no location can be found for $namespaceUri, otherwise the location of the XSD file.
     */
    public static function getXsdLocation(DOMDocument $document, $namespaceUri)
    {
        $root = $document->documentElement;
        $location = false;

        if ($root !== null) {
            $schemaLocation = trim(
                $root->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation')
            );
            if ($schemaLocation !== '') {
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
                $newElement->setAttributeNS($attrNode->namespaceURI, $attrNode->prefix . ':' . $attrName, $attrNode->value);
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
                $traversed[] = $node;
                $stack->push($node);

                for ($i = 0; $i < $node->childNodes->length; $i++) {
                    $stack->push($node->childNodes->item($i));
                }
            } elseif ($node->nodeType === XML_ELEMENT_NODE && $node->childNodes->length > 0 && in_array($node, $traversed, true)) {
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

                $children[] = $newNode;
            } else {
                $children[] = $node->cloneNode();
            }
        }

        return $children[0];
    }

    /**
     * Import all the child nodes of DOMElement $from to DOMElement $into.
     *
     * @param DOMElement $from The source DOMElement.
     * @param DOMElement $into The target DOMElement.
     * @param bool $deep Whether or not to import the whole node hierarchy.
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

    /**
     * Escape XML Special characters from a given string.
     *
     * The list below describe each escaped character and its replacement.
     *
     * * " --> &quot;
     * * ' --> &apos;
     * * < --> &lt;
     * * > --> $gt;
     * * & --> &amp;
     *
     * @param string $string An input string.
     * @param bool $isAttribute Whether or not to escape ', >, < which do not have to be escaped in attributes.
     * @return string An escaped string.
     */
    public static function escapeXmlSpecialChars($string, $isAttribute = false)
    {
        if ($isAttribute !== false) {
            return str_replace(['&', '"'], ['&amp;', '&quot;'], $string);
        }

        $fullSearch = ['&', '"', "'", '<', '>'];
        $fullReplace = ['&amp;', '&quot;', '&apos;', '&lt;', '&gt;'];

        return str_replace($fullSearch, $fullReplace, $string);
    }

    /**
     * Web Component friendly version of a QTI name (without qti-).
     *
     * This method returns the Web Component friendly version of a QTI attribute name.
     *
     * Example: "minChoices" becomes "min-choices".
     *
     * @param string $qtiName
     * @return string
     */
    public static function webComponentFriendlyAttributeName($qtiName)
    {
        return strtolower(preg_replace('/([A-Z])/', '-$1', $qtiName));
    }

    /**
     * Web Component friendly version of a QTI name (with qti-).
     *
     * This method returns the Web Component friendly version of a QTI class name.
     *
     * Example: "choiceInteraction" becomes "qti-choice-interaction".
     *
     * @param string $qtiName
     * @return string
     */
    public static function webComponentFriendlyClassName($qtiName)
    {
        return 'qti-' . self::webComponentFriendlyAttributeName($qtiName);
    }

    /**
     * QTI friendly name of a Web Component friendly name.
     *
     * This method returns the QTI friendly name of a Web Component friendly name.
     *
     * Example: "qti-choice-interaction" becomes "choiceInteraction".
     *
     * @param string $wcName
     * @return string
     */
    public static function qtiFriendlyName($wcName)
    {
        $qtiName = strtolower($wcName);
        $qtiName = preg_replace('/^qti-/', '', $qtiName);

        return lcfirst(str_replace('-', '', ucwords($qtiName, '-')));
    }

    /**
     * Get the attribute value of a given DOMElement object, cast in a given datatype.
     *
     * @param DOMElement $element The element the attribute you want to retrieve the value is bound to.
     * @param string $attribute The attribute name.
     * @param string $datatype The returned datatype. Accepted values are 'string', 'integer', 'float', 'double' and 'boolean'.
     * @return mixed The attribute value with the provided $datatype, or null if the attribute does not exist in $element.
     * @throws InvalidArgumentException If $datatype is not in the range of possible values.
     */
    public static function getDOMElementAttributeAs(DOMElement $element, string $attribute, $datatype = 'string')
    {
        $attr = $element->getAttribute($attribute);

        if ($attr === '') {
            return null;
        }

        switch ($datatype) {
            case 'string':
                return htmlspecialchars_decode($attr);

            case 'integer':
                return (int)$attr;

            case 'double':
            case 'float':
                return (float)$attr;

            case 'boolean':
                return $attr === 'true';
        }

        if (in_array(Enumeration::class, class_implements($datatype), true)) {
            /** @var Enumeration $datatype */
            if ($attr !== null) {
                $constant = $datatype::getConstantByName($attr);
                // Returns the original value when it's unknown in the enumeration.
                if ($constant === false) {
                    return $attr;
                }
                $attr = $constant;
            }

            return $attr;
        }

        throw new InvalidArgumentException("Unknown datatype '${datatype}'.");
    }

    /**
     * Set the attribute value of a given DOMElement object. Boolean values will be transformed
     *
     * @param DOMElement $element A DOMElement object.
     * @param string $attribute An XML attribute name.
     * @param mixed $value A given value.
     */
    public static function setDOMElementAttribute(DOMElement $element, string $attribute, $value)
    {
        $element->setAttribute($attribute, self::valueAsString($value));
    }

    /**
     * Set the node value of a given DOMElement object. Boolean values will be transformed as 'true'|'false'.
     *
     * @param DOMElement $element A DOMElement object.
     * @param mixed $value A given value.
     */
    public static function setDOMElementValue(DOMElement $element, $value)
    {
        $element->nodeValue = self::valueAsString($value);
    }

    /**
     * Converts value to an XML insertable string.
     * Boolean is converted to either 'true' or 'false' string.
     * Other variable types are optionally using string conversion.
     *
     * @param mixed $value
     * @return string
     */
    public static function valueAsString($value)
    {
        if (is_bool($value)) {
            return $value === true ? 'true' : 'false';
        }
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }

    /**
     * Get the child elements of a given element by tag name. This method does
     * not behave like DOMElement::getElementsByTagName. It only returns the direct
     * child elements that matches $tagName but does not go recursive.
     *
     * @param DOMElement $element A DOMElement object.
     * @param mixed $tagName The name of the tags you would like to retrieve or an array of tags to match.
     * @param bool $exclude (optional) Whether the $tagName parameter must be considered as a blacklist.
     * @param bool $withText (optional) Whether text nodes must be returned or not.
     * @return array An array of DOMElement objects.
     */
    public static function getChildElementsByTagName($element, $tagName, $exclude = false, $withText = false)
    {
        if (!is_array($tagName)) {
            $tagName = [$tagName];
        }

        $rawElts = self::getChildElements($element, $withText);
        $returnValue = [];

        foreach ($rawElts as $elt) {
            if (in_array($elt->localName, $tagName) === !$exclude) {
                $returnValue[] = $elt;
            }
        }

        return $returnValue;
    }

    /**
     * Get the children DOM Nodes with nodeType attribute equals to XML_ELEMENT_NODE.
     *
     * @param DOMElement $element A DOMElement object.
     * @param bool $withText Whether text nodes must be returned or not.
     * @return array An array of DOMNode objects.
     */
    public static function getChildElements($element, $withText = false)
    {
        $children = $element->childNodes;
        $returnValue = [];

        for ($i = 0; $i < $children->length; $i++) {
            if ($children->item($i)->nodeType === XML_ELEMENT_NODE || ($withText === true && ($children->item($i)->nodeType === XML_TEXT_NODE || $children->item($i)->nodeType === XML_CDATA_SECTION_NODE))) {
                $returnValue[] = $children->item($i);
            }
        }

        return $returnValue;
    }

    /**
     * Removes namespaces defined on non-root element when they are already
     * defined on the root element.
     *
     * @param string $subject
     * @param array $redundantNamespaces
     * @return string
     */
    public static function cleanRedundantNamespaces(string $subject, array $redundantNamespaces): string
    {
        foreach ($redundantNamespaces as $prefix => $namespace) {
            $subject = self::removeAllButFirstOccurrence($subject, ' xmlns:' . $prefix . '="' . $namespace . '"');
        }
        return $subject;
    }

    /**
     * Removes all but first occurrences of a string within a string.
     *
     * @param string $subject
     * @param string $toRemove
     * @return string
     */
    public static function removeAllButFirstOccurrence(string $subject, string $toRemove): string
    {
        $firstPosition = strpos($subject, $toRemove);
        if ($firstPosition !== false) {
            $begin = substr($subject, 0, $firstPosition + strlen($toRemove));
            $end = substr($subject, $firstPosition + strlen($toRemove));
            $subject = $begin . str_replace($toRemove, '', $end);
        }
        return $subject;
    }

    /**
     * Finds all the custom namespaces defined in the xml payload.
     *
     * @param string $xml
     * @return array
     */
    public static function findExternalNamespaces(string $xml): array
    {
        $doc = new SimpleXMLElement($xml);
        return array_filter(
            $doc->getDocNamespaces(),
            static function ($key) {
                return $key !== '' && $key !== 'xsi';
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param callable $command
     * @param string $exceptionMessage
     * @param int $exceptionCode
     * @throws XmlStorageException
     */
    public static function executeSafeXmlCommand(
        callable $command,
        string $exceptionMessage,
        int $exceptionCode
    ): void {
        // Disable xml warnings and errors and fetch error information as needed.
        $oldErrorConfig = libxml_use_internal_errors(true);
        $command();
        $libXmlErrors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($oldErrorConfig);

        if (count($libXmlErrors)) {
            // Formats the xml errors and filters out the warning for duplicate schema inclusion.
            $formattedErrors = self::formatLibXmlErrors($libXmlErrors);
            if ($formattedErrors !== '') {
                throw new XmlStorageException(
                    "${exceptionMessage}:\n${formattedErrors}",
                    $exceptionCode,
                    null,
                    new LibXmlErrorCollection($libXmlErrors)
                );
            }
        }
    }

    /**
     * Format some $libXmlErrors into an array of strings instead of an array of arrays.
     *
     * @param LibXMLError[] $libXmlErrors
     * @return string
     */
    protected static function formatLibXmlErrors(array $libXmlErrors): string
    {
        $formattedErrors = [];

        foreach ($libXmlErrors as $error) {
            switch ($error->level) {
                case LIBXML_ERR_WARNING:
                    // Since QTI 2.2, some schemas are imported multiple times.
                    // Xerces does not produce errors, but libxml does...
                    if (preg_match('/Skipping import of schema located/ui', $error->message) === 0) {
                        $formattedErrors[] = 'Warning: ' . trim($error->message) . ' at ' . $error->line . ':' . $error->column . '.';
                    }

                    break;

                case LIBXML_ERR_ERROR:
                    $formattedErrors[] = 'Error: ' . trim($error->message) . ' at ' . $error->line . ':' . $error->column . '.';
                    break;

                case LIBXML_ERR_FATAL:
                    $formattedErrors[] = 'Fatal Error: ' . trim($error->message) . ' at ' . $error->line . ':' . $error->column . '.';
                    break;
            }
        }

        $formattedErrors = implode("\n", $formattedErrors);

        return $formattedErrors;
    }
}
