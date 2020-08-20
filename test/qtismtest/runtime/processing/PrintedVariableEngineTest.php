<?php

namespace qtismtest\runtime\processing;

use qtism\common\datatypes\files\FileSystemFile;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiIntOrIdentifier;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\content\PrintedVariable;
use qtism\data\content\TextRun;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\State;
use qtism\runtime\common\TemplateVariable;
use qtism\runtime\processing\PrintedVariableEngine;
use qtism\runtime\processing\PrintedVariableProcessingException;
use qtismtest\QtiSmTestCase;

class PrintedVariableEngineTest extends QtiSmTestCase
{
    /**
     * @param mixed $value
     * @param string $expected
     * @param string $format
     * @param boolean $powerForm
     * @param integer|string $base
     * @param $integer |string $index
     * @param string $delimiter
     * @param string $field
     * @param stribng $mappingIndicator
     * @dataProvider printedVariableProvider
     */
    public function testPrintedVariable($expected, $identifier, State $state, $format = '', $powerForm = false, $base = 10, $index = -1, $delimiter = ';', $field = '', $mappingIndicator = '=')
    {
        $printedVariable = new PrintedVariable($identifier);
        $printedVariable->setFormat($format);
        $printedVariable->setPowerForm($powerForm);
        $printedVariable->setBase($base);
        $printedVariable->setIndex($index);
        $printedVariable->setDelimiter($delimiter);
        $printedVariable->setField($field);
        $printedVariable->setMappingIndicator($mappingIndicator);

        $engine = new PrintedVariableEngine($printedVariable);
        $engine->setContext($state);
        $this->assertEquals($expected, $engine->process());
    }

    public function printedVariableProvider()
    {
        $state = new State();

        $state->setVariable(new OutcomeVariable('nullValue', Cardinality::SINGLE, BaseType::BOOLEAN, null));

        // Scalar values.
        $state->setVariable(new OutcomeVariable('nonEmptyString', Cardinality::SINGLE, BaseType::STRING, new QtiString('Non Empty String')));
        $state->setVariable(new OutcomeVariable('emptyString', Cardinality::SINGLE, BaseType::STRING, new QtiString('')));
        $state->setVariable(new TemplateVariable('positiveInteger', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(25)));
        $state->setVariable(new TemplateVariable('zeroInteger', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(0)));
        $state->setVariable(new TemplateVariable('negativeInteger', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(-25)));
        $state->setVariable(new TemplateVariable('positiveFloat', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(25.3455322345)));
        $state->setVariable(new OutcomeVariable('zeroFloat', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(0.0)));
        $state->setVariable(new OutcomeVariable('negativeFloat', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(-53000.0)));
        $state->setVariable(new OutcomeVariable('false', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false)));
        $state->setVariable(new OutcomeVariable('true', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true)));
        $state->setVariable(new OutcomeVariable('URI', Cardinality::SINGLE, BaseType::URI, new QtiUri('http://qtism.taotesting.com')));
        $state->setVariable(new TemplateVariable('zeroIntOrIdentifier', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, new QtiIntOrIdentifier(0)));
        $state->setVariable(new TemplateVariable('positiveIntOrIdentifier', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, new QtiIntOrIdentifier(25)));
        $state->setVariable(new TemplateVariable('zeroIntOrIdentifier', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, new QtiIntOrIdentifier(0)));
        $state->setVariable(new OutcomeVariable('identifierIntOrIdentifier', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, new QtiIntOrIdentifier('woot')));
        $state->setVariable(new TemplateVariable('negativeIntOrIdentifier', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(-25)));
        $state->setVariable(new OutcomeVariable('duration', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT3M26S')));
        $state->setVariable(new OutcomeVariable('pair', Cardinality::SINGLE, BaseType::PAIR, new QtiPair('A', 'B')));
        $state->setVariable(new OutcomeVariable('directedPair', Cardinality::SINGLE, BaseType::DIRECTED_PAIR, new QtiDirectedPair('B', 'C')));
        $state->setVariable(new OutcomeVariable('identifier', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('woot')));

        // -- Multiple containers.
        $state->setVariable(new TemplateVariable('multipleIntegerSingle', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10)])));
        $state->setVariable(new TemplateVariable('multipleInteger', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10), new QtiInteger(20), new QtiInteger(-1)])));
        $state->setVariable(new OutcomeVariable('multipleFloat', Cardinality::MULTIPLE, BaseType::FLOAT, new MultipleContainer(BaseType::FLOAT, [new QtiFloat(10.0), new QtiFloat(20.0), new QtiFloat(-1.0)])));
        $state->setVariable(new OutcomeVariable('multipleString', Cardinality::MULTIPLE, BaseType::STRING, new MultipleContainer(BaseType::STRING, [new QtiString('Ta'), new QtiString('Daaa'), new QtiString('h'), new QtiString('')])));
        $state->setVariable(new OutcomeVariable('multipleBoolean', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(true)])));
        $state->setVariable(new OutcomeVariable('multipleURI', Cardinality::MULTIPLE, BaseType::URI, new MultipleContainer(BaseType::URI, [new QtiUri('http://www.taotesting.com'), new QtiUri('http://www.rdfabout.com')])));
        $state->setVariable(new OutcomeVariable('multipleIdentifier', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('9thing'), new QtiIdentifier('woot-woot')])));
        $state->setVariable(new TemplateVariable('multipleDuration', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, [new QtiDuration('PT0S'), new QtiDuration('PT3M')])));
        $state->setVariable(new OutcomeVariable('multiplePair', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'B'), new QtiPair('C', 'D'), new QtiPair('E', 'F')])));
        $state->setVariable(new OutcomeVariable('multipleDirectedPair', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F')])));
        $state->setVariable(new OutcomeVariable('multipleIntOrIdentifier', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER, new MultipleContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier('woot'), new QtiIntOrIdentifier(25), new QtiIntOrIdentifier(0), new QtiIntOrIdentifier(-25)])));
        $state->setVariable(new OutcomeVariable('multipleEmpty', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER)));
        $state->setVariable(new TemplateVariable('multipleContainsNull', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER, [new QtiInteger(-10), null, null])));

        // -- Ordered containers, no value for the 'index' attribute.
        $state->setVariable(new TemplateVariable('orderedInteger', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10), new QtiInteger(20), new QtiInteger(-1)])));
        $state->setVariable(new OutcomeVariable('orderedFloat', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, [new QtiFloat(10.0), new QtiFloat(20.0), new QtiFloat(-1.0)])));
        $state->setVariable(new OutcomeVariable('orderedString', Cardinality::ORDERED, BaseType::STRING, new OrderedContainer(BaseType::STRING, [new QtiString('Ta'), new QtiString('Daaa'), new QtiString('h'), new QtiString('')])));
        $state->setVariable(new OutcomeVariable('orderedBoolean', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, [new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(true)])));
        $state->setVariable(new OutcomeVariable('orderedURI', Cardinality::ORDERED, BaseType::URI, new OrderedContainer(BaseType::URI, [new QtiUri('http://www.taotesting.com'), new QtiUri('http://www.rdfabout.com')])));
        $state->setVariable(new OutcomeVariable('orderedIdentifier', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, [new QtiIdentifier('9thing'), new QtiIdentifier('woot-woot')])));
        $state->setVariable(new TemplateVariable('orderedDuration', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, [new QtiDuration('PT0S'), new QtiDuration('PT3M')])));
        $state->setVariable(new OutcomeVariable('orderedPair', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, [new QtiPair('A', 'B'), new QtiPair('C', 'D'), new QtiPair('E', 'F')])));
        $state->setVariable(new OutcomeVariable('orderedDirectedPair', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F')])));
        $state->setVariable(new OutcomeVariable('orderedIntOrIdentifier', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier('woot'), new QtiIntOrIdentifier(25), new QtiIntOrIdentifier(0), new QtiIntOrIdentifier(-25)])));
        $state->setVariable(new TemplateVariable('orderedEmpty', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER)));
        $state->setVariable(new TemplateVariable('orderedContainsNull', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, [null, null, new QtiInteger(10)])));

        // -- Ordered containers, value for the 'index' attribute set.
        $state->setVariable(new TemplateVariable('orderedIndexedInteger', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10), new QtiInteger(20), new QtiInteger(-1)])));
        // The field is extracted from a variable ref.
        $state->setVariable(new OutcomeVariable('fieldVariableRefInteger', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(1)));
        // Set up a wrong variable reference for 'index' value.
        $state->setVariable(new OutcomeVariable('fieldVariableRefString', Cardinality::SINGLE, BaseType::STRING, new QtiString('index')));
        $state->setVariable(new OutcomeVariable('orderedIndexedFloat', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, [new QtiFloat(10.0), new QtiFloat(20.0), new QtiFloat(-1.0)])));
        $state->setVariable(new OutcomeVariable('orderedIndexedString', Cardinality::ORDERED, BaseType::STRING, new OrderedContainer(BaseType::STRING, [new QtiString('Ta'), new QtiString('Daaa'), new QtiString('h'), new QtiString('')])));
        $state->setVariable(new OutcomeVariable('orderedIndexedBoolean', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, [new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(true)])));
        $state->setVariable(new OutcomeVariable('orderedIndexedURI', Cardinality::ORDERED, BaseType::URI, new OrderedContainer(BaseType::URI, [new QtiUri('http://www.taotesting.com'), new QtiUri('http://www.rdfabout.com')])));
        $state->setVariable(new OutcomeVariable('orderedIndexedIdentifier', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, [new QtiIdentifier('9thing'), new QtiIdentifier('woot-woot')])));
        $state->setVariable(new TemplateVariable('orderedIndexedDuration', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, [new QtiDuration('PT0S'), new QtiDuration('PT3M')])));
        $state->setVariable(new OutcomeVariable('orderedIndexedPair', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, [new QtiPair('A', 'B'), new QtiPair('C', 'D'), new QtiPair('E', 'F')])));
        $state->setVariable(new OutcomeVariable('orderedIndexedDirectedPair', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F')])));
        $state->setVariable(new OutcomeVariable(
            'orderedIndexedIntOrIdentifier',
            Cardinality::ORDERED,
            BaseType::INT_OR_IDENTIFIER,
            new OrderedContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier('woot'), new QtiIntOrIdentifier(25), new QtiIntOrIdentifier(0), new QtiIntOrIdentifier(-25)])
        ));

        // -- Record containers.
        $state->setVariable(new OutcomeVariable('recordSingle', Cardinality::RECORD, -1, new RecordContainer(['a' => new QtiFloat(25.3)])));
        $state->setVariable(new OutcomeVariable('recordMultiple', Cardinality::RECORD, -1, new RecordContainer([
            'a' => new QtiInteger(-3),
            'b' => new QtiFloat(3.3),
            'c' => new QtiBoolean(true),
            'd' => new QtiBoolean(false),
            'e' => new QtiString('string'),
            'f' => new QtiUri('http://www.rdfabout.com'),
            'g' => new QtiDuration('PT3M'),
            'h' => new QtiPair('A', 'B'),
            'i' => new QtiDirectedPair('C', 'D'),
        ])));
        $state->setVariable(new TemplateVariable('recordEmpty', Cardinality::RECORD, -1, new RecordContainer()));
        $state->setVariable(new OutcomeVariable('recordContainsNull', Cardinality::RECORD, -1, new RecordContainer(['a' => new QtiInteger(-3), 'b' => null, 'c' => new QtiBoolean(true)])));

        // -- Power form.
        $state->setVariable(new OutcomeVariable('powerFormScalarPositiveInteger', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(250)));
        $state->setVariable(new OutcomeVariable('powerFormScalarZeroInteger', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(0)));
        $state->setVariable(new OutcomeVariable('powerFormScalarNegativeInteger', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(-23)));
        $state->setVariable(new OutcomeVariable('powerFormScalarPositiveFloat', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(250.35)));
        $state->setVariable(new OutcomeVariable('powerFormScalarZeroFloat', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(0.0)));
        $state->setVariable(new OutcomeVariable('powerFormScalarNegativeFloat', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(-23.0)));

        // -- IMS NumberFormatting. See http://www.imsglobal.org/question/qtiv2p1/imsqti_implv2p1.html#section10017
        $state->setVariable(new OutcomeVariable('integerMinus987', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(-987)));

        return [
            ['', 'nonExistingVariable', $state],
            ['', 'nullValue', $state],

            ['Non Empty String', 'nonEmptyString', $state],
            ['', 'emptyString', $state],
            ['25', 'positiveInteger', $state],
            ['0', 'zeroInteger', $state],
            ['-25', 'negativeInteger', $state],
            ['2.534553e+1', 'positiveFloat', $state],
            ['0.000000e+0', 'zeroFloat', $state],
            ['-5.300000e+4', 'negativeFloat', $state],
            ['false', 'false', $state],
            ['true', 'true', $state],
            ['http://qtism.taotesting.com', 'URI', $state],
            ['25', 'positiveIntOrIdentifier', $state],
            ['0', 'zeroIntOrIdentifier', $state],
            ['-25', 'negativeIntOrIdentifier', $state],
            ['woot', 'identifierIntOrIdentifier', $state],
            ['206', 'duration', $state],
            ['A B', 'pair', $state],
            ['B C', 'directedPair', $state],
            ['woot', 'identifier', $state],

            ['10', 'multipleIntegerSingle', $state],
            ['10;20;-1', 'multipleInteger', $state],
            ['1.000000e+1;2.000000e+1;-1.000000e+0', 'multipleFloat', $state],
            ['Ta;Daaa;h;', 'multipleString', $state],
            ['true;false;true;true', 'multipleBoolean', $state],
            ['http://www.taotesting.com;http://www.rdfabout.com', 'multipleURI', $state],
            ['9thing;woot-woot', 'multipleIdentifier', $state],
            ['0;180', 'multipleDuration', $state],
            ['A B;C D;E F', 'multiplePair', $state],
            ['woot;25;0;-25', 'multipleIntOrIdentifier', $state],
            ['', 'multipleEmpty', $state],
            ['-10;null;null', 'multipleContainsNull', $state],

            ['10;20;-1', 'orderedInteger', $state],
            ['1.000000e+1;2.000000e+1;-1.000000e+0', 'orderedFloat', $state],
            ['Ta;Daaa;h;', 'orderedString', $state],
            ['true;false;true;true', 'orderedBoolean', $state],
            ['http://www.taotesting.com;http://www.rdfabout.com', 'orderedURI', $state],
            ['9thing;woot-woot', 'orderedIdentifier', $state],
            ['0;180', 'orderedDuration', $state],
            ['A B;C D;E F', 'orderedPair', $state],
            ['woot;25;0;-25', 'orderedIntOrIdentifier', $state],
            ['null;null;10', 'orderedContainsNull', $state],

            ['10', 'orderedIndexedInteger', $state, '', false, 10, 0],
            ['20', 'orderedIndexedInteger', $state, '', false, 10, 1],
            ['-1', 'orderedIndexedInteger', $state, '', false, 10, 2],

            // The index does not exist, the full container content is displayed.
            ['10;20;-1', 'orderedIndexedInteger', $state, '', false, 10, 3],
            // The index is a valid variable reference.
            ['20', 'orderedIndexedInteger', $state, '', false, 10, 'fieldVariableRefInteger'],
            // The index is an unresolvable variable reference, the full container content is displayed.
            ['10;20;-1', 'orderedIndexedInteger', $state, '', false, 10, 'XRef'],
            // The index value is not a string, the full container content is displayed.
            ['10;20;-1', 'orderedIndexedInteger', $state, '', false, 10, 'fieldVariableRefString'],

            ['1.000000e+1', 'orderedIndexedFloat', $state, '', false, 10, 0],
            ['2.000000e+1', 'orderedIndexedFloat', $state, '', false, 10, 1],
            ['-1.000000e+0', 'orderedIndexedFloat', $state, '', false, 10, 2],
            ['Ta', 'orderedIndexedString', $state, '', false, 10, 0],
            ['Daaa', 'orderedIndexedString', $state, '', false, 10, 1],
            ['h', 'orderedIndexedString', $state, '', false, 10, 2],
            ['', 'orderedIndexedString', $state, '', false, 10, 3],
            ['true', 'orderedIndexedBoolean', $state, '', false, 10, 0],
            ['false', 'orderedIndexedBoolean', $state, '', false, 10, 1],
            ['true', 'orderedIndexedBoolean', $state, '', false, 10, 2],
            ['true', 'orderedIndexedBoolean', $state, '', false, 10, 3],
            ['http://www.taotesting.com', 'orderedIndexedURI', $state, '', false, 10, 0],
            ['http://www.rdfabout.com', 'orderedIndexedURI', $state, '', false, 10, 1],
            ['9thing', 'orderedIndexedIdentifier', $state, '', false, 10, 0],
            ['woot-woot', 'orderedIndexedIdentifier', $state, '', false, 10, 1],
            ['0', 'orderedIndexedDuration', $state, '', false, 10, 0],
            ['180', 'orderedIndexedDuration', $state, '', false, 10, 1],
            ['A B', 'orderedIndexedPair', $state, '', false, 10, 0],
            ['C D', 'orderedIndexedPair', $state, '', false, 10, 1],
            ['E F', 'orderedIndexedPair', $state, '', false, 10, 2],
            ['A B', 'orderedIndexedDirectedPair', $state, '', false, 10, 0],
            ['C D', 'orderedIndexedDirectedPair', $state, '', false, 10, 1],
            ['E F', 'orderedIndexedDirectedPair', $state, '', false, 10, 2],
            ['woot', 'orderedIndexedIntOrIdentifier', $state, '', false, 10, 0],
            ['25', 'orderedIndexedIntOrIdentifier', $state, '', false, 10, 1],
            ['0', 'orderedIndexedIntOrIdentifier', $state, '', false, 10, 2],
            ['-25', 'orderedIndexedIntOrIdentifier', $state, '', false, 10, 3],

            // -- Power form (only in force with float values).
            ['250', 'powerFormScalarPositiveInteger', $state, '', true],
            ['0', 'powerFormScalarZeroInteger', $state, '', true],
            ['-23', 'powerFormScalarNegativeInteger', $state, '', true],
            ['2.503500 x 10²', 'powerFormScalarPositiveFloat', $state, '', true],
            ['0.000000 x 10⁰', 'powerFormScalarZeroFloat', $state, '', true],
            ['-2.300000 x 10¹', 'powerFormScalarNegativeFloat', $state, '', true],

            // -- Record containers.
            ['a=2.530000e+1', 'recordSingle', $state],
            ['a=-3;b=3.300000e+0;c=true;d=false;e=string;f=http://www.rdfabout.com;g=180;h=A B;i=C D', 'recordMultiple', $state],
            ['', 'recordEmpty', $state],
            ['a=-3;b=null;c=true', 'recordContainsNull', $state],

            // -- Funny format tests.
            ['bla', 'positiveInteger', $state, 'bla'],
            [' yeah', 'positiveInteger', $state, '%-P yeah'],

            // -- Real tests with format.
            ['25', 'positiveInteger', $state, '%s'],
            ['Integer as string:25', 'positiveInteger', $state, 'Integer as string:%s'],
            ['Preceding with zeros: 0000000025', 'positiveInteger', $state, 'Preceding with zeros: %010d'],
            ['Preceding with zeros (signed): +000000025', 'positiveInteger', $state, 'Preceding with zeros (signed): %+010i'],
            ['Preceding with blanks:         25', 'positiveInteger', $state, 'Preceding with blanks: %10d'],
            ['31', 'positiveInteger', $state, '%#o'],
            ['31', 'positiveFloat', $state, '%+o'],

            ['-987', 'integerMinus987', $state, '%i'],
        ];
    }

    public function testPrintedVariableWithUnknownValueType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The PrintedVariableEngine class only accepts PrintedVariable objects to be executed.");

        new PrintedVariableEngine(new TextRun('crash'));
    }

    public function testPrintedVariableFromFile()
    {
        $tmp = tempnam('/tmp', 'qtism');
        $state = new State([new OutcomeVariable('file', Cardinality::SINGLE, BaseType::FILE, FileSystemFile::createFromData('test', $tmp, 'text/plain'))]);
        $printedVariable = new PrintedVariable('file');

        $engine = new PrintedVariableEngine($printedVariable);
        $engine->setContext($state);

        try {
            $engine->process();
            $this->assertFalse(true, "Should not be able to process a printed variable rendering from a QTI File.");
        } catch (PrintedVariableProcessingException $e) {
            $this->assertEquals("The 'file' BaseType is not supported yet by PrintedVariableEngine implementation.", $e->getMessage());
        }

        unlink($tmp);
    }

    /**
     *
     * @dataProvider newProvider
     *
     * @param $expected int
     * @param $id string
     * @param $state State
     */

    public function testForNewProvider($expected, $id, $state)
    {
        $printedVariable = new PrintedVariable($id);
        $printedVariable->setFormat("%d");

        $engine = new PrintedVariableEngine($printedVariable);
        $engine->setContext($state);
        $this->assertEquals($expected, $engine->process());
    }

    public function newProvider()
    {
        $state = new State();

        $state->setVariable(new OutcomeVariable('test1', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(97.2)));
        $state->setVariable(new OutcomeVariable('test2', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(97.5)));
        $state->setVariable(new OutcomeVariable('test3', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(97.9)));
        $state->setVariable(new OutcomeVariable('test4', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(98.0)));

        return [
            [97, "test1", $state],
            [97, "test2", $state],
            [97, "test3", $state],
            [98, "test4", $state],
        ];
    }
}
