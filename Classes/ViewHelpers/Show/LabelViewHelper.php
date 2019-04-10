<?php
namespace Fab\Messenger\ViewHelpers\Show;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Tca\Tca;

/**
 * View helper which renders a label given by the fieldName in the context.
 */
class LabelViewHelper extends AbstractViewHelper
{

    /**
     * Return a label given by the fieldName in the context.
     *
     * @return string
     */
    public function render()
    {
        $label = '';
        $fieldName = $this->templateVariableContainer->get('fieldName');
        if ($this->templateVariableContainer->exists('dataType')) {
            $dataType = $this->templateVariableContainer->get('dataType');
            $label = Tca::table($dataType)->field($fieldName)->getLabel();
        }
        return $label;
    }
}
