<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 10.04.17
 * Time: 09:11
 */

namespace qtism\runtime\tests;

class BranchRuleTargetException extends AssessmentTestSessionException
{
    /**
     * The target is unknown.
     *
     * @var integer
     */
    const UNKNOWN_TARGET = 30;


    /**
     * The target may or will cause a recursive loop in the test.
     *
     * @var integer
     */
    const RECURSIVE_BRANCHING = 31;

    /**
     * The target may or will go to an item already passed.
     *
     * @var integer
     */
    const BACKWARD_BRANCHING = 32;

    /**
     * @var QtiComponent The AssessmentTest, AssessmentSection or Assessment ItemRef whose BranchRule caused
     * this Exception.
     */

    private $source;

    /**
     * BranchRuleTargetException object.
     *
     * @param string $message A human-readable message.
     * @param integer $code A exception code (see class constants).
     * @param QtiComponent A QtiComponent from where the Exception comes from.
     * @param \Exception $previous An eventual previous Exception object.
     */
    public function __construct($message, $code = 0, $source = null, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}