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
    protected StrategyInterface $converter;

    /**
     * @var array
     */
    protected array $possibleConverters;

    public function __construct()
    {
        $this->possibleConverters[] = GeneralUtility::makeInstance(LynxStrategy::class);
        $this->possibleConverters[] = GeneralUtility::makeInstance(RegexpStrategy::class);
        $this->converter = GeneralUtility::makeInstance(LynxStrategy::class);
    }

    public static function getInstance(): Html2Text
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Convert HTML using the best strategy
     *
     * @param string $content to be converted
     * @return string
     */
    public function convert(string $content): string
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
    public function findBestConverter(): StrategyInterface
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
     * @return StrategyInterface
     */
    public function getConverter(): StrategyInterface
    {
        return $this->converter;
    }

    public function setConverter(StrategyInterface $converter): void
    {
        $this->converter = $converter;
    }

    /**
     * @return array
     */
    public function getPossibleConverters(): array
    {
        return $this->possibleConverters;
    }

    public function setPossibleConverters(array $possibleConverters): void
    {
        $this->possibleConverters = $possibleConverters;
    }

    /**
     * @param StrategyInterface $possibleConverter
     */
    public function addPossibleConverter(StrategyInterface $possibleConverter): void
    {
        $this->possibleConverters[] = $possibleConverter;
    }
}
