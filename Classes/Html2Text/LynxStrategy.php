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

    /**
     * @var string
     */
    protected $lynx = '';

    /**
     * Constructor
     *
     * @return \Fab\Messenger\Html2Text\LynxStrategy
     */
    public function __construct()
    {
        $this->lynx = $this->getLynx();
    }

    /**
     * Convert a given HTML input to Text
     *
     * @param string $input
     * @return string
     */
    public function convert($input)
    {

        $output = '';

        // Only if lynx path exists
        if ($this->lynx) {
            $command = sprintf('echo "%s" | %s --dump -stdin | %s',
                $input,
                $this->lynx,
                "sed -e 's/^   //g'"
            );
            exec($command, $result);
            $output = implode("\n", $result);
        }

        return trim($output);
    }

    /**
     * Try to guess the lynx binary path
     *
     * @return string
     */
    public function getLynx()
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
    public function setLynx($lynx)
    {
        $this->lynx = $lynx;
    }

    /**
     * Whether the converter is available
     *
     * @return boolean
     */
    public function available()
    {
        return !empty($this->lynx);
    }
}
