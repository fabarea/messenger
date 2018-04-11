<?php
namespace Fab\Messenger\Module;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

/**
 * Class ModuleLoader for Vidi modules of Messenger
 */
class ModuleLoader
{

    protected static $enabledModules = [];

    /**
     * @param string $shortDataType
     * @throws \InvalidArgumentException
     */
    static public function register($shortDataType)
    {
        $enabledModules = self::getEnableModules();

        // Only load if requested by the User.
        $dataType = 'tx_messenger_domain_model_' . $shortDataType;
        if (in_array($shortDataType, $enabledModules, true)) {

            /** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
            $moduleLoader = GeneralUtility::makeInstance(
                \Fab\Vidi\Module\ModuleLoader::class,
                $dataType
            );
            $moduleLoader->setIcon('EXT:messenger/Resources/Public/Icons/module-' . $shortDataType . '.svg')
                ->setModuleLanguageFile('LLL:EXT:messenger/Resources/Private/Language/' . $dataType . '.xlf')
                #->addStyleSheetFiles(['EXT:messenger/Resources/Public/StyleSheet/Backend/' . $dataType .'.css'])
                ->setDefaultPid(1)
                ->setMainModule('messenger')
                ->register();
        }
    }

    /**
     * @return array
     */
    static protected function getEnableModules()
    {

        if (!self::$enabledModules) {
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            /** @var ConfigurationUtility $configurationUtility */
            $configurationUtility = $objectManager->get(ConfigurationUtility::class);
            $configuration = $configurationUtility->getCurrentConfiguration('messenger');
            self::$enabledModules = GeneralUtility::trimExplode(',', $configuration['enabledModules']['value']);
        }
        return self::$enabledModules;
    }

}
