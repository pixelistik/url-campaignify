<?php

class UrlCampaignify
{
	private $campaignKey = 'pk_campaign';
	private $keywordKey = 'pk_keyword';
	
	/**
	 * Add a campaign and (optionally) keyword param to all URLs in a text
	 */
	public function campaignify($text, $campaign, $keyword = false) {
		// \S+ means: Just take any non-whitespace characters after a http(s)://
		preg_match_all("/(http|https)\:\/\/\S+/", $text, $urlMatches);
		
		foreach( $urlMatches[0] as $url ) {
			$text = str_replace(
				$url,
				$this->campaignifyUrl($url, $campaign, $keyword),
				$text
			);
		}
		return $text;
	}
	
	/**
	 * Add a campaign and (optionally) keyword param to a single URL
	 */
	protected function campaignifyUrl($url, $campaign, $keyword = false) {
		// Parse existing querystring into an array
		$query = parse_url($url, PHP_URL_QUERY);
		$params = array();
		parse_str($query, $params);
		
		// Add our params, if no campaign is there yet
		if( !isset($params[$this->campaignKey]) ){
			$params[$this->campaignKey] = $campaign;
			if( $keyword ) {
				$params[$this->keywordKey] = $keyword;
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