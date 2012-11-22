<?php
/**
 * Utility class that helps adding web statistics campaigns to URLs
 *
 * Many web statistics tools, like Piwik or Google Analytics allow you
 * to add a campaign and a keyword to a URL, so you can track the sources
 * of your traffic better. This class aims to be a convenient way to add
 * such parameters to any URL or even a string containing multiple URLs.
 */
 
 // URL regex from http://stackoverflow.com/a/2015516/376138
 // (except beginning/end conditions)
 define('URL_FORMAT',
	'/(https?):\/\/'.                                          // protocol
	'(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+'.         // username
	'(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?'.      // password
	'@)?(?#'.                                                  // auth requires @
	')((([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*'.           // domain segments AND
	'[a-z][a-z0-9-]*[a-z0-9]'.                                 // top level domain  OR
	'|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}'.
	'(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])'.                 // IP address
	')(:\d+)?'.                                                // port
	')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*'. // path
	'(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)'.      // query string
	'?)?)?'.                                                   // path and query string optional
	'(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?'.      // fragment
	'/i');
 
class UrlCampaignify
{
	/**
	 * Name of the URL param for a campaign
	 */
	protected $campaignKey = 'pk_campaign';
	/**
	 * Name of the URL param for a keyword
	 */
	protected $keywordKey = 'pk_keyword';
	
	/**
	 * Value for the campaign name that should be added to URLs
	 */
	protected $campaignValue = null;
	/**
	 * Value for the keyword that should be added to URLs
	 */
	protected $keywordValue = null;
	
	/**
	 * Add a campaign and (optionally) keyword param to a single URL
	 */
	protected function campaignifyUrl($urlMatches) {
		// Full regex match is passed at index 0
		$url = $urlMatches[0];
		
		// Parse existing querystring into an array
		$query = parse_url($url, PHP_URL_QUERY);
		$params = array();
		parse_str($query, $params);
		
		// Add our params, if no campaign is there yet, plus keyword if given
		if( !isset($params[$this->campaignKey]) ){
			$params[$this->campaignKey] = $this->campaignValue;
			if( $this->keywordValue ) {
				$params[$this->keywordKey] = $this->keywordValue;
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
		
		return $newUrl;
	}
	
	/**
	 * Add a campaign and (optionally) keyword param to all URLs in a text
	 */
	public function campaignify($text, $campaign, $keyword = null) {
		$this->campaignValue = $campaign;
		$this->keywordValue = $keyword;
		
		$text = preg_replace_callback(URL_FORMAT, array($this, 'campaignifyUrl'),$text);
		
		return $text;
	}
}