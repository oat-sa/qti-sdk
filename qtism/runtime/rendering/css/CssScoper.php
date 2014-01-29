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
 * @subpackage
 *
 */
namespace qtism\runtime\rendering\css;

use qtism\runtime\rendering\RenderingException;
use qtism\runtime\rendering\Renderable;
use qtism\common\storage\BinaryStream;
use qtism\common\storage\BinaryStreamException;

class CssScoper implements Renderable {
	
	const RUNNING = 0;
	
	const IN_ATRULE = 1;
	
	const IN_ATRULESTRING = 2;
	
	const IN_MAINCOMMENT = 3;
	
	const IN_SELECTOR = 4;
	
	const IN_CLASSBODY = 5;
	
	const IN_CLASSSTRING = 6;
	
	const IN_CLASSCOMMENT = 7;
	
	const IN_ATRULEBODY = 8;
	
	const CHAR_AT = "@";
	
	const CHAR_DOUBLEQUOTE = '"';
	
	const CHAR_TERMINATOR = ";";
	
	const CHAR_ESCAPE = "\\";
	
	const CHAR_TAB = "\t";
	
	const CHAR_SPACE = " ";
	
	const CHAR_NEWLINE = "\n";
	
	const CHAR_CARRIAGERETURN = "\r";
	
	const CHAR_VERTICALTAB = "\v";
	
	const CHAR_OPENINGBRACE = "{";
	
	const CHAR_CLOSINGBRACE = "}";
	
	const CHAR_STAR = "*";
	
	const CHAR_SLASH = "/";
	
	/**
	 * The current state.
	 * 
	 * @var integer
	 */
	private $state = self::RUNNING;
	
	/**
	 * The identifier used as a scope.
	 * 
	 * @var string
	 */
	private $id = '';
	
	/**
	 * The stream to read.
	 * 
	 * @var BinaryStream
	 */
	private $stream;
	
	/**
	 * The previously read char.
	 * 
	 * @var string
	 */
	private $previousChar = false;
	
	/**
	 * The previously read significant char.
	 * 
	 * @var string
	 */
	private $previousSignificantChar = false;
	
	/**
	 * The currently read char.
	 * 
	 * @var string
	 */
	private $currentChar = false;
	
	/**
	 * The buffer.
	 * 
	 * @var array
	 */
	private $buffer;
	
	/**
	 * The output string.
	 * 
	 * @var string
	 */
	private $output = '';
	
	/**
     * Rescope the content of a given CSS file.
     *
     * @param string $file The path to the file that has to be rescoped.
     * @param string $id The scope identifier. If not given, will be randomly generated.
     * @return string The rescoped content of $file.
     * @throws RenderingException If something goes wrong while rescoping the content.
     */
    public function render($file, $id = '') {
    	
    	if (empty($id) === true) {
    		$id = uniqid();
    	}
    	
    	$this->init($id, $file);
    	
    	$stream = $this->getStream();
    	
    	while ($stream->eof() === false) {
    	    
        	try {
        		$char = $stream->read(1);
        		$this->beforeCharReading($char);
        		
            	switch ($this->getState()) {
            		case self::RUNNING:
            			$this->runningState();
            		break;
            		
            		case self::IN_ATRULE:
            			$this->inAtRuleState();
            		break;
            		
            		case self::IN_ATRULESTRING:
            			$this->inAtRuleStringState();
            		break;
            		
            		case self::IN_SELECTOR:
            			$this->inSelectorState();
            		break;
            		
            		case self::IN_CLASSBODY:
            			$this->inClassbodyState();
            		break;
            		
            		case self::IN_MAINCOMMENT:
            			$this->inMainCommentState();
            		break;
            		
            		case self::IN_CLASSSTRING:
            			$this->inClassStringState();
            		break;
            		
            		case self::IN_CLASSCOMMENT:
            			$this->inClassCommentState();
            		break;
            		
            		case self::IN_ATRULEBODY:
            		    $this->inAtRuleBodyState();    
            		break;
            	}
            	
            	$this->afterCharReading($char);
        	}
        	catch (BinaryStreamException $e) {
        	    $stream->close();
        		$msg = "An unexpected error occured while reading the CSS file '${file}'.";
        		throw new RenderingException($msg, RenderingException::RUNTIME, $e);
        	}
    	}
    	
    	$stream->close();
    	return $this->getOutput();
    }
    
    /**
     * Initialize the object to be ready for a new rescoping.
     */
    protected function init($id, $file) {
    	$this->setState(self::RUNNING);
    	$this->setId($id);
    	$this->setBuffer(array());
    	$this->setOutput('');
    	$this->setPreviousChar(false);
    	$this->setPreviousSignificantChar(false);
    	
    	if (($data = @file_get_contents($file)) !== false) {
    		$stream = new BinaryStream($data);
    		$stream->open();
    		$this->setStream($stream);
    	}
    	else {
    		throw new RenderingException("The CSS file '${file}' could not be open.", RenderingException::RUNTIME);
    	}
    }
    
    /**
     * Set the current state.
     * 
     * @param integer $state
     */
    protected function setState($state) {
    	$this->state = $state;
    }
    
    /**
     * Get the current state.
     * 
     * @return integer
     */
    protected function getState() {
    	return $this->state;
    }
    
    /**
     * Get the current id used as a scope.
     * 
     * @param string $id
     */
    protected function setId($id) {
    	$this->id = $id;
    }
    
    /**
     * Get the current id used as a scope.
     * 
     * @return string
     */
    protected function getId() {
    	return $this->id;
    }
    
    /**
     * Set the stream to be read.
     * 
     * @param BinaryStream $stream
     */
    protected function setStream(BinaryStream $stream) {
    	$this->stream = $stream;
    }
    
    /**
     * Get the stream to be read.
     * 
     * @return BinaryStream
     */
    protected function getStream() {
    	return $this->stream;
    }
    
    protected function beforeCharReading($char) {
    	$this->setCurrentChar($char);
    }
    
    protected function afterCharReading($char) {
        
        $this->setPreviousChar($char);
        
    	if (self::isWhiteSpace($char) === false) {
    		$this->setPreviousSignificantChar($char);	
    	}
    }
    
    /**
     * Get the previous significant char (non whitespace).
     * 
     * @return string The previous read significant char or false if there is no previous significant char.
     */
    protected function getPreviousSignificantChar() {
    	return $this->previousSignificantChar;
    }
    
    /**
     * Set the previous significant char.
     * 
     * @param string $char
     */
    protected function setPreviousSignificantChar($char) {
    	$this->previousSignificantChar = $char;
    }
    
    protected function getPreviousChar() {
        return $this->previousChar;
    }
    
    protected function setPreviousChar($char) {
        $this->previousChar = $char;
    }
    
    /**
     * Set the current char.
     * 
     * @param string $char
     */
    protected function setCurrentChar($char) {
    	$this->currentChar = $char;
    }
    
    /**
     * Get the current char.
     * 
     * @return string $char A char or false if no current char is set.
     */
    protected function getCurrentChar() {
    	return $this->currentChar;
    }
    
    protected function runningState() {
    	$char = $this->getCurrentChar();
    	
    	if ($char === self::CHAR_AT) {
    		$this->setState(self::IN_ATRULE);
    		$this->bufferize($char);
    		$this->output($char);
    	}
    	else if ($char === self::CHAR_STAR && $this->getPreviousChar() === self::CHAR_SLASH) {
    		$this->setState(self::IN_MAINCOMMENT);
    		$this->output($char);
    	}
    	else if ($char === self::CHAR_SLASH) {
    	    $this->output($char);
    	}
    	else if (self::isWhiteSpace($char) === false && $char !== self::CHAR_CLOSINGBRACE) {
    	    $this->bufferize($char);
    		$this->setState(self::IN_SELECTOR);
    	}
    	else {
    	    $this->output($char);
    	}
    }
    
    protected function inAtRuleState() {
    	$char = $this->getCurrentChar();
    	
    	if ($char === self::CHAR_DOUBLEQUOTE) {
    		$this->setState(self::IN_ATRULESTRING);
    	}
    	else if ($char === self::CHAR_TERMINATOR) {
    		$this->setState(self::RUNNING);
    		$this->cleanBuffer();
    	}
    	else if ($char === self::CHAR_OPENINGBRACE && (($buffer = implode('', $this->getBuffer())) && (strpos($buffer, '@media') !== false))) {
    	    $this->setState(self::RUNNING);
    	    $this->cleanBuffer();
    	}
    	else if ($char === self::CHAR_OPENINGBRACE) {
    	    $this->setState(self::IN_ATRULEBODY);
    	    $this->cleanBuffer();
    	    $this->bufferize($char);
    	}
    	else {
    	    $this->bufferize($char);
    	}
    	
    	$this->output($char);
    }
    
    protected function inAtRuleStringState() {
    	$char = $this->getCurrentChar();
    	
    	if ($char === self::CHAR_DOUBLEQUOTE && $this->isEscaping() === false) {
    		$this->cleanBuffer();
    		$this->setState(self::IN_ATRULE);
    	}
    	else if ($char === self::CHAR_ESCAPE) {
    		$this->bufferize($char);
    	}
    	else {
    		$this->cleanBuffer();
    	}
    	
    	$this->output($char);
    }
    
    protected function inAtRuleBodyState() {
        $char = $this->getCurrentChar();
        
        if ($char === self::CHAR_CLOSINGBRACE) {
            $buffer = implode('', $this->getBuffer());
            $openingCount = substr_count($buffer, self::CHAR_OPENINGBRACE);
            $closingCount = substr_count($buffer, self::CHAR_CLOSINGBRACE) + 1;
            
            if ($openingCount === $closingCount) {
                $this->cleanBuffer();
                $this->setState(self::RUNNING);
            }
            else {
                $this->bufferize($char);
            }
        }
        else {
            $this->bufferize($char);
        }
        
        $this->output($char);
    }
    
    protected function inSelectorState() {
    	$char = $this->getCurrentChar();
    	
    	if ($char === self::CHAR_OPENINGBRACE) {
    	    $this->updateSelector();
    	    $this->cleanBuffer();
    	    $this->setState(self::IN_CLASSBODY);
    	}
    	else {
    	    $this->bufferize($char);
    	}
    }
    
    protected function inClassBodyState() {
    	$char = $this->getCurrentChar();
    	
    	if ($char === self::CHAR_DOUBLEQUOTE) {
    		$this->setState(self::IN_CLASSSTRING);
    	}
    	else if ($char === self::CHAR_CLOSINGBRACE) {
    		$this->setState(self::RUNNING);
    	}
    	else if ($char === self::CHAR_STAR && $this->getPreviousChar() === self::CHAR_SLASH) {
    		$this->setState(self::IN_CLASSCOMMENT);
    	}
    	
    	$this->output($char);
    }
    
    protected function inMainCommentState() {
    	$char = $this->getCurrentChar();
    	
    	if ($char === self::CHAR_SLASH && $this->getPreviousChar() === self::CHAR_STAR) {
    		$this->setState(self::RUNNING);
    	}
    	
    	$this->output($char);
    }
    
    protected function inClassCommentState() {
        $char = $this->getCurrentChar();
        
    	if ($char === self::CHAR_SLASH && $this->getPreviousChar() === self::CHAR_STAR) {
    		$this->setState(self::IN_CLASSBODY);
    	}
    	
    	$this->output($char);
    }
    
    protected function inClassStringState() {
    	$char = $this->getCurrentChar();
    	
    	if ($char === self::CHAR_DOUBLEQUOTE && $this->isEscaping() === false) {
    		$this->cleanBuffer();
    		$this->setState(self::IN_CLASSBODY);
    	}
    	else if ($char === self::CHAR_ESCAPE) {
    		$this->bufferize($char);
    	}
    	else {
    		$this->cleanBuffer();
    	}
    	
    	$this->output($char);
    }
    
    static private function isWhiteSpace($char) {
    	return $char === self::CHAR_SPACE || $char === self::CHAR_CARRIAGERETURN || $char === self::CHAR_NEWLINE || $char === self::CHAR_TAB || $char === self::CHAR_VERTICALTAB;
    }
    
    protected function getBuffer() {
    	return $this->buffer;
    }
    
    protected function setBuffer(array $buffer) {
    	$this->buffer = $buffer;
    }
    
    protected function cleanBuffer() {
    	$this->setBuffer(array());
    }
    
    protected function bufferize($char) {
    	$buffer = $this->getBuffer();
    	$buffer[] = $char;
    	$this->setBuffer($buffer);
    }
    
    protected function setOutput($output) {
        $this->output = $output;
    }
    
    protected function getOutput() {
        return $this->output;
    }
    
    protected function output($char) {
        $output = $this->getOutput();
        $output .= $char;
        $this->setOutput($output);
    }
    
    protected function isEscaping() {
    	$count = count($this->getBuffer());
    	
    	if ($count === 0) {
    		return false;
    	}
    	
    	return $count % 2 !== 0;
    }
    
    protected function updateSelector() {
        $buffer = implode('', $this->getBuffer());
        
        if (strpos($buffer, ',') === false) {
            $this->output('#' . $this->getId() . ' ' . $buffer . '{');
        }
        else {
            $classes = explode(',', $buffer);
            $newClasses = array();
            
            foreach ($classes as $c) {
                $newC =  '#' . $this->getId() . ' ' . trim($c);
                $newC = str_replace(trim($c), $newC, $c);
                $newClasses[] = $newC;
            }
            
            $this->output(implode(',', $newClasses) . '{');
        }
    }
}