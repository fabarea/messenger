<?php
namespace Vanilla\Messenger\Html2Text;

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

/**
 * Strategy Interface for converting HTML to text.
 */
interface StrategyInterface {

	/**
	 * Convert a given HTML input to Text
	 *
	 * @param string $input
	 * @return string
	 */
	public function convert($input);

	/**
	 * Whether the converter is available
	 *
	 * @return boolean
	 */
	public function available();
}
