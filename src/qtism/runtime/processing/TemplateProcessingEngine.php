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
 * Copyright (c) 2013-2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\processing;

use qtism\data\processing\TemplateProcessing;
use qtism\runtime\rules\RuleProcessorFactory;
use qtism\runtime\rules\RuleProcessingException;
use qtism\data\QtiComponent;
use qtism\runtime\common\State;
use qtism\runtime\common\ProcessingException;
use qtism\runtime\common\AbstractEngine;
use \InvalidArgumentException;

/**
 * The TemplateProcessingEngine class aims at providing a single engine to execute
 * any TemplateProcessing object.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TemplateProcessingEngine extends AbstractEngine
{
    /**
     * The factory to be used to retrieve
     * the relevant processor to a given rule.
     *
     * @var \qtism\runtime\rules\RuleProcessorFactory
     */
    private $ruleProcessorFactory;

    /**
     * Create a new OutcomeProcessingEngine object.
     *
     * @param \qtism\data\QtiComponent $templateProcessing A QTI Data Model TemplateProcessing object.
     * @param \qtism\runtime\common\State $context A State object as the execution context.
     */
    public function __construct(QtiComponent $templateProcessing, State $context = null)
    {
        parent::__construct($templateProcessing, $context);
        $this->setRuleProcessorFactory(new RuleProcessorFactory());
    }

    /**
     * Set the TemplateProcessing object to be executed by the engine depending
     * on the current context.
     *
     * @param \qtism\data\QtiComponent $templateProcessing A TemplateProcessing object.
     * @throws \InvalidArgumentException If $outcomeProcessing is not A TemplateProcessing object.
     */
    public function setComponent(QtiComponent $templateProcessing)
    {
        if ($templateProcessing instanceof TemplateProcessing) {
            parent::setComponent($templateProcessing);
        } else {
            $msg = "The TemplateProcessing class only accepts TemplateProcessing objects to be executed.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Set the factory to be used to get the relevant rule processors.
     *
     * @param \qtism\runtime\rules\RuleProcessorFactory $ruleProcessorFactory A RuleProcessorFactory object.
     */
    public function setRuleProcessorFactory(RuleProcessorFactory $ruleProcessorFactory)
    {
        $this->ruleProcessorFactory = $ruleProcessorFactory;
    }

    /**
     * Get the factory to be used to get the relevant rule processors.
     *
     * @return \qtism\runtime\rules\RuleProcessorFactory A RuleProcessorFactory object.
     */
    public function getRuleProcessorFactory()
    {
        return $this->ruleProcessorFactory;
    }

    /**
     * 
     * @throws \qtism\runtime\common\ProcessingException If an error occurs while executing the TemplateProcessing.
     */
    public function process()
    {
        $context = $this->getContext();

        $templateProcessing = $this->getComponent();
        
        try {
            foreach ($templateProcessing->getTemplateRules() as $rule) {
                $processor = $this->getRuleProcessorFactory()->createProcessor($rule);
                $processor->setState($context);
                $processor->process();
                $this->trace($rule->getQtiClassName() . ' executed.');
            }
        } catch (RuleProcessingException $e) {
            if ($e->getCode() !== RuleProcessingException::EXIT_TEMPLATE) {
                throw $e;
            } else {
                $this->trace('Termination of template processing.');
            }
        }
    }
}
