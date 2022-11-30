<?php

declare(strict_types=1);

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

namespace qtism\data\expressions\operators;

use InvalidArgumentException;
use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 *
 * The stringMatch operator takes two sub-expressions which must have single and a
 * base-type of string. The result is a single boolean with a value of true if the
 * two strings match according to the comparison rules defined by the attributes
 * below and false if they don't. If either sub-expression is NULL then the operator
 * results in NULL.
 */
class StringMatch extends Operator
{
    /**
     * From IMS QTI:
     *
     * Whether or not the match is to be carried out case sensitively.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $caseSensitive;

    /**
     * From IMS QTI:
     *
     * This attribute is now deprecated, the substring operator should be used instead.
     * If true, then the comparison returns true if the first string contains the
     * second one, otherwise it returns true only if they match entirely.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $substring = false;

    /**
     * Create a new instance of StringMatch.
     *
     * @param ExpressionCollection $expressions A collection of Expression objects.
     * @param bool $caseSensitive Whether or not the match to be carried out case sensitively.
     * @param bool $substring Deprecated argument, use the substring operator instead.
     * @throws InvalidArgumentException If $caseSensitive or $substring are not booleans or if the $expressions count is greather than 2.
     */
    public function __construct(ExpressionCollection $expressions, $caseSensitive, $substring = false)
    {
        parent::__construct($expressions, 2, 2, [OperatorCardinality::SINGLE], [OperatorBaseType::STRING]);
        $this->setCaseSensitive($caseSensitive);
        $this->setSubstring($substring);
    }

    /**
     * Set Whether or not the match is to be carried out case sensitively.
     *
     * @param bool $caseSensitive Case sensitiveness.
     * @throws InvalidArgumentException If $caseSensitive is not a boolean.
     */
    public function setCaseSensitive($caseSensitive): void
    {
        if (is_bool($caseSensitive)) {
            $this->caseSensitive = $caseSensitive;
        } else {
            $msg = "The caseSensitive argument must be a boolean, '" . gettype($caseSensitive) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether or not the match is to be carried out case sensitively.
     *
     * @return bool True if it has to, false otherwise.
     */
    public function isCaseSensitive(): bool
    {
        return $this->caseSensitive;
    }

    /**
     * Set the substring attribute.
     *
     * @param bool $substring A boolean value.
     * @throws InvalidArgumentException If $substring is not a boolean.
     * @deprecated
     */
    public function setSubstring($substring): void
    {
        if (is_bool($substring)) {
            $this->substring = $substring;
        } else {
            $msg = "The substring argument must be a boolean, '" . gettype($substring) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the substring attribute.
     *
     * @return bool
     * @deprecated
     */
    public function mustSubstring(): bool
    {
        return $this->substring;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'stringMatch';
    }
}
