<p align="center"><img src="https://cloud.githubusercontent.com/assets/4217431/6734391/3b3ef4e8-ce58-11e4-83a1-d6e39a91400f.png" alt="QTI-SDK" /></p>

[![Latest Version](https://img.shields.io/github/tag/oat-sa/qti-sdk.svg?style=flat&label=release)](https://github.com/oat-sa/qti-sdk/tags)
[![Build Status](https://travis-ci.org/oat-sa/qti-sdk.svg?branch=master)](https://travis-ci.org/oat-sa/qti-sdk)
[![Coverage Status](https://coveralls.io/repos/github/oat-sa/qti-sdk/badge.svg?branch=develop)](https://coveralls.io/github/oat-sa/qti-sdk?branch=develop)
[![License GPL2](http://img.shields.io/badge/licence-gpl2-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Packagist Downloads](http://img.shields.io/packagist/dt/qtism/qtism.svg)](https://packagist.org/packages/qtism/qtism)

# QTI Software Development Kit for PHP

An IMS QTI (Question &amp; Test Interoperability) Software Development Kit for PHP 5.5 and higher supporting a wide 
range of features described by the [IMS QTI specification family](http://www.imsglobal.org/question).

__This implementation of QTI is under constant enhancement. The API of the master branch might change at any time.__

## Features

* Targets QTI 2.0, 2.1 and partially 2.2
* Complete QTI Information Model
* Complete QTI Rule Engine Support
* Custom Operator Hooks through PSR-0/PSR-4
* [Wilbert Kraan's](http://blogs.cetis.ac.uk/wilbert/2013/11/06/using-standards-to-make-assessment-in-e-textbooks-scalable-engaging-but-robust) / [Steve Lay's](http://swl10.blogspot.co.uk/2013/09/transforming-qti-v2-into-xhtml-5.html) Goldilocks Rendering
* CSS Parser for direct QTI Information Model mapping at rendering time
* Item and Test Sessions (with lightning fast binary persistence)
* Nice and Clean API for QTI Document manipulation/traversal
* PreConditions & Branching
* Selection and Ordering
* Response, Outcome and Template Processing
* Unit test driven from PHP 5.5 to 7.0

## Installation (developers)

1. Clone the repository.
2. Make sure you know how [Composer](https://getcomposer.org/download/) works and it is installed on your system.
3. php composer.phar install
4. You are ready!

## Unit Tests (developers)

Run Unit Tests by invoking the following shell command:

```shell
cp phpunit.xml.dist phpunit.xml
./vendor/bin/phpunit test
```

## Contribute

We are always looking for people to feed the project with:

* Bug reports
* Unit tests
* New features

[Please make yourself known](https://github.com/bugalot)!

## QTI Item Session Management

### Introduction Example

The following example demonstrates how to instantiate an item session for a given QTI XML item document. The item
in use in this example is the [*"Composition of Water"*](http://www.imsglobal.org/question/qtiv2p1/examples/items/choice_multiple.xml) item, from the [QTI 2.1 Implementation Guide](http://www.imsglobal.org/question/qtiv2p1/imsqti_implv2p1.html).

```php
<?php
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\common\State;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\tests\AssessmentItemSession;

// Instantiate a new QTI XML document, and load a QTI XML document.
$itemDoc = new XmlDocument('2.1');
$itemDoc->load('choice_multiple.xml');

/* 
 * A QTI XML document can be used to load various pieces of QTI content such as assessmentItem,
 * assessmentTest, responseProcessing, ... components. Our target is an assessmentItem, which is the
 * root component of our document.
 */
$item = $itemDoc->getDocumentComponent();

/* 
 * The item session represents the collected and computed data related to the interactions a
 * candidate performs on a single assessmentItem. As per the QTI specification, "an item session
 * is the accumulation of all attempts at a particular instance of an assessmentItem made by
 * a candidate.
 */
$itemSession = new AssessmentItemSession($item);

// The candidate is entering the item session, and is beginning his first attempt.
$itemSession->beginItemSession();
$itemSession->beginAttempt();

/* 
 * We instantiate the responses provided by the candidate for this assessmentItem, for the current
 * item session. For this assessmentItem, the data collected from the candidate is represented by 
 * a State, composed of a single ResponseVariable named 'RESPONSE'.
 */
$responses = new State(
    array(
        // The 'RESPONSE' ResponseVariable has a QTI multiple cardinality, and a QTI identifier baseType.
        new ResponseVariable(
            'RESPONSE',
            Cardinality::MULTIPLE,
            BaseType::IDENTIFIER,
            /* 
             * The ResponseVariable value is a Container with multiple cardinality and an identifier
             * baseType to meet the cardinality and baseType requirements of the ResponseVariable.
             */
            new MultipleContainer(
                BaseType::IDENTIFIER,
                /*
                 * The values composing the Container are identifiers 'H' and 'O', which represent
                 * the correct response to this item.
                 */
                array(
                    new QtiIdentifier('H'),
                    new QtiIdentifier('O')
                )
            )
        )
    )
);

/*
 * The candidate is finishing the current attempt, by providing a correct response.
 * ResponseProcessing takes place to produce a new value for the 'SCORE' OutcomeVariable.
 */
$itemSession->endAttempt($responses);

// The item session variables and their values can be accessed by their identifier.
echo 'numAttempts: ' . $itemSession['numAttempts'] . "\n";
echo 'completionStatus: ' . $itemSession['completionStatus'] . "\n";
echo 'RESPONSE: ' . $itemSession['RESPONSE'] . "\n";
echo 'SCORE: ' . $itemSession['SCORE'] . "\n";

/*
 * numAttempts: 1
 * completionStatus: completed
 * RESPONSE: ['H'; 'O']
 * SCORE: 2
 */

// End the current item session.
$itemSession->endItemSession();
```

### Multiple Attempts Example

As per the QTI specification, item sessions allow a single attempt to be performed by default. Trying to begin an
attempt that will make the item session exceeding the maximum number of attempts will lead to a PHP exception, as in
the following example.

```php
<?php
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentItemSessionException;

$itemDoc = new XmlDocument('2.1');
$itemDoc->load('choice_multiple.xml');
$item = $itemDoc->getDocumentComponent();

$itemSession = new AssessmentItemSession($item);
$itemSession->beginItemSession();

// Begin 1st attempt.
$itemSession->beginAttempt();
// End attempt by providing an empty response...
$itemSession->endAttempt(new State());

// Begin 2nd attempt, but by default, maximum number of attempts is 1.
try {
    $itemSession->beginAttempt();
} catch (AssessmentItemSessionException $e) {
    echo $e->getMessage();
    // A new attempt for item 'choiceMultiple' is not allowed. The maximum number of attempts (1) is reached.
}
```

If multiple attempts are permitted on a given assessmentItem, the `itemSessionControl`'s `maxAttempts` attribute 
can be modified to allow multiple or unlimited attempts that can be performed by a candidate.

```php
<?php
use qtism\data\ItemSessionControl;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentItemSession;

$itemDoc = new XmlDocument('2.1');
$itemDoc->load('choice_multiple.xml');
$item = $itemDoc->getDocumentComponent();
$itemSession = new AssessmentItemSession($item);

// Set the maximum number of attempts to 0 (means unlimited).
$itemSessionControl = new ItemSessionControl();
$itemSessionControl->setMaxAttempts(0);
$itemSession->setItemSessionControl($itemSessionControl);

// Performing multiple attempts will not lead to a PHP exception anymore, because the maximum number of attemps is unlimited!
$itemSession->beginItemSession();

// 1st attempt will be an incorrect response from the candidate (['H'; 'Cl']).
$responses = new State(
    array(
        new ResponseVariable(
            'RESPONSE',
            Cardinality::MULTIPLE,
            BaseType::IDENTIFIER,
            new MultipleContainer(
                BaseType::IDENTIFIER,
                array(
                    new QtiIdentifier('H'),
                    new QtiIdentifier('Cl')
                )
            )
        )
    )
);

$itemSession->endAttempt($responses);

echo "numAttempts: " . $itemSession['numAttempts'] . "\n";
echo "completionStatus: " . $itemSession['completionStatus'] . "\n";
echo "RESPONSE: " . $itemSession['RESPONSE'] . "\n";
echo "SCORE: " . $itemSession['SCORE'] . "\n";

/*
 * numAttempts: 1
 * completionStatus: completed
 * RESPONSE: ['H'; 'N']
 * SCORE: 0
 */

// 2nd attempt will send a correct response this time (['H'; 'O'])!
$itemSession->beginAttempt();
$responses['RESPONSE'][1]->setValue('O');
$itemSession->endAttempt();

echo "numAttempts: " . $itemSession['numAttempts'] . "\n";
echo "completionStatus: " . $itemSession['completionStatus'] . "\n";
echo "RESPONSE: " . $itemSession['RESPONSE'] . "\n";
echo "SCORE: " . $itemSession['SCORE'] . "\n";

/*
 * numAttempts: 2
 * completionStatus: completed
 * RESPONSE: ['H'; 'O']
 * SCORE: 2
 */

$itemSession->endItemSession();
```

## QTI Rendering

The QTI Software Development Kit enables you to transform XML serialized QTI files
into their (X)HTML5 Goldilocks equivalent. The following shell command renders the `path/to/qti.xml` QTI file into an HTML5 
document using the (X)HTML5 Golidlocks rendering flavour with indentation formatting. The rendering output (stdout) 
is redirected to the `/home/jerome/qti.html` file.

```shell
./vendor/bin/qtisdk render -df --source path/to/qti.xml --flavour goldilocks > /home/jerome/qti.html
```

For additionnal help and information, just call the help screen to know about the features provided by the rendering binaries!

```shell
./vendor/bin/qtisdk render --help
```

## Configuration

As for other major PHP frameworks such as [Doctrine](http://stackoverflow.com/questions/21925354/doctrine-is-freaking-out-when-i-turn-on-php-opcache-on), Zend Framework 2 or PHPUnit, QTI-SDK makes use
of annotations. In such a context, the two following Zend Opcache configuration directives must be
configured as below.

### PHP5 Configuration

* [opcache.save_comments](http://php.net/manual/en/opcache.configuration.php#ini.opcache.save-comments): 1
* [opcache.load_comments](http://php.net/manual/en/opcache.configuration.php#ini.opcache.load-comments): 1

### PHP7 Configuration

The `opcache.load.comments` option was removed from PHP7. Only `opcache.save.comments` remains.

* [opcache.save_comments](http://php.net/manual/en/opcache.configuration.php#ini.opcache.save-comments): 1