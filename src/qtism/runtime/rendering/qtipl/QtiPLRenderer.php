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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Tom Verhoof <tomv@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\rendering\qtipl;

use qtism\runtime\rendering\qtipl\expressions;
use qtism\runtime\rendering\qtipl\expressions\operators;
use qtism\runtime\rendering\qtipl\rules;
use qtism\runtime\rendering\RenderingException;

/**
 * The generic expression QtiPLRenderer. Transforms an Operator or Rule's
 * expression into QtiPL.
 */
class QtiPLRenderer extends AbstractQtiPLRenderer
{
    /**
     * @var string The element opening to where the child elements are written.
     */

    private $openChildElement = '(';

    /**
     * @var string The element closing to where the child elements are written.
     */

    private $closeChildElement = ')';

    /**
     * @var string The element opening to where the attributes are written.
     */

    private $openAttribute = '[';

    /**
     * @var string The element closing to where the attributes are written.
     */

    private $closeAttribute = ']';

    /**
     * @var array of Renderable A registry containing all instances of possible QtiPLRenderer
     */

    private $registry;

    /**
     * Creates a new QtiPLRenderer object.
     *
     * @param ConditionRenderingOptions $cro
     */
    public function __construct(ConditionRenderingOptions $cro)
    {
        parent::__construct($cro);
        $this->registry = [];
        $this->registry['anyN'] = new operators\AnyNQtiPLRenderer($cro);
        $this->registry['baseValue'] = new expressions\BaseValueQtiPLRenderer($cro);
        $this->registry['branchRule'] = new rules\BranchRuleQtiPLRenderer($cro);
        $this->registry['correct'] = new expressions\CorrectQtiPLRenderer($cro);
        $this->registry['customOperator'] = new operators\CustomOperatorQtiPLRenderer($cro);
        $this->registry['default'] = new expressions\DefaultValQtiPLRenderer($cro);
        $this->registry['equal'] = new operators\EqualQtiPLRenderer($cro);
        $this->registry['equalRounded'] = new operators\EqualRoundedQtiPLRenderer($cro);
        $this->registry['fieldValue'] = new operators\FieldValueQtiPLRenderer($cro);
        $this->registry['index'] = new operators\IndexQtiPLRenderer($cro);
        $this->registry['inside'] = new operators\InsideQtiPLRenderer($cro);
        $this->registry['lookupOutcomeValue'] = new rules\LookupOutcomeValueQtiPLRenderer($cro);
        $this->registry['mapResponse'] = new expressions\MapResponseQtiPLRenderer($cro);
        $this->registry['mapResponsePoint'] = new expressions\MapResponsePointQtiPLRenderer($cro);
        $this->registry['mathConstant'] = new expressions\MathConstantQtiPLRenderer($cro);
        $this->registry['mathOperator'] = new operators\MathOperatorQtiPLRenderer($cro);
        $this->registry['numberCorrect'] = new expressions\ItemSubsetQtiPLRenderer($cro);
        $this->registry['numberIncorrect'] = new expressions\ItemSubsetQtiPLRenderer($cro);
        $this->registry['numberPresented'] = new expressions\ItemSubsetQtiPLRenderer($cro);
        $this->registry['numberResponded'] = new expressions\ItemSubsetQtiPLRenderer($cro);
        $this->registry['numberSelected'] = new expressions\ItemSubsetQtiPLRenderer($cro);
        $this->registry['not'] = new operators\NotQtiPLRenderer($cro);
        $this->registry['outcomeCondition'] = new rules\OutcomeConditionQtiPLRenderer($cro);
        $this->registry['outcomeElse'] = new rules\OutcomeElseQtiPLRenderer($cro);
        $this->registry['outcomeElseIf'] = new rules\OutcomeElseIfQtiPLRenderer($cro);
        $this->registry['outcomeIf'] = new rules\OutcomeIfQtiPLRenderer($cro);
        $this->registry['outcomeMaximum'] = new expressions\OutcomeMaximumQtiPLRenderer($cro);
        $this->registry['outcomeMinimum'] = new expressions\OutcomeMinimumQtiPLRenderer($cro);
        $this->registry['patternMatch'] = new operators\PatternMatchQtiPLRenderer($cro);
        $this->registry['preCondition'] = new rules\RuleQtiPLRenderer($cro);
        $this->registry['randomFloat'] = new expressions\RandomFloatQtiPLRenderer($cro);
        $this->registry['randomInteger'] = new expressions\RandomIntegerQtiPLRenderer($cro);
        $this->registry['repeat'] = new operators\RepeatQtiPLRenderer($cro);
        $this->registry['responseCondition'] = new rules\ResponseConditionQtiPLRenderer($cro);
        $this->registry['responseElse'] = new rules\ResponseElseQtiPLRenderer($cro);
        $this->registry['responseElseIf'] = new rules\ResponseElseIfQtiPLRenderer($cro);
        $this->registry['responseIf'] = new rules\ResponseIfQtiPLRenderer($cro);
        $this->registry['roundTo'] = new operators\RoundToQtiPLRenderer($cro);
        $this->registry['setCorrectResponse'] = new rules\SetCorrectResponseQtiPLRenderer($cro);
        $this->registry['setDefaultValue'] = new rules\SetDefaultValueQtiPLRenderer($cro);
        $this->registry['setOutcomeValue'] = new rules\SetOutcomeValueQtiPLRenderer($cro);
        $this->registry['setTemplateValue'] = new rules\SetTemplateValueQtiPLRenderer($cro);
        $this->registry['statsOperator'] = new operators\StatsOperatorQtiPLRenderer($cro);
        $this->registry['stringMatch'] = new operators\StringMatchQtiPLRenderer($cro);
        $this->registry['substring'] = new operators\SubstringQtiPLRenderer($cro);
        $this->registry['templateCondition'] = new rules\TemplateConditionQtiPLRenderer($cro);
        $this->registry['templateConstraint'] = new rules\RuleQtiPLRenderer($cro);
        $this->registry['templateElse'] = new rules\TemplateElseQtiPLRenderer($cro);
        $this->registry['templateElseIf'] = new rules\TemplateElseIfQtiPLRenderer($cro);
        $this->registry['templateIf'] = new rules\TemplateIfQtiPLRenderer($cro);
        $this->registry['testVariables'] = new expressions\TestVariablesQtiPLRenderer($cro);
        $this->registry['variable'] = new expressions\VariableQtiPLRenderer($cro);

        $this->registry['operator'] = new operators\OperatorQtiPLRenderer($cro);
    }

    /**
     * Render a QtiComponent object into another constitution.
     *
     * @param mixed $something Something to render into another consitution.
     * @return mixed The rendered component into another constitution.
     * @throws RenderingException
     */
    #[\ReturnTypeWillChange]
    public function render($something)
    {
        if (array_key_exists($something->getQtiClassName(), $this->registry)) {
            return $this->registry[$something->getQtiClassName()]->render($something);
        } elseif (in_array($something->getQtiClassName(), operators\OperatorQtiPLRenderer::getOperatorClassNames())) {
            return $this->registry['operator']->render($something);
        } else {
            return $this->getDefaultRendering($something);
        }
    }

    /**
     * Returns the default QtiPL rendering for an Operator.
     *
     * @param mixed $something Something to render into another consitution.
     * @return string The default QtiPL rendering for an Operator
     * @throws RenderingException
     */
    public function getDefaultRendering($something): string
    {
        return $something->getQtiClassName() . $this->writeChildElements();
    }

    /**
     * @return string The element opening to where the child elements are written.
     */
    public function getOpenChildElement(): string
    {
        return $this->openChildElement;
    }

    /**
     * @return string The element closing to where the child elements are written.
     */
    public function getCloseChildElement(): string
    {
        return $this->closeChildElement;
    }

    /**
     * @return string The element opening to where the attributes are written.
     */
    public function getOpenAttributes(): string
    {
        return $this->openAttribute;
    }

    /**
     * @return string The element closing to where the attributes are written.
     */
    public function getCloseAttributes(): string
    {
        return $this->closeAttribute;
    }

    /**
     * @param string $childElement The child element of the expression to render.
     * @return string The child Element in the open and close child elements
     * @throws RenderingException
     */
    public function writeChildElement($childElement): string
    {
        return $this->getOpenChildElement() . $this->render($childElement) . $this->getCloseChildElement();
    }

    /**
     * @param array of string $childElements The child elements of the expression to render.
     * @return string The child Elements in the open and close child elements
     * @throws RenderingException
     */
    public function writeChildElements($childElements = []): string
    {
        $childPL = [];

        foreach ($childElements as $ce) {
            $childPL[] = $this->render($ce);
        }

        return $this->getOpenChildElement() . implode(', ', $childPL) . $this->getCloseChildElement();
    }

    /**
     * @param array of string $childElements The child elements of the element to render.
     * @return string The child Elements in the open and close child elements
     */
    public function writeAttributes($attributes = []): string
    {
        if (count($attributes) > 0) {
            $attribPL = [];

            foreach ($attributes as $key => $value) {
                $attribPL[] = $key . '=' . $value;
            }

            return $this->getOpenAttributes() . implode(', ', $attribPL) . $this->getCloseAttributes();
        } else {
            return '';
        }
    }
}
