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

namespace qtism\common\beans;

use \InvalidArgumentException;
use \ReflectionObject;
use \ReflectionMethod;
use \ReflectionProperty;

/**
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Bean {
    
    /**
     * The object to be wrapped as a bean as a PHP ReflectionObject.
     * 
     * @var ReflectionObject
     */
    private $object;
    
    /**
     * Create a new Bean object.
     * 
     * @param mixed $object The object to be wrapped as a Bean.
     * @throws InvalidArgumentException If $object is not an object.
     */
    public function __construct($object) {
        $this->setObject(new ReflectionObject($object));
    }
    
    /**
     * Set the object to be wrapped as a Bean as a PHP ReflectionObject.
     * 
     * @param ReflectionObject $object A ReflectionObject object. 
     */
    protected function setObject(ReflectionObject $object) {
        $this->object = $object;
    }
    
    /**
     * Get the object to be wrapped as a Bean as a PHP ReflectionObject.
     * 
     * @return mixed A ReflectionObject object.
     */
    protected function getObject() {
        return $this->object;
    }
    
    /**
     * 
     * @param string $propertyName
     * @return ReflectionMethod
     */
    public function getGetter($propertyName) {
        
    }
    
    /**
     * 
     * @param string $propertyName
     * @return boolean
     */
    public function hasGetter($propertyName) {
        
    }
    
    /**
     * 
     * @return array An array of ReflectionMethod objects (should be a collection).
     */
    public function getGetters() {
        
    }
    
    /**
     * 
     * @param string $propertyName
     * @return ReflectionMethod
     */
    public function getSetter($propertyName) {
        
    }
    
    /**
     * 
     * @param string $propertyName
     * @return boolean
     */
    public function hasSetter($propertyName) {
        
    }
    
    /**
     * 
     * @return array An array of ReflectionMethod objects (should be a collection).
     */
    public function getSetters() {
        
    }
    
    /**
     * 
     * @param string $propertyName
     * @return boolean
     */
    public function hasProperty($propertyName) {
        
    }
    
    /**
     * 
     * @param string $propertyName
     * @return ReflectionProperty
     */
    public function getProperty($propertyName) {
        
    }
    
    /**
     * 
     * @return array An array of ReflectionParameter objects (must be a collection).
     */
    public function getConstructorParameters() {
        
    }
    
    /**
     * Contains the internal logic of bean validation. Throws exception
     * to know why it's not a valid bean ;) !
     */
    protected function validateBean() {
        
    }
}