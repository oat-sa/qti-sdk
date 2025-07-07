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

namespace qtism\data\content\interactions;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\QtiComponentCollection;
use qtism\data\state\ResponseValidityConstraint;

/**
 * From IMS QTI:
 *
 * A textEntry interaction is an inlineInteraction that obtains a simple piece of
 * text from the candidate. Like inlineChoiceInteraction, the delivery engine must
 * allow the candidate to review their choice within the context of the surrounding text.
 *
 * The textEntryInteraction must be bound to a response variable with single cardinality only.
 * The baseType must be one of string, integer or float.
 */
class TextEntryInteraction extends InlineInteraction implements StringInteraction
{
    /**
     * From IMS QTI:
     *
     * If the string interaction is bound to a numeric response variable then the base attribute
     * must be used to set the number base in which to interpret the value entered by the candidate.
     *
     * @var int
     * @qtism-bean-property
     */
    private $base = 10;

    /**
     * From IMS QTI:
     *
     * If the string interaction is bound to a numeric response variable then the actual string
     * entered by the candidate can also be captured by binding the interaction to a second
     * response variable (of base-type string).
     *
     * @var string
     * @qtism-bean-property
     */
    private $stringIdentifier = '';

    /**
     * From IMS QTI:
     *
     * The expectedLength attribute provides a hint to the candidate as to the expected overall
     * length of the desired response measured in number of characters. A Delivery Engine should
     * use the value of this attribute to set the size of the response box, where applicable.
     * This is not a validity constraint.
     *
     * @var int|null
     * @qtism-bean-property
     */
    private $expectedLength;

    /**
     * From IMS QTI:
     *
     * If given, the pattern mask specifies a regular expression that the candidate's response
     * must match in order to be considered valid. The regular expression language used is
     * defined in Appendix F of [XML_SCHEMA2]. Care is needed to ensure that the format of
     * the required input is clear to the candidate, especially when validity checking of
     * responses is required for progression through a test. This could be done by providing
     * an illustrative sample response in the prompt, for example.
     *
     * @var string
     * @qtism-bean-property
     */
    private $patternMask = '';

    /**
     * From IMS QTI:
     *
     * In visual environments, string interactions are typically represented by empty boxes
     * into which the candidate writes or types. However, in speech based environments it
     * is helpful to have some placeholder text that can be used to vocalize the interaction.
     * Delivery engines should use the value of this attribute (if provided) instead of their
     * default placeholder text when this is required. Implementors should be aware of the
     * issues concerning the use of default values described in the section on Response Variables.
     *
     * @var string
     * @qtism-bean-property
     */
    private $placeholderText = '';

    /**
     * Create a new TextEntryInteraction object.
     *
     * @param string $responseIdentifier
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If any of the arguments is invalid.
     */
    public function __construct($responseIdentifier, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($responseIdentifier, $id, $class, $lang, $label);
        $this->setBase(10);
        $this->setStringIdentifier('');
        $this->setPatternMask('');
        $this->setPlaceholderText('');
    }

    /**
     * If the interaction is bound to a numeric response variable, get the number base in which
     * to interpret the value entered by the candidate.
     *
     * @param int $base A positive (>= 0) integer.
     * @throws InvalidArgumentException If $base is not a positive integer.
     */
    public function setBase($base)
    {
        if (is_int($base) && $base >= 0) {
            $this->base = $base;
        } else {
            $msg = "The 'base' argument must be a positive (>= 0) integer value, '" . gettype($base) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * If the interaction is bound to a numeric response variable, get the number base in which
     * to interpret the value entered by the candidate.
     *
     * @return int A positive (>= 0) integer.
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * If the interaction is bound to a numeric response variable, set the identifier of the response variable where the
     * plain text entered by the candidate will be stored. If $stringIdentifier is an empty string, it means that
     * there is no value for the stringIdentifier attribute.
     *
     * @param string $stringIdentifier A QTI Identifier or an empty string.
     * @throws InvalidArgumentException If $stringIdentifier is not a valid QTIIdentifier nor an empty string.
     */
    public function setStringIdentifier($stringIdentifier)
    {
        if (Format::isIdentifier($stringIdentifier, false) === true || (is_string($stringIdentifier) && empty($stringIdentifier))) {
            $this->stringIdentifier = $stringIdentifier;
        } else {
            $msg = "The 'stringIdentifier' argument must be a valid QTI identifier or an empty string, '" . $stringIdentifier . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * If the interaction is bound to a numeric response variable, get the identifier of the response variable where the
     * plain text entered by the candidate will be stored. If the returned value is an empty string, it means that there
     * is no value defined for the stringIdentifier attribute.
     *
     * @return string A QTI identifier or an empty string.
     */
    public function getStringIdentifier()
    {
        return $this->stringIdentifier;
    }

    /**
     * Whether a value is defined for the stringIdentifier attribute.
     *
     * @return bool
     */
    public function hasStringIdentifier()
    {
        return $this->getStringIdentifier() !== '';
    }

    /**
     * Set the hint to the candidate about the expected overall length of its
     * response. A null value unsets expectedLength.
     *
     * @param int|null $expectedLength A non-negative integer (>= 0) or null to unset expectedLength.
     * @throws InvalidArgumentException If $expectedLength is not a non-negative integer nor null.
     */
    public function setExpectedLength($expectedLength)
    {
        if ($expectedLength !== null && (!is_int($expectedLength) || $expectedLength < 0)) {
            $given = is_int($expectedLength)
                ? $expectedLength
                : gettype($expectedLength);

            $msg = 'The "expectedLength" argument must be a non-negative integer (>= 0) or null, "' . $given . '" given.';
            throw new InvalidArgumentException($msg);
        }

        $this->expectedLength = $expectedLength;
    }

    /**
     * Get the hint to the candidate about the expected overall length of its response. If the returned
     * value is -1, it means that no value is defined for the expectedLength attribute.
     *
     * @return int|null A non-negative integer (>= 0) or null if undefined.
     */
    public function getExpectedLength()
    {
        return $this->expectedLength;
    }

    /**
     * Whether a value is defined for the expectedLength attribute.
     *
     * @return bool
     */
    public function hasExpectedLength()
    {
        return $this->getExpectedLength() !== null;
    }

    /**
     * Set the pattern mask specifying an XML Schema 2 regular expression that the candidate response must
     * match with. If $patternMask is an empty string, it means that there is no value defined for patternMask.
     *
     * @param string $patternMask An XML Schema 2 regular expression or an empty string.
     * @throws InvalidArgumentException If $patternMask is not a string value.
     */
    public function setPatternMask($patternMask)
    {
        if (is_string($patternMask)) {
            $this->patternMask = $patternMask;
        } else {
            $msg = "The 'patternMask' argument must be a string value, '" . gettype($patternMask) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the pattern mask specifying an XML Schema 2 regular expression that the candidate response must
     * match with. If the returned value is an empty string, it means that there is no value defined
     * for patternMask.
     *
     * @return string An XML Schema 2 regular expression or an empty string.
     */
    public function getPatternMask()
    {
        return $this->patternMask;
    }

    /**
     * Whether a value is defined for the patternMask attribute.
     *
     * @return bool
     */
    public function hasPatternMask()
    {
        return $this->patternMask !== '';
    }

    /**
     * Set a placeholder text. If $placeholderText is an empty string, it means that no value is defined
     * for the placeholderText attribute.
     *
     * @param string $placeholderText A placeholder text or an empty string.
     * @throws InvalidArgumentException If $placeholderText is not a string value.
     */
    public function setPlaceholderText($placeholderText)
    {
        if (is_string($placeholderText)) {
            $this->placeholderText = $placeholderText;
        } else {
            $msg = "The 'placeholderText' argument must be a string value, '" . gettype($placeholderText) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the placeholder text. If the returned value is an empty string, it means that no value
     * is defined for the placeholderText attribute.
     *
     * @return string A placeholder text or an empty string.
     */
    public function getPlaceholderText()
    {
        return $this->placeholderText;
    }

    /**
     * Whether a value for the placeholderText attribute is defined.
     *
     * @return bool
     */
    public function hasPlaceholderText()
    {
        return $this->getPlaceholderText() !== '';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents()
    {
        return new QtiComponentCollection();
    }

    public function getResponseValidityConstraint(): ResponseValidityConstraint
    {
        return new ResponseValidityConstraint($this->getResponseIdentifier(), 0, 1, $this->getPatternMask());
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'textEntryInteraction';
    }
}
