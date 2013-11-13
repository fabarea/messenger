<?php
namespace TYPO3\CMS\Messenger\Strategy\Html2Text;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Fabien Udriot <fudriot@cobweb.ch>, Cobweb
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
 * @see http://www.chuggnutt.com/html2text
 *
 */
class RegexpStrategy implements \TYPO3\CMS\Messenger\Strategy\Html2Text\StrategyInterface {

	/**
	 *  Contains the HTML content to convert.
	 *
	 * @var string $html
	 * @access public
	 */
	protected $html;

	/**
	 *  Contains the converted, formatted text.
	 *
	 * @var string $text
	 * @access public
	 */
	protected $text;

	/**
	 *  Maximum width of the formatted text, in columns.
	 *  Set this value to 0 (or less) to ignore word wrapping
	 *  and not constrain text to a fixed-width column.
	 *
	 * @var integer $width
	 * @access public
	 */
	protected $width = 70;

	/**
	 *  List of preg* regular expression patterns to search for,
	 *  used in conjunction with $replace.
	 *
	 * @var array $search
	 * @access public
	 * @see $replace
	 */
	protected $search = array(
		"/\r/", // Non-legal carriage return
		"/[\n\t]+/", // Newlines and tabs
		'/[ ]{2,}/', // Runs of spaces, pre-handling
		'/<script[^>]*>.*?<\/script>/i', // <script>s -- which strip_tags supposedly has problems with
		'/<style[^>]*>.*?<\/style>/i', // <style>s -- which strip_tags supposedly has problems with
		//'/<!-- .* -->/',                         // Comments -- which strip_tags might have problem a with
		'/<h[123][^>]*>(.*?)<\/h[123]>/ie', // H1 - H3
		'/<h[456][^>]*>(.*?)<\/h[456]>/ie', // H4 - H6
		'/<p[^>]*>/i', // <P>
		'/<br[^>]*>/i', // <br>
		'/<b[^>]*>(.*?)<\/b>/ie', // <b>
		'/<strong[^>]*>(.*?)<\/strong>/ie', // <strong>
		'/<i[^>]*>(.*?)<\/i>/i', // <i>
		'/<em[^>]*>(.*?)<\/em>/i', // <em>
		'/(<ul[^>]*>|<\/ul>)/i', // <ul> and </ul>
		'/(<ol[^>]*>|<\/ol>)/i', // <ol> and </ol>
		'/<li[^>]*>(.*?)<\/li>/i', // <li> and </li>
		'/<li[^>]*>/i', // <li>
		'/<a [^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/ie',
		// <a href="">
		'/<hr[^>]*>/i', // <hr>
		'/(<table[^>]*>|<\/table>)/i', // <table> and </table>
		'/(<tr[^>]*>|<\/tr>)/i', // <tr> and </tr>
		'/<td[^>]*>(.*?)<\/td>/i', // <td> and </td>
		'/<th[^>]*>(.*?)<\/th>/ie', // <th> and </th>
		'/&(nbsp|#160);/i', // Non-breaking space
		'/&(quot|rdquo|ldquo|#8220|#8221|#147|#148);/i',
		// Double quotes
		'/&(apos|rsquo|lsquo|#8216|#8217);/i', // Single quotes
		'/&gt;/i', // Greater-than
		'/&lt;/i', // Less-than
		'/&(amp|#38);/i', // Ampersand
		'/&(copy|#169);/i', // Copyright
		'/&(trade|#8482|#153);/i', // Trademark
		'/&(reg|#174);/i', // Registered
		'/&(mdash|#151|#8212);/i', // mdash
		'/&(ndash|minus|#8211|#8722);/i', // ndash
		'/&(bull|#149|#8226);/i', // Bullet
		'/&(pound|#163);/i', // Pound sign
		'/&(euro|#8364);/i', // Euro sign
		'/&[^&;]+;/i', // Unknown/unhandled entities
		'/[ ]{2,}/' // Runs of spaces, post-handling
	);

	/**
	 *  List of pattern replacements corresponding to patterns searched.
	 *
	 * @var array $replace
	 * @access public
	 * @see $search
	 */
	protected $replace = array(
		'', // Non-legal carriage return
		' ', // Newlines and tabs
		' ', // Runs of spaces, pre-handling
		'', // <script>s -- which strip_tags supposedly has problems with
		'', // <style>s -- which strip_tags supposedly has problems with
		//'',                                     // Comments -- which strip_tags might have problem a with
		"strtoupper(\"\n\n\\1\n\n\")", // H1 - H3
		"ucwords(\"\n\n\\1\n\n\")", // H4 - H6
		"\n\n\t", // <P>
		"\n", // <br>
		'strtoupper("\\1")', // <b>
		'strtoupper("\\1")', // <strong>
		'_\\1_', // <i>
		'_\\1_', // <em>
		"\n\n", // <ul> and </ul>
		"\n\n", // <ol> and </ol>
		"\t* \\1\n", // <li> and </li>
		"\n\t* ", // <li>
		'$this->_BuildLinkList("\\1", "\\2")',
		// <a href="">
		"\n-------------------------\n", // <hr>
		"\n\n", // <table> and </table>
		"\n", // <tr> and </tr>
		"\t\t\\1\n", // <td> and </td>
		"strtoupper(\"\t\t\\1\n\")", // <th> and </th>
		' ', // Non-breaking space
		'"', // Double quotes
		"'", // Single quotes
		'>',
		'<',
		'&',
		'(c)',
		'(tm)',
		'(R)',
		'--',
		'-',
		'*',
		'�',
		'EUR', // Euro sign. � ?
		'', // Unknown/unhandled entities
		' ' // Runs of spaces, post-handling
	);

	/**
	 * Contains a list of HTML tags to allow in the resulting text.
	 *
	 * @var string $allowedTags
	 * @see setAllowedTags()
	 */
	protected $allowedTags = '';

	/**
	 *  Contains the base URL that relative links should resolve to.
	 *
	 * @var string $url
	 */
	protected $url;

	/**
	 *  Indicates whether content in the $html variable has been converted yet.
	 *
	 * @var boolean $_converted
	 * @access private
	 * @see $html, $text
	 */
	protected $_converted = FALSE;

	/**
	 *  Contains URL addresses from links to be rendered in plain text.
	 *
	 * @var string $linkList
	 * @access private
	 * @see _BuildLinkList()
	 */
	protected $linkList = '';

	/**
	 *  Number of valid links detected in the text, used for plain text
	 *  display (rendered similar to footnotes).
	 *
	 * @var integer $linkCount
	 * @access private
	 * @see _BuildLinkList()
	 */
	protected $linkCount = 0;

	/**
	 *  Constructor.
	 *  If the HTML source string (or file) is supplied, the class
	 *  will instantiate with that source propagated, all that has
	 *  to be done it to call get_text().
	 *
	 * @param string $source HTML content
	 * @param boolean $from_file Indicates $source is a file to pull content from
	 * @return \TYPO3\CMS\Messenger\Strategy\Html2Text\RegexpStrategy
	 */
	public function  __construct($source = '', $from_file = FALSE) {
		if (!empty($source)) {
			$this->setHtml($source, $from_file);
		}
		$this->setBaseUrl();
	}

	/**
	 *  Loads source HTML into memory, either from $source string or a file.
	 *
	 * @param string $source HTML content
	 * @param boolean $from_file Indicates $source is a file to pull content from
	 * @return void
	 */
	public function setHtml($source, $from_file = FALSE) {
		$this->html = $source;

		if ($from_file && file_exists($source)) {
			$fp = fopen($source, 'r');
			$this->html = fread($fp, filesize($source));
			fclose($fp);
		}

		$this->_converted = FALSE;
	}

	/**
	 *  Returns the text, converted from HTML.
	 *
	 * @param string $input
	 * @param bool $fromFile
	 * @return string
	 */
	public function convert($input, $fromFile = FALSE) {
		if (!empty($input)) {
			$this->setHtml($input, $fromFile);
		}

		if (!$this->_converted) {
			$this->_convert();
		}

		return $this->improveOutput($this->text);
	}

	/**
	 * Make the output more beautiful
	 *
	 * @param string $text
	 * @return string
	 */
	protected function improveOutput($text = '') {
		$lines = explode("\n", $text);
		$lines = array_map('trim', $lines);
		return implode("\n", $lines);
	}

	/**
	 *  Sets the allowed HTML tags to pass through to the resulting text.
	 *  Tags should be in the form "<p>", with no corresponding closing tag.
	 *
	 * @param string $allowedTags
	 * @return void
	 */
	public function setAllowedTags($allowedTags = '') {
		if (!empty($allowedTags)) {
			$this->allowedTags = $allowedTags;
		}
	}

	/**
	 *  Sets a base URL to handle relative links.
	 *
	 * @param string $url
	 * @return void
	 */
	public function setBaseUrl($url = '') {
		if (empty($url)) {
			if (!empty($_SERVER['HTTP_HOST'])) {
				$this->url = 'http://' . $_SERVER['HTTP_HOST'];
			} else {
				$this->url = '';
			}
		} else {
			// Strip any trailing slashes for consistency (relative
			// URLs may already start with a slash like "/file.html")
			if (substr($url, -1) == '/') {
				$url = substr($url, 0, -1);
			}
			$this->url = $url;
		}
	}

	/**
	 *  Workhorse function that does actual conversion.
	 *  First performs custom tag replacement specified by $search and
	 *  $replace arrays. Then strips any remaining HTML tags, reduces whitespace
	 *  and newlines to a readable format, and word wraps the text to
	 *  $width characters.
	 *
	 * @return void
	 */
	protected function _convert() {
		// Variables used for building the link list
		$this->linkCount = 0;
		$this->linkList = '';

		$text = trim(stripslashes($this->html));

		// Run our defined search-and-replace
		$text = preg_replace($this->search, $this->replace, $text);

		// Strip any other HTML tags
		$text = strip_tags($text, $this->allowedTags);

		// Bring down number of empty lines to 2 max
		$text = preg_replace("/\n\s+\n/", "\n\n", $text);
		$text = preg_replace("/[\n]{3,}/", "\n\n", $text);

		// Add link list
		if (!empty($this->linkList)) {
			$text .= "\n\nLinks:\n------\n" . $this->linkList;
		}

		// Wrap the text to a readable format
		// for PHP versions >= 4.0.2. Default width is 75
		// If width is 0 or less, don't wrap the text.
		if ($this->width > 0) {
			$text = wordwrap($text, $this->width);
		}

		$this->text = $text;

		$this->_converted = true;
	}

	/**
	 *  Helper function called by preg_replace() on link replacement.
	 *  Maintains an internal list of links to be displayed at the end of the
	 *  text, with numeric indices to the original point in the text they
	 *  appeared. Also makes an effort at identifying and handling absolute
	 *  and relative links.
	 *
	 * @param string $link URL of the link
	 * @param string $display Part of the text to associate number with
	 * @return string
	 */
	protected function _BuildLinkList($link, $display) {
		if (substr($link, 0, 7) == 'http://' || substr($link, 0, 8) == 'https://' ||
			substr($link, 0, 7) == 'mailto:'
		) {
			$this->linkCount++;
			$this->linkList .= "[" . $this->linkCount . "] $link\n";
			$additional = ' [' . $this->linkCount . ']';
		} elseif (substr($link, 0, 11) == 'javascript:') {
			// Don't count the link; ignore it
			$additional = '';
			// what about href="#anchor" ?
		} else {
			$this->linkCount++;
			$this->linkList .= "[" . $this->linkCount . "] " . $this->url;
			if (substr($link, 0, 1) != '/') {
				$this->linkList .= '/';
			}
			$this->linkList .= "$link\n";
			$additional = ' [' . $this->linkCount . ']';
		}

		return $display . $additional;
	}

	/**
	 * Whether the converter is available
	 *
	 * @return boolean
	 */
	public function available() {
		return TRUE;
	}

}

?>