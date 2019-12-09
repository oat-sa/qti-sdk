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
namespace qtism\runtime\expressions;

use qtism\runtime\expressions\operators\OperatorProcessor;
use qtism\runtime\common\AbstractEngine;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\data\QtiComponent;
use qtism\runtime\common\StackTrace;
use qtism\runtime\expressions\operators\OperatorProcessorFactory;
use qtism\data\expressions\operators\Operator;
use qtism\data\expressions\Expression;
use qtism\runtime\common\State;
use \InvalidArgumentException;

/**
 * The ExpressionEngine class provides a bed for Expression processing, by processing
 * a given Expression object following a given execution context (a State object) providing
 * the variables and the values needed to process the Expression.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExpressionEngine extends AbstractEngine
{
    /**
     *
     * The expression trail.
     *
     * @var array
     */
    private $trail = array();

    /**
     * The expression marker.
     *
     * @var array
     */
    private $marker = array();

    /**
     * The ExpressionProcessorFactory object.
     *
     * @var \qtism\runtime\expressions\ExpressionProcessorFactory
     */
    private $expressionProcessorFactory;

    /**
     * The OperatorProcessorFactory object.
     *
     * @var \qtism\runtime\expressions\operators\OperatorProcessingException
     */
    private $operatorProcessorFactory;

    /**
     * The operands stack.
     *
     * @var \qtism\runtime\expressions\operators\OperandsCollection
     */
    private $operands;

    /**
     * Create a new ExpressionEngine object.
     *
     * @param \qtism\data\QtiComponent $expression The Expression object to be processed.
     * @param \qtism\runtime\common\State $context (optional) The execution context. If no execution context is given, a virgin one will be set up.
     */
    public function __construct(QtiComponent $expression, State $context = null)
    {
        parent::__construct($expression, $context);

        $this->setExpressionProcessorFactory(new ExpressionProcessorFactory());
        $this->setOperatorProcessorFactory(new OperatorProcessorFactory());
        $this->setStackTrace(new StackTrace());
        $this->setOperands(new OperandsCollection());
    }

    /**
     * Set the Expression object to be processed.
     *
     * @param \qtism\data\QtiComponent $expression An Expression object.
     * @throws \InvalidArgumentException If $expression is not an Expression object.
     */
    public function setComponent(QtiComponent $expression)
    {
        if ($expression instanceof Expression) {
            parent::setComponent($expression);
        } else {
            $msg = "The ExpressionEngine class only accepts QTI Data Model Expression objects to be processed.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Set the ExpressionProcessorFactory object to be used by the engine.
     *
     * @param \qtism\runtime\expressions\ExpressionProcessorFactory $expressionProcessorFactory An ExpressionProcessorFactory object.
     */
    public function setExpressionProcessorFactory(ExpressionProcessorFactory $expressionProcessorFactory)
    {
        $this->expressionProcessorFactory = $expressionProcessorFactory;
    }

    /**
     * Set the OperatorProcessorFactory object to be used by the engine.
     *
     * @param \qtism\runtime\expressions\operators\OperatorProcessorFactory $operatorProcessorFactory An OperatorProcessorFactory object.
     */
    public function setOperatorProcessorFactory(OperatorProcessorFactory $operatorProcessorFactory)
    {
        $this->operatorProcessorFactory = $operatorProcessorFactory;
    }

    /**
     * Set the Operands stack.
     *
     * @param \qtism\runtime\expressions\operators\OperandsCollection $operands An OperandsCo
     */
    protected function setOperands(OperandsCollection $operands)
    {
        $this->operands = $operands;
    }

    /**
     * Push an Expression object on the trail stack.
     *
     * @param \qtism\data\expressions\Expression|\qtism\data\expressions\ExpressionCollection $expression An Expression/ExpressionCollection object to be pushed on top of the trail stack.
     */
    protected function pushTrail($expression)
    {
        if ($expression instanceof Expression) {
            array_push($this->trail, $expression);
        } else {
            // Add the collection in reverse order.
            $i = count($expression);
            while ($i >= 1) {
                $i--;
                array_push($this->trail, $expression[$i]);
            }
        }
    }

    /**
     * Pop an Expression object from the trail stack.
     *
     * @return \qtism\data\expressions\Expression $expression The Expression object at the top of the trail stack.
     */
    protected function popTrail()
    {
        return array_pop($this->trail);
    }

    /**
     * Get a reference on the trail stack.
     *
     * @return array A reference on the trail stack.
     */
    protected function &getTrail() {
        return $this->trail;
    }

    /**
     * Mark a given $expression object as explored.
     *
     * @param \qtism\data\expressions\Expression $expression An explored Expression object.
     */
    protected function mark(Expression $expression)
    {
        array_push($this->marker, $expression);
    }

    /**
     * Whether a given $expression object is already marked as explored.
     *
     * @param \qtism\data\expressions\Expression $expression An Expression object.
     * @return boolean Whether $expression is marked as explored.
     */
    protected function isMarked(Expression $expression)
    {
        return in_array($expression, $this->marker, true);
    }

    /**
     * Process the current Expression object according to the current
     * execution context.
     *
     * @throws \qtism\runtime\expressions\ExpressionProcessingException|\qtism\runtime\expressions\operators\OperatorProcessingException If an error occurs during the Expression processing.
     */
    public function process()
    {
        $expression = $this->getComponent();

        // Reset trail and marker arrays.
        $this->trail = array();
        $this->marker = array();

        $this->pushTrail($expression);

        while (count($this->getTrail()) > 0) {

            $expression = $this->popTrail();

            if ($this->isMarked($expression) === false && $expression instanceof Operator) {
                // This is an operator, first pass. Repush for a second pass.
                $this->mark($expression);
                $this->pushTrail($expression);
                $this->pushTrail($expression->getExpressions());
            } elseif ($this->isMarked($expression)) {
                // Operator, second pass. Process it.
                $popCount = count($expression->getExpressions());
                $operands = $this->operands->pop($popCount);
                $processor = $this->operatorProcessorFactory->createProcessor($expression, $operands);
                $processor->setState($this->getContext());
                $result = $processor->process();

                // trace the processing of the operator.
                $qtiName = $expression->getQtiClassName();
                $trace = "Operator '${qtiName}' processed.";
                $this->traceOperator($processor, $result);

                if ($expression !== $this->getComponent()) {
                    $this->operands->push($result);
                }
            } else {
                // Simple expression, process it.
                $processor = $this->expressionProcessorFactory->createProcessor($expression);
                $processor->setState($this->getContext());

                $result = $processor->process();
                $this->operands->push($result);

                // trace the processing of the expression.
                $qtiName = $expression->getQtiClassName();
                $trace = "Expression '${qtiName}' processed.";
                $this->traceExpression($processor, $result);
            }
        }

        return $result;
    }

    /**
     * Trace a given Expression $processor execution.
     *
     * @param \qtism\runtime\expressions\ExpressionProcessor $processor The processor that undertook the processing.
     * @param mixed $result The result of the processing.
     */
    protected function traceExpression(ExpressionProcessor $processor, $result)
    {
        $qtiClassName = $processor->getExpression()->getQtiClassName();
        $this->trace("${qtiClassName} [${result}]");
    }

    /**
     * Trace a given Operator $processor execution.
     *
     * @param \qtism\runtime\expressions\operators\OperatorProcessor $processor The processor that undertook the processing.
     * @param mixed $result The result of the processing.
     */
    protected function traceOperator(OperatorProcessor $processor, $result)
    {
        $stringOperands = array();

        foreach ($processor->getOperands() as $operand) {
            $stringOperands[] = '' . $operand;
        }

        $qtiClassName = $processor->getExpression()->getQtiClassName();
        $msg = "${qtiClassName}(" . implode(', ', $stringOperands) . ") [${result}]";
        $this->trace($msg);
    }
}
