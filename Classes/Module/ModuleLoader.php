<?php

namespace Fab\Messenger\Module;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ModuleLoader for Vidi modules of Messenger
 */
class ModuleLoader
{

    /**
     * @param $dataType
     * @return \Fab\Vidi\Module\ModuleLoader
     */
    public static function register($dataType): \Fab\Vidi\Module\ModuleLoader
    {
        /** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
        $moduleLoader = GeneralUtility::makeInstance(
            \Fab\Vidi\Module\ModuleLoader::class,
            $dataType
        );

        return $moduleLoader->setIcon('EXT:messenger/Resources/Public/Icons/' . $dataType . '.svg')
            ->setModuleLanguageFile('LLL:EXT:messenger/Resources/Private/Language/' . $dataType . '.xlf')
            #->addStyleSheetFiles(['EXT:messenger/Resources/Public/StyleSheet/Backend/' . $dataType .'.css'])
            ->setDefaultPid(1)
            ->setMainModule('messenger')
            ->register();
    }

}
