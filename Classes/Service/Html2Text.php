<?php
namespace Vanilla\Messenger\Service;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @see http://www.chuggnutt.com/html2text
 */

class Html2Text implements SingletonInterface {

	/**
	 * @var \Vanilla\Messenger\Strategy\Html2Text\StrategyInterface
	 */
	protected $converter;

	/**
	 * @var array
	 */
	protected $possibleConverters;

	/**
	 * Returns a class instance
	 *
	 * @return \Vanilla\Messenger\Service\Html2Text
	 */
	static public function getInstance() {
		return GeneralUtility::makeInstance('Vanilla\Messenger\Service\Html2Text');
	}

	/**
	 * Constructor
	 *
	 * @return \Vanilla\Messenger\Service\Html2Text
	 */
	public function __construct() {
		$this->possibleConverters[] = GeneralUtility::makeInstance('Vanilla\Messenger\Strategy\Html2Text\LynxStrategy');
		$this->possibleConverters[] = GeneralUtility::makeInstance('Vanilla\Messenger\Strategy\Html2Text\RegexpStrategy');
	}

	/**
	 * Convert HTML using the best strategy
	 *
	 * @param string $content to be converted
	 * @return string
	 */
	public function convert($content) {
		if (empty($this->converter)) {
			$this->converter = $this->findBestConverter();
		}
		return $this->converter->convert($content);
	}

	/**
	 * Find the best suitable converter
	 *
	 * @return \Vanilla\Messenger\Strategy\Html2Text\StrategyInterface
	 */
	public function findBestConverter() {

		if (! empty($this->converter)) {
			return $this->converter;
		}

		// Else find the best suitable converter
		$converter = end($this->possibleConverters);
		foreach ($this->possibleConverters as $possibleConverter) {
			/** @var \Vanilla\Messenger\Strategy\Html2Text\StrategyInterface $possibleConverter */
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
	 * @param \Vanilla\Messenger\Strategy\Html2Text\StrategyInterface $converter
	 * @return void
	 */
	public function setConverter(\Vanilla\Messenger\Strategy\Html2Text\StrategyInterface $converter) {
		$this->converter = $converter;
	}

	/**
	 * @return \Vanilla\Messenger\Strategy\Html2Text\StrategyInterface
	 */
	public function getConverter() {
		return $this->converter;
	}

	/**
	 * @return Array
	 */
	public function getPossibleConverters() {
		return $this->possibleConverters;
	}

	/**
	 * @param array $possibleConverters
	 */
	public function setPossibleConverters(array $possibleConverters) {
		$this->possibleConverters = $possibleConverters;
	}

	/**
	 * @param \Vanilla\Messenger\Strategy\Html2Text\StrategyInterface $possibleConverter
	 */
	public function addPossibleConverter($possibleConverter) {
		$this->possibleConverters[] = $possibleConverter;
	}

}
