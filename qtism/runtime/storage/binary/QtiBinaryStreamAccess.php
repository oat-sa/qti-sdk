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

namespace qtism\runtime\storage\binary;

use Exception;
use InvalidArgumentException;
use OutOfBoundsException;
use qtism\common\datatypes\files\FileHash;
use qtism\common\datatypes\files\FileManager;
use qtism\common\datatypes\files\FileManagerException;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFile;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiScalar;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\storage\BinaryStreamAccess;
use qtism\common\storage\BinaryStreamAccessException;
use qtism\common\storage\IStream;
use qtism\common\storage\StreamAccessException;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentSectionCollection;
use qtism\data\IAssessmentItem;
use qtism\data\ItemSessionControl;
use qtism\data\rules\BranchRuleCollection;
use qtism\data\rules\PreConditionCollection;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\VariableDeclaration;
use qtism\data\TestPart;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\common\Utils;
use qtism\runtime\common\Variable;
use qtism\runtime\common\VariableFactory;
use qtism\runtime\common\VariableFactoryInterface;
use qtism\runtime\storage\common\AssessmentTestSeeker;
use qtism\runtime\tests\AbstractSessionManager;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\PendingResponses;
use qtism\runtime\tests\RouteItem;

/**
 * The QtiBinaryStreamAccess aims at providing access to QTI data stored
 * in a binary form such as variable values, item sessions, ...
 */
class QtiBinaryStreamAccess extends BinaryStreamAccess
{
    public const RW_VALUE = 0;
    public const RW_DEFAULTVALUE = 1;
    public const RW_CORRECTRESPONSE = 2;

    /** @var FileManager */
    private $fileManager;

    /** @var VariableFactoryInterface */
    private $variableFactory;

    /**
     * Create a new QtiBinaryStreamAccess object.
     *
     * @param IStream $stream The IStream object to be accessed.
     * @param FileManager $fileManager The FileManager object to handle file variable.
     * @throws StreamAccessException
     */
    public function __construct(
        IStream $stream,
        FileManager $fileManager,
        VariableFactoryInterface $variableFactory = null
    ) {
        parent::__construct($stream);
        $this->setFileManager($fileManager);
        $this->variableFactory = $variableFactory ?? new VariableFactory();
    }

    /**
     * Set the FileManager object.
     *
     * @param FileManager $fileManager A FileManager object.
     */
    protected function setFileManager(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * Get the FileManager object.
     *
     * @return FileManager
     */
    protected function getFileManager()
    {
        return $this->fileManager;
    }

    /**
     * Read and fill the Variable $variable with its value contained
     * in the current stream.
     *
     * @param Variable $variable A QTI Runtime Variable object.
     * @param int The kind of value to be read (self::RW_VALUE | self::RW_DEFAULTVALUE | self::RW_CORRECTRESPONSE)
     * @throws BinaryStreamAccessException If an error occurs at the binary level.
     */
    public function readVariableValue(Variable $variable, $valueType = self::RW_VALUE)
    {
        switch ($valueType) {
            case self::RW_DEFAULTVALUE:
                $setterToCall = 'setDefaultValue';
                break;

            case self::RW_CORRECTRESPONSE:
                $setterToCall = 'setCorrectResponse';
                break;

            default:
                $setterToCall = 'setValue';
                break;
        }

        try {
            $isNull = $this->readBoolean();

            if ($isNull === true) {
                // Nothing more to be read.
                $variable->$setterToCall(null);
                return;
            }

            $count = ($this->readBoolean() === true) ? 1 : $this->readShort();

            $cardinality = $variable->getCardinality();
            $baseType = $variable->getBaseType();

            if ($cardinality === Cardinality::RECORD) {
                // Deal with records.
                $values = new RecordContainer();
                for ($i = 0; $i < $count; $i++) {
                    $isNull = $this->readBoolean();
                    $val = $this->readRecordField($isNull);
                    $values[$val[0]] = $val[1];
                }

                $variable->$setterToCall($values);
            } else {
                $toCall = 'read' . ucfirst(BaseType::getNameByConstant($baseType));

                if ($cardinality === Cardinality::SINGLE) {
                    // Deal with a single value.
                    $variable->$setterToCall(Utils::valueToRuntime($this->$toCall(), $baseType));
                } else {
                    // Deal with multiple values.
                    $values = ($cardinality === Cardinality::MULTIPLE) ? new MultipleContainer($baseType) : new OrderedContainer($baseType);
                    for ($i = 0; $i < $count; $i++) {
                        $isNull = $this->readBoolean();
                        $values[] = ($isNull === true) ? null : Utils::valueToRuntime($this->$toCall(), $baseType);
                    }

                    $variable->$setterToCall($values);
                }
            }
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading a Variable value.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::VARIABLE, $e);
        } catch (InvalidArgumentException $e) {
            $msg = "Datatype mismatch for variable '" . $variable->getIdentifier() . "'.";
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::VARIABLE, $e);
        }
    }

    /**
     * Write the value of $variable in the current binary stream.
     *
     * @param Variable $variable A QTI Runtime Variable object.
     * @param int $valueType
     * @throws QtiBinaryStreamAccessException
     */
    public function writeVariableValue(Variable $variable, $valueType = self::RW_VALUE)
    {
        switch ($valueType) {
            case self::RW_DEFAULTVALUE:
                $getterToCall = 'getDefaultValue';
                break;

            case self::RW_CORRECTRESPONSE:
                $getterToCall = 'getCorrectResponse';
                break;

            default:
                $getterToCall = 'getValue';
        }

        try {
            $value = $variable->$getterToCall();
            $cardinality = $variable->getCardinality();
            $baseType = $variable->getBaseType();

            if ($value === null) {
                $this->writeBoolean(true);

                return;
            }

            // Non-null value.
            $this->writeBoolean(false);

            if ($cardinality === Cardinality::RECORD) {
                // is-scalar
                $this->writeBoolean(false);

                // count
                $this->writeShort(count($value));

                // content
                foreach ($value as $k => $v) {
                    $this->writeRecordField([$k, $v], $v === null);
                }
            } else {
                $toCall = 'write' . ucfirst(BaseType::getNameByConstant($baseType));

                if ($cardinality === Cardinality::SINGLE) {
                    // is-scalar
                    $this->writeBoolean(true);

                    // content
                    $this->$toCall(($value instanceof QtiScalar) ? $value->getValue() : $value);
                } else {
                    // is-scalar
                    $this->writeBoolean(false);

                    // count
                    $this->writeShort(count($value));

                    // MULTIPLE or ORDERED
                    foreach ($value as $v) {
                        if ($v !== null) {
                            $this->writeBoolean(false);
                            $this->$toCall(($v instanceof QtiScalar) ? $v->getValue() : $v);
                        } else {
                            $this->writeBoolean(true);
                        }
                    }
                }
            }
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing a Variable value.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::VARIABLE, $e);
        }
    }

    /**
     * Read a record field value from the current binary stream. A record field is
     * composed of a key string and a value.
     *
     * @param bool $isNull
     * @return array An array where the value at index 0 is the key string and index 1 is the value.
     * @throws QtiBinaryStreamAccessException
     */
    public function readRecordField($isNull = false)
    {
        try {
            $key = $this->readString();

            if ($isNull === false) {
                $baseType = $this->readTinyInt();

                $toCall = 'read' . ucfirst(BaseType::getNameByConstant($baseType));
                $value = Utils::valueToRuntime($this->$toCall(), $baseType);
            } else {
                $value = null;
            }

            return [$key, $value];
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading a Record Field.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::RECORDFIELD, $e);
        }
    }

    /**
     * Write a record field value in the current binary stream. A record field is composed of a key string and a value.
     *
     * @param array $recordField An array where index 0 is the key string, and the index 1 is the value.
     * @param bool $isNull
     * @throws QtiBinaryStreamAccessException
     */
    public function writeRecordField(array $recordField, $isNull = false)
    {
        try {
            $this->writeBoolean($isNull);
            $key = $recordField[0];
            $this->writeString($key);

            if ($isNull === false) {
                $value = $recordField[1];
                $baseType = Utils::inferBaseType($value);

                $this->writeTinyInt($baseType);
                $toCall = 'write' . ucfirst(BaseType::getNameByConstant($baseType));
                if ($value instanceof MultipleContainer) {
                    foreach ($value as $singleRecordValue) {
                        $this->$toCall($singleRecordValue);
                    }
                    return;
                }

                $this->$toCall($value instanceof QtiScalar ? $value->getValue() : $value);
            }
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing a Record Field.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::RECORDFIELD);
        }
    }

    /**
     * Read an identifier from the current binary stream.
     *
     * @return string An identifier.
     * @throws QtiBinaryStreamAccessException
     */
    public function readIdentifier()
    {
        try {
            return $this->readString();
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading an identifier.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::IDENTIFIER, $e);
        }
    }

    /**
     * Write an identifier in the current binary stream.
     *
     * @param string $identifier A QTI Identifier.
     * @throws QtiBinaryStreamAccessException
     */
    public function writeIdentifier($identifier)
    {
        try {
            $this->writeString($identifier);
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing an identifier.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::IDENTIFIER, $e);
        }
    }

    /**
     * Read a Point from the current binary stream.
     *
     * @return QtiPoint A QtiPoint object.
     * @throws QtiBinaryStreamAccessException
     */
    public function readPoint()
    {
        try {
            return new QtiPoint($this->readShort(), $this->readShort());
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading a point.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::POINT, $e);
        }
    }

    /**
     * Write a Point in the current binary stream.
     *
     * @param QtiPoint $point A QtiPoint object.
     * @throws QtiBinaryStreamAccessException
     */
    public function writePoint(QtiPoint $point)
    {
        try {
            $this->writeShort($point->getX());
            $this->writeShort($point->getY());
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing a point.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::POINT, $e);
        }
    }

    /**
     * Read a Pair from the current binary stream.
     *
     * @return QtiPair A QtiPair object.
     * @throws QtiBinaryStreamAccessException
     */
    public function readPair()
    {
        try {
            return new QtiPair($this->readString(), $this->readString());
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading a pair.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::PAIR, $e);
        }
    }

    /**
     * Write a Pair in the current binary stream.
     *
     * @param QtiPair $pair A Pair object.
     * @throws QtiBinaryStreamAccessException
     */
    public function writePair(QtiPair $pair)
    {
        try {
            $this->writeString($pair->getFirst());
            $this->writeString($pair->getSecond());
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing a pair.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::PAIR, $e);
        }
    }

    /**
     * Read a DirectedPair from the current binary stream.
     *
     * @return QtiDirectedPair A DirectedPair object.
     * @throws QtiBinaryStreamAccessException
     */
    public function readDirectedPair()
    {
        try {
            return new QtiDirectedPair($this->readString(), $this->readString());
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading a directedPair.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::DIRECTEDPAIR, $e);
        }
    }

    /**
     * Write a DirectedPair in the current binary stream.
     *
     * @param QtiDirectedPair $directedPair A DirectedPair object.
     * @throws QtiBinaryStreamAccessException
     */
    public function writeDirectedPair(QtiDirectedPair $directedPair)
    {
        try {
            $this->writeString($directedPair->getFirst());
            $this->writeString($directedPair->getSecond());
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing a directedPair.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::DIRECTEDPAIR, $e);
        }
    }

    /**
     * Read a Duration from the current binary stream.
     *
     * @return QtiDuration A QtiDuration object.
     * @throws QtiBinaryStreamAccessException
     */
    public function readDuration()
    {
        try {
            return new QtiDuration($this->readString());
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading a duration.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::DURATION, $e);
        }
    }

    /**
     * Write a Duration in the current binary stream.
     *
     * @param QtiDuration $duration A QtiDuration object.
     * @throws QtiBinaryStreamAccessException
     */
    public function writeDuration(QtiDuration $duration)
    {
        try {
            $this->writeString($duration->__toString());
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing a duration.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::DURATION, $e);
        }
    }

    /**
     * Read a URI (Uniform Resource Identifier) from the current binary stream.
     *
     * @return string A URI.
     * @throws QtiBinaryStreamAccessException
     */
    public function readUri()
    {
        try {
            return $this->readString();
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading a URI.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::URI, $e);
        }
    }

    /**
     * Write a URI (Uniform Resource Identifier) in the current binary stream.
     *
     * @param string $uri A URI.
     * @throws QtiBinaryStreamAccessException
     */
    public function writeUri($uri)
    {
        try {
            $this->writeString($uri);
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing a URI.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::URI, $e);
        }
    }

    /**
     * Read an intOrIdentifier from the current binary stream.
     *
     * @return int|string An integer or a string depending on the nature of the intOrIdentifier datatype.
     * @throws QtiBinaryStreamAccessException
     */
    public function readIntOrIdentifier()
    {
        try {
            $isInt = $this->readBoolean();

            return ($isInt === true) ? $this->readInteger() : $this->readIdentifier();
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading an intOrIdentifier.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::INTORIDENTIFIER, $e);
        }
    }

    /**
     * Write an intOrIdentifier in the current binary stream.
     *
     * @param int|string $intOrIdentifier An integer or a string value.
     * @throws QtiBinaryStreamAccessException
     */
    public function writeIntOrIdentifier($intOrIdentifier)
    {
        try {
            if (is_int($intOrIdentifier)) {
                $this->writeBoolean(true);
                $this->writeInteger($intOrIdentifier);
            } elseif (is_string($intOrIdentifier)) {
                $this->writeBoolean(false);
                $this->writeString($intOrIdentifier);
            } else {
                $msg = "The intOrIdentifier value to be written must be an integer or a string, '" . gettype($intOrIdentifier) . "' given.";
                throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::INTORIDENTIFIER);
            }
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing an intOrIdentifier.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::INTORIDENTIFIER, $e);
        }
    }

    /**
     * Read an AssessmentItemSession from the current binary stream.
     *
     * @param AbstractSessionManager $manager
     * @param AssessmentTestSeeker $seeker An AssessmentTestSeeker object from where 'assessmentItemRef', 'outcomeDeclaration' and 'responseDeclaration' QTI components will be pulled out.
     * @param QtiBinaryVersion $version
     * @return AssessmentItemSession
     * @throws QtiBinaryStreamAccessException
     */
    public function readAssessmentItemSession(
        AbstractSessionManager $manager,
        AssessmentTestSeeker $seeker,
        QtiBinaryVersion $version
    ) {
        try {
            $itemRefPosition = $this->readShort();
            /** @var IAssessmentItem $assessmentItemRef */
            $assessmentItemRef = $seeker->seekComponent('assessmentItemRef', $itemRefPosition);

            $session = $manager->createAssessmentItemSession($assessmentItemRef);
            $session->setAssessmentItem($assessmentItemRef);
            $session->setState($this->readTinyInt());
            $session->setNavigationMode($this->readTinyInt());
            $session->setSubmissionMode($this->readTinyInt());

            // The is-attempting field was added in Binary Storage v2.
            if ($version->storesAttempting()) {
                $session->setAttempting($this->readBoolean());
            }

            if ($this->readBoolean() === true) {
                /** @var ItemSessionControl $itemSessionControl */
                $itemSessionControl = $seeker->seekComponent('itemSessionControl', $this->readShort());
                $session->setItemSessionControl($itemSessionControl);
            }

            if ($session->getState() !== AssessmentItemSessionState::NOT_SELECTED) {
                $session['numAttempts'] = new QtiInteger($this->readTinyInt());
                $session['duration'] = $this->readDuration();
                $session['completionStatus'] = new QtiIdentifier($this->readString());

                if ($session['numAttempts']->getValue() > 0) {
                    $session->setTimeReference($this->readDateTime());
                }
            }

            // Read the number of item-specific variables involved in the session.
            $varCount = $version->storesVariableCountAsInteger()
                ? $this->readInteger()
                : $this->readTinyInt();

            for ($i = 0; $i < $varCount; $i++) {
                $isOutcome = $this->readBoolean();
                $varPosition = $this->readShort();

                try {
                    /** @var VariableDeclaration $variableDeclaration */
                    $variableDeclaration = $seeker->seekComponent(
                        ($isOutcome === true) ? 'outcomeDeclaration' : 'responseDeclaration',
                        $varPosition
                    );
                } catch (OutOfBoundsException $e) {
                    $msg = "No variable found at position ${varPosition} in the assessmentTest tree structure.";
                    throw new QtiBinaryStreamAccessException(
                        $msg,
                        $this,
                        QtiBinaryStreamAccessException::ITEM_SESSION,
                        $e
                    );
                }

                $variable = $session->getVariable($variableDeclaration->getIdentifier())
                    ?? $this->variableFactory->createFromDataModel($variableDeclaration);

                // If we are here, we have our variable.
                $this->readVariableValue($variable);
                $session->setVariable($variable);
            }

            return $session;
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading an assessment item session.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::ITEM_SESSION, $e);
        } catch (OutOfBoundsException $e) {
            $msg = "No assessmentItemRef found at position ${itemRefPosition} in the assessmentTest tree structure.";
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::ITEM_SESSION, $e);
        }
    }

    /**
     * Write an AssessmetnItemSession from the current binary stream.
     *
     * @param AssessmentTestSeeker $seeker The AssessmentTestSeeker object from where the position of components will be pulled out.
     * @param AssessmentItemSession $session An AssessmentItemSession object.
     * @throws QtiBinaryStreamAccessException
     */
    public function writeAssessmentItemSession(AssessmentTestSeeker $seeker, AssessmentItemSession $session)
    {
        try {
            $this->writeShort($seeker->seekPosition($session->getAssessmentItem()));
            $this->writeTinyInt($session->getState());
            $this->writeTinyInt($session->getNavigationMode());
            $this->writeTinyInt($session->getSubmissionMode());
            $this->writeBoolean($session->isAttempting());

            $isItemSessionControlDefault = $session->getItemSessionControl()->isDefault();
            if ($isItemSessionControlDefault === true) {
                $this->writeBoolean(false);
            } else {
                $this->writeBoolean(true);
                $this->writeShort($seeker->seekPosition($session->getItemSessionControl()));
            }

            if ($session->getState() !== AssessmentItemSessionState::NOT_SELECTED) {
                $this->writeTinyInt($session['numAttempts']->getValue());
                $this->writeDuration($session['duration']);
                $this->writeString($session['completionStatus']->getValue());

                if ($session['numAttempts']->getValue() > 0) {
                    $this->writeDateTime($session->getTimeReference());
                }
            }

            // minus the 3 built-in variables
            $varCount = count($session) - 3;
            $this->writeInteger($varCount);

            $itemOutcomes = $session->getAssessmentItem()->getOutcomeDeclarations();
            $itemResponses = $session->getAssessmentItem()->getResponseDeclarations();

            foreach ($session->getKeys() as $varId) {
                if (in_array($varId, ['numAttempts', 'duration', 'completionStatus']) === false) {
                    $var = $session->getVariable($varId);
                    $isOutcome = $var instanceof OutcomeVariable;
                    /** @var VariableDeclaration $variableDeclaration */
                    $variableDeclaration = ($isOutcome === true) ? $itemOutcomes[$varId] : $itemResponses[$varId];

                    try {
                        $this->writeBoolean($isOutcome);
                        $this->writeShort($seeker->seekPosition($variableDeclaration));
                        $this->writeVariableValue($var);
                    } catch (OutOfBoundsException $e) {
                        $msg = 'No variable found in the assessmentTest tree structure.';
                        throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::ITEM_SESSION, $e);
                    }
                }
            }
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing an assessment item session.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::ITEM_SESSION, $e);
        } catch (OutOfBoundsException $e) {
            $msg = 'No assessmentItemRef found in the assessmentTest tree structure.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::ITEM_SESSION, $e);
        }
    }

    /**
     * Read a route item from the current binary stream.
     *
     * @param AssessmentTestSeeker $seeker An AssessmentTestSeeker object where components will be pulled out by position.
     * @param QtiBinaryVersion $version
     *
     * @return RouteItem
     * @throws QtiBinaryStreamAccessException
     */
    public function readRouteItem(AssessmentTestSeeker $seeker, QtiBinaryVersion $version)
    {
        try {
            $occurrence = $this->readTinyInt();
            /** @var AssessmentItemRef $itemRef */
            $itemRef = $seeker->seekComponent('assessmentItemRef', $this->readShort());

            // Prior to version 3, only a singe assessmentSection might be bound to the RouteItem.
            if (!$version->storesMultipleSections()) {
                $sections = $seeker->seekComponent('assessmentSection', $this->readShort());
            }

            /** @var TestPart $testPart */
            $testPart = $seeker->seekComponent('testPart', $this->readShort());

            // From version 3, multiple assessmentSections might be bound to the RouteItem.
            if ($version->storesMultipleSections()) {
                $sectionsCount = $this->readTinyInt();
                $sections = new AssessmentSectionCollection();

                for ($i = 0; $i < $sectionsCount; $i++) {
                    $sections[] = $seeker->seekComponent('assessmentSection', $this->readShort());
                }
            }

            $branchRulesCount = $this->readTinyInt();
            $branchRules = new BranchRuleCollection();

            for ($i = 0; $i < $branchRulesCount; $i++) {
                $branchRules[] = $seeker->seekComponent('branchRule', $this->readShort());
            }

            $preConditionsCount = $this->readTinyInt();
            $preConditions = new PreConditionCollection();

            for ($i = 0; $i < $preConditionsCount; $i++) {
                $preConditions[] = $seeker->seekComponent('preCondition', $this->readShort());
            }

            $routeItem = new RouteItem($itemRef, $sections, $testPart, $seeker->getAssessmentTest());
            $routeItem->setOccurence($occurrence);
            $routeItem->setBranchRules($branchRules);
            $routeItem->setPreConditions($preConditions);

            return $routeItem;
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading a route item.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::ROUTE_ITEM, $e);
        } catch (OutOfBoundsException $e) {
            $msg = 'A QTI Component was not found in the assessmentTest tree structure.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::ROUTE_ITEM, $e);
        }
    }

    /**
     * Write a route item in the current binary stream.
     *
     * @param AssessmentTestSeeker $seeker An AssessmentTestSeeker object in order to know tree position for involved QTI Components.
     * @param RouteItem $routeItem A RouteItem object.
     * @throws QtiBinaryStreamAccessException
     */
    public function writeRouteItem(AssessmentTestSeeker $seeker, RouteItem $routeItem)
    {
        try {
            $this->writeTinyInt($routeItem->getOccurence());
            $this->writeShort($seeker->seekPosition($routeItem->getAssessmentItemRef()));
            $this->writeShort($seeker->seekPosition($routeItem->getTestPart()));

            $assessmentSections = $routeItem->getAssessmentSections();
            $this->writeTinyInt(count($assessmentSections));

            foreach ($assessmentSections as $assessmentSection) {
                $this->writeShort($seeker->seekPosition($assessmentSection));
            }

            $branchRules = $routeItem->getBranchRules();
            $this->writeTinyInt(count($branchRules));

            foreach ($branchRules as $branchRule) {
                $this->writeShort($seeker->seekPosition($branchRule));
            }

            $preConditions = $routeItem->getPreConditions();
            $this->writeTinyInt(count($preConditions));

            foreach ($preConditions as $preCondition) {
                $this->writeShort($seeker->seekPosition($preCondition));
            }
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing a route item.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::ROUTE_ITEM, $e);
        } catch (OutOfBoundsException $e) {
            $msg = 'A QTI Component position was not found in the assessmentTest tree structure.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::ROUTE_ITEM, $e);
        }
    }

    /**
     * Read a PendingResponse object from the current binary stream.
     *
     * @param AssessmentTestSeeker $seeker An AssessmentTestSeeker object in order to know tree position for involved QTI Components.
     * @return PendingResponses A PendingResponses object.
     * @throws QtiBinaryStreamAccessException
     */
    public function readPendingResponses(AssessmentTestSeeker $seeker)
    {
        try {
            // Read the state.
            $state = new State();
            $varCount = $this->readTinyInt();

            for ($i = 0; $i < $varCount; $i++) {
                /** @var ResponseDeclaration $responseDeclaration */
                $responseDeclaration = $seeker->seekComponent('responseDeclaration', $this->readShort());
                $responseVariable = ResponseVariable::createFromDataModel($responseDeclaration);
                $this->readVariableValue($responseVariable);
                $state->setVariable($responseVariable);
            }

            // Read the assessmentItemRef.
            /** @var AssessmentItemRef $itemRef */
            $itemRef = $seeker->seekComponent('assessmentItemRef', $this->readShort());

            // Read the occurrence number.
            $occurrence = $this->readTinyInt();

            return new PendingResponses($state, $itemRef, $occurrence);
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading some pending responses.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::PENDING_RESPONSES, $e);
        } catch (OutOfBoundsException $e) {
            $msg = 'A QTI component was not found in the assessmentTest tree structure.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::PENDING_RESPONSES, $e);
        }
    }

    /**
     * Write a PendingResponses object in the current binary stream.
     *
     * @param AssessmentTestSeeker $seeker An AssessmentTestSeeker object from where positions in the assessmentTest tree will be pulled out.
     * @param PendingResponses $pendingResponses The read PendingResponses object.
     * @throws QtiBinaryStreamAccessException
     */
    public function writePendingResponses(AssessmentTestSeeker $seeker, PendingResponses $pendingResponses)
    {
        try {
            $state = $pendingResponses->getState();
            $itemRef = $pendingResponses->getAssessmentItemRef();
            $occurrence = $pendingResponses->getOccurence();

            // Write the state.
            $responseDeclarations = $itemRef->getResponseDeclarations();
            $varCount = count($state);
            $this->writeTinyInt($varCount);

            foreach ($state as $responseVariable) {
                $respId = $responseVariable->getIdentifier();
                if (isset($responseDeclarations[$respId])) {
                    $this->writeShort($seeker->seekPosition($responseDeclarations[$respId]));
                    $this->writeVariableValue($responseVariable);
                } else {
                    $msg = "No response variable with identifier '${respId}' found in related assessmentItemRef.";
                    throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::PENDING_RESPONSES);
                }
            }

            // Write the assessmentItemRef.
            $this->writeShort($seeker->seekPosition($itemRef));

            // Write the occurrence number.
            $this->writeTinyInt($occurrence);
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading some pending responses.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::PENDING_RESPONSES, $e);
        } catch (OutOfBoundsException $e) {
            $msg = 'A QTI component position could not be found in the assessmentTest tree structure.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::PENDING_RESPONSES, $e);
        }
    }

    /**
     * Write a QtiFile object in the current binary stream.
     *
     * @param QtiFile $file
     * @throws BinaryStreamAccessException
     * @throws QtiBinaryStreamAccessException
     */
    public function writeFile(QtiFile $file)
    {
        $toPersist = $file instanceof FileHash
            ? json_encode($file)
            : $file->getIdentifier();

        try {
            $this->writeString($toPersist);
        } catch (QtiBinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading a QTI File.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::FILE, $e);
        }
    }

    /**
     * Read a QtiFile object from the current binary stream.
     *
     * @return QtiFile
     * @throws QtiBinaryStreamAccessException
     * @throws FileManagerException
     */
    public function readFile()
    {
        try {
            $id = $this->readString();
        } catch (Exception $e) {
            $msg = 'An error occurred while writing a QTI File.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::FILE, $e);
        }

        $decoded = json_decode($id, true);
        if (is_array($decoded)) {
            return FileHash::createFromArray($decoded);
        }

        return $this->getFileManager()->retrieve($id);
    }

    /**
     * Read the path from the current binary stream.
     *
     * @return array An array of integer values representing flow positions.
     * @throws QtiBinaryStreamAccessException
     */
    public function readPath()
    {
        try {
            $pathCount = $this->readShort();
            $path = [];

            for ($i = 0; $i < $pathCount; $i++) {
                $path[] = $this->readShort();
            }

            return $path;
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while reading the path.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::PATH, $e);
        }
    }

    /**
     * Write the path in the current binary stream.
     *
     * @param array $path An array of integer values representing flow positions.
     * @throws QtiBinaryStreamAccessException
     */
    public function writePath(array $path)
    {
        try {
            $this->writeShort(count($path));
            foreach ($path as $p) {
                $this->writeShort($p);
            }
        } catch (BinaryStreamAccessException $e) {
            $msg = 'An error occurred while writing the path.';
            throw new QtiBinaryStreamAccessException($msg, $this, QtiBinaryStreamAccessException::PATH, $e);
        }
    }
}
