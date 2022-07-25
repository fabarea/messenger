<?php
namespace Fab\Messenger\Service;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Html2Text\LynxStrategy;
use Fab\Messenger\Html2Text\RegexpStrategy;
use Fab\Messenger\Html2Text\StrategyInterface;
use InvalidArgumentException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @see http://www.chuggnutt.com/html2text
 */
class Html2Text implements SingletonInterface
{

    /**
     * @var StrategyInterface
     */
    protected $converter;

    /**
     * @var array
     */
    protected $possibleConverters;

    /**
     * Returns a class instance
     *
     * @return Html2Text
     * @throws InvalidArgumentException
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Constructor
     *
     * @return Html2Text
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->possibleConverters[] = GeneralUtility::makeInstance(LynxStrategy::class);
        $this->possibleConverters[] = GeneralUtility::makeInstance(RegexpStrategy::class);
    }

    /**
     * Convert HTML using the best strategy
     *
     * @param string $content to be converted
     * @return string
     */
    public function convert($content)
    {
        if ($this->converter === null) {
            $this->converter = $this->findBestConverter();
        }
        return $this->converter->convert($content);
    }

    /**
     * Find the best suitable converter
     *
     * @return StrategyInterface
     */
    public function findBestConverter()
    {

        $converter = null;
        if ($this->converter) {
            return $this->converter;
        }

        // Else find the best suitable converter
        $converter = end($this->possibleConverters);
        foreach ($this->possibleConverters as $possibleConverter) {
            /** @var StrategyInterface $possibleConverter */
            if ($possibleConverter->available()) {
                $converter = $possibleConverter;
                break;
            }
        }

        return $converter;
    }

    /**
     * Set strategy
     *
     * @return void
     */
    public function setConverter(StrategyInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @return StrategyInterface
     */
    public function getConverter()
    {
        return $this->converter;
    }

    /**
     * @return array
     */
    public function getPossibleConverters()
    {
        return $this->possibleConverters;
    }

    public function setPossibleConverters(array $possibleConverters)
    {
        $this->possibleConverters = $possibleConverters;
    }

    /**
     * @param StrategyInterface $possibleConverter
     */
    public function addPossibleConverter($possibleConverter)
    {
        $this->possibleConverters[] = $possibleConverter;
    }

}
