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
use qtism\common\utils\Format;
use qtism\data\expressions\ExpressionCollection;

/**
 * The field-value operator takes a sub-expression with a record container value. The
 * result is the value of the field with the specified fieldIdentifier. If there is
 * no field with that identifier then the result of the operator is NULL.
 */
class FieldValue extends Operator
{
    /**
     * The identifier of the field to lookup.
     *
     * @var string
     * @qtism-bean-property
     */
    private $fieldIdentifier;

    /**
     * Create a new instance of FieldValue.
     *
     * @param ExpressionCollection $expressions A collection of Expression objects.
     * @param string $fieldIdentifier A QTI Identifier.
     */
    public function __construct(ExpressionCollection $expressions, $fieldIdentifier)
    {
        parent::__construct($expressions, 1, 1, [OperatorCardinality::RECORD], [OperatorBaseType::ANY]);
        $this->setFieldIdentifier($fieldIdentifier);
    }

    /**
     * Set the fieldIdentifier attribute.
     *
     * @param string $fieldIdentifier A QTI Identifier.
     * @throws InvalidArgumentException If $fieldIdentifier is not a valid QTI Identifier.
     */
    public function setFieldIdentifier($fieldIdentifier): void
    {
        if (Format::isIdentifier($fieldIdentifier)) {
            $this->fieldIdentifier = $fieldIdentifier;
        } else {
            $msg = "'${fieldIdentifier}' is not a valid QTI Identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the fieldIdentifier attribute.
     *
     * @return string A QTI Identifier.
     */
    public function getFieldIdentifier(): string
    {
        return $this->fieldIdentifier;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'fieldValue';
    }
}
