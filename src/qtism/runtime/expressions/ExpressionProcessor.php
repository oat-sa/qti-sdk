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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\expressions;

use qtism\runtime\common\Processable;
use qtism\runtime\common\State;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The ExpressionProcessor class aims at processing QTI Data Model
 * Expressions.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class ExpressionProcessor implements Processable
{
    /**
	 * The QTI Data Model expression to be Processed.
	 *
	 * @var \qtism\data\expressions\Expression
	 */
    private $expression = null;

    /**
	 * A state.
	 *
	 * @var \qtism\runtime\common\State
	 */
    private $state = null;

    /**
	 * Create a new ExpressionProcessor object.
	 *
	 * @param \qtism\data\expressions\Expression $expression The QTI Data Model Expression to be processed.
	 */
    public function __construct(Expression $expression)
    {
        $this->setExpression($expression);
        $this->setState(new State());
    }

    /**
	 * Set the QTI Data Model Expression to be processed.
	 *
	 * @param \qtism\data\expressions\Expression $expression A QTI Data Model Expression object.
	 * @throws \InvalidArgumentException If $expression is not a subclass nor implements the Expression type returned by the getExpressionType method.
	 */
    public function setExpression(Expression $expression)
    {
        $givenType = get_class($expression);
        $expectedType = $this->getExpressionType();
        
        if ($givenType === $expectedType || is_subclass_of($givenType, $expectedType) === true || in_array($expectedType, class_implements($givenType)) === true) {
            $this->expression = $expression;
        } else {
            $procClass = get_class($this);
            $msg = "The ${procClass} Expression Processor only processes ${expectedType} Expression objects, ${givenType} given.";
            throw new InvalidArgumentException($msg);
        }
        
        $this->expression = $expression;
    }

    /**
	 * Get the QTI Data Model Expression to be processed.
	 *
	 * @return \qtism\data\expressions\Expression A QTI Data Model Expression object.
	 */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
	 * Set the current State object.
	 *
	 * @param \qtism\runtime\common\State $state A State object.
	 */
    public function setState(State $state)
    {
        $this->state = $state;
    }

    /**
	 * Get the current State object.
	 *
	 * @return \qtism\runtime\common\State
	 */
    public function getState()
    {
        return $this->state;
    }
    
    /**
     * Get the expected type (fully qualifed class name) of the Expression objects that can be processed
     * by the actual implementation.
     * 
     * @return string A Fully Qualified PHP Class Name (FQCN).
     */
    abstract protected function getExpressionType();
}
