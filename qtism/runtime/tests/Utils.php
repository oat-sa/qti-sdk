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

namespace qtism\runtime\tests;

use qtism\common\datatypes\QtiDatatype;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\state\ResponseValidityConstraint;
use qtism\runtime\common\Utils as RuntimeUtils;
use qtism\runtime\expressions\operators\Utils as OperatorUtils;
use RuntimeException;

/**
 * Utility methods for Tests.
 */
class Utils
{
    private const MAX_ENTRY_RESTRICTION_PATTERN = '/^\/\^(?P<splitPattern>\([^{]+)\{(?P<min>\d+)(?:(?P<isRange>,)|,(?P<max>\d+))?\}\$\/\w*$/';
    private const SOURCE_MAX_WORDS_SPLIT_PATTERN = '(?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*)';
    private const MAX_WORDS_SPLIT_PATTERN = '/[\s.,:;?!&#%\/*+=]+/';
    private const CLOSE_MATCH_GROUP_TOKEN = ')';
    private const OPEN_MATCH_GROUP_TOKEN = '(';

    /**
     * Whether a QtiDatatype object is considered valid against a given ResponseValidityConstraint object $constraint.
     *
     * Min and Max constraints will be checked first, followed by the patternMask check.
     *
     * Please note that pattern masks described by the $constraint will only be applied on variables with the
     * QTI String baseType. In case of a patternMask to be applied on a Multiple or Ordered Container, the patternMask
     * will be applied on all String values within the Container. All String values have to comply with the patternMask
     * to see the whole Container being validated. In case of an Empty Multiple or Ordered Container with a PatternMask,
     * the method will return true as there is no String values to be validated. PatternMask are not checked against Record
     * Containers.
     *
     * Moreover, null values given as a $response will be considered to have no cardinality i.e. count($response) = 0.
     *
     * @param QtiDatatype|null $response
     * @param ResponseValidityConstraint $constraint
     * @return bool
     */
    public static function isResponseValid(QtiDatatype $response = null, ResponseValidityConstraint $constraint): bool
    {
        $min = $constraint->getMinConstraint();
        $max = $constraint->getMaxConstraint();
        $cardinality = ($response === null) ? Cardinality::SINGLE : $response->getCardinality();

        if (($isNull = RuntimeUtils::isNull($response)) === true) {
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
        if (($patternMask = $constraint->getPatternMask()) !== '' && $isNull === false && ($response->getBaseType() === BaseType::STRING || $response->getBaseType() === -1 && isset($response['stringValue']))) {
            if ($response->getCardinality() === Cardinality::RECORD) {
                // Record cadinality, only used in conjunction with stringInteraction in core QTI (edge-case).
                $values = [$response['stringValue']];
            } else {
                // Single, Multiple, or Ordered cardinality.
                $values = ($cardinality === Cardinality::SINGLE) ? [$response->getValue()] : $response->getArrayCopy();
            }

            $patternMask = OperatorUtils::prepareXsdPatternForPcre($patternMask);

            $isMaxEntryRestriction = preg_match(self::MAX_ENTRY_RESTRICTION_PATTERN, $patternMask, $matches)
                && self::isSingleMatchGroup($patternMask)
                && $matches['splitPattern'] === self::SOURCE_MAX_WORDS_SPLIT_PATTERN;


            foreach ($values as $value) {
                if ($isMaxEntryRestriction) {
                    [$min, $max] = self::extractMaxEntryRestrictions($matches);
                    $entries = count(array_filter(preg_split(self::MAX_WORDS_SPLIT_PATTERN, $value)));
                    if ($entries > $max || $entries < $min) {
                        return false;
                    }
                } else {
                    $normalizedValue = preg_replace('/\n/', '', (string)$value);
                    $result = @preg_match($patternMask, $normalizedValue);

                    if ($result === 0) {
                        return false;
                    } elseif ($result === false) {
                        throw new RuntimeException(OperatorUtils::lastPregErrorMessage());
                    }
                }
            }
        }

        // Associations check...
        if ($response !== null && $cardinality !== Cardinality::RECORD && ($response->getBaseType() === BaseType::PAIR || $response->getBaseType() === BaseType::DIRECTED_PAIR)) {
            $toCheck = ($cardinality === Cardinality::SINGLE) ? [$response] : $response->getArrayCopy();

            foreach ($constraint->getAssociationValidityConstraints() as $associationConstraint) {
                $associations = 0;
                $identifier = $associationConstraint->getIdentifier();

                foreach ($toCheck as $pair) {
                    if ($pair->getFirst() === $identifier) {
                        $associations++;
                    }

                    if ($pair->getSecond() === $identifier) {
                        $associations++;
                    }
                }

                $min = $associationConstraint->getMinConstraint();
                $max = $associationConstraint->getMaxConstraint();
                if ($associations < $min || ($max !== 0 && $associations > $max)) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function isSingleMatchGroup(string $patternMask): bool
    {
        $closeBracketPosition = strpos($patternMask, self::CLOSE_MATCH_GROUP_TOKEN);
        return strpos(substr($patternMask, $closeBracketPosition), self::OPEN_MATCH_GROUP_TOKEN) === false;
    }

    /**
     * @return array [(string)$splitPattern, (int)$min, (int)$max]
     */
    private static function extractMaxEntryRestrictions(array $matches): array
    {
        extract($matches);
        $isRange = !empty($isRange);
        $max ??= $isRange ? PHP_INT_MAX : $min;

        return [(int)$min, (int)$max];
    }
}
