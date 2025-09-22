<?php

namespace Fab\Messenger\Html2Text;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * @see https://github.com/mtibben/html2text
 */
class RegexpStrategy implements StrategyInterface
{
    final public const ENCODING = 'UTF-8';

    /**
     * List of preg* regular expression patterns to search for,
     * used in conjunction with $replace.
     *
     * @var array
     * @see $replace
     */
    protected array $search = [
        "/\r/",
        // Non-legal carriage return
        "/[\n\t]+/",
        // Newlines and tabs
        '/<head[^>]*>.*?<\/head>/i',
        // <head>
        '/<script[^>]*>.*?<\/script>/i',
        // <script>s -- which strip_tags supposedly has problems with
        '/<style[^>]*>.*?<\/style>/i',
        // <style>s -- which strip_tags supposedly has problems with
        '/<p[^>]*>/i',
        // <P>
        '/<br[^>]*>/i',
        // <br>
        '/<i[^>]*>(.*?)<\/i>/i',
        // <i>
        '/<em[^>]*>(.*?)<\/em>/i',
        // <em>
        '/(<ul[^>]*>|<\/ul>)/i',
        // <ul> and </ul>
        '/(<ol[^>]*>|<\/ol>)/i',
        // <ol> and </ol>
        '/(<dl[^>]*>|<\/dl>)/i',
        // <dl> and </dl>
        '/<li[^>]*>(.*?)<\/li>/i',
        // <li> and </li>
        '/<dd[^>]*>(.*?)<\/dd>/i',
        // <dd> and </dd>
        '/<dt[^>]*>(.*?)<\/dt>/i',
        // <dt> and </dt>
        '/<li[^>]*>/i',
        // <li>
        '/<hr[^>]*>/i',
        // <hr>
        '/<div[^>]*>/i',
        // <div>
        '/(<table[^>]*>|<\/table>)/i',
        // <table> and </table>
        '/(<tr[^>]*>|<\/tr>)/i',
        // <tr> and </tr>
        '/<td[^>]*>(.*?)<\/td>/i',
        // <td> and </td>
        '/<span class="_html2text_ignore">.+?<\/span>/i',
    ];

    /**
     * List of pattern replacements corresponding to patterns searched.
     *
     * @var array
     * @see $search
     */
    protected array $replace = [
        '',
        // Non-legal carriage return
        ' ',
        // Newlines and tabs
        '',
        // <head>
        '',
        // <script>s -- which strip_tags supposedly has problems with
        '',
        // <style>s -- which strip_tags supposedly has problems with
        "\n\n",
        // <P>
        "\n",
        // <br>
        '_\\1_',
        // <i>
        '_\\1_',
        // <em>
        "\n\n",
        // <ul> and </ul>
        "\n\n",
        // <ol> and </ol>
        "\n\n",
        // <dl> and </dl>
        "\t* \\1\n",
        // <li> and </li>
        " \\1\n",
        // <dd> and </dd>
        "\t* \\1",
        // <dt> and </dt>
        "\n\t* ",
        // <li>
        "\n-------------------------\n",
        // <hr>
        "<div>\n",
        // <div>
        "\n\n",
        // <table> and </table>
        "\n",
        // <tr> and </tr>
        "\t\t\\1\n",
        // <td> and </td>
        '',
    ];

    /**
     * List of preg* regular expression patterns to search for,
     * used in conjunction with $entReplace.
     *
     * @var array
     * @see $entReplace
     */
    protected array $entSearch = [
        '/&#153;/i',
        // TM symbol in win-1252
        '/&#151;/i',
        // m-dash in win-1252
        '/&(amp|#38);/i',
        // Ampersand: see converter()
        '/[ ]{2,}/',
    ];

    /**
     * List of pattern replacements corresponding to patterns searched.
     *
     * @var array
     * @see $entSearch
     */
    protected array $entReplace = [
        '™',
        // TM symbol
        '—',
        // m-dash
        '|+|amp|+|',
        // Ampersand: see converter()
        ' ',
    ];

    /**
     * List of preg* regular expression patterns to search for
     * and replace using callback function.
     *
     * @var array
     */
    protected array $callbackSearch = [
        '/<(h)[123456]( [^>]*)?>(.*?)<\/h[123456]>/i',
        // h1 - h6
        '/<(b)( [^>]*)?>(.*?)<\/b>/i',
        // <b>
        '/<(strong)( [^>]*)?>(.*?)<\/strong>/i',
        // <strong>
        '/<(th)( [^>]*)?>(.*?)<\/th>/i',
        // <th> and </th>
        '/<(a) [^>]*href=("|\')([^"\']+)\2([^>]*)>(.*?)<\/a>/i',
    ];

    /**
     * List of preg* regular expression patterns to search for in PRE body,
     * used in conjunction with $preReplace.
     *
     * @var array
     * @see $preReplace
     */
    protected array $preSearch = ["/\n/", "/\t/", '/ /', '/<pre[^>]*>/', '/<\/pre>/'];

    /**
     * List of pattern replacements corresponding to patterns searched for PRE body.
     *
     * @var array
     * @see $preSearch
     */
    protected array $preReplace = ['<br>', '&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;', '', ''];

    /**
     * Temporary workspace used during PRE processing.
     *
     * @var string
     */
    protected string $preContent = '';

    /**
     * Contains the base URL that relative links should resolve to.
     *
     * @var string
     */
    protected string $baseurl = '';

    /**
     * Indicates whether content in the $html variable has been converted yet.
     *
     * @var bool
     * @see $html, $text
     */
    protected bool $converted = false;

    /**
     * Contains URL addresses from links to be rendered in plain text.
     *
     * @var array
     * @see buildlinkList()
     */
    protected array $linkList = [];

    /**
     * Various configuration options (able to be set in the constructor)
     *
     * @var array
     */
    protected array $options = [
        'do_links' => 'inline',
        // 'none'
        // 'inline' (show links inline)
        // 'nextline' (show links on the next line)
        // 'table' (if a table of link URLs should be listed after the text.
        'width' => 70,
    ];

    /**
     * @param string $html Source HTML
     * @param array $options Set configuration options
     */
    public function __construct(protected $html = '', $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Sets a base URL to handle relative links.
     *
     * @param string $baseUrl
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseurl = $baseUrl;
    }

    /**
     *  Returns the text, converted from HTML.
     *
     * @param string $input
     * @return string
     */
    public function convert(string $input): string
    {
        $this->setHtml($input);

        $this->linkList = [];

        $text = trim(stripslashes($this->html));

        $this->converter($text);

        if ($this->linkList) {
            $text .= "\n\nLinks:\n------\n";
            foreach ($this->linkList as $i => $url) {
                $text .= '[' . ($i + 1) . '] ' . $url . "\n";
            }
        }

        $this->converted = true;
        return $text;
    }

    public function setHtml(string $html): void
    {
        $this->html = $html;
        $this->converted = false;
    }

    /**
     * @param $text
     */
    protected function converter(&$text): void
    {
        $this->convertBlockquotes($text);
        $this->convertPre($text);
        $text = preg_replace($this->search, $this->replace, $text);
        $text = preg_replace_callback($this->callbackSearch, $this->pregCallback(...), $text);
        $text = strip_tags($text);
        $text = preg_replace($this->entSearch, $this->entReplace, $text);
        $text = html_entity_decode($text, ENT_QUOTES, self::ENCODING);

        // Remove unknown/unhandled entities (this cannot be done in search-and-replace block)
        $text = preg_replace('/&([a-zA-Z0-9]{2,6}|#[0-9]{2,4});/', '', $text);

        // Convert "|+|amp|+|" into "&", need to be done after handling of unknown entities
        // This properly handles situation of "&amp;quot;" in input string
        $text = str_replace('|+|amp|+|', '&', $text);

        // Normalise empty lines
        $text = preg_replace("/\n\s+\n/", "\n\n", $text);
        $text = preg_replace("/[\n]{3,}/", "\n\n", $text);

        // remove leading empty lines (can be produced by eg. P tag on the beginning)
        $text = ltrim($text, "\n");

        if ($this->options['width'] > 0) {
            $text = wordwrap($text, $this->options['width']);
        }
    }

    /**
     * Helper function for BLOCKQUOTE body conversion.
     *
     * @param string $text HTML content
     */
    protected function convertBlockquotes(string &$text): void
    {
        if (preg_match_all('/<\/*blockquote[^>]*>/i', $text, $matches, PREG_OFFSET_CAPTURE)) {
            $start = 0;
            $taglen = 0;
            $level = 0;
            $diff = 0;
            foreach ($matches[0] as $m) {
                if ($m[0][0] == '<' && $m[0][1] == '/') {
                    $level--;
                    if ($level < 0) {
                        $level = 0; // malformed HTML: go to next blockquote
                    } elseif ($level > 0) {
                        // skip inner blockquote
                    } else {
                        $end = $m[1];
                        $len = $end - $taglen - $start;
                        // Get blockquote content
                        $body = substr($text, $start + $taglen - $diff, $len);

                        // Set text width
                        $pWidth = $this->options['width'];
                        if ($this->options['width'] > 0) {
                            $this->options['width'] -= 2;
                        }
                        // Convert blockquote content
                        $body = trim($body);
                        $this->converter($body);
                        // Add citation markers and create PRE block
                        $body = preg_replace('/((^|\n)>*)/', '\\1> ', trim((string)$body));
                        $body = '<pre>' . htmlspecialchars($body) . '</pre>';
                        // Re-set text width
                        $this->options['width'] = $pWidth;
                        // Replace content
                        $text =
                            substr($text, 0, $start - $diff) .
                            $body .
                            substr($text, $end + strlen((string)$m[0]) - $diff);

                        $diff = $len + $taglen + strlen((string)$m[0]) - strlen($body);
                        unset($body);
                    }
                } else {
                    if ($level == 0) {
                        $start = $m[1];
                        $taglen = strlen((string)$m[0]);
                    }
                    $level++;
                }
            }
        }
    }

    protected function convertPre(&$text): void
    {
        // get the content of PRE element
        while (preg_match('/<pre[^>]*>(.*)<\/pre>/ismU', (string)$text, $matches)) {
            $this->preContent = $matches[1];

            // Run our defined tags search-and-replace with callback
            $this->preContent = preg_replace_callback(
                $this->callbackSearch,
                $this->pregCallback(...),
                $this->preContent,
            );

            // convert the content
            $this->preContent = sprintf(
                '<div><br>%s<br></div>',
                preg_replace($this->preSearch, $this->preReplace, $this->preContent),
            );

            // replace the content (use callback because content can contain $0 variable)
            $text = preg_replace_callback('/<pre[^>]*>.*<\/pre>/ismU', $this->pregPreCallback(...), $text, 1);

            // free memory
            $this->preContent = '';
        }
    }

    /**
     * Whether the converter is available
     *
     * @return bool
     */
    public function available(): bool
    {
        return true;
    }

    /**
     * Callback function for preg_replace_callback use.
     *
     * @param array $matches PREG matches
     * @return string
     */
    protected function pregCallback(array $matches): string
    {
        switch (strtolower((string)$matches[1])) {
            case 'b':
            case 'strong':
                return $this->toupper($matches[3]);
            case 'th':
                return $this->toupper("\t\t" . $matches[3] . "\n");
            case 'h':
                return $this->toupper("\n\n" . $matches[3] . "\n\n");
            case 'a':
                // override the link method
                $linkOverride = null;
                if (preg_match('/_html2text_link_(\w+)/', (string)$matches[4], $linkOverrideMatch)) {
                    $linkOverride = $linkOverrideMatch[1];
                }
                // Remove spaces in URL (#1487805)
                $url = str_replace(' ', '', (string)$matches[3]);

                return $this->buildlinkList($url, $matches[5], $linkOverride);
        }

        return '';
    }

    /**
     * Strtoupper function with HTML tags and entities handling.
     *
     * @param string $str Text to convert
     * @return string Converted text
     */
    private function toupper(string $str): string
    {
        // string can contain HTML tags
        $chunks = preg_split('/(<[^>]*>)/', $str, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        // convert toupper only the text between HTML tags
        foreach ($chunks as $i => $chunk) {
            if ($chunk[0] != '<') {
                $chunks[$i] = $this->strtoupper($chunk);
            }
        }

        return implode($chunks);
    }

    /**
     * Strtoupper multibyte wrapper function with HTML entities handling.
     *
     * @param string $str Text to convert
     * @return string Converted text
     */
    private function strtoupper(string $str): string
    {
        $str = html_entity_decode($str, ENT_COMPAT, self::ENCODING);

        if (function_exists('mb_strtoupper')) {
            $str = mb_strtoupper($str, self::ENCODING);
        } else {
            $str = strtoupper($str);
        }

        return htmlspecialchars($str, ENT_COMPAT, self::ENCODING);
    }

    /**
     * Helper function called by preg_replace() on link replacement.
     *
     * Maintains an internal list of links to be displayed at the end of the
     * text, with numeric indices to the original point in the text they
     * appeared. Also makes an effort at identifying and handling absolute
     * and relative links.
     *
     * @param string $link URL of the link
     * @param string $display Part of the text to associate number with
     * @param null $linkOverride
     * @return string
     */
    protected function buildlinkList(string $link, string $display, $linkOverride = null): string
    {
        $linkMethod = $linkOverride ?: $this->options['do_links'];
        if ($linkMethod == 'none') {
            return $display;
        }

        // Ignored link types
        if (preg_match('!^(javascript:|mailto:|#)!i', $link)) {
            return $display;
        }

        if (preg_match('!^([a-z][a-z0-9.+-]+:)!i', $link)) {
            $url = $link;
        } else {
            $url = $this->baseurl;
            if (substr($link, 0, 1) != '/') {
                $url .= '/';
            }
            $url .= $link;
        }

        if ($linkMethod == 'table') {
            if (($index = array_search($url, $this->linkList)) === false) {
                $index = count($this->linkList);
                $this->linkList[] = $url;
            }

            return $display . ' [' . ($index + 1) . ']';
        }
        if ($linkMethod == 'nextline') {
            return $display . "\n[" . $url . ']';
        }
        // link_method defaults to inline
        return $display . ' [' . $url . ']';

    }

    /**
     * Callback function for preg_replace_callback use in PRE content handler.
     *
     * @param array $matches PREG matches
     * @return string
     */
    protected function pregPreCallback /** @noinspection PhpUnusedParameterInspection */ (array $matches): string
    {
        return $this->preContent;
    }
}
