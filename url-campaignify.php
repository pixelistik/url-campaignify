<?php
/**
 * Utility class that helps adding web statistics campaigns to URLs
 *
 * Many web statistics tools, like Piwik or Google Analytics allow you
 * to add a campaign and a keyword to a URL, so you can track the sources
 * of your traffic better. This class aims to be a convenient way to add
 * such parameters to any URL or even a string containing multiple URLs.
 */
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
		
		// \S+ means: Just take any non-whitespace characters after a http(s)://
		$text = preg_replace_callback("/(http|https)\:\/\/\S+/", array($this, 'campaignifyUrl'),$text);
		
		return $text;
	}
}