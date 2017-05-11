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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;

/**
 * From IMS QTI:
 *
 * If the expression given in the templateIf or templateElseIf evaluates to true then
 * the sub-rules contained within it are followed and any following templateElseIf or
 * templateElse parts are ignored for this template condition.
 *
 * If the expression given in the templateIf or templateElseIf does not evaluate to true
 * then consideration passes to the next templateElseIf or, if there are no more
 * templateElseIf parts then the sub-rules of the templateElse are followed (if specified).
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TemplateCondition extends QtiComponent implements TemplateRule
{
    /**
     * The TemplateIf object composing the template condition.
     *
     * @var \qtism\data\rules\TemplateIf
     * @qtism-bean-property
     */
    private $templateIf;

    /**
     * The collection of TemplateElseIf objects composing the template condition.
     *
     * @var \qtism\data\rules\TemplateElseIfCollection
     * @qtism-bean-property
     */
    private $templateElseIfs;

    /**
     * An optional TemplateElse object composing the complate condition.
     *
     * @var \qtism\data\rules\TemplateElse
     * @qtism-bean-property
     */
    private $templateElse = null;

    /**
     * Create a new TemplateCondition object.
     *
     * @param \qtism\data\rules\TemplateIf $templateIf The TemplateIf object composing the template condition.
     * @param \qtism\data\rules\TemplateElseIfCollection $templateElseIfs The collection of TemplateElseIf objects composing the template condition.
     * @param \qtism\data\rules\TemplateElse $templateElse An optional TemplateElse object composing the template condition.
     */
    public function __construct(TemplateIf $templateIf, TemplateElseIfCollection $templateElseIfs = null, TemplateElse $templateElse = null)
    {
        $this->setTemplateIf($templateIf);
        $this->setTemplateElseIfs((is_null($templateElseIfs)) ? new TemplateElseIfCollection() : $templateElseIfs);
        $this->setTemplateElse($templateElse);
    }

    /**
     * Set the TemplateIf object composing the template condition.
     *
     * @param \qtism\data\rules\TemplateIf $templateIf A TemplateIf object.
     */
    public function setTemplateIf(TemplateIf $templateIf)
    {
        $this->templateIf = $templateIf;
    }

    /**
     * Get the TemplateIf object composing the template condition;
     *
     * @return \qtism\data\rules\TemplateIf A TemplateIf object.
     */
    public function getTemplateIf()
    {
        return $this->templateIf;
    }

    /**
     * Set the collection of TemplateElseIf objects composing the template condition.
     *
     * @param \qtism\data\rules\TemplateElseIfCollection $templateElseIfs A collection of TemplateElseIf objects.
     */
    public function setTemplateElseIfs(TemplateElseIfCollection $templateElseIfs)
    {
        $this->templateElseIfs = $templateElseIfs;
    }

    /**
     * Get the collection of TemplateElseIf objects composing the template condition.
     *
     * @return \qtism\data\rules\TemplateElseIfCollection A collection of TemplateElseIf objects.
     */
    public function getTemplateElseIfs()
    {
        return $this->templateElseIfs;
    }

    /**
     * Set the TemplateElse object composing the template condition.
     *
     * @param \qtism\data\rules\TemplateElse $templateElse A TemplateElse object.
     */
    public function setTemplateElse(TemplateElse $templateElse = null)
    {
        $this->templateElse = $templateElse;
    }

    /**
     * Get the TemplateElse object composing the template condition.
     *
     * @return \qtism\data\rules\TemplateElse A TemplateElse object.
     */
    public function getTemplateElse()
    {
        return $this->templateElse;
    }

    /**
     * Whether or not a TemplateElse object is defined for this template condition.
     *
     * @return boolean
     */
    public function hasTemplateElse()
    {
        return $this->getTemplateElse() !== null;
    }

    /**
     * @see \qtism\data\QtiComponent::getComponents()
     */
    public function getComponents()
    {
        $merge = array_merge(array($this->getTemplateIf()), $this->getTemplateElseIfs()->getArrayCopy());
        $components = new QtiComponentCollection($merge);
        if (($else = $this->getTemplateElse()) !== null) {
            $components[] = $else;
        }

        return $components;
    }

    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'templateCondition';
    }

    /**
     * Transforms this rule into a Qti-PL string.
     *
     *@return string A Qti-PL representation of the rule
     */
    public function toQtiPL()
    {
        $qtipl = $this->templateIf->toQtiPL();
        $qtipl .= (count($this->templateElseIfs) > 0) ? $this->templateElseIfs->toQtiPL(): "";
        $qtipl .= ($this->templateElse == null) ? "" : " " . $this->templateElse->toQtiPL();
        return $qtipl;
    }
}
