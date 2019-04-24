<?php

namespace Fab\Messenger\Grid;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Grid\ColumnRendererAbstract;

/**
 * Class for rendering the "Button Group" in the Grid, e.g. edit, delete, etc..
 */
class UuidRenderer extends ColumnRendererAbstract
{

    /**
     * Render the "Button Group" in the Grid, e.g. edit, delete, etc..
     *
     * @return string
     */
    public function render()
    {
        return sprintf(
            '<a href="/?type=1556100596&uuid=%s&source=%s" target="_blank">%s</a>',
            $this->object['uuid'],
            empty($this->gridRendererConfiguration['source']) ? 'sentMessages' : $this->gridRendererConfiguration['source'],
            $this->object['uuid']
        );
    }

}
