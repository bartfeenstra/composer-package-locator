<?php

namespace BartFeenstra\ComposerPackageLocator;

use Composer\Json\JsonFile;
use Composer\Repository\InstalledFilesystemRepository as ComposerInstalledFilesystemRepository;

/**
 * Provides a file system repository for the installed packages.
 */
class InstalledFileSystemRepository extends ComposerInstalledFilesystemRepository
{

    /**
     * Creates a new instance.
     */
    public function __construct()
    {
        $constantName = 'BARTFEENSTRA_COMPOSER_PACKAGE_LOCATOR_VENDOR_DIR';
        if (!defined($constantName)) {
            // @codingStandardsIgnoreStart
            throw new \RuntimeException(sprintf('%s was not defined. Maybe you installed Composer packages with `--no-plugin`. Run `composer dump-autoload` again.', $constantName));
            // @codingStandardsIgnoreEnd
        }
        parent::__construct(new JsonFile(BARTFEENSTRA_COMPOSER_PACKAGE_LOCATOR_VENDOR_DIR
            . '/composer/installed.json'));
    }
}
