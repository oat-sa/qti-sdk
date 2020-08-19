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

namespace qtism\runtime\rules;

use qtism\data\rules\ExitTemplate;

/**
 * From IMS QTI:
 *
 * The exit template rule terminates template processing immediately.
 */
class ExitTemplateProcessor extends RuleProcessor
{
    /**
     * Process the ExitTemplate rule. It simply throws a RuleProcessingException with
     * the special code RuleProcessingException::EXIT_TEMPLATE to simulate the
     * template processing termination.
     *
     * @throws RuleProcessingException with code = RuleProcessingException::EXIT_TEMPLATE In any case.
     */
    public function process()
    {
        $msg = 'Termination of Template Processing.';
        throw new RuleProcessingException($msg, $this, RuleProcessingException::EXIT_TEMPLATE);
    }

    /**
     * @see \qtism\runtime\rules\RuleProcessor::getRuleType()
     */
    protected function getRuleType()
    {
        return ExitTemplate::class;
    }
}
