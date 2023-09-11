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

namespace qtism\data\state;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\expressions\Expression;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * The default value of a template variable in an item can be overridden based
 * on the test context in which the template is instantiated. The value is obtained
 * by evaluating an expression defined within the reference to the item at test
 * level and which may therefore depend on the values of variables taken from
 * other items in the test or from outcomes defined at test level itself.
 */
class TemplateDefault extends QtiComponent
{
    /**
     * The identifier of the template variable affected.
     *
     * @var string
     * @qtism-bean-property
     */
    private $templateIdentifier;

    /**
     * An expression which must result in a value with baseType and cardinality matching
     * the declaration of the assossiated variable's templateDeclaration.
     *
     * @var Expression
     * @qtism-bean-property
     */
    private $expression;

    /**
     * Create a new instance of TemplateDefault.
     *
     * @param string $templateIdentifier The identifier of the template variable affected.
     * @param Expression $expression The expression that produces the new template default.
     * @throws InvalidArgumentException If $templateIdentifier is not a valid QTI Identifier.
     */
    public function __construct($templateIdentifier, Expression $expression)
    {
        $this->setTemplateIdentifier($templateIdentifier);
        $this->setExpression($expression);
    }

    /**
     * Get the identifier of the template variable affected.
     *
     * @return string A QTI identifier.
     */
    public function getTemplateIdentifier(): string
    {
        return $this->templateIdentifier;
    }

    /**
     * Set the identifier of the template variable affected.
     *
     * @param string $templateIdentifier A QTI identifier.
     * @throws InvalidArgumentException If $templateIdentifier is not a valid QTI Identifier.
     */
    public function setTemplateIdentifier($templateIdentifier): void
    {
        if (Format::isIdentifier($templateIdentifier)) {
            $this->templateIdentifier = $templateIdentifier;
        } else {
            $msg = "'{$templateIdentifier}' is not a valid QTI Identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the expression that produces the new template default.
     *
     * @return Expression A QTI Expression.
     */
    public function getExpression(): Expression
    {
        return $this->expression;
    }

    /**
     * Set the expression that produces the new template defaul.
     *
     * @param Expression $expression A QTI Expression.
     */
    public function setExpression(Expression $expression): void
    {
        $this->expression = $expression;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'templateDefault';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection([$this->getExpression()]);
    }
}
