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

use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiPLisable;

/**
 * The QTI templateElse class.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TemplateElse extends QtiComponent implements QtiPLisable
{
    /**
     * The collection of TemplateRule objects to be evaluated.
     *
     * @var \qtism\data\rules\TemplateRuleCollection
     * @qtism-bean-property
     */
    private $templateRules;

    /**
     * Create a new TemplateElse object.
     *
     * @param \qtism\data\rules\TemplateRuleCollection $templateRules A collection of TemplateRule objects.
     * 
     */
    public function __construct(TemplateRuleCollection $templateRules)
    {
        $this->setTemplateRules($templateRules);
    }

    /**
     * Set the TemplateRule objects to be evaluated.
     *
     * @param \qtism\data\rules\TemplateRuleCollection $templateRules A collection of TemplateRule objects.
     */
    public function setTemplateRules(TemplateRuleCollection $templateRules)
    {
        $this->templateRules = $templateRules;
    }

    /**
     * Get the TemplateRule objects to be evaluated.
     *
     * @return \qtism\data\rules\TemplateRuleCollection A collection of TemplateRule objects.
     */
    public function getTemplateRules()
    {
        return $this->templateRules;
    }

    /**
     * @see \qtism\data\QtiComponent::getComponents()
     */
    public function getComponents()
    {
        return new QtiComponentCollection($this->getTemplateRules()->getArrayCopy());
    }

    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'templateElse';
    }

    /**
     * Transforms this QtiComponent into a Qti-PL string.
     *
     *@return string A Qti-PL representation of the QtiComponent
     */
    public function toQtiPL()
    {
        return "else {\n" . $this->getTemplateRules()->toQtiPL() . "}";
    }
}
