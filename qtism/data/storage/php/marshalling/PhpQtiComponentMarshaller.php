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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\php\marshalling;

use qtism\common\beans\Bean;
use qtism\common\beans\BeanException;
use qtism\data\QtiComponent;
use qtism\data\storage\php\PhpArgument;
use qtism\data\storage\php\PhpArgumentCollection;
use qtism\data\storage\php\PhpVariable;

/**
 * A PHP Marshaller implementation focusing on QtiComponent marshalling.
 *
 * @see \qtism\data\QtiComponent
 */
class PhpQtiComponentMarshaller extends PhpMarshaller
{
    /**
     * The name of the variable the QtiComponent
     * has to be set to.
     *
     * @var string
     */
    private $variableName = '';

    /**
     * Marshall the QtiComponent as an instance of...
     *
     * @var string
     */
    private $asInstanceOf = '';

    /**
     * PhpQtiComponentMarshaller constructor.
     *
     * @param PhpMarshallingContext $context
     * @param $toMarshall
     */
    public function __construct(PhpMarshallingContext $context, $toMarshall)
    {
        parent::__construct($context, $toMarshall);
    }

    /**
     * The QtiComponent to be marshalled has to be an instance of...
     *
     * @param string $asInstanceOf
     */
    public function setAsInstanceOf($asInstanceOf)
    {
        $this->asInstanceOf = $asInstanceOf;
    }

    /**
     * The QtiComponent to be marshalled has to be an instance of...
     *
     * @return string
     */
    public function getAsInstanceOf()
    {
        return $this->asInstanceOf;
    }

    /**
     * Set the name of the variable the QtiComponent has to be set to.
     *
     * @param string $variableName
     */
    public function setVariableName($variableName)
    {
        $this->variableName = $variableName;
    }

    /**
     * Get the name of the variable the QtiComponent has to be set to.
     *
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    public function marshall()
    {
        $ctx = $this->getContext();
        $access = $ctx->getStreamAccess();
        $component = $this->getToMarshall();

        try {
            $asInstanceOf = $this->getAsInstanceOf();
            $bean = new Bean($component, false, $asInstanceOf);

            // -- Component Instantiation.
            $ctorArgs = $bean->getConstructorParameters();
            $ctorArgsCount = count($ctorArgs);

            $phpArgs = new PhpArgumentCollection();

            if ($ctorArgsCount > 0) {
                $poppedVarNames = $ctx->popFromVariableStack($ctorArgsCount);

                for ($i = 0; $i < $ctorArgsCount; $i++) {
                    $phpArgs[] = new PhpArgument(new PhpVariable($poppedVarNames[$i]));
                }
            }

            $componentVarName = $this->getVariableName();
            $componentVarName = (empty($componentVarName)) ? $ctx->generateVariableName($component) : $componentVarName;

            $access->writeVariable($componentVarName);
            $access->writeEquals($ctx->mustFormatOutput());
            $access->writeInstantiation((empty($asInstanceOf)) ? get_class($component) : $asInstanceOf, $phpArgs);
            $access->writeSemicolon($ctx->mustFormatOutput());

            // -- Call to setters (that are not involved in the component construction).
            $setters = $bean->getSetters(true);
            $settersCount = count($setters);

            if ($settersCount > 0) {
                $poppedVarNames = $ctx->popFromVariableStack($settersCount);

                for ($i = 0; $i < $settersCount; $i++) {
                    $phpArgs = new PhpArgumentCollection();
                    $phpArgs[] = new PhpArgument(new PhpVariable($poppedVarNames[$i]));
                    $access->writeMethodCall($componentVarName, $setters[$i]->getName(), $phpArgs);
                    $access->writeSemicolon($ctx->mustFormatOutput());
                }
            }

            $ctx->pushOnVariableStack($componentVarName);
        } catch (BeanException $e) {
            $msg = 'The given QtiComponent to be marshalled into PHP source code is not a strict bean.';
            throw new PhpMarshallingException($msg, PhpMarshallingException::RUNTIME, $e);
        }
    }

    /**
     * @param mixed $toMarshall
     * @return bool
     */
    protected function isMarshallable($toMarshall)
    {
        return $toMarshall instanceof QtiComponent;
    }
}
