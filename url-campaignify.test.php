<?php
require_once('url-campaignify.php');

class UrlCampaignifyTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
		$this->uc = new UrlCampaignify();
	}
	
	/* Tests for single URLs */
	/**
	 * Test if the conversion works with URLs being fed in that do not have a 
	 * querystring already
	 */
	public function testSingleUrlsNoQuerystring() {
		// Just a campaign added
		$input = 'http://test.de';
		$expected = 'http://test.de?pk_campaign=newsletter-nov-2012';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012');
		$this->assertEquals($expected, $result);
		
		$input = 'http://test.de/kontakt.html';
		$expected = 'http://test.de/kontakt.html?pk_campaign=newsletter-nov-2012';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012');
		$this->assertEquals($expected, $result);
		
		// A campaign added plus keyword
		$input = 'http://test.de';
		$expected = 'http://test.de?pk_campaign=newsletter-nov-2012&pk_keyword=link1';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012', 'link1');
		$this->assertEquals($expected, $result);
		
		$input = 'http://test.de/impressum.htm';
		$expected = 'http://test.de/impressum.htm?pk_campaign=newsletter-nov-2012&pk_keyword=link1';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012', 'link1');
		$this->assertEquals($expected, $result);
	}
	
	/**
	 * Test if the conversion works with URLs being fed in that do not have a 
	 * querystring already, but a "?" at the end
	 */
	public function testSingleUrlsQuerySign() {
		$input = 'http://test.de?';
		$expected = 'http://test.de?pk_campaign=newsletter-nov-2012';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012');
		$this->assertEquals($expected, $result);
	}
	
	/**
	 * Test if the conversion works with URLs being fed in that have a 
	 * querystring already
	 */
	public function testSingleUrlsExistingQuerystring() {
		// Just a campaign added
		$input = 'http://test.de?param1=one&param2=two';
		$expected = 'http://test.de?param1=one&param2=two&pk_campaign=newsletter-nov-2012';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012');
		$this->assertEquals($expected, $result);
		
		// A campaign added plus keyword
		$input = 'http://test.de?p1=one&param2=two';
		$expected = 'http://test.de?p1=one&param2=two&pk_campaign=newsletter-nov-2012&pk_keyword=link1';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012', 'link1');
		$this->assertEquals($expected, $result);
	}
	
	/**
	 * Test if the conversion properly accepts and produces urlencoded strings
	 */
	public function testSingleUrlsUrlencode() {
		// Given URL already has urlencoded strings
		$input = 'http://test.de?p1=one%2Cvalue&param2=two';
		$expected = 'http://test.de?p1=one%2Cvalue&param2=two&pk_campaign=newsletter-nov-2012&pk_keyword=link1';
		$result = $this->uc->campaignify($input, 'newsletter-nov-2012', 'link1');
		$this->assertEquals($expected, $result);
		// Campaign and keyword have chars that need to be urlencoded, too
		$input = 'http://test.de?p1=one%2Cvalue&param2=two';
		$expected = 'http://test.de?p1=one%2Cvalue&param2=two&pk_campaign=newsletter+nov%2C2012&pk_keyword=link%2C1';
		$result = $this->uc->campaignify($input, 'newsletter nov,2012', 'link,1');
		$this->assertEquals($expected, $result);
	}
	
	/**
	 * Test if the conversion leaves existing campaigns alone 
	 */
	public function testSingleUrlsExistingCampaign() {
		// Just a campaign existing, should stay
		$input = 'http://test.de?pk_campaign=leave-me-alone';
		$expected = 'http://test.de?pk_campaign=leave-me-alone';
		$result = $this->uc->campaignify($input, 'override-attempt');
		$this->assertEquals($expected, $result);
		
		// A campaign plus keyword existing
		$input = 'http://test.de?pk_campaign=leave-me-alone&pk_keyword=me-too';
		$expected = 'http://test.de?pk_campaign=leave-me-alone&pk_keyword=me-too';
		$result = $this->uc->campaignify($input, 'override-attempt', 'override-attempt');
		$this->assertEquals($expected, $result);
		
		// A campaign existing, keyword should NOT be added
		// (keywords mostly make no sense without their campaign)
		$input = 'http://test.de?pk_campaign=leave-me-alone';
		$expected = 'http://test.de?pk_campaign=leave-me-alone';
		$result = $this->uc->campaignify($input, 'override-attempt', 'override-attempt');
		$this->assertEquals($expected, $result);
	}
	
	/**
	 * If a param is another URL (properly encoded), it should be left alone
	 */
	public function testSingleUrlsUrlInParam() {
		$input = 'http://test.de?share=http%3A%2F%2Fexample.org';
		$expected = 'http://test.de?share=http%3A%2F%2Fexample.org&pk_campaign=news';
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);
	}
	
	/* Tests for entire texts */
	
	public function testTextMultipleURLs() {
		$input = "Lorem ipsum dolor https://test.com/ sit 
		amet, consetetur sadipscing elitr, 
		sed diam nonumy eirmod tempor invidunt ut labore et dolore magna 
		aliquyam erat, sed diam voluptua. 
		At http://test.co.uk/test.html";
		
		$expected = "Lorem ipsum dolor https://test.com/?pk_campaign=news sit 
		amet, consetetur sadipscing elitr, 
		sed diam nonumy eirmod tempor invidunt ut labore et dolore magna 
		aliquyam erat, sed diam voluptua. 
		At http://test.co.uk/test.html?pk_campaign=news";
		
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);
	}
	
	/**
	 * Text with the same URL repeated twice
	 */
	public function testTextMultipleRepeatedURLs() {
		$input = "Lorem http://test.com ipsum http://test.com";
		
		$expected = "Lorem http://test.com?pk_campaign=news ipsum http://test.com?pk_campaign=news";
		
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);
	}
	
	/**
	 * Test correct handling of URLs in <> brackets
	 */
	public function testTextBracketedUrl() {
		$input = "Lorem <http://test.com>";
		
		$expected = "Lorem <http://test.com?pk_campaign=news>";
		
		$result = $this->uc->campaignify($input, 'news');
		$this->assertEquals($expected, $result);
	}
}