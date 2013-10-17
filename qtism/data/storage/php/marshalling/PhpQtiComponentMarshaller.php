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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package
 */

namespace qtism\data\storage\php\marshalling;

use qtism\data\storage\php\PhpVariable;
use qtism\data\storage\php\PhpArgument;
use qtism\data\storage\php\PhpArgumentCollection;
use qtism\common\beans\Bean;
use qtism\data\QtiComponent;

class PhpQtiComponentMarshaller extends PhpMarshaller {
    
    public function marshall() {
        $ctx = $this->getContext();
        $access = $ctx->getStreamAccess();
        $component = $this->getToMarshall();
        
        try {
            $bean = new Bean($component, true);
            
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
            
            
            $componentVarName = $ctx->generateVariableName($component);
            
            $access->writeVariable($componentVarName);
            $access->writeEquals($ctx->mustFormatOutput());
            $access->writeInstantiation(get_class($component), $phpArgs);
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
        }
        catch (BeanException $e) {
            $msg = "The given QtiComponent to be marshalled into PHP source code is not a strict bean.";
            throw new PhpMarshallingException($msg, PhpMarshallingException::RUNTIME, $e);
        }
    }
    
    protected function isMarshallable($toMarshall) {
        return $toMarshall instanceof QtiComponent;
    }
}