## URL-Campaignify

### Background

The open source web analytics tool Piwik can use campaigns and keywords
for categorising incoming links. They work by appending additional GET
params to your HTTP URLs:

    http://my-site.tld/?pk_campaign=newsletter-5&pk_kwd=header-link

[Read more about this technique in the Piwik docs](http://piwik.org/docs/tracking-campaigns/)

[Google analytics](https://support.google.com/analytics/bin/answer.py?hl=en&answer=1033863)
and probably most other analytics tool do basically the same thing.

### What

This class aims to make it easier to dynamically append such parameters to URLs.

#### Single URLs

Instead of worrying about `?` and `&` you can just do this:

    $uc = new UrlCampaignify();
    
    $url = "http://some-blog.tld/cms.php?post=123&layout=default";
    
    $newUrl = $uc->campaignify($url, "newsletter-5", "header-link");

The result has properly appended parameters:

    http://some-blog.tld/cms.php?post=123&layout=default&pk_campaign=newsletter-5&pk_kwd=header-link

#### Text blocks

You can also throw entire blobs of text at the function. It will find and
campaignify all HTTP URLs in it.

    $uc = new UrlCampaignify();
    
    $text = "Look at http://my-site.tld especially".
            "here: http://my-site.tld/news.htm";

    $newUrl = $uc->campaignify($text, "newsletter-5", "header-link");

If you are expecting HTML input, it makes sense to only change the URLs
in `href` attributes. Use `campaignifyHref()` for this. It will turn

    See <a href="http://site.tld">http://site.tld</a> for more information.

into

    See <a href="http://site.tld?pk_campaign=foo">http://site.tld</a> for more information.

Have a look at the test cases to see which situations and edge cases have been
covered -- or not.

#### Auto-number URLs in text blocks

All campaignified URLs in a text block are counted (starting at 1). You can use
the current number of a URL in your keyword in `sprintf()` style. This is useful
if you want to differentiate between several identical URLs in one text.

    $uc = new UrlCampaignify();
    
    $text = "Here comes the header link: http://my-site.tld".
            "here is a long and verbose text".
            "and another link at the end: http://my-site.tld";

    $newUrl = $uc->campaignify($text, "news", "link-%d");

Will give you

> Here comes the header link: http://my-site.tld?pk_campaign=news&pk_kwd=link-1
> here is a long and verbose text and another link at the end:
> http://my-site.tld?pk_campaign=news&pk_kwd=link-2";

#### Domains

It only makes sense to add campaigns if you actually analyse them. This implies
that you control the site and its analytics tool. You can restrict UrlCampaignify
to only work on URLs on a given Domain. Just pass it to the constructor

    $uc = new UrlCampaignify('my-site.tld')

Note that subdomains are not automatically included, so the above instance will
*not* touch URLs on `www.my-site.tld`. You can specify multiple domains as an
array, though:

    $uc = new UrlCampaignify(array('my-site.tld', 'www.my-site.tld', 'my-other-site.tld'))