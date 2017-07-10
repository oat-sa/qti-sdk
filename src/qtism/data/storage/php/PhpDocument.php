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
 * Copyright (c) 2013-2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\php;

use qtism\data\ExtendedAssessmentTest;
use qtism\common\datatypes\QtiCoords;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\QtiDocument;
use qtism\data\storage\php\marshalling\PhpQtiDatatypeMarshaller;
use qtism\common\datatypes\QtiDatatype;
use qtism\data\AssessmentSection;
use qtism\data\processing\ResponseProcessing;
use qtism\data\AssessmentItem;
use qtism\data\AssessmentTest;
use qtism\data\storage\php\marshalling\PhpCollectionMarshaller;
use qtism\data\storage\php\marshalling\PhpQtiComponentMarshaller;
use qtism\data\storage\php\marshalling\PhpArrayMarshaller;
use qtism\common\beans\Bean;
use qtism\common\collections\AbstractCollection;
use qtism\data\storage\php\marshalling\PhpScalarMarshaller;
use qtism\common\storage\MemoryStream;
use qtism\data\storage\php\marshalling\PhpMarshallingContext;
use qtism\data\QtiComponent;
use qtism\data\storage\php\Utils as PhpUtils;
use \SplStack;
use \Exception;

/**
 * Represents a PHP source code document containing the appropriate source code
 * to load a QTI Component and the components it contains.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PhpDocument extends QtiDocument
{
    /**
     * Create a new PhpDocument object.
     * 
     * As PHP-serialized QTI documents contain the necessary information about the QTI version they are
     * refering to, the value of the $version argument will simply be ignored. 
     *
     * @param string $version (optional) Expected QTI version (default is "2.1").
     * @param QtiComponent $documentComponent The root QtiComponent object to contained by the PhpDocument.
     */
    public function __construct($version = '2.1', QtiComponent $documentComponent = null)
    {
        parent::__construct($version, $documentComponent);
    }

    /**
     * Save the PhpDocument to a specific location.
     *
     * @param string $url A URL (Uniform Resource Locator) describing where to save the document.
     * @throws PhpStorageException If an error occurs while saving.
     */
    public function save($url)
    {
        $stack = new SplStack();
        $stack->push($this->getDocumentComponent());

        // 1st/2nd pass marker.
        $marker = array();
        
        // marshalling context.
        $stream = new MemoryStream();
        $stream->open();
        $streamAccess = new PhpStreamAccess($stream);
        $streamAccess->writeOpeningTag();
        $ctx = new PhpMarshallingContext($streamAccess);
        $ctx->setFormatOutput(true);

        while (count($stack) > 0) {

            $component = $stack->pop();
            $isMarked = in_array($component, $marker, true);

            if ($isMarked === false && ($component instanceof QtiComponent)) {
                // -- QtiComponent node, 1st pass.
                // Mark as explored.
                array_push($marker, $component);
                // Ask for a 2nd pass.
                $stack->push($component);

                // Let's look at the Bean properties and ask for a future exploration.
                $bean = new Bean($component, false, self::getBaseImplementation($component));
                $ctorGetters = $bean->getConstructorGetters();
                
                // MAKE THE COMPILATION OPTIMIZATION CONFIGURABLE. DEFAULT REMAINS FALSE.
                
                // RETURN ONLY NON-OPTIMIZABLE GETTERS.
                // Create bean method to return getters/setters for that $bean
                // that are not optimizable.
                // 
                // A getter is optimizable if:
                //
                // - Case A: the getter is qtism-bean-optinit flagged, has an AbstractCollection range, and has its bean value as an empty AbstractCollection.
                // - Case B: (Case A failed) the getter is qtism-bean-optinit flagged, has a property default value equal to its bean value. This annotation also consider null as a scalar value.
                //
                // OPTIMIZABLE PROPERTIES:
                // 
                // - 
                
                $bodyGetters = $bean->getGetters(true);
                $getters = array_reverse(array_merge($bodyGetters->getArrayCopy(), $ctorGetters->getArrayCopy()));

                foreach ($getters as $getter) {
                    
                    // REMEMBER THE GETTER NAME SO THAT WE TO WHAT GETTER THE VALUE BELONGS TO.
                    $stack->push(call_user_func(array($component, $getter->getName())));
                }
            }
            // Warning!!! Check for Coords Datatype objects. Indeed, it extends AbstractCollection, but must not be considered as it is.
            elseif ($isMarked === false && ($component instanceof AbstractCollection && !$component instanceof QtiCoords)) {
                // AbstractCollection node, 1st pass.
                // Mark as explored.
                array_push($marker, $component);
                // Ask for a 2nd pass.
                $stack->push($component);

                // Explore all values of the collection please!
                $values = array_reverse($component->getArrayCopy());
                foreach ($values as $val) {
                    $stack->push($val);
                }
            } elseif ($isMarked === true && $component instanceof QtiComponent) {
                // QtiComponent, 2nd pass.
                $marshaller = new PhpQtiComponentMarshaller($ctx, $component);
                $marshaller->setAsInstanceOf(self::getBaseImplementation($component));

                if ($component === $this->getDocumentComponent()) {
                    $marshaller->setVariableName('rootcomponent');
                }

                $marshaller->marshall();
            } elseif ($component instanceof QtiDatatype) {
                // Leaf node QtiDataType.
                $marshaller = new PhpQtiDatatypeMarshaller($ctx, $component);
                $marshaller->marshall();
            } elseif ($isMarked === true && $component instanceof AbstractCollection) {
                // AbstractCollection, 2nd pass.
                $marshaller = new PhpCollectionMarshaller($ctx, $component);
                $marshaller->marshall();
            } elseif (PhpUtils::isScalar($component) === true) {
                // Leaf node (QtiDatatype or PHP scalar (including the null value)).
                $marshaller = new PhpScalarMarshaller($ctx, $component);
                $marshaller->marshall();
            } elseif (is_array($component) === true) {
                // Leaf node array.
                $marshaller = new PhpArrayMarshaller($ctx, $component);
                $marshaller->marshall();
            } else {
                $msg = "Datatype '" . gettype($component) . "' cannot be handled by the PhpDocument::save() method.";
                throw new PhpStorageException($msg);
            }
        }

        $exists = file_exists($url);
        $written = @file_put_contents($url, $stream->getBinary());
        
        if ($written === false) {
            throw new PhpStorageException("File located at '${url}' could not be written.");
        }
        
        if ($written !== false && $exists === true && function_exists('opcache_invalidate') === true) {
            opcache_invalidate($url, true);
        }
    }

    /**
     * Load a PHP QTI document at the specified URL.
     *
     * @param string $url A URL (Uniform Resource Locator) describing where to find the PHP document to load.
     * @throws PhpStorageException If an error occurs while loading the PHP file located at $url.
     */
    public function load($url)
    {
        if (is_readable($url) === false) {
            $msg = "The PHP document located at '${url}' is not readable or does not exist.";
            throw new PhpStorageException($msg, PhpStorageException::READ);
        }

        try {
            // Will return $rootcomponent.
            require $url;
            $this->setDocumentComponent($rootcomponent);
            $this->setUrl($url);
        } catch (Exception $e) {
            $msg = "A PHP Runtime Error occured while executing the PHP source code representing the document to be loaded at '${url}'.";
            throw new PhpStorageException($msg, PhpStorageException::UNKNOWN, $e);
        }
    }

    protected static function getBaseImplementation($object)
    {
        if ($object instanceof ExtendedAssessmentTest) {
            return "qtism\\data\\ExtendedAssessmentTest";
        } elseif ($object instanceof AssessmentTest) {
            return "qtism\\data\\AssessmentTest";
        } elseif ($object instanceof AssessmentItem) {
            return "qtism\\data\\AssessmentItem";
        } elseif ($object instanceof ResponseProcessing) {
            return "qtism\\data\\processing\\ResponseProcessing";
        } elseif ($object instanceof ExtendedAssessmentSection) {
            return "qtism\\data\\ExtendedAssessmentSection";
        } elseif ($object instanceof AssessmentSection) {
            return "qtism\\data\\AssessmentSection";
        } else {
            return get_class($object);
        }
    }
}
