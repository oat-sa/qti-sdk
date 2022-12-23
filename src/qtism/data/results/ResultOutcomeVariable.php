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
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille <camille@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\results;

use InvalidArgumentException;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\data\QtiComponentCollection;
use qtism\data\state\ValueCollection;
use qtism\data\View;

/**
 * Class ResultOutcomeVariable
 *
 * The Item result information related to a 'Outcome Variable'.
 */
class ResultOutcomeVariable extends ItemVariable
{
    /**
     * The views (if any) declared for the outcome must be copied to the report to enable systems that render the report
     * to hide information not relevant in a specific situation. If no values are given, the outcome's value should be considered relevant in all views.
     *
     * Multiplicity [0,1]
     *
     * @var int
     */
    protected $view = null;

    /**
     * A human readable interpretation of the default value.
     *
     * Multiplicity [0,1]
     *
     * @var QtiString
     */
    protected $interpretation = null;

    /**
     * An optional link to an extended interpretation of the outcome variable's value.
     *
     * Multiplicity [0,1]
     *
     * @var QtiUri
     */
    protected $longInterpretation = null;

    /**
     * The normalMaximum attribute optionally defines the maximum magnitude of numeric outcome variables, it must be a positive value.
     * If given, the outcome's value can be divided by normalMaximum and then truncated (if necessary) to obtain a normalized score
     * in the range [-1.0,1.0]. normalMaximum has no affect on responseProcessing or the values that the outcome variable itself can take.
     *
     * Multiplicity [0,1]
     *
     * @var QtiFloat
     */
    protected $normalMaximum = null;

    /**
     * The normalMinimum attribute optionally defines the minimum value of numeric outcome variables, it may be negative.
     *
     * Multiplicity [0,1]
     *
     * @var QtiFloat
     */
    protected $normalMinimum = null;

    /**
     * The masteryValue attribute optionally defines a value for numeric outcome variables above
     * which the aspect being measured is considered to have been mastered by the candidate.
     *
     * Multiplicity [0,1]
     *
     * @var QtiFloat
     */
    protected $masteryValue = null;

    /**
     * The value(s) of the outcome variable. The order of the values is significant only if the outcome was declared with ordered cardinality.
     *
     * Multiplicity [0,*]
     *
     * @var ValueCollection
     */
    protected $values = null;

    /**
     * ResultOutcomeVariable constructor.
     *
     * @param QtiIdentifier $identifier
     * @param $cardinality
     * @param null $baseType
     * @param null|ValueCollection $values
     * @param null $view
     * @param QtiString|null $interpretation
     * @param QtiUri|null $longInterpretation
     * @param QtiFloat|null $normalMaximum
     * @param QtiFloat|null $normalMinimum
     * @param QtiFloat|null $masteryValue
     * @throws InvalidArgumentException
     */
    public function __construct(
        QtiIdentifier $identifier,
        $cardinality,
        $baseType = null,
        ValueCollection $values = null,
        $view = null,
        QtiString $interpretation = null,
        QtiUri $longInterpretation = null,
        QtiFloat $normalMaximum = null,
        QtiFloat $normalMinimum = null,
        QtiFloat $masteryValue = null
    ) {
        parent::__construct($identifier, $cardinality, $baseType);
        $this->setValues($values);
        $this->setView($view);
        $this->setInterpretation($interpretation);
        $this->setLongInterpretation($longInterpretation);
        $this->setNormalMaximum($normalMaximum);
        $this->setNormalMinimum($normalMinimum);
        $this->setMasteryValue($masteryValue);
    }

    /**
     * Returns the QTI class name as per QTI 2.1 specification.
     *
     * @return string A QTI class name.
     */
    public function getQtiClassName(): string
    {
        return 'outcomeVariable';
    }

    /**
     * Get the direct child components of this one.
     *
     * @return QtiComponentCollection A collection of QtiComponent objects.
     */
    public function getComponents(): QtiComponentCollection
    {
        $components = [];
        if ($this->hasValues()) {
            $components = $this->getValues()->getArrayCopy();
        }
        return new QtiComponentCollection($components);
    }

    /**
     * Get the outcome values
     *
     * @return ValueCollection|null
     */
    public function getValues(): ?ValueCollection
    {
        return $this->values;
    }

    /**
     * Set the outcome values
     *
     * @param ValueCollection $values
     * @return $this
     */
    public function setValues(ValueCollection $values = null): self
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Check if the values are set
     *
     * @return bool
     */
    public function hasValues(): bool
    {
        return $this->values !== null;
    }

    /**
     * Get the view
     *
     * @return int|null
     */
    public function getView(): ?int
    {
        return $this->view;
    }

    /**
     * Set the view
     *
     * @param $view
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setView($view = null): self
    {
        if ($view !== null && !in_array($view, View::asArray())) {
            $msg = sprintf('Invalid View. Should be one of "%s"', implode('", "', View::asArray()));
            throw new InvalidArgumentException($msg);
        }
        $this->view = $view;
        return $this;
    }

    /**
     * Check if the view is set
     *
     * @return bool
     */
    public function hasView(): bool
    {
        return $this->view !== null;
    }

    /**
     * Set the interpretation
     *
     * @return QtiString|null
     */
    public function getInterpretation(): ?QtiString
    {
        return $this->interpretation;
    }

    /**
     * Get the interpretation
     *
     * @param QtiString $interpretation
     * @return $this
     */
    public function setInterpretation(QtiString $interpretation = null): self
    {
        $this->interpretation = $interpretation;
        return $this;
    }

    /**
     * Check if the interpretation is set
     *
     * @return bool
     */
    public function hasInterpretation(): bool
    {
        return $this->interpretation !== null;
    }

    /**
     * Get the long interpretation
     *
     * @return QtiUri|null
     */
    public function getLongInterpretation(): ?QtiUri
    {
        return $this->longInterpretation;
    }

    /**
     * Set the long interpretation
     *
     * @param QtiUri $longInterpretation
     * @return $this
     */
    public function setLongInterpretation(QtiUri $longInterpretation = null): self
    {
        $this->longInterpretation = $longInterpretation;
        return $this;
    }

    /**
     * Check if the long interpretation is set
     *
     * @return bool
     */
    public function hasLongInterpretation(): bool
    {
        return $this->longInterpretation !== null;
    }

    /**
     * Get the normal maximum
     *
     * @return QtiFloat|null
     */
    public function getNormalMaximum(): ?QtiFloat
    {
        return $this->normalMaximum;
    }

    /**
     * Set the normal maximum
     *
     * @param QtiFloat $normalMaximum
     * @return $this
     */
    public function setNormalMaximum(QtiFloat $normalMaximum = null): self
    {
        $this->normalMaximum = $normalMaximum;
        return $this;
    }

    /**
     * Check if the normal maximum is set
     *
     * @return bool
     */
    public function hasNormalMaximum(): bool
    {
        return $this->normalMaximum !== null;
    }

    /**
     * Get the normal minimum
     *
     * @return QtiFloat|null
     */
    public function getNormalMinimum(): ?QtiFloat
    {
        return $this->normalMinimum;
    }

    /**
     * Set the normal minimum
     *
     * @param QtiFloat $normalMinimum
     * @return $this
     */
    public function setNormalMinimum(QtiFloat $normalMinimum = null): self
    {
        $this->normalMinimum = $normalMinimum;
        return $this;
    }

    /**
     * Check if the normal minimum is set
     *
     * @return bool
     */
    public function hasNormalMinimum(): bool
    {
        return $this->normalMinimum !== null;
    }

    /**
     * Get the mastery value
     *
     * @return QtiFloat|null
     */
    public function getMasteryValue(): ?QtiFloat
    {
        return $this->masteryValue;
    }

    /**
     * Set the mastery value
     *
     * @param QtiFloat $masteryValue
     * @return $this
     */
    public function setMasteryValue(QtiFloat $masteryValue = null): self
    {
        $this->masteryValue = $masteryValue;
        return $this;
    }

    /**
     * Check if the mastery value is set
     *
     * @return bool
     */
    public function hasMasteryValue(): bool
    {
        return $this->masteryValue !== null;
    }
}
