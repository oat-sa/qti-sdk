<?php

namespace qtismtest;

use DateTime;
use DateTimeZone;
use DOMDocument;
use DOMElement;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerFactory;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\versions\QtiVersion;
use RuntimeException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * Class QtiSmTestCase
 */
abstract class QtiSmTestCase extends TestCase
{
    /**
     * @var Filesystem
     */
    private $fileSystem = null;

    /**
     * @var Filesystem
     */
    private $outputFileSystem = null;

    public function setUp(): void
    {
        parent::setUp();

        // Set up File System Local adapter for testing.
        $adapter = new Local(self::samplesDir());
        $this->setFileSystem(new Filesystem($adapter));

        // Set up File System Local adapter for output.
        $adapter = new Local(sys_get_temp_dir() . '/qsmout');
        $this->setOutputFileSystem(new Filesystem($adapter));
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Get File System
     *
     * Setup the FileSystem implementation to be used for testing.
     *
     * @return Filesystem
     */
    protected function getFileSystem(): Filesystem
    {
        return $this->fileSystem;
    }

    /**
     * Set File System
     *
     * Setup the FileSystem implementation to be used for testing.
     *
     * @param Filesystem $filesystem
     */
    protected function setFileSystem(Filesystem $filesystem): void
    {
        $this->fileSystem = $filesystem;
    }

    /**
     * Get Output File System
     *
     * Setup the FileSystem implementation to be used for testing.
     *
     * @return Filesystem
     */
    protected function getOutputFileSystem(): Filesystem
    {
        return $this->outputFileSystem;
    }

    /**
     * Set Output File System
     *
     * Setup the FileSystem implementation to be used for testing.
     *
     * @param Filesystem $filesystem
     */
    protected function setOutputFileSystem(Filesystem $filesystem): void
    {
        $this->outputFileSystem = $filesystem;
    }

    public function getMarshallerFactory(string $versionNumber = '2.1'): MarshallerFactory
    {
        $version = QtiVersion::create($versionNumber);
        return $version->getMarshallerFactory();
    }

    /**
     * Asserts that a file does not exist.
     *
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileDoesNotExist(string $filename, string $message = ''): void
    {
        $parent = get_parent_class(self::class);

        $fileDoesNotExistAssertionMethod = method_exists($parent, __FUNCTION__)
            ? __FUNCTION__
            : 'assertFileNotExists';

        [$parent, $fileDoesNotExistAssertionMethod]($filename, $message);
    }

    /**
     * Returns the canonical path to the samples directory, with the
     * trailing slash.
     *
     * @return string
     */
    public static function samplesDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;
    }

    /**
     * Create a directory in OS temp directory with a unique name.
     *
     * @return string The path to the created directory.
     * @throws RuntimeException If the directory has not been created.
     */
    public static function tempDir(): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'qsm');

        // Tempnam creates a file with 600 chmod. Remove
        // it and create a directory.
        if (file_exists($tmpFile) === true) {
            unlink($tmpFile);
        }

        if (!mkdir($tmpFile) && !is_dir($tmpFile)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $tmpFile));
        }

        return $tmpFile;
    }

    /**
     * Create a copy of $source to the temp directory. The copied
     * file will receive a unique file name.
     *
     * @param string $source The source file to be copied.
     * @return string The path to the copied file.
     */
    public static function tempCopy($source): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'qsm');

        // Same as for QtiSmTestCase::tempDir...
        if (file_exists($tmpFile) === true) {
            unlink($tmpFile);
        }

        copy($source, $tmpFile);

        return $tmpFile;
    }

    /**
     * Create a DOMElement from an XML string.
     *
     * @param string $xmlString A string containing XML markup
     * @return DOMElement The according DOMElement;
     */
    public function createDOMElement($xmlString): DOMElement
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($xmlString);
        return $dom->documentElement;
    }

    /**
     * Create a DateTime object from a $date with format
     * Y-m-d H:i:s, and an optional timezone name.
     *
     * @param string $date A date
     * @param string $tz A timezone name.
     * @return DateTime
     */
    public static function createDate($date, $tz = 'UTC'): DateTime
    {
        return DateTime::createFromFormat('Y-m-d H:i:s', $date, new DateTimeZone($tz));
    }

    /**
     * Create a QtiComponent object from an XML String.
     *
     * @param string $xmlString An XML String to transform in a QtiComponent object.
     * @param string $version A QTI version rule the creation of the component.
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createComponentFromXml($xmlString, $version = '2.1.0'): QtiComponent
    {
        $element = $this->createDOMElement($xmlString);
        $factory = $this->getMarshallerFactory($version);
        $marshaller = $factory->createMarshaller($element);
        return $marshaller->unmarshall($element);
    }
}
