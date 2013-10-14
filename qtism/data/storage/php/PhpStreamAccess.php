<?php

namespace qtism\data\storage\php;

use qtism\data\storage\php\Utils as PhpUtils;
use qtism\common\storage\StreamAccessException;
use qtism\common\storage\IStream;
use qtism\common\storage\AbstractStreamAccess;
use \InvalidArgumentException;

/**
 * 
 * The PhpStreamAccess class provides methods to write some
 * PHP Code into a given IStream object.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PhpStreamAccess extends AbstractStreamAccess {
    
    /**
     * Creates a new PhpStreamAccess object.
     * 
     * @param IStream $stream The stream to write some PHP into.
     * @throws StreamAccessException If $stream is not open yet.
     */
    public function __construct(IStream $stream) {
        parent::__construct($stream);
    }
    
    /**
     * Write a scalar value into the current stream.
     * 
     * @param mixed $scalar A PHP scalar value or null.
     * @throws InvalidArgumentException If $scalar is not a PHP scalar value nor null.
     * @throws StreamAccessException If an error occurs while writing the scalar value.
     */
    public function writeScalar($scalar) {
        if (Utils::isScalar() === false) {
            $msg = "'${scalar}' is not a PHP scalar value nor null.";
            throw new InvalidArgumentException($msg);
        }
        
        if (is_numeric($scalar) === true) {
            $this->getStream()->write($scalar);
        }
        else if (is_string($scalar) === true) {
            $escapes = array("\\", '"', "\n", "\t", "\v", "\r", "\f");
            $replace = array("\\\\", '\\"', "\\n", "\\t", "\\v", "\\r", "\\f");
            
            $this->getStream()->write("\"${scalar}\"");
        }
        else if (is_bool($scalar) === true) {
            $this->getStream()->write(($scalar === true) ? 'true' : 'false');
        }
        else if (is_null($scalar) === true) {
            $this->getStream()->write('null');
        }
    }
    
    /**
     * Write the PHP equality symbol into the current stream.
     * 
     * @param boolean $spaces Whether to surround the equality symbol with spaces.
     * @throws StreamAccessException If an error occurs while writing the equality symbol.
     */
    public function writeEquals($spaces = true) {
        if ($spaces === true) {
            $this->getStream()->write(' = ');
        }
        else {
            $this->getStream()->write('=');
        }
    }
    
    /**
     * Write a newline escape sequence in the current stream.
     * 
     * @throws StreamAccessException If an error occurs while writing the equality symbol.
     */
    public function writeNewline() {
        $this->getStream()->write("\n");
    }
    
    /**
     * Write a PHP opening tag in the current stream.
     * 
     * @param boolean $newline Whether a newline escape sequence must be written after the opening tag.
     * @throws StreamAccessException If an error occurs while writing the opening tag.
     */
    public function writeOpeningTag($newline = true) {
        $this->getStream()->write('<?php');
        if ($newLine === true) {
            $this->writeNewline();
        }
    }
    
    /**
     * Write a PHP closing tag in the current string.
     * 
     * @param boolean $newline
     */
    public function writeClosingTag($newline = true) {
        if ($newline === true) {
            $this->writeNewline();
        }
        $this->getStream()->write('?>');
    }
    
    /**
     * Write a PHP semicolon (;) in the current stream.
     * 
     * @param boolean $newline Wether a newline escape sequence follows the semicolon.
     * @throws StreamAccessException If an error occurs while writing the semicolon;
     */
    public function writeSemicolon($newline = true) {
        $this->getStream()->write(';');
        if ($newline === true) {
            $this->writeNewline();
        }
    }
    
    /**
     * Write a PHP colon (:) in the current stream.
     * 
     * @throws StreamAccessException If an error occurs while writing the colon.
     */
    public function writeColon() {
        $this->getStream()->write(':');
    }
    
    /**
     * Write a PHP scope resolution operator (::) in the current stream.
     * 
     * @throws StreamAccessException If an error occurs while writing the scope resolution operator.
     */
    public function writeScopeResolution() {
        $this->getStream()->write('::');
    }
    
    /**
     * An alias to PhpStreamAccess::writeScopeResolution ;-).
     * 
     * @see PhpStreamAccess::writeScopeResolution
     * @throws StreamAccessException If an error occurs while writing the "Paamayim Nekudotayim".
     */
    public function writePaamayimNekudotayim() {
        $this->writeScopeResolution();
    }
    
    /**
     * Write an opening parenthesis in the current stream.
     * 
     * @throws StreamAccessException If an error occurs while writing the opening parenthesis.
     */
    public function writeOpeningParenthesis() {
        $this->getStream()->write('(');
    }
    
    /**
     * Write a closing parenthesis in the current stream.
     * 
     * @throws StreamAccessException If an error occurs while writing the closing parenthesis.
     */
    public function writeClosingParenthesis() {
        $this->getStream()->write(')');
    }
    
    /**
     * Write a comma in the current stream.
     * 
     * @param boolean $space Whether a white space must be written after the comma.
     * @throws StreamAccessException If an error occurs while writing the comma.
     */
    public function writeComma($space = true) {
        $this->getStream()->write(',');
        if ($space === true) {
            $this->writeSpace();
        }
    }
    
    /**
     * Write a white space in the current stream.
     * 
     * @throws StreamAccessException If an error occurs while writing the white space.
     */
    public function writeSpace() {
        $this->getStream()->write(' ');
    }
    
    /**
     * Write a variable reference in the current stream.
     * 
     * @param string $varname The name of the variable reference to write.
     * @throws StreamAccessException If an error occurs while writing the variable reference.
     */
    public function writeVariable($varname) {
        $this->getStream()->write('$' . $varname);
    }
    
    /**
     * Write a object operator (->) in the current stream.
     * 
     * @throws StreamAccessException If an error occurs while writing the object operator.
     */
    public function writeObjectOperator() {
        $this->getStream()->write('->');
    }
    
    /**
     * Write a function call in the current stream.
     * 
     * @param string $funcname The name of the function that has to be called.
     * @param PhpArgumentCollection $arguments A collection of PhpArgument objects representing the arguments to be given to the function call.
     * @throws StreamAccessException If an error occurs while writing the function call.
     */
    public function writeFunctionCall($funcname, PhpArgumentCollection $arguments = null) {
        
        $this->getStream()->write($funcname);
        $this->writeOpeningParenthesis();
        
        if (is_null($arguments) === false) {
            $this->writeArguments($arguments);
        }
        
        $this->writeClosingParenthesis();
    }
    
    /**
     * Write a method call in the current stream.
     * 
     * @param string $objectname The name of the variable where the object on which you want to call the method is stored e.g. 'foobar'.
     * @param string $methodname The name of the method you want to call.
     * @param PhpArgumentCollection $arguments A collection of PhpArgument objects.
     * @param boolean $static Whether or not the call is static.
     * @throws StreamAccessException If an error occurs while writing the method call.
     */
    public function writeMethodCall($objectname, $methodname, PhpArgumentCollection $arguments = null, $static = false) {
        
        $this->writeVariable($objectname);
        
        if ($static === false) {
            $this->writeObjectOperator();
        }
        else {
            $this->writePaamayimNekudotayim();
        }
        
        $this->getStream()->write($methodname);
        $this->writeOpeningParenthesis();
        
        if (is_null($arguments) === false) {
            $this->writeArguments($arguments);
        }
        
        $this->writeClosingParenthesis();
    }
    
    /**
     * Write the new operator in the current stream.
     * 
     * @param boolean $space Whether to write an extra white space after the new operator.
     * @throws StreamAccessException If an error occurs while writing the new operator.
     */
    public function writeNew($space = true) {
        $this->getStream()->write('new');
        if ($space === true) {
            $this->writeSpace();
        }
    }
    
    /**
     * Write the instantiation of a given $classname with some $arguments.
     * 
     * @param string $classname The name of the class to be instantiated. Fully qualified class names are supported.
     * @param PhpArgumentCollection $arguments A collection of PhpArgument objects.
     */
    public function writeInstantiation($classname, PhpArgumentCollection $arguments = null) {
        
        $this->writeNew();
        $this->getStream()->write(str_replace("\\", "\\\\", $classname));
        $this->writeOpeningParenthesis();
        
        if (is_null($arguments) === false) {
            $this->writeArguments($arguments);
        }
        
        $this->writeClosingParenthesis();
    }
    
    /**
     * Write a sequence of arguments in the current stream.
     * 
     * @param PhpArgumentCollection $arguments A collection of PhpArgument objects.
     * @throws StreamAccessException If an error occurs while writing the sequence of arguments.
     */
    public function writeArguments(PhpArgumentCollection $arguments) {
        $argsCount = count($arguments);
        
        for ($i = 0; $i < $argsCount; $i++) {
        
            $this->writeArgument($arguments[$i]);
        
            if ($i < $argsCount - 1) {
                $this->writeComma();
            }
        }
    }
    
    /**
     * Write a PHP function/method argument in the current stream.
     * 
     * @param PhpArgument $argument A PhpArgument object.
     * @throws StreamAccessException If an error occurs while writing the PHP argument.
     */
    public function writeArgument(PhpArgument $argument) {
        $value = $argument->getValue();
        
        if (is_string($value) === true && mb_strpos($value, '$') === 0) {
            $this->getStream()->write($value);
        }
        else {
            $this->writeScalar($value);
        }
    }
}