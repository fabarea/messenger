<?php
namespace Fab\Messenger\ViewHelpers\Show;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which makes possible to traverse an object as for an associative array(key => value).
 */
class SanitizeViewHelper extends AbstractViewHelper
{

    /**
     * Return a traversable object as for an associative array(key => value).
     *
     * @param mixed $item
     * @return string
     */
    public function render($item)
    {
        $item = $this->makeItemTraversable($item);
        $item = $this->convertPropertiesToFields($item);
        return $item;
    }

    /**
     * @param mixed $item
     * @return string
     */
    protected function convertPropertiesToFields($item)
    {
        $convertedItem = array();
        foreach ($item as $propertyName => $value) {
            $fieldName = GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
            $convertedItem[$fieldName] = $value;
        }
        return $convertedItem;
    }

    /**
     * Return a traversable object as for an associative array(key => value).
     *
     * @param mixed $item
     * @return string
     */
    protected function makeItemTraversable($item)
    {
        if ($item instanceof AbstractEntity) {
            $item = $item->_getProperties();
        }
        return $item;
    }

}
