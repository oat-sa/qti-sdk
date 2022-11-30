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
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\rules;

use InvalidArgumentException;
use qtism\common\utils\Reflection;
use qtism\data\rules\Rule;
use qtism\runtime\common\Processable;
use qtism\runtime\common\State;

/**
 * The RuleProcessor class aims at processing QTI Data Model Rule objects which are:
 *
 * * responseCondition
 * * outcomeCondition
 * * setOutcomeValue
 * * lookupOutcomeValue
 * * branchRule
 */
abstract class RuleProcessor implements Processable
{
    /**
     * The Rule object to be processed.
     *
     * @var Rule
     */
    private $rule;

    /**
     * The State object.
     *
     * @var State
     */
    private $state;

    /**
     * Create a new RuleProcessor object aiming at processing the $rule Rule object.
     *
     * @param Rule $rule A Rule object to be processed by the processor.
     * @throws InvalidArgumentException If $rule is not compliant with the rule processor implementation.
     */
    public function __construct(Rule $rule)
    {
        $this->setRule($rule);
        $this->setState(new State());
    }

    /**
     * Set the QTI Data Model Rule object to be processed.
     *
     * @param Rule $rule
     * @throws InvalidArgumentException If $rule is not compliant with the rule processor implementation.
     */
    public function setRule(Rule $rule): void
    {
        $expectedType = $this->getRuleType();

        if (!Reflection::isInstanceOf($rule, $expectedType)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The %s Rule Processor only processes %s Rule objects, %s given.',
                    get_class($this),
                    $expectedType,
                    get_class($rule)
                )
            );
        }

        $this->rule = $rule;
    }

    /**
     * Get the QTI Data Model Rule object to be processed.
     *
     * @return Rule
     */
    public function getRule(): Rule
    {
        return $this->rule;
    }

    /**
     * Set the current State object.
     *
     * @param State $state A State object.
     */
    public function setState(State $state): void
    {
        $this->state = $state;
    }

    /**
     * Get the current State object.
     *
     * @return State
     */
    public function getState(): State
    {
        return $this->state;
    }

    /**
     * Get the expected type (fully qualifed class name) of the Rule objects that can be processed
     * by the actual implementation.
     *
     * @return string A Fully Qualified PHP Class Name (FQCN).
     */
    abstract protected function getRuleType(): string;
}
