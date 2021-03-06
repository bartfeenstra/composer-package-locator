<?php

namespace BartFeenstra\ComposerPackageLocator;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Provides a Composer plugin that stores the location of the vendor directory.
 */
class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{

    public function activate(Composer $composer, IOInterface $io)
    {
        $io->write('<info>Activating the Composer Package Locator plugin</info>');
        $composer->getEventDispatcher()->addSubscriber($this);
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_AUTOLOAD_DUMP => 'storeVendorDir',
        ];
    }

    public function storeVendorDir(Event $event)
    {
        $this->storeConstant(
            $event,
            'BARTFEENSTRA_COMPOSER_PACKAGE_LOCATOR_VENDOR_DIR',
            $event->getComposer()->getConfig()->get('vendor-dir')
        );
        $this->storeConstant(
            $event,
            'BARTFEENSTRA_COMPOSER_PACKAGE_LOCATOR_HOME_FILE',
            $event->getComposer()->getConfig()->get('home') . '/' . Factory::getComposerFile()
        );
    }

    public function storeConstant(Event $event, $constantName, $constantValue)
    {
        $vendorDirectory = $event->getComposer()->getConfig()->get('vendor-dir');
        $autoloadFile = $vendorDirectory . '/autoload.php';

        if (!file_exists($autoloadFile)) {
            throw new \Exception(sprintf(
                'Could not adjust autoloader: The file %s was not found.',
                $autoloadFile
            ));
        }

        $event->getIO()->write(sprintf('<info>Generating the "%s" constant</info>', $constantName));
        $contents = file_get_contents($autoloadFile);
        $additionalContents = sprintf("if (!defined('%s')) {\n", $constantName);
        $additionalContents .= sprintf("    define('%s', '%s');\n", $constantName, $constantValue);
        $additionalContents .= "}\n\n";
        // Regex modifiers:
        // "m": \s matches newlines
        // "D": $ matches at EOF only
        // Translation: insert before the last "return" in the file
        $contents = preg_replace(
            '/\n(?=return [^;]+;\s*$)/mD',
            "\n" . $additionalContents,
            $contents
        );
        file_put_contents($autoloadFile, $contents);
    }
}
