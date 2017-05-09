<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 09.05.17
 * Time: 15:44
 */

namespace qtism\data\expressions;

/**
 * @TODO doc + verify if really needed
 * Allows to check if the expression or function is pure or not,
 * @package qtism\data\expressions
 *
 * @author Tom Verhoof <tomv@taotesting.com>
 */

interface QtiPLisable
{
    /**
     * Transforms an expression into a Qti-PL string.
     *
     *@return string A Qti-PL representation of the expression
     */

    public function toQtiPL();
}