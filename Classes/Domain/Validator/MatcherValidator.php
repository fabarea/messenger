<?php
namespace Vanilla\Messenger\Domain\Validator;
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

/**
 * Validator for \Vanilla\Messenger\QueryElement\Matcher
 */
class MatcherValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

	/**
	 * Remove matching value containing "?" which stands for nothing to match.
	 *
	 * @param Tx_Messenger_QueryElement_Match $matcher
	 * @return boolean true
	 */
	public function isValid($matcher) {
		$matches = array();
		foreach ($matcher->getMatches() as $key => $value) {
			if ($value !== '?') {
				$matches[$key] = $value;
			}
		};
		$matcher->setMatches($matches);
		return TRUE;
	}

}
