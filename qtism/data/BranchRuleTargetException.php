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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Tom Verhoof <tomv@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data;

use Exception;

/**
 * An exception to be thrown when an error occurs while dealing with
 * target of BranchingRules in AssessmentTest.
 */
class BranchRuleTargetException extends Exception
{
    /**
     * The target is unknown.
     *
     * @var int
     */
    public const UNKNOWN_TARGET = 0;

    /**
     * The target may or will cause a recursive loop in the test.
     *
     * @var int
     */
    public const RECURSIVE_BRANCHING = 1;

    /**
     * The target may or will go to an item already passed.
     *
     * @var int
     */
    public const BACKWARD_BRANCHING = 2;

    /**
     * @var QtiComponent The AssessmentTest, AssessmentSection or Assessment ItemRef whose BranchRule caused
     * this Exception.
     */

    private $source;

    /**
     * BranchRuleTargetException object.
     *
     * @param string $message A human-readable message.
     * @param int $code A exception code (see class constants).
     * @param QtiComponent A QtiComponent from where the Exception comes from.
     * @param Exception $previous An eventual previous Exception object.
     */
    public function __construct($message, $code = 0, $source = null, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
