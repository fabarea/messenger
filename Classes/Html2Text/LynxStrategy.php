<?php

namespace Fab\Messenger\Html2Text;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Use lynx to convert html 2 text
 */
class LynxStrategy implements StrategyInterface
{
    protected string $lynx = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lynx = $this->getLynx();
    }

    /**
     * Try to guess the lynx binary path
     *
     * @return string
     */
    public function getLynx(): string
    {
        if (!empty($this->lynx)) {
            return $this->lynx;
        }

        $lynxPath = '';
        $command = 'which lynx';
        exec($command, $result);
        if (!empty($result)) {
            $lynxPath = $result[0];
        }
        return $lynxPath;
    }

    /**
     * Set the lynx path
     *
     * @param string $lynx
     */
    public function setLynx(string $lynx): void
    {
        $this->lynx = $lynx;
    }

    /**
     * Convert a given HTML input to Text
     *
     * @param string $input
     * @return string
     */
    public function convert(string $input): string
    {
        $output = '';

        // Only if lynx path exists
        if ($this->lynx) {
            $command = sprintf('echo "%s" | %s --dump -stdin | %s', $input, $this->lynx, "sed -e 's/^   //g'");
            exec($command, $result);
            $output = implode("\n", $result);
        }

        return trim($output);
    }

    /**
     * Whether the converter is available
     *
     * @return bool
     */
    public function available(): bool
    {
        return !empty($this->lynx);
    }
}
