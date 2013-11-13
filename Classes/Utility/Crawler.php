<?php
namespace TYPO3\CMS\Messenger\Utility;
/**
 * Class to handle cURL requests
 *
 * @author Cyril Mazur    www.cyrilmazur.com    twitter.com/CyrilMazur    facebook.com/CyrilMazur
 */
class Crawler {

	/**
	 * Contains the vars to send by POST
	 *
	 * @var array
	 */
	private $postVars = array();

	/**
	 * Contains the vars to send by GET
	 *
	 * @var array
	 */
	private $getVars = array();

	/**
	 * cURL handler
	 *
	 * @var resource
	 */
	private $ch;

	/**
	 * The headers to send
	 *
	 * @var string
	 */
	private $headers;

	/**
	 * The number of the current channel
	 *
	 * @var int
	 */
	private $channels;

	/**
	 * The resulted text
	 *
	 * @var string
	 */
	private $r_text;

	/**
	 * The resulted headers
	 *
	 * @var string
	 */
	private $r_headers;

	/**
	 * Constructor
	 */
	public function __construct($n = 1) {
		putenv('TZ=US/Pacific');

		$headers = array();
		$headers['agent'] = 'User-Agent: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)';
		$headers['cookie'] = ROOT . '/tmp/curl/cookies/' . $n;
		$headers['randDate'] = mktime(0, 0, 0, date('m'), date('d') - rand(3, 26), date('Y'));

		$this->channels = $n;
		$this->headers = $headers;
		$this->ch = curl_init();
	}

	/**
	 * Add post vars
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function addPostVar($name, $value) {
		$this->postVars[$name] = $value;
	}

	/**
	 * Add get vars
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function addGetVar($name, $value) {
		$this->getVars[$name] = $value;
	}

	/**
	 * Execute the request and return the result
	 *
	 * @param string $url
	 * @return string
	 */
	public function exec($url) {

		// format url
		if (!empty($this->getVars)) {
			$delimiter = '?';
			foreach ($this->getVars as $key => $value) {
				$url .= sprintf('%s%s=%s',
					$delimiter,
					urlencode($key),
					urlencode($value)
				);
				$delimiter = '&';
			}
		}

		// Set the options
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_USERAGENT, $this->headers['agent']);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->headers['cookie']);
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->headers['cookie']);

		curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
		#curl_setopt($this->ch, CURLOPT_HEADER, true);

		// Send the POST vars
		if (sizeof($this->postVars) > 0) {
			$postVars = '';
			foreach ($this->postVars as $name => $value) {
				$postVars .= $name . '=' . $value . '&';
			}
			$postVars = substr($postVars, 0, strlen($postVars) - 1);

			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postVars);
			curl_setopt($this->ch, CURLOPT_POST, 1);
		}

		// Execute and retrieve the result
		$t = '';
		while ($t == '') {
			$t = curl_exec($this->ch);
		}

		$this->r_text = $t;
		$this->r_headers = curl_getinfo($this->ch, CURLINFO_HEADER_OUT);

		return $this->r_text;
	}

	/**
	 * Return the resulted text
	 *
	 * @return string
	 */
	public function getResult() {
		return $this->r_text;
	}

	/**
	 * Return the headers
	 *
	 * @return string
	 */
	public function getHeader() {
		return $this->r_headers;
	}
}

?>