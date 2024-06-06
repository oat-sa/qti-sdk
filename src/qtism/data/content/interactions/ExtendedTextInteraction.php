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
 * An extended text interaction is a blockInteraction that allows the candidate to enter
 * an extended amount of text.
 *
 * The extendedTextInteraction must be bound to a response variable with baseType of string,
 * integer or float. When bound to response variable with single cardinality a single string
 * of text is required from the candidate. When bound to a response variable with multiple or
 * ordered cardinality several separate text strings may be required, see maxStrings below.
 */
class ExtendedTextInteraction extends BlockInteraction implements StringInteraction
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
     * From IMS QTI:
     *
     * The maxStrings attribute is required when the interaction is bound to a response
     * variable that is a container. A Delivery Engine must use the value of this attribute
     * to control the maximum number of separate strings accepted from the candidate. When
     * multiple strings are accepted, expectedLength applies to each string.
     *
     * @var int
     * @qtism-bean-property
     */
    private $maxStrings = -1;

    /**
     * From IMS QTI:
     *
     * The minStrings attribute specifies the minimum number separate (non-empty) strings required
     * from the candidate to form a valid response. If minStrings is 0 then the candidate is not
     * required to enter any strings at all. minStrings must be less than or equal to the limit
     * imposed by maxStrings. If the interaction is not bound to a container then there is a
     * special case in which minStrings may be 1. In this case the candidate must enter a
     * non-empty string to form a valid response. More complex constraints on the form of
     * the string can be controlled with the patternMask attribute.
     *
     * @var int
     * @qtism-bean-property
     */
    private $minStrings = 0;

    /**
     * From IMS QTI:
     *
     * The expectedLines attribute provides a hint to the candidate as to the expected number
     * of lines of input required. A line is expected to have about 72 characters. A Delivery
     * Engine should use the value of this attribute to set the size of the response box,
     * where applicable. This is not a validity constraint.
     *
     * @var int|null
     * @qtism-bean-property
     */
    private $expectedLines;

    /**
     * From IMS QTI:
     *
     * Used to control the format of the text entered by the candidate. See textFormat below.
     * This attribute affects the way the value of the associated response variable should be
     * interpreted by response processing engines and also controls the way it should be
     * captured in the delivery engine.
     *
     * @var int
     * @qtism-bean-property
     */
    private $format = TextFormat::PLAIN;

    // This option disable also validation for patternMask
    private $isDisabledMaxWordValidation = false;

    /**
     * Create a new ExtendedTextInteraction object.
     *
     * @param string $responseIdentifier The identifier of the associated response variable.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The lang of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If any of the arguments is invalid.
     */
    public function __construct($responseIdentifier, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($responseIdentifier, $id, $class, $lang, $label);
    }

    /**
     * If the interaction is bound to a numeric response variable, get the number base in which
     * to interpret the value entered by the candidate.
     *
     * @param int $base A positive (>= 0) integer.
     * @throws InvalidArgumentException If $base is not a positive integer.
     */
    public function setBase($base): void
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
    public function getBase(): int
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
    public function setStringIdentifier($stringIdentifier): void
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
    public function getStringIdentifier(): string
    {
        return $this->stringIdentifier;
    }

    /**
     * Whether a value is defined for the stringIdentifier attribute.
     *
     * @return bool
     */
    public function hasStringIdentifier(): bool
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
    public function setExpectedLength($expectedLength): void
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
     * Get the hint to the candidate about the expected overall length of its response.
     * A null return means that no value is defined for the expectedLength attribute.
     *
     * @return int|null A non-negative integer (>= 0) or null if undefined.
     */
    public function getExpectedLength(): int
    {
        return $this->expectedLength ?? -1;
    }

    /**
     * Whether a value is defined for the expectedLength attribute.
     *
     * @return bool
     */
    public function hasExpectedLength(): bool
    {
        return $this->getExpectedLength() !== null && $this->getExpectedLength() >= 0;
    }

    /**
     * Set the pattern mask specifying an XML Schema 2 regular expression that the candidate response must
     * match with. If $patternMask is an empty string, it means that there is no value defined for patternMask.
     *
     * @param string $patternMask An XML Schema 2 regular expression or an empty string.
     * @throws InvalidArgumentException If $patternMask is not a string value.
     */
    public function setPatternMask($patternMask): void
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
    public function getPatternMask(): string
    {
        return !$this->isDisabledMaxWordValidation() ? $this->patternMask : '';
    }

    /**
     * Whether a value is defined for the patternMask attribute.
     *
     * @return bool
     */
    public function hasPatternMask(): bool
    {
        return !$this->isDisabledMaxWordValidation() && $this->getPatternMask() !== '';
    }

    /**
     * Set a placeholder text. If $placeholderText is an empty string, it means that no value is defined
     * for the placeholderText attribute.
     *
     * @param string $placeholderText A placeholder text or an empty string.
     * @throws InvalidArgumentException If $placeholderText is not a string value.
     */
    public function setPlaceholderText($placeholderText): void
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
    public function getPlaceholderText(): string
    {
        return $this->placeholderText;
    }

    /**
     * Whether a value is defined for the placeholderText attribute.
     *
     * @return bool
     */
    public function hasPlaceholderText(): bool
    {
        return $this->getPlaceholderText() !== '';
    }

    /**
     * If the interaction is bound to a numeric response variable, get the number of separate strings
     * accepted from the candidate. If $maxStrings is -1, it means no value is defined for the attribute.
     *
     * @param int $maxStrings A strictly positive (> 0) integer or -1.
     * @throws InvalidArgumentException If $maxStrings is not a strictly positive integer nor -1.
     */
    public function setMaxStrings($maxStrings): void
    {
        if (is_int($maxStrings) && ($maxStrings > 0 || $maxStrings === -1)) {
            $this->maxStrings = $maxStrings;
        } else {
            $msg = "The 'maxStrings' argument must be a strictly positive (> 0) integer or -1, '" . gettype($maxStrings) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * If the interaction is bound to a numeric response variable, get the number of separate strings
     * accepted from the candidate. If the returned value is -1, it means no value is defined for the attribute.
     *
     * @return int A strictly positive (> 0) integer or -1.
     */
    public function getMaxStrings(): int
    {
        return !$this->isDisabledMaxWordValidation() ? $this->maxStrings : -1;
    }

    /**
     * Whether a value for the maxStrings attribute is defined.
     *
     * @return bool
     */
    public function hasMaxStrings(): bool
    {
        return !$this->isDisabledMaxWordValidation() && $this->getMaxStrings() !== -1;
    }

    /**
     * Set the minimum separate (non-empty) strings required from the candidate.
     *
     * @param string $minStrings A positive (>= 0) integer.
     * @throws InvalidArgumentException If $minStrings is not a positive integer.
     */
    public function setMinStrings($minStrings): void
    {
        if (is_int($minStrings) && $minStrings >= 0) {
            $this->minStrings = $minStrings;
        } else {
            $msg = "The 'minStrings' argument must be a positive (>= 0) integer, '" . gettype($minStrings) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the minimum separate (non-empty) strings required from the candidate.
     *
     * @return int A positive (>= 0) integer.
     */
    public function getMinStrings(): int
    {
        return $this->minStrings;
    }

    /**
     * Set the hint to the candidate about the expected number of lines of its
     * response. A null value unsets expectedLines.
     *
     * @param int|null $expectedLines A non-negative integer (>= 0) or null.
     * @throws InvalidArgumentException If $expectedLines is not a non-negative integer nor null.
     */
    public function setExpectedLines($expectedLines): void
    {
        if ($expectedLines !== null && (!is_int($expectedLines) || $expectedLines < 0)) {
            $given = is_int($expectedLines)
                ? $expectedLines
                : gettype($expectedLines);

            $msg = 'The "expectedLines" argument must be a non-negative integer (>= 0) or null, "' . $given . '" given.';
            throw new InvalidArgumentException($msg);
        }

        $this->expectedLines = $expectedLines;
    }

    /**
     * Get the hint to the candidate as to the expected number of lines of input required.
     * A null return means that no value is defined for the expectedLines attribute.
     *
     * @return int|null A non-negative integer (>= 0) or null if undefined.
     */
    public function getExpectedLines(): ?int
    {
        return $this->expectedLines;
    }

    /**
     * Whether a value for the expectedLines attribute is defined.
     *
     * @return bool
     */
    public function hasExpectedLines(): bool
    {
        return $this->getExpectedLines() !== null;
    }

    /**
     * Set the format of the text entered by the candidate.
     *
     * @param int $format A value from the TextFormat enumeration.
     * @throws InvalidArgumentException If $format is not a value from the TextFormat enumeration.
     */
    public function setFormat($format): void
    {
        if (in_array($format, TextFormat::asArray())) {
            $this->format = $format;
        } else {
            $msg = "The 'format' argument must be a value from the TextFormat enumeration, '" . gettype($format) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the format of the text entered by the candidate.
     *
     * @return int A value from the TextFormat enumeration.
     */
    public function getFormat(): int
    {
        return $this->format;
    }

    /**
     * @return ResponseValidityConstraint
     */
    public function getResponseValidityConstraint(): ResponseValidityConstraint
    {
        return new ResponseValidityConstraint(
            $this->getResponseIdentifier(),
            $this->getMinStrings(),
            ($this->hasMaxStrings() === false) ? 0 : $this->getMaxStrings(),
            ($this->isDisabledMaxWordValidation() == false) ? $this->getPatternMask() : '',
        );
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        return parent::getComponents();
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'extendedTextInteraction';
    }

    /**
     *  This option disable also validation for patternMask
    */
    public function isDisabledMaxWordValidation(): bool
    {
        return $this->isDisabledMaxWordValidation;
    }

    public function setIsDisabledMaxWordValidation(bool $isDisabledMaxWordValidation): void
    {
        $this->isDisabledMaxWordValidation = $isDisabledMaxWordValidation;
    }

    public function disabledMaxWordValidation(): void
    {
        $this->setIsDisabledMaxWordValidation(true);
    }
}
