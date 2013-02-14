<?php
/**
 * Utility class that helps adding web statistics campaigns to URLs
 *
 * Many web statistics tools, like Piwik or Google Analytics allow you
 * to add a campaign and a keyword to a URL, so you can track the sources
 * of your traffic better. This class aims to be a convenient way to add
 * such parameters to any URL or even a string containing multiple URLs.
 *
 * MIT licensed, see LICENSE
 */

class UrlCampaignify
{
	/**
	 * Regex to find URLs
	 *
	 * Taken from
	 * http://stackoverflow.com/a/2015516/376138
	 * (except added beginning/end conditions)
	*/
	const URL_REGEX =
		'/((href\s*=\s*["\'])?)                                 # optional preceding href attribute
		((https?):\/\/                                          # protocol
		(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+         # username
		(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?      # password
		@)?(?#                                                  # auth requires @
		)((([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*           # domain segments AND
		[a-z][a-z0-9-]*[a-z0-9]                                 # top level domain  OR
		|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}
		(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])                 # IP address
		)(:\d+)?                                                # port
		)(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)* # path
		(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)      # query string
		?)?)?                                                   # path and query string optional
		(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?      # fragment
		))/ix';

	/**
	 * Name of the URL param for a campaign
	 */
	protected $campaignKey = 'pk_campaign';
	/**
	 * Name of the URL param for a keyword
	 */
	protected $keywordKey = 'pk_kwd';

	/**
	 * Value for the campaign name that should be added to URLs
	 */
	protected $campaignValue = null;
	/**
	 * Value for the keyword that should be added to URLs
	 */
	protected $keywordValue = null;
	
	/**
	 * Counter that starts at 1 and is increased for every URL found in a
	 * multiple URL texts that is campaigified. Used for auto-increased keywords.
	 */
	protected $urlInTextNumber;
	
	/**
	* If set to true, UrlCampaignify::campaignify() will only look at URLs
	* in a href="" HTML attribute.
	*/
	protected $hrefOnly = false;
	
	/**
	 * String (one) or array (multiple strings). If specified, only URLs for 
	 * these domain(s) will be campaignified.
	 * Note that www.domain.tld and domain.tld have to be specified separately.
	 */
	protected $domain = null;
	
	public function __construct($domain = null) {
		if( is_string($domain) ) {
			$domain = array($domain);
		}
		$this->domain = $domain;
	}

	/**
	 * Add a campaign and (optionally) keyword param to a single URL
	 */
	protected function campaignifyUrl($urlMatches) {
		// Full regex match is passed at index [0]
		// Entire URL is at [3]
		// Domain.tld is at [9]
		// Possible href=" is at [1]
		$url = $urlMatches[3];
		$domain = $urlMatches[9];
		$hrefPart = $urlMatches[1];

		// Are we on hrefOnly and not in a href attribute?
		$skipOnHref = $this->hrefOnly && $hrefPart === "";
		// Is a domain configured and we are not on it?
		$skipOnDomain = $this->domain && !in_array($domain, $this->domain);
		
		if( $skipOnHref || $skipOnDomain ) {
			$newUrl = $url;
		} else {
			/* Do the thing: */
			// Parse existing querystring into an array
			$query = parse_url($url, PHP_URL_QUERY);
			$params = array();
			parse_str($query, $params);
	
			// Add our params, if no campaign is there yet, plus keyword if given
			if( !isset($params[$this->campaignKey]) ){
				$params[$this->campaignKey] = $this->campaignValue;
				if( $this->keywordValue ) {
					// Put URL count into formatted keyword string (if given)
					$keywordValue = sprintf($this->keywordValue, $this->urlInTextNumber);

					$params[$this->keywordKey] = $keywordValue;
				}
			}
	
			$newQuery = http_build_query($params);
	
			if( $query ){
				// If there was a querystring already, replace it
				$newUrl = str_replace($query, $newQuery, $url);
			} else {
				// or just append the new one
				$newUrl = $url . '?' . $newQuery;
				// remove possible "??" if the URL already had a final "?"
				$newUrl = str_replace("??", "?", $newUrl);
			}
			
			$this->urlInTextNumber++;
		}
		
		// Re-attach possible href="
		return $hrefPart . $newUrl;
	}

	/**
	 * Add a campaign and (optionally) keyword param to all URLs in a text
	 */
	public function campaignify($text, $campaign, $keyword = null) {
		$this->campaignValue = $campaign;
		$this->keywordValue = $keyword;
		
		$this->urlInTextNumber = 1;
		
		$this->hrefOnly = false;

		$text = preg_replace_callback(self::URL_REGEX, array($this, 'campaignifyUrl'),$text);

		return $text;
	}
	
	/**
	 * Add a campaign and (optionally) keyword param to all URLs in href attributes
	 */
	public function campaignifyHref($text, $campaign, $keyword = null) {
		$this->campaignValue = $campaign;
		$this->keywordValue = $keyword;
		
		$this->urlInTextNumber = 1;
		
		$this->hrefOnly = true;

		$text = preg_replace_callback(self::URL_REGEX, array($this, 'campaignifyUrl'),$text);

		return $text;
	}
}