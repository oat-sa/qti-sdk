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

use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * If the expression given in the outcomeIf or outcomeElseIf evaluates to true then
 * the sub-rules contained within it are followed and any following outcomeElseIf
 * or outcomeElse parts are ignored for this outcome condition.
 *
 * If the expression given in the outcomeIf or outcomeElseIf does not evaluate
 * to true then consideration passes to the next outcomeElseIf or, if there are
 * no more outcomeElseIf parts then the sub-rules of the outcomeElse are
 * followed (if specified).
 */
class OutcomeCondition extends QtiComponent implements OutcomeRule
{
    /**
     * An OutcomeIf object.
     *
     * @var OutcomeIf
     * @qtism-bean-property
     */
    private $outcomeIf;

    /**
     * A collection of OutcomeElseIf objects.
     *
     * @var OutcomeElseIfCollection
     * @qtism-bean-property
     */
    private $outcomeElseIfs;

    /**
     * An optional OutcomeElse object.
     *
     * @var OutcomeElse
     * @qtism-bean-property
     */
    private $outcomeElse = null;

    /**
     * Create a new instance of OutcomeCondition.
     *
     * @param OutcomeIf $outcomeIf An OutcomeIf object.
     * @param OutcomeElseIfCollection $outcomeElseIfs A collection of OutcomeElseIf objects.
     * @param OutcomeElse $outcomeElse An OutcomeElse object.
     */
    public function __construct(OutcomeIf $outcomeIf, OutcomeElseIfCollection $outcomeElseIfs = null, OutcomeElse $outcomeElse = null)
    {
        $this->setOutcomeIf($outcomeIf);
        $this->setOutcomeElse($outcomeElse);
        $this->setOutcomeElseIfs($outcomeElseIfs ?? new OutcomeElseIfCollection());
    }

    /**
     * Get the OutcomeIf object.
     *
     * @return OutcomeIf An OutcomeIf object.
     */
    public function getOutcomeIf(): OutcomeIf
    {
        return $this->outcomeIf;
    }

    /**
     * Set the OutcomeIf object.
     *
     * @param OutcomeIf $outcomeIf An OutcomeIf object.
     */
    public function setOutcomeIf(OutcomeIf $outcomeIf): void
    {
        $this->outcomeIf = $outcomeIf;
    }

    /**
     * Get the collection of OutcomeElseIf objects.
     *
     * @return OutcomeElseIfCollection An OutcomeElseIfCollection object.
     */
    public function getOutcomeElseIfs(): OutcomeElseIfCollection
    {
        return $this->outcomeElseIfs;
    }

    /**
     * Set the collection of OutcomeElseIf objects.
     *
     * @param OutcomeElseIfCollection $outcomeElseIfs An OutcomeElseIfCollection object.
     */
    public function setOutcomeElseIfs(OutcomeElseIfCollection $outcomeElseIfs): void
    {
        $this->outcomeElseIfs = $outcomeElseIfs;
    }

    /**
     * Get the optional OutcomeElse object. Returns null if not specified.
     *
     * @return OutcomeElse|null An OutcomeElse object.
     */
    public function getOutcomeElse(): ?OutcomeElse
    {
        return $this->outcomeElse;
    }

    /**
     * Set the optional OutcomeElse object. A null value means there is no else.
     *
     * @param OutcomeElse $outcomeElse An OutcomeElse object.
     */
    public function setOutcomeElse(OutcomeElse $outcomeElse = null): void
    {
        $this->outcomeElse = $outcomeElse;
    }

    /**
     * Whether an OutcomeElse object is defined for the outcome condition.
     *
     * @return bool
     */
    public function hasOutcomeElse(): bool
    {
        return $this->getOutcomeElse() !== null;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'outcomeCondition';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        $comp = array_merge(
            [$this->getOutcomeIf()],
            $this->getOutcomeElseIfs()->getArrayCopy()
        );

        if ($this->getOutcomeElse() !== null) {
            $comp[] = $this->getOutcomeElse();
        }

        return new QtiComponentCollection($comp);
    }
}
