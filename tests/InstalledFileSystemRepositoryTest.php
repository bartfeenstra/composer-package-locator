<?php

namespace BartFeenstra\ComposerPackageLocator\Tests;

use BartFeenstra\ComposerPackageLocator\InstalledFileSystemRepository;
use Composer\Config;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Glob\Test\TestUtil;

/**
 * @coversDefaultClass \BartFeenstra\ComposerPackageLocator\InstalledFileSystemRepository
 *
 * @runTestsInSeparateProcesses
 */
class InstalledFileSystemRepositoryTest extends PHPUnit_Framework_TestCase
{

    /**
     * The application's root directory.
     *
     * @var string
     */
    protected $rootDirectory;

    /**
     * The system under test.
     *
     * @var \BartFeenstra\ComposerPackageLocator\InstalledFileSystemRepository
     */
    protected $sut;

    protected function setUp()
    {
        $this->rootDirectory = TestUtil::makeTempDir('bartfeenstra-composer-package-locator', __CLASS__);
        $vendorDirectoryName = 'the-vendor';
        $vendorDirectory = $this->rootDirectory . '/' . $vendorDirectoryName;

        $fileSystem = new Filesystem();
        $fileSystem->mirror(__DIR__ . '/../fixtures/dependentRoot', $this->rootDirectory);

        define('BARTFEENSTRA_COMPOSER_PACKAGE_LOCATOR_VENDOR_DIR', $vendorDirectory);

        $this->sut = new InstalledFileSystemRepository();
    }

    protected function tearDown()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->rootDirectory);
    }

    /**
     * @covers ::getPackages
     */
    public function testGetPackages()
    {
        $packages = $this->sut->getPackages();
        $this->assertCount(1, $packages);
        $package = $packages[0];
        $this->assertSame('bartfeenstra/composer-package-locator', $package->getName());
    }
}
