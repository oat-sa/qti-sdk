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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 *  
 *
 */
namespace qtism\runtime\tests;

/**
 * An AssessmentTestSessionFactory implementation that creates basic
 * AssessmentTestSession objects from a given AssessmentTest definition
 * and an optional Route to be taken.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentTestSessionFactory extends AbstractAssessmentTestSessionFactory {
    
    /**
     * Instantiates an AssessmentTestSession with the default implementation.
     * 
     * @return AssessmentTestSession
     */
    protected function instantiateAssessmentTestSession(Route $route) {
        return new AssessmentTestSession($this->getAssessmentTest(), $this->createAssessmentItemSessionFactory(), $route, $this->mustConsiderMinTime());
    }
    
    /**
     * Creates a brand new AssessmentItemSessionFactory object.
     * 
     * @return AssessmentItemSessionFactory
     */
    public function createAssessmentItemSessionFactory() {
        return new AssessmentItemSessionFactory($this->mustConsiderMinTime());
    }
}