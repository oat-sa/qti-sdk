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
 * Copyright (c) 2013-2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Tom Verhoof <tomv@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\rendering\qtipl;

use qtism\runtime\rendering\Renderable;
use qtism\runtime\rendering\qtipl\expressions;
use qtism\runtime\rendering\qtipl\expressions\operators;
use qtism\runtime\rendering\qtipl\rules;

/**
 * The generic expression QtiPLRenderer. Transforms an Operator or Rule's
 * expression into QtiPL.
 *
 * @author Tom Verhoof <tomv@taotesting.com>
 */
class QtiPLRenderer implements Renderable
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
     * Creates a new QtiPLRenderer object.
     */
    public function __construct()
    {
    }

    /**
     * Render a QtiComponent object into another constitution.
     *
     * @param mixed $something Something to render into another consitution.
     * @return mixed The rendered component into another constitution.
     * @throws \qtism\runtime\rendering\RenderingException If something goes wrong while rendering the component.
     */
    public function render($something)
    {
        switch($something->getQtiClassName()) {

            case "anyN":
                return (new operators\AnyNQtiPLRenderer())->render($something);

            case "baseValue":
                return (new expressions\BaseValueQtiPLRenderer())->render($something);

            case "branchRule":
                return (new rules\BranchRuleQtiPLRenderer())->render($something);;

            case "correct":
                return (new expressions\CorrectQtiPLRenderer())->render($something);

            case "customOperator":
                return (new operators\CustomOperatorQtiPLRenderer())->render($something);

            case "default":
                return (new expressions\DefaultValQtiPLRenderer())->render($something);

            case "equal":
                return (new operators\EqualQtiPLRenderer())->render($something);

            case "equalRounded":
                return (new operators\EqualRoundedQtiPLRenderer())->render($something);

            case "fieldValue":
                return (new operators\FieldValueQtiPLRenderer())->render($something);

            case "index":
                return (new operators\IndexQtiPLRenderer())->render($something);

            case "inside":
                return (new operators\InsideQtiPLRenderer())->render($something);

            case "lookupOutcomeValue":
                return (new rules\LookupOutcomeValueQtiPLRenderer())->render($something);

            case "mapResponse":
                return (new expressions\MapResponseQtiPLRenderer())->render($something);

            case "mapResponsePoint":
                return (new expressions\MapResponsePointQtiPLRenderer())->render($something);

            case "mathConstant":
                return (new expressions\MathConstantQtiPLRenderer())->render($something);

            case "mathOperator":
                return (new operators\MathOperatorQtiPLRenderer())->render($something);

            case "numberCorrect":
                return (new expressions\ItemSubsetQtiPLRenderer())->render($something);

            case "numberIncorrect":
                return (new expressions\ItemSubsetQtiPLRenderer())->render($something);

            case "numberPresented":
                return (new expressions\ItemSubsetQtiPLRenderer())->render($something);

            case "numberResponded":
                return (new expressions\ItemSubsetQtiPLRenderer())->render($something);

            case "numberSelected":
                return (new expressions\ItemSubsetQtiPLRenderer())->render($something);

            case "not":
                return (new operators\NotQtiPLRenderer())->render($something);

            case "outcomeCondition":
                return (new rules\OutcomeConditionQtiPLRenderer())->render($something);

            case "outcomeElse":
                return (new rules\OutcomeElseQtiPLRenderer())->render($something);

            case "outcomeElseIf":
                return (new rules\OutcomeElseIfQtiPLRenderer())->render($something);

            case "outcomeIf":
                return (new rules\OutcomeIfQtiPLRenderer())->render($something);    

            case "outcomeMaximum":
                return (new expressions\OutcomeMaximumQtiPLRenderer())->render($something);

            case "outcomeMinimum":
                return (new expressions\OutcomeMinimumQtiPLRenderer())->render($something);

            case "patternMatch":
                return (new operators\PatternMatchQtiPLRenderer())->render($something);

            case "preCondition":
                return (new rules\RuleQtiPLRenderer())->render($something);

            case "randomFloat":
                return (new expressions\RandomFloatQtiPLRenderer())->render($something);

            case "randomInteger":
                return (new expressions\RandomIntegerQtiPLRenderer())->render($something);

            case "repeat":
                return (new operators\RepeatQtiPLRenderer())->render($something);

            case "responseCondition":
                return (new rules\ResponseConditionQtiPLRenderer())->render($something);

            case "responseElse":
                return (new rules\ResponseElseQtiPLRenderer())->render($something);

            case "responseElseIf":
                return (new rules\ResponseElseIfQtiPLRenderer())->render($something);

            case "responseIf":
                return (new rules\ResponseIfQtiPLRenderer())->render($something);

            case "roundTo":
                return (new operators\RoundToQtiPLRenderer())->render($something);

            case "setCorrectResponse":
                return (new rules\SetCorrectResponseQtiPLRenderer())->render($something);

            case "setDefaultValue":
                return (new rules\SetDefaultValueQtiPLRenderer())->render($something);

            case "setOutcomeValue":
                return (new rules\SetOutcomeValueQtiPLRenderer())->render($something);

            case "setTemplateValue":
                return (new rules\SetTemplateValueQtiPLRenderer())->render($something);

            case "statsOperator":
                return (new operators\StatsOperatorQtiPLRenderer())->render($something);

            case "stringMatch":
                return (new operators\StringMatchQtiPLRenderer())->render($something);

            case "substring":
                return (new operators\SubstringQtiPLRenderer())->render($something);

            case "templateCondition":
                return (new rules\TemplateConditionQtiPLRenderer())->render($something);

            case "templateConstraint":
                return (new rules\RuleQtiPLRenderer())->render($something);

            case "templateElse":
                return (new rules\TemplateElseQtiPLRenderer())->render($something);

            case "templateElseIf":
                return (new rules\TemplateElseIfQtiPLRenderer())->render($something);

            case "templateIf":
                return (new rules\TemplateIfQtiPLRenderer())->render($something);    

            case "testVariables":
                return (new expressions\TestVariablesQtiPLRenderer())->render($something);

            case "variable":
                return (new expressions\VariableQtiPLRenderer())->render($something);

            default:
                if (in_array($something->getQtiClassName(), operators\OperatorQtiPLRenderer::getOperatorClassNames())) {
                    return (new operators\OperatorQtiPLRenderer())->render($something);
                } else {
                    return $this->getDefaultRendering($something);
                }
        }
    }

    /**
     * Returns the default QtiPL rendering for an Operator.
     * @param mixed $something Something to render into another consitution.
     * @return string The default QtiPL rendering for an Operator
     */
    public function getDefaultRendering($something) {
        return $something->getQtiClassName() . $this->writeChildElements();
    }

    /**
     * @return string The element opening to where the child elements are written.
     */
    public function getOpenChildElement() {
        return $this->openChildElement;
    }

    /**
     * @return string The element closing to where the child elements are written.
     */
    public function getCloseChildElement() {
        return $this->closeChildElement;
    }

    /**
     * @return string The element opening to where the attributes are written.
     */
    public function getOpenAttributes() {
        return $this->openAttribute;
    }

    /**
     * @return string The element closing to where the attributes are written.
     */
    public function getCloseAttributes() {
        return $this->closeAttribute;
    }

    /**
     * @param string $childElement The child element of the expression to render.
     * @return string The child Element in the open and close child elements
     */
    public function writeChildElement($childElement) {

        return $this->getOpenChildElement() . $this->render($childElement) . $this->getCloseChildElement();
    }

    /**
     * @param array of string $childElements The child elements of the expression to render.
     * @return string The child Elements in the open and close child elements
     */
    public function writeChildElements($childElements = []) {

        $childPL = [];

        foreach ($childElements as $ce) {
            $childPL[] = $this->render($ce);
        }

        return $this->getOpenChildElement() . join(", ", $childPL) . $this->getCloseChildElement();
    }

    /**
     * @param array of string $childElements The child elements of the element to render.
     * @return string The child Elements in the open and close child elements
     */
    public function writeAttributes($attributes = []) {

        if (count($attributes) > 0) {

            $attribPL = [];

            foreach ($attributes as $key => $value) {
                $attribPL[] = $key . "=" . $value;
            }

            return $this->getOpenAttributes() . join(", ", $attribPL) . $this->getCloseAttributes();
        }
        else {
            return "";
        }
    }
}