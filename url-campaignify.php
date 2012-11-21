<?php

class UrlCampaignify
{
	private $campaignKey = 'pk_campaign';
	private $keywordKey = 'pk_keyword';
	
	public function campaignify($url, $campaign, $keyword = false) {
		// Parse existing querystring into an array
		$query = parse_url($url, PHP_URL_QUERY);
		$params = array();
		parse_str($query, $params);
		
		// Add our params
		$params[$this->campaignKey] = $campaign;
		if( $keyword ) {
			$params[$this->keywordKey] = $keyword;
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
}