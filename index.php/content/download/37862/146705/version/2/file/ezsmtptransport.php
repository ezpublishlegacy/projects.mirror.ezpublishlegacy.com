
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<!-- /extension/ezno2005/design/ezno2005/stylesheets/core.css sphere5 -->
<style type="text/css">
    @import url(/extension/ezno2005/design/ezno2005/stylesheets/core.css);
    @import url(/extension/ezno2005/design/ezno2005/stylesheets/pagelayout.css);
    @import url(/extension/ezno2005/design/ezno2005/stylesheets/classes.css);
    @import url(/extension/nxc_download/design/ezno2005/stylesheets/download.css);
    </style>

<link type="text/css" rel="stylesheet" href="/extension/ezno2005/design/ezno2005/stylesheets/print.css" media="print" />

    <link rel="alternate" title="ez.no News" href="/rss/feed/news" type="application/rss+xml" /><link rel="alternate" title="ez.no Developer News" href="/rss/feed/communitynews" type="application/rss+xml" />
<link rel="alternate" title="ez.no Developer Articles" href="/rss/feed/community_articles" type="application/rss+xml" />
<link rel="alternate" title="ez.no Forum" href="/rss/feed/forum" type="application/rss+xml" />
<link rel="alternate" title="ez.no Contributions" href="/rss/feed/contribs" type="application/rss+xml" />
<link rel="alternate" title="ez.no Open Funding" href="/rss/feed/openfunding_suggestions" type="application/rss+xml" />

<!-- IE conditional comments; for bug fixes for different IE versions -->
<!--[if IE 5]>     <style type="text/css"> @import url(/extension/ezno2005/design/ezno2005/stylesheets/ie5.css);    </style> <![endif]-->
<!--[if lte IE 6]> <style type="text/css"> @import url(/extension/ezno2005/design/ezno2005/stylesheets/ie6lte.css); </style> <![endif]-->
<!--[if gte IE 6]> <style type="text/css"> @import url(/extension/ezno2005/design/ezno2005/stylesheets/ie6gte.css); </style> <![endif]-->

<!-- Load special stylesheet (if necessary) for newer Netscape decendants, Mozilla and Firefox, using the Gecko renderer -->
<script type="text/javascript"><!-- if ((navigator.userAgent.indexOf("Gecko") != -1)&&(navigator.userAgent.indexOf("KHTML") == -1)) document.write("<style type=\"text/css\">@import url(/extension/ezno2005/design/ezno2005/stylesheets/gecko.css);</style>");--></script>

                                                                        
<title>
            kernel (3)     /         Error            
</title>
             
<meta http-equiv="Content-Language" content="en-GB" />
<meta name="msvalidate.01" content="C781FE18B439404A9C94213E5ED594FF" />
<meta name="MSSmartTagsPreventParsing" content="TRUE" />
<meta name="generator" content="eZ Publish" />
<meta name="author" content="eZ Systems" />
<meta name="copyright" content="eZ Systems as" />
<meta name="description" content="eZ Publish is an Open Source Content Management System, providing web content management solutions for intranets, e-commerce and digital media publishing." />
<meta name="keywords" content="eZ Publish, ezpublish, ez publish, CMS, open source content management, enterprise content management, intranet, ecommerce, digital media publishing, PHP, Web content management" />





<script type="text/javascript" src="/extension/ezno2005/design/ezno2005/javascripts/yui-event-dom-animation.js"></script>

<!-- Combo-handled YUI CSS files: -->
<link rel="stylesheet" type="text/css" href="/extension/ezno2005/design/ezno2005/javascripts/yui/build/assets/skins/sam/container.css"">
<!-- Combo-handled YUI JS files: -->
<script type="text/javascript" src="/extension/ezno2005/design/ezno2005/javascripts/yui/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="/extension/ezno2005/design/ezno2005/javascripts/yui/build/connection/connection-min.js"></script>
<script type="text/javascript" src="/extension/ezno2005/design/ezno2005/javascripts/yui/build/container/container-min.js"></script>
<script type="text/javascript" src="/extension/ezno2005/design/ezno2005/javascripts/yui/build/json/json-min.js"></script>
<script type="text/javascript" src="/extension/ezno2005/design/ezno2005/javascripts/yui/build/stylesheet/stylesheet-min.js"></script>
<link rel="stylesheet" type="text/css" href="/extension/ezno2005/design/ezno2005/javascripts/yui/build/button/assets/skins/sam/button.css">
<link rel="stylesheet" type="text/css" href="/extension/ezno2005/design/ezno2005/javascripts/yui/build/fonts/fonts-min.css">
<link rel="stylesheet" type="text/css" href="/extension/ezno2005/design/ezno2005/javascripts/yui/build/element/element-min.js">
<link rel="stylesheet" type="text/css" href="/extension/ezno2005/design/ezno2005/javascripts/yui/build/button/button-min.js">


<!--begin custom header content for this example-->


<script type="text/javascript" src="/extension/ezno2005/design/ezno2005/javascripts/tools.js"></script>
<script type="text/javascript" src="/extension/ezno2005/design/ezno2005/javascripts/dropdownmenu.js"></script>

</head>

<body onload="menuInit();" class=" yui-skin-sam">

<!-- Complete page area: START -->
<div id="page">

<!-- Header area: START -->
<div id="header">
<div id="header-insert">

<!-- User menu area: START -->
<div id="usermenu">

<div id="page-width1">

<div id="logo">
<h1><a href="/"><span class="hide">eZ Systems - The Content Management Ecosystem</span></a></h1>
</div>



<p class="hide"><a href="#main">Skip to main content</a></p>

<hr class="hide" />
<div id="searchbox">
<form action="/ezsearchredirect/action" method="get">

<div class="block">
<div class="element">
<input id="searchbutton" class="button" type="submit" value="Search" />
</div>

<div class="element searchstring">
    <label for="searchtext" class="hide">Search text:</label>
    <input id="searchtext" name="SearchText" type="text" size="12" /><br />
    

            <div class="section">
            <label for="searcharea" class="hide">Search area:</label>
            <select id="searcharea" name="SubTreeArray[]">
                <option value="2" selected="selected">All ez.no</option>
                <option value="306">Bug reports</option>
                <option value="21358">Contributions</option>
                <option value="projects">Projects</option>
                <option value="documentation">Documentation</option>
                <option value="308">Forum</option>
            </select>
            </div>
    </div>

</div>
</form>
</div>

<hr class="hide" />
<h2 class="hide">User menu</h2>

<!-- User menu content: START -->
<ul>
            <li><a href="/download"><span>Downloads</span></a></li>
    <li><a rel="nofollow" href="/company/contact"><span>Contact</span></a></li>
            <li><a rel="nofollow" href="/user/login"><span>Log in</span></a></li>
        <li><a href="/user/register"><span>Register</span></a></li>
    
            <li><a rel="nofollow" href="/sitemap"><span>Sitemap</span></a></li>
    
    <li id="language-menu">
        <a class="highlighted" title="English" href="/content/download/48032/124546/file/ezsmtptransport.php"><img src="/extension/ezno2005/design/ezno2005/images/eng-GB.gif" width="18" height="12" alt="English" /></a>
        <a  title="Deutsch" href="/de/content/download/48032/124546/file/ezsmtptransport.php"><img src="/extension/ezno2005/design/ezno2005/images/ger-DE.gif" width="18" height="12" alt="Deutsch" /></a>
        <a  title="Português" href="/br/content/download/48032/124546/file/ezsmtptransport.php"><img src="/extension/ezno2005/design/ezno2005/images/por-BR.gif" width="18" height="12" alt="Português" /></a>
        <a  title="Français" href="/fr/content/download/48032/124546/file/ezsmtptransport.php"><img src="/extension/ezno2005/design/ezno2005/images/fre-FR.gif" width="18" height="12" alt="Français" /></a>
        <a  title="日本語" href="/jp/content/download/48032/124546/file/ezsmtptransport.php"><img src="/extension/ezno2005/design/ezno2005/images/jpn-JP.gif" width="18" height="12" alt="日本語" /></a>        
        <a  title="中文" href="/tw/content/download/48032/124546/file/ezsmtptransport.php"><img src="/extension/ezno2005/design/ezno2005/images/chi-TW.gif" width="18" height="12" alt="中文" /></a>        
        <a  title="Norsk" href="/no/content/download/48032/124546/file/ezsmtptransport.php"><img src="/extension/ezno2005/design/ezno2005/images/nor-NO.gif" width="18" height="12" alt="Norsk" /></a>        
        <a  title="Italiano" href="/it/content/download/48032/124546/file/ezsmtptransport.php"><img src="/extension/ezno2005/design/ezno2005/images/ita-IT.gif" width="18" height="12" alt="Italiano" /></a>
        <a  title="Español" href="/es/content/download/48032/124546/file/ezsmtptransport.php"><img src="/extension/ezno2005/design/ezno2005/images/esl-ES.gif" width="18" height="12" alt="Español" /></a>
    </li>
</ul>
<!-- User menu content: END -->

</div>

</div>
<!-- User menu area: END -->

</div>
</div>
<!-- Header area: END --><script type="text/javascript">
<!--
var menu = new Array();

menu[0] = ['Content Management System', '/ezpublish/content_management_system', 'Use eZ Publish', '/ezpublish/use_ez_publish', 'Manage eZ Publish', '/ezpublish/manage_ez_publish', 'Develop with eZ Publish', '/ezpublish/develop_with_ez_publish', 'Webinars', '/ezpublish/ez_publish_webinars'];
menu[1] = ['eZ Publish Premium', '/support_and_services/ez_publish_premium', 'Training', '/support_and_services/training', 'Certification', '/support_and_services/certification', 'Expert Consulting', '/support_and_services/expert_consulting_services', 'Store', '/support_and_services/store'];
menu[2] = ['eZ Publish', '/ezpublish', 'eZ Components', '/ezcomponents', 'eZ Flow', '/ezflow', 'eZ Find', '/ezfind', 'eZ Newsletter', '/eznewsletter', 'eZ Certified Extensions', '/software/ez_certified_extensions', 'Partner Certified extensions', '/software/partner_certified_extensions', 'Proprietary License Option', '/products/proprietary_license_options', 'eZ Publish installer', '/download/ez_publish_installer'];
menu[3] = ['Digital Media', '/solutions/digital_media', 'Enterprise Content Management', '/solutions/enterprise_content_management', 'Intranet', '/solutions/intranet', 'E-Commerce', '/solutions/e_commerce', 'Community Portal', '/solutions/community_portal', 'Mobile', '/solutions/mobile'];
menu[4] = ['Selection of eZ Publish Users', '/customers/selection_of_ez_publish_users', 'References', '/customers/references', 'Case Studies', '/customers/case_studies', 'User Quotes', '/customers/user_quotes', 'Digital Media Customers', '/customers/digital_media_customers'];
menu[5] = ['Worldwide Partners', '/partner/worldwide_partners', 'eZ Partner Program', '/partner/ez_partner_program', 'Knowledge Centre', '/partner/knowledge_centre'];
menu[6] = ['Download', '/download', 'Articles', '/developer/articles', 'Documentation', '/doc', 'News', '/developer/news', 'Developer Information', '/developer/developer_information', 'Projects and Contributions', '/developer/contribs', 'Forum', '/developer/forum', 'IRC', '/developer/irc', 'Issue Tracker', '/bugs/redirect', 'Open Funding', '/developer/open_funding', 'Security', '/developer/security'];
menu[7] = ['Open Source Business Model', '/company/open_source_business_model', 'News', '/company/news', 'Awards', '/company/awards', 'Media & Press', '/company/media_press', 'Events', '/company/events', 'Career', '/company/career', 'eZ Conference & Awards', '/company/ez_conference_awards', 'Board of Directors', '/company/board', 'Contact', '/company/contact'];
menu[8] = [];

//-->
</script>
<div id="menuborder">

<!-- Main menu area: START -->
<div id="menu" onmouseover="menuHover();" onmouseout="tryHideSubMenues();">

<div id="mainmenu" class="float-break">
<div id="page-width2">
    <ul id="mainmenulist" class="float-break">
<li><a href="/ezpublish" onmouseover="switchSubMenu( this );">eZ Publish</a></li>
<li><a href="/support_and_services" onmouseover="switchSubMenu( this );">Support and Services</a></li>
<li><a href="/software" onmouseover="switchSubMenu( this );">Software</a></li>
<li><a href="/solutions" onmouseover="switchSubMenu( this );">Solutions</a></li>
<li><a href="/customers" onmouseover="switchSubMenu( this );">Customers</a></li>
<li><a href="/partner" onmouseover="switchSubMenu( this );">Partners</a></li>
<li><a href="/developer" onmouseover="switchSubMenu( this );">Developer</a></li>
<li><a href="/company" onmouseover="switchSubMenu( this );">Company</a></li>
<li class="last customerportal"><a href="/customer_portal/(login)" onmouseover="switchSubMenu( this );">Customer Portal</a></li>
</ul>

</div>
</div>


<div id="submenu">
<div id="page-width3">

</div>
</div>

<script type="text/javascript"><!--
    document.getElementById('submenu').style.display = 'none';
--></script>

</div>
<!-- Main menu area: END -->

</div>

<hr class="hide" /><!-- Path area: START -->
<div id="path">

<div id="page-width4">
<h2 class="hide">Path</h2>

    <!-- Path content: START -->
    <p>
                                    error
            
                                    /
                                        kernel (3)
            
                        </p>
    <!-- Path content: END -->
</div>

</div>
<!-- Path area: END -->

<hr class="hide" />

<!-- Main area: START -->

<div id="page-width5">
<div id="main" class="float-break">

<!-- Main area content: START -->


<div class="warning">
<h2>Object is unavailable</h2>
<p>The object you requested is not currently available.</p>
<p>Possible reasons for this are:</p>
<ul>
    <li>The id or name of the object was misspelled, try changing it.</li>
    <li>The object is no longer available on the site.</li>
</ul>
</div>


<!-- Main area content: END -->

</div>
</div>

<!-- Main area: END -->

<hr class="hide" />

<!-- Footer area: START -->
<div id="footer-design">
<div id="page-width6">
<div id="footer">
    <div class="sitemap">
        

<ul>
    <li>Software</li>    <li>    
    <a href="/ezpublish">eZ Publish</a></li>    <li>    
    <a href="/ezcomponents">eZ Components</a></li>    <li>    
    <a href="/ezflow">eZ Flow</a></li>    <li>    
    <a href="/ezfind">eZ Find</a></li>    <li>    
    <a href="/eznewsletter">eZ Newsletter</a></li>
</ul>

<ul>
    <li>Support &amp; Services</li>    <li>    
    <a href="/support_and_services/ez_publish_premium">eZ Publish Premium</a></li>    <li>    
    <a href="/support_and_services/training">Training</a></li>    <li>    
    <a href="/support_and_services/certification">Certification</a></li>    <li>    
    <a href="/support_and_services/expert_consulting">Expert Consulting Services</a></li>
</ul>

<ul class="last">
    <li>Solutions</li>    <li>    
    <a href="/solutions/digital_media">Digital Media</a></li>    <li>    
    <a href="/solutions/enterprise_content_management">Enterprise CMS</a></li>    <li>    
    <a href="/solutions/intranet">Intranet</a></li>    <li>    
    <a href="/solutions/e_commerce">E-Commerce</a></li>    <li>    
    <a href="/solutions/community_portal">Community Portal</a></li>    <li>    
    <a href="/solutions/mobile">Mobile</a></li>
</ul>
    </div>
<address>
    Powered by eZ Publish&reg; Content Management System.
    Copyright &copy; 2010 eZ Systems AS (except where otherwise noted). All rights reserved.
</address>

</div>
</div>
</div>
<!-- Footer area: END -->

</div>
<!-- Complete page area: END -->

    <script type="text/javascript">
        var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
        document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script type="text/javascript">
        var pageTracker = _gat._getTracker("UA-303624-1");
        pageTracker._initData();
        pageTracker._trackPageview();
    </script>
            <script type="text/javascript" src="/extension/ezno2005/design/ezno2005/javascripts/indextools_ssl.js"></script>
        <noscript>
            <div>
                <img src="https://secure.indextools.com/p.pl?a=1000672744030&amp;js=no" width="1" height="1" alt="" />
            </div>
        </noscript>

        <script src="https://syndication.prospectxtractor.no/x.js" type="text/javascript"></script>
        <script type="text/javascript">
            <!--
            _pxId = "6AC633C6-4A2F-44EC-AF0E-81C94533C2BD";
            _pxReg();
            //-->
        </script>
    

</body>
</html>
