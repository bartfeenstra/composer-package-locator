# Composer Package Locator

[![Build Status](https://travis-ci.org/bartfeenstra/composer-package-locator.svg?branch=master)](https://travis-ci.org/bartfeenstra/composer-package-locator)

This package allows other Composer packages to retrieve information about all installed Composer packages.
 
## Usage
Add a dependency on this package to your project by running
`composer require bartfeenstra/composer-package-locator:0.2.*`.

To find all installed files, simply create an instance of 
`\BartFeenstra\ComposerPackageLocator\InstalledFileSystemRepository` and use it like any other 
`\Composer\Repository\InstalledRepositoryInterface`.
