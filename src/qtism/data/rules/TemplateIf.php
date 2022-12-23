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

namespace qtism\data\rules;

use qtism\data\expressions\Expression;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * A templateIf part consists of an expression which must have an effective
 * baseType of boolean and single cardinality. For more information about the
 * runtime data model employed see Expressions. It also contains a set of
 * sub-rules. If the expression is true then the sub-rules are processed,
 * otherwise they are skipped (including if the expression is NULL) and the
 * following templateElseIf or templateElse parts (if any) are considered instead.
 */
class TemplateIf extends QtiComponent
{
    /**
     * The expression to be evaluated.
     *
     * @var Expression
     * @qtism-bean-property
     */
    private $expression;

    /**
     * The template rules to be evaluated if the expression
     * returns true.
     *
     * @var TemplateRuleCollection
     * @qtism-bean-property
     */
    private $templateRules;

    /**
     * Create a new TemplateIf object.
     *
     * @param Expression $expression The Expression to be evaluated.
     * @param TemplateRuleCollection $templateRules The TemplateRule objects to be evaluated if the expression returns true.
     */
    public function __construct(Expression $expression, TemplateRuleCollection $templateRules)
    {
        $this->setExpression($expression);
        $this->setTemplateRules($templateRules);
    }

    /**
     * Set the Expression object to be evaluated.
     *
     * @param Expression $expression An Expression object.
     */
    public function setExpression(Expression $expression): void
    {
        $this->expression = $expression;
    }

    /**
     * Get the Expression object to be evaluated.
     *
     * @return Expression An Expression object.
     */
    public function getExpression(): Expression
    {
        return $this->expression;
    }

    /**
     * Set the collection of TemplateRule objects to be evaluated if the
     * expression returns true.
     *
     * @param TemplateRuleCollection $templateRules A collection of TemplateRule objects.
     */
    public function setTemplateRules(TemplateRuleCollection $templateRules): void
    {
        $this->templateRules = $templateRules;
    }

    /**
     * Get the collection of TemplateRule objects to be evaluated if the expression
     * returns true.
     *
     * @return TemplateRuleCollection A collection of TemplateRule objects.
     */
    public function getTemplateRules(): TemplateRuleCollection
    {
        return $this->templateRules;
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection(array_merge([$this->getExpression()], $this->getTemplateRules()->getArrayCopy()));
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'templateIf';
    }
}
