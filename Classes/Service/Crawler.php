<?php
namespace Vanilla\Messenger\Service;
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
	protected $postVars = array();

	/**
	 * Contains the vars to send by GET
	 *
	 * @var array
	 */
	protected $getVars = array();

	/**
	 * cURL handler
	 *
	 * @var resource
	 */
	protected $ch;

	/**
	 * The headers to send
	 *
	 * @var string
	 */
	protected $headers;

	/**
	 * The number of the current channel
	 *
	 * @var int
	 */
	protected $channels;

	/**
	 * The url
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * The resulted text
	 *
	 * @var string
	 */
	protected $result;

	/**
	 * The resulted headers
	 *
	 * @var string
	 */
	protected $header;

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
	 * @return $this
	 */
	public function addPostVar($name, $value) {
		// If value is an array, dig recursively into the structure
		if (is_array($value)) {
			foreach ($value as $key => $subValue) {
				$this->addPostVar($name . '[' . $key . ']', $subValue);
			}
		} else {
			$this->postVars[$name] = $value;
		}
		return $this;
	}

	/**
	 * Add get vars
	 *
	 * @param string $name
	 * @param string $value
	 * @return $this
	 */
	public function addGetVar($name, $value) {
		$this->getVars[$name] = $value;
		return $this;
	}

	/**
	 * Execute the request and return the result
	 *
	 * @throws \RuntimeException
	 * @internal param string $url
	 * @return string
	 */
	public function getFinalUrl() {
		if (empty($this->url)) {
			throw new \RuntimeException('Crawler: I could not find the URL. Please set one!', 1400408814);
		}

		$url = $this->url;
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
		return $url;
	}

	/**
	 * Execute the request and return the result
	 *
	 * @return string
	 */
	public function exec() {

		// Set the options
		curl_setopt($this->ch, CURLOPT_URL, $this->getFinalUrl());
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

		$this->result = $t;
		$this->header = curl_getinfo($this->ch, CURLINFO_HEADER_OUT);

		return $this->result;
	}

	/**
	 * Return the headers
	 *
	 * @return string
	 */
	public function getHeader() {
		return $this->header;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param string $url
	 * @return $this
	 */
	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}
}
