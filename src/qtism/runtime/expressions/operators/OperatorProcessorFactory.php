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
 * Copyright (c) 2013-2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */
namespace qtism\runtime\expressions\operators;

use qtism\data\expressions\operators\CustomOperator;
use qtism\data\QtiComponent;
use qtism\data\expressions\operators\Operator;
use qtism\runtime\expressions\ExpressionProcessorFactory;
use \InvalidArgumentException;
use \RuntimeException;

/**
 * The OperatorProcessorFactory class focus on creating the right OperatorProcessor
 * object regarding a given Operator object.
 *
 * @author <jerome@taotesting.com>
 *
 */
class OperatorProcessorFactory extends ExpressionProcessorFactory
{
    /**
     * Create a new OperatorProcessorFactory object.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create the OperatorProcessor relevant to the given $expression.
     *
     * @param \qtism\data\expressions\Expression $expression The Operator object you want to get the processor.
     * @param \qtism\runtime\expressions\operators\OperandsCollection $operands The operands to be involved in the Operator object.
     * @return \qtism\runtime\expressions\operators\OperatorProcessor An OperatorProcessor object ready to process $expression.
     * @throws \qtism\runtime\expressions\operators\OperatorProcessingException If the $operands count is not compliant with the Operator object to be processed.
     * @throws \InvalidArgumentException If $expression is not an Operator object.
     * @throws \RuntimeException If no relevant OperatorProcessor is found for the given $expression.
     */
    public function createProcessor(QtiComponent $expression, OperandsCollection $operands = null)
    {
        if ($expression instanceof Operator) {
            
            if (is_null($operands) === true) {
                $operands = new OperandsCollection();
            }

            if ($expression instanceof CustomOperator) {
                // QTI custom operator. Try to load an autoloaded class by using
                // its class attribute value.
                if ($expression->hasClass() === true) {
                    $className = Utils::customOperatorClassToPhpClass($expression->getClass());

                    if (class_exists($className) === true) {
                        return new $className($expression, $operands);
                    } else {
                        $msg = "No custom operator implementation found for class '" . $expression->getClass() . "'.";
                        throw new RuntimeException($msg);
                    }
                } else {
                    $msg = "Only custom operators with a 'class' attribute value can be processed.";
                    throw new RuntimeException($msg);
                }
            } else {
                // QTI built-in operator.
                $qtiClassName = ucfirst($expression->getQtiClassName());
                $nsPackage = 'qtism\\runtime\\expressions\\operators\\';
                $className = $nsPackage . $qtiClassName . 'Processor';

                return new $className($expression, $operands);
            }
        } else {
            $msg = "The OperatorProcessorFactory only accepts to create processors for Operator objects.";
            throw new InvalidArgumentException($msg);
        }
    }
}
