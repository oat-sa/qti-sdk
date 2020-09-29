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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\processing;

use InvalidArgumentException;
use qtism\data\processing\TemplateProcessing;
use qtism\data\QtiComponent;
use qtism\runtime\common\AbstractEngine;
use qtism\runtime\common\ProcessingException;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\rules\RuleProcessingException;
use qtism\runtime\rules\RuleProcessorFactory;

/**
 * The TemplateProcessingEngine class aims at providing a single engine to execute
 * any TemplateProcessing object.
 */
class TemplateProcessingEngine extends AbstractEngine
{
    /**
     * The factory to be used to retrieve
     * the relevant processor to a given rule.
     *
     * @var RuleProcessorFactory
     */
    private $ruleProcessorFactory;

    /**
     * Create a new TemplateProcessingEngine object.
     *
     * @param QtiComponent $templateProcessing A QTI Data Model TemplateProcessing object.
     * @param State $context A State object as the execution context.
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
     * @param QtiComponent $templateProcessing A TemplateProcessing object.
     * @throws InvalidArgumentException If $templateProcessing is not A TemplateProcessing object.
     */
    public function setComponent(QtiComponent $templateProcessing)
    {
        if ($templateProcessing instanceof TemplateProcessing) {
            parent::setComponent($templateProcessing);
        } else {
            $msg = 'The TemplateProcessing class only accepts TemplateProcessing objects to be executed.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Set the factory to be used to get the relevant rule processors.
     *
     * @param RuleProcessorFactory $ruleProcessorFactory A RuleProcessorFactory object.
     */
    public function setRuleProcessorFactory(RuleProcessorFactory $ruleProcessorFactory)
    {
        $this->ruleProcessorFactory = $ruleProcessorFactory;
    }

    /**
     * Get the factory to be used to get the relevant rule processors.
     *
     * @return RuleProcessorFactory A RuleProcessorFactory object.
     */
    public function getRuleProcessorFactory()
    {
        return $this->ruleProcessorFactory;
    }

    /**
     * Process the template processing.
     *
     * @throws ProcessingException If an error occurs while executing the TemplateProcessing.
     */
    public function process()
    {
        $context = $this->getContext();
        /** @var TemplateProcessing $templateProcessing */
        $templateProcessing = $this->getComponent();
        $trialCount = 0;

        // Make a copy of possibly impacted variables.
        $impactedVariables = Utils::templateProcessingImpactedVariables($templateProcessing);

        $tplVarCopies = [];
        foreach ($impactedVariables as $varIdentifier) {
            $tplVarCopies[] = clone $context->getVariable($varIdentifier);
        }

        do {
            $validConstraints = true;

            try {
                $trialCount++;

                foreach ($templateProcessing->getTemplateRules() as $rule) {
                    $processor = $this->getRuleProcessorFactory()->createProcessor($rule);
                    $processor->setState($context);
                    $processor->process();
                    $this->trace($rule->getQtiClassName() . ' executed.');
                }
            } catch (RuleProcessingException $e) {
                if ($e->getCode() === RuleProcessingException::EXIT_TEMPLATE) {
                    $this->trace('Termination of template processing.');
                } elseif ($e->getCode() === RuleProcessingException::TEMPLATE_CONSTRAINT_UNSATISFIED) {
                    $this->trace('Unsatisfied template constraint.');

                    // Reset variables with their originals.
                    foreach ($tplVarCopies as $copy) {
                        $context->getVariable($copy->getIdentifier())->setValue($copy->getDefaultValue());
                        $context->getVariable($copy->getIdentifier())->setDefaultValue($copy->getDefaultValue());

                        if ($copy instanceof ResponseVariable) {
                            $context->getVariable($copy->getIdentifier())->setCorrectResponse($copy->getCorrectResponse());
                        }
                    }

                    $validConstraints = false;
                } else {
                    throw $e;
                }
            }
        } while ($validConstraints === false && $trialCount < 100);
    }
}
