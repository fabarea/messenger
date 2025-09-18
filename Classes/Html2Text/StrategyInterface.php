<?php

namespace Fab\Messenger\Html2Text;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Strategy Interface for converting HTML to text.
 */
interface StrategyInterface
{
    /**
     * Convert a given HTML input to Text
     *
     * @param string $input
     * @return string
     */
    public function convert(string $input): string;

    /**
     * Whether the converter is available
     *
     * @return bool
     */
    public function available(): bool;
}
