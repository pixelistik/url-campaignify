<?php

class UrlCampaignify
{
	protected $campaignKey = 'pk_campaign';
	protected $keywordKey = 'pk_keyword';
	
	protected $campaignValue = null;
	protected $keywordValue = null;
	
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
	
	/**
	 * Add a campaign and (optionally) keyword param to a single URL
	 */
	protected function campaignifyUrl($urlMatches) {
		$url = $urlMatches[0];
		
		// Parse existing querystring into an array
		$query = parse_url($url, PHP_URL_QUERY);
		$params = array();
		parse_str($query, $params);
		
		// Add our params, if no campaign is there yet
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
		echo "\nCampaignified\n".
			"    ".$url."\n".
			"into\n".
			"    ".$newUrl."\n";
		return $newUrl;
	}
}