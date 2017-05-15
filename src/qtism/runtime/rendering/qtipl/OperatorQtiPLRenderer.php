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

/**
 * Class OperatorQtiPLRenderer
 * @package qtism\runtime\rendering\qtipl
 */

class OperatorQtiPLRenderer implements Renderable
{
    /**
     * @var array The list of all existing operators
     */
    private static $operatorClassNames = array('and', 'anyN', 'containerSize', 'contains', 'customOperator',
        'delete', 'divide', 'durationGTE', 'durationLT', 'equal', 'equalRounded', 'fieldValue', 'gcd',  'gt', 'gte', 'index',
        'inside', 'integerDivide', 'integerModulus', 'integerToFloat', 'isNull', 'lcm', 'lt', 'lte', 'match',
        'mathOperator', 'max', 'min', 'member', 'multiple', 'not', 'or', 'ordered', 'patternMatch', 'power', 'product', 'random',
        'repeat', 'round', 'roundTo', 'statsOperator', 'stringMatch', 'substring', 'subtract', 'sum', 'truncate');

    /**
     * @return array
     */
    public function getSignAsOperatorMap()
    {
        $map = [];
        $map['and'] = "&&";
        $map['divide'] = "/";
        $map['gt'] = ">";
        $map['gte'] = ">=";
        $map['integerModulus'] = "%";
        $map['lt'] = "<";
        $map['lte'] = "<=";
        $map['not'] = "!";
        $map['or'] = "||";
        $map['power'] = "^";
        $map['product'] = "*";
        $map['subtract'] = "-";
        $map['match'] = "==";


        return $map;
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
        if (!array_key_exists($something->getQtiClassName(), $this->getSignAsOperatorMap()) ||
            $something->getExpressions()->count() != 2) {

            return (new QtiPLRenderer())->getDefaultRendering($something);
        }
        else { // With operator sign form
            return $this->renderWithSignAsOperator($something);
        }
    }

    /**
     * @TODO
     * @param $something
     * @return string
     */
    private function renderWithSignAsOperator($something)
    {
        $qtipl = "";
        $renderer = new QtiPLRenderer();
        $needsparenthesis0 = array_key_exists($something->getExpressions()[0]->getQtiClassName(), $this->getSignAsOperatorMap())
            && $something->getExpressions()[0]->getExpressions()->count() == 2;
        $needsparenthesis1 = array_key_exists($something->getExpressions()[1]->getQtiClassName(), $this->getSignAsOperatorMap())
            && $something->getExpressions()[1]->getExpressions()->count() == 2;

        $qtipl .= ($needsparenthesis0) ? $renderer->getOpenChildElement() .
            $renderer->render($something->getExpressions()[0]) . $renderer->getCloseChildElement():
            $renderer->render($something->getExpressions()[0]);
        $qtipl .= " " . $this->getSignAsOperatorMap()[$something->getQtiClassName()] . " ";
        $qtipl .= ($needsparenthesis1) ? $renderer->getOpenChildElement() .
            $renderer->render($something->getExpressions()[1]) . $renderer->getCloseChildElement():
            $renderer->render($something->getExpressions()[1]);

        return $qtipl;
    }

    /**
     * Returns an array of string which are all the class names that
     * are sub classes of the 'operator' QTI class.
     *
     * @return array An array of string values.
     */
    public static function getOperatorClassNames()
    {
        return self::$operatorClassNames;
    }
}