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
 * Copyright (c) 2013-2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\tests;

use qtism\common\datatypes\QtiDatatype;
use qtism\common\enums\Cardinality;
use qtism\data\state\ResponseValidityConstraint;
use qtism\runtime\common\Utils as RuntimeUtils;
use qtism\runtime\expressions\operators\Utils as OperatorUtils;
use \RuntimeException;

/**
 * Utility methods for Tests.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Utils
{
    /**
     * Wheter or not a QtiDatatype object is considered valid against a given ResponseValidityConstraint object $constraint.
     * 
     * Please note that pattern masks described by the $constraint will only be applied on variables with the
     * QTI String baseType. Moreover, null values given as a $response will be considered to have no cardinality
     * i.e. count($response) = 0.
     * 
     * @param \qtism\common\datatypes\QtiDatatype $response
     * @param \qtism\data\state\ResponseValidityConstraint $constraint
     * @throws \RuntimeException If An error occured while validating a patternMask.
     * @return boolean
     */
    static public function isResponseValid(QtiDatatype $response = null, ResponseValidityConstraint $constraint)
    {
        $min = $constraint->getMinConstraint();
        $max = $constraint->getMaxConstraint();
        $cardinality = (is_null($response) === true) ? Cardinality::SINGLE : $response->getCardinality();
        
        if (RuntimeUtils::isNull($response) === true) {
            $count = 0;
        } elseif ($cardinality === Cardinality::SINGLE || $cardinality === Cardinality::RECORD) {
            $count = 1;
        } else {
            $count = count($response);
        }
        
        // Cardinality check...
        if ($count < $min || ($max !== 0 && $count > $max)) {
            return false;
        }
        
        // Pattern Mask check...
        if (($patternMask = $constraint->getPatternMask()) !== '' && is_null($response) === false && $response->getBaseType() === BaseType::STRING) {
            $patternMask = OperatorUtils::prepareXsdPatternForPcre($patternMask);
            $result = @preg_match($patternMask, $response->getValue());
            
            if ($result === 0) {
                return false;
            } elseif ($result === false) {
                throw new \RuntimeException(OperatorUtils::lastPregErrorMessage());
            }
        }
        
        return true;
    }
}
