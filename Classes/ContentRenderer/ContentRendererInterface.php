<?php
namespace Fab\Messenger\ContentRenderer;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */


/**
 * Interface for rendering content.
 */
interface ContentRendererInterface
{

    /**
     *
     * @param string $content
     * @param array $markers
     * @return string
     */
    public function render($content, array $markers);
}