<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\common;

use InvalidArgumentException;

/**
 * The PrefixedIdentifier class is a POO representation
 * of variable identifiers. Variable identifiers can be simple or prefixed.
 *
 * Allowed characters in variable names are a-zA-Z0-9-_. Numbers are not allowed for the
 * first character (be carefull!).
 *
 * Examples of simple variable names:
 *
 * * Q01
 * * Q_01
 *
 * Examples of prefixed variable names:
 *
 * * Q01.SCORE (The very last submitted SCORE of item Q1)
 * * Q01.3.SCORE (The 3rd time SCORE was submitted for item Q1). '3' is called the 'sequence number'.
 *
 * @link http://www.imsglobal.org/question/qtiv2p1/imsqti_infov2p1.html#element10572 The QTI variable name prefixing technique.
 */
class VariableIdentifier
{
    /**
     * The identifier as given to the __construct method.
     *
     * @var string
     */
    private $identifier;

    /**
     * The detected sequence number.
     *
     * @var int
     */
    private $sequenceNumber = 0;

    /**
     * The detected variable name. If the identifier is 'Q01.1.SCORE',
     * the variable name is 'SCORE'.
     *
     * @var string
     */
    private $variableName = '';

    /**
     * The detected prefix. If the identifier is 'Q01.1.SCORE',
     * the prefix is 'Q01'.
     *
     * @var string
     */
    private $prefix = '';

    /**
     * Create a new VariableIdentifier object.
     *
     * @param string $identifier A prefixed identifier.
     * @throws InvalidArgumentException If $identifier is not a valid variable identifier.
     *
     */
    public function __construct($identifier)
    {
        $this->setIdentifier($identifier);

        // The set identifier throws an InvalidArgumentException
        // if the identifier is syntaxically invalid. We are then
        // safe from any extra check.

        // contains dot(s)?
        $dots = mb_stripos($identifier, '.', 0, 'UTF-8');
        if ($dots !== false) {
            // prefixing technique applied on $identifier.
            $parts = explode('.', $identifier);
            if (count($parts) === 2) {
                // no sequence number.
                $this->setVariableName($parts[1]);
            } else {
                // count = 3, sequence number found.
                $this->setVariableName($parts[2]);
                $this->setSequenceNumber((int)$parts[1]);
            }

            $this->setPrefix($parts[0]);
        } else {
            // simple variable name.
            $this->setVariableName($identifier);
        }
    }

    /**
     * Set the identifier string.
     *
     * @param string $identifier A prefixed identifier.
     * @throws InvalidArgumentException If $identifier is not a valid prefixed identifier.
     */
    protected function setIdentifier($identifier)
    {
        if (Utils::isValidVariableIdentifier($identifier)) {
            $this->identifier = $identifier;
        } else {
            $msg = "The identifier '${identifier}' is not a valid QTI Variable Name Identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the identifier string as given to the __construct method.
     *
     * @return string A prefixed identifier.
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set the detected sequence number.
     *
     * @param int $sequenceNumber A integer sequence number.
     */
    protected function setSequenceNumber($sequenceNumber)
    {
        $this->sequenceNumber = $sequenceNumber;
    }

    /**
     * Returns the sequence number found in the variable identifier. If no such
     * sequence number is found, integer 0 is returned.
     *
     * @return int A strictly positive sequence number if there is a sequence number in the identifier, otherwise zero.
     */
    public function getSequenceNumber()
    {
        return $this->sequenceNumber;
    }

    /**
     * Whether the identifier is composed by a sequence number.
     *
     * @return bool
     */
    public function hasSequenceNumber()
    {
        return $this->getSequenceNumber() > 0;
    }

    /**
     * Set the variable name found in the identifier.
     *
     * @param string $variableName A variable name.
     */
    protected function setVariableName($variableName)
    {
        $this->variableName = $variableName;
    }

    /**
     * Get the variable name found in the identifier.
     *
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     * Set the prefix part of the identifier.
     *
     * @param string $prefix The prefix of the variable identifier.
     */
    protected function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get the prefix part of the identifier. If the identifier is 'Q01.SCORE' or
     * 'Q01.1.SCORE', the prefix is 'Q01'.
     *
     * If no prefix is detected, this method returns an empty string ('').
     *
     * @return string The detected variable identifier prefix or an empty string if there is no prefix in the identifier.
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Whether a prefix was found in the variable identifier.
     *
     * @return bool
     */
    public function hasPrefix()
    {
        return $this->getPrefix() !== '';
    }

    /**
     * Returns the variable identifier as a string such as:
     *
     * * VARNAME
     * * PREFIX.VARNAME
     * * PREFIX.SEQ.VARNAME
     *
     * depending on the nature of the variable identifier.
     *
     * @return string The stringified VariableIdentifier object.
     */
    public function __toString()
    {
        if ($this->hasSequenceNumber() === true) {
            return $this->getPrefix() . '.' . $this->getSequenceNumber() . '.' . $this->getVariableName();
        } elseif ($this->hasPrefix() === true) {
            return $this->getPrefix() . '.' . $this->getVariableName();
        } else {
            return $this->getVariableName();
        }
    }
}
