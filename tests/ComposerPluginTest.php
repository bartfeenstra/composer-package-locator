<?php

namespace BartFeenstra\ComposerPackageLocator\Tests;

use BartFeenstra\ComposerPackageLocator\ComposerPlugin;
use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Glob\Test\TestUtil;

/**
 * @coversDefaultClass \BartFeenstra\ComposerPackageLocator\ComposerPlugin
 *
 * @runTestsInSeparateProcesses
 */
class ComposerPluginTest extends PHPUnit_Framework_TestCase
{

    /**
     * The system under test.
     *
     * @var \BartFeenstra\ComposerPackageLocator\ComposerPlugin
     */
    protected $sut;

    protected function setUp()
    {
        $this->sut = new ComposerPlugin();
    }

    /**
     * @covers ::activate
     */
    public function testActivate()
    {
        $eventDispatcher = $this->prophesize(EventDispatcher::class);
        $eventDispatcher->addSubscriber($this->sut)->shouldBeCalled();

        $composer = $this->prophesize(Composer::class);
        $composer->getEventDispatcher()->willReturn($eventDispatcher);

        $io = $this->prophesize(IOInterface::class);

        $this->sut->activate($composer->reveal(), $io->reveal());
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $sut = $this->sut;
        $events = $sut::getSubscribedEvents();
        $this->assertInternalType('array', $events);
        foreach ($events as $eventName => $method) {
            $this->assertInternalType('string', $eventName);
            $this->assertInternalType('string', $method);
        }
    }

    /**
     * @covers ::storeVendorDir
     */
    public function testStoreVendorDir()
    {
        $rootDirectory = TestUtil::makeTempDir('bartfeenstra-composer-package-locator', __CLASS__);
        $vendorDirectoryName = 'the-vendor';
        $vendorDirectory = $rootDirectory . '/' . $vendorDirectoryName;

        $fileSystem = new Filesystem();
        $fileSystem->mirror(__DIR__ . '/../fixtures/dependentRoot', $rootDirectory);

        $io = $this->prophesize(IOInterface::class);

        $config = new Config(false, $rootDirectory);
        $config->merge([
            'config' => [
                'home' => $rootDirectory,
                'vendor-dir' => $vendorDirectoryName,
            ],
        ]);

        $composer = $this->prophesize(Composer::class);
        $composer->getConfig()->willReturn($config);

        $event = $this->prophesize(Event::class);
        $event->getComposer()->willReturn($composer);
        $event->getIO()->willReturn($io);

        $this->sut->storeVendorDir($event->reveal());

        $this->assertConstant('BARTFEENSTRA_COMPOSER_PACKAGE_LOCATOR_VENDOR_DIR', $vendorDirectory, $vendorDirectory);
        $this->assertConstant(
            'BARTFEENSTRA_COMPOSER_PACKAGE_LOCATOR_HOME_FILE',
            $rootDirectory . '/./composer.json',
            $vendorDirectory
        );

        $fileSystem->remove($rootDirectory);
    }

    /**
     * @param $constantName
     * @param $constantValue
     * @param $vendorDirectory
     */
    protected function assertConstant($constantName, $constantValue, $vendorDirectory)
    {
        $autoloadFile = $vendorDirectory . '/autoload.php';
        ob_start();
        require_once $autoloadFile;
        eval(sprintf('echo %s;', $constantName));
        $vendorDirectoryConstantValue = ob_get_contents();
        ob_end_clean();
        $this->assertSame($constantValue, $vendorDirectoryConstantValue);
    }
}
