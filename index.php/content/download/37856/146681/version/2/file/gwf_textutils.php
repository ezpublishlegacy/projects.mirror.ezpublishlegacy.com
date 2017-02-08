<?php
// Created on: <28-Mar-2004 21:56:00 gwf>
//
// Copyright (C) 2004 Verlag Franz. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
//	Utilities for converting text strings in ez3, http://www.ez.no
//	Written by Georg Franz, 
//	Feedback / Bug reports: Georg Franz <georg@verlagfranz.com>
//  uses the php-extensions mbstring and iconv, if they are installed
//
//  Attention: The class is not using the textcodec-methods of ez3!!
//  Use it only if you are using UTF-8 in combination with ISO-8859-1 charsets
//  or if you are using ISO-8859-1 charset alone.
//  Otherwise you have to rewrite some methods!
//
// Installation: 
// You need 
// [MailSettings]
// MailHost=www.yourdomain.com
// in your site.ini.append if you are using gwf_TextUtils::add_host
// Look there for further infos.
// 
// If you are using the "email conversion" you need the javascript code too.
// look at gwf_TextUtils::change_email for further details
//
/* 
javascript code:
--------------------
function buildtext(friday,monday,tuesday,sunday,thirsday) 
{
  window.open(tuesday+sunday+monday,'','');
}

function writeMessage(friday,monday,tuesday,sunday,thirsday,christmas) 
{
  document.write(christmas+thirsday+monday+sunday);
}

function displayEmailStatusMsg(sunday,friday,monday,tuesday,thirsday) { 
	status=friday+thirsday+tuesday;
	document.MM_returnValue = true;
}

function MM_displayStatusMsg(msgStr) 
{ 
	status=msgStr;
	document.MM_returnValue = true;
}

*/
class gwf_TextUtils
{
	function gwf_TextUtils()
	{
		
	}
	
	/*
		Converts $text to a correct url alias
		Tries to replace specials characters like
		ä => ae
		ö => oe
		Tries to convert "Word-Characters" like &#8220;
		make a url like:
		/this-is-a/test-url
		or for german-speaking users:
		Heute ist ein schöner Tag mit ß =>
		heute-ist-ein-schoener-tag-mit-ss
		
	*/
	function convertToAlias ($text)
	{
		// Checks if the string is utf-8 encoded
		// If yes -> convert it back to ISO-8859-1
		if (gwf_TextUtils::isUtf8($text))	
		{
			$text = gwf_TextUtils::gwfutf8decode ( $text );
		}
		
		// mb_strtolower treats special chars like Á in a correct way
		if ( function_exists ('mb_strtolower') )
			$text = mb_strtolower ( $text );
		else
			$text = gwf_TextUtils::convertSpecialChars ( $text, true );
		
		$text = gwf_TextUtils::replaceWordChars ( $text, true );
		$text = gwf_TextUtils::replaceHtmlEntities ( $text );
		$text = gwf_TextUtils::cleanUpHTML ( $text );
		$text = gwf_TextUtils::html_normal_chars ( $text );
		$text = gwf_TextUtils::strip_whitespaces ( $text );
		$text = trim ( $text );
		
		$specialChars  = array ( "à", "á", "â", "ã", "ä", "å", "æ", "è", "é", "ê", "ß", " ", "'", "´", "`",
								 "ë", "Ç", "í", "ì", "ò", "ó", "ô", "õ", "ö", "ù", "ú", "û", "ü");
		$normalChars   = array ( "a", "a", "a", "a", "ae", "a", "ae", "e", "e", "e", "ss", "-", "", "", "",
							     "e", "c", "i", "i", "o", "o", "o", "o", "oe", "u", "u", "u", "ue");
		
		$text = str_replace ( $specialChars, $normalChars, $text );	
		
		$text = preg_replace( array( "#[^a-z0-9\- ]#",
                                           "/ /",
                                           "/-+/",
                                           "/^-|-$/"
                                            ),
                                    array( " ",
                                           "-",
                                           "-",
                                           ""
                                            ),
                                      $text );
		return $text;
	}
	
	/* 
		returns true, if $text is ut8-encoded
	*/
	function isUtf8 ($text) 
	{
		if ( !function_exists ( 'iconv' ) )
			return false;
		if (@iconv('UTF-8', 'UTF-8', $text) != $text)
			return false;
		else
			return true;
	}
	
	/*
		converts a text for output
		usefull for e.g. forum messages
	*/
	function textWash ( $text )
	{
		$isUtf8 = false;
		if (gwf_TextUtils::isUtf8 ($text) )	
		{
			$text = gwf_TextUtils::gwfutf8decode ( $text );
			$isUtf8 = true;
		}
		
		$text = str_replace ( "&amp;#", "&#", $text);
		$text = gwf_TextUtils::replaceWordChars($text,true);
		
		$text = gwf_TextUtils::replaceHtmlEntities($text);
		$text = gwf_TextUtils::replaceWordChars($text,true);
		$text = gwf_TextUtils::cleanUpHTML($text);
		$text = wordwrap( $text, 120, "\n", 1);
		$text = htmlspecialchars ( $text );
		$text = nl2br($text);
		
		if ( $isUtf8 ) 
			$text = gwf_TextUtils::gwfutf8encode ( $text );
		
		return $text;
	}
	
	/*
		checks if the text is utf8-encoded and
		converts it back to ISO-8859-1
	*/
	function gwfutf8decode ($text)
	{
		if ( trim ($text) == "" )
			return $text;
		if ( function_exists ('mb_detect_encoding') and
		     function_exists ('iconv') )
		{
			$codec = mb_detect_encoding($text);
			if ($codec == "ASCII")
				return $text;
			
			if (gwf_TextUtils::isUtf8($text)) 
			{ 
				// converts it back to iso-8859-1
			    $text = utf8_decode ($text);
			}
		}
		return $text;
	}
	
	/*
		checks if the text is utf8-encoded and
		- if not - converts it to utf8
	*/
	function gwfutf8encode ($text)
	{
		if ( trim ($text) == "" )
			return $text;
		
		if ( function_exists ('mb_detect_encoding') and
		     function_exists ('iconv') )
		{
			$codec = mb_detect_encoding($text);
			if ($codec == "ASCII")
				return $text;
			
			if (!gwf_TextUtils::isUtf8($text)) 
			{ 
				// convert to UTF-8
			    $text = utf8_encode ($text);
			}
		}
		return $text;
	}
	
	/*
		Converts a html-text to plain text.
		e.g. Usefull for getting a html-mail input and make a 
		"plain text" copy
		
		text: The input text
		break_word: if using word_wrap()
		break_width: the cutting position of word_wrap()
	*/
	function convert_plain ($text, $break_word = false, $break_width = 60)
	{
		$isUtf8 = false;
		if (gwf_TextUtils::isUtf8($text))	
		{
			$text = gwf_TextUtils::gwfutf8decode($text);
			$isUtf8 = true;
		}
		
		$temp = $text;
		
		$temp = str_replace ("\r", "", $temp);
		$temp = str_replace ("\t", " ", $temp);
		$temp = preg_replace ("#<br([^>]*)>#si", "\n", $temp);
			
		$temp = gwf_TextUtils::replaceWordChars($temp,true);
		$temp = gwf_TextUtils::replaceHtmlEntities($temp);
		
		$temp = gwf_TextUtils::remove_img($temp,true);
		$temp = gwf_TextUtils::convert_url($temp,true);
		
		$temp = gwf_TextUtils::cleanUpHTML($temp);
		
		$temp = gwf_TextUtils::html_normal_chars($temp);
		
		$temp = gwf_TextUtils::strip_whitespaces($temp);
		$temp = gwf_TextUtils::strip_multiple_lines($temp);
		
		if ($break_word === true)
			$temp = wordwrap( $temp, $break_width, "\n", 0);
		
		$temp = trim($temp);
		
		if ($isUtf8)	
			$temp = gwf_TextUtils::gwfutf8encode($temp);
		
		return $temp;
	}
	
	/*
		Tries to return a "keyword"-string of the given html-text.
		Example: If you have html-code of your article-intro this method is 
		stripping out the html-code, strips out word-duplicates and returns
		the comma-separated string.
		e.g. input:
		-----------
		<div class="folder_description">
		<p>
			Das geht auf keine Kuhhaut! Im Kuhrier kannst Du bunte und verrückte 
			Nachrichten aus aller Welt lesen. Wenn Du auch eine für uns hast, dann 
			schick sie doch <a href="mailto:kuhrier@kuh.at">an unsere Redaktion</a>!
		</p>
		</div>	
		----------
		output:
		----------
		keine, Kuhhaut, Kuhrier, kannst, bunte, verrückte, Nachrichten, aller, lesen, schick, unsere, Redaktion
		
		Usefull for the 
		<meta name="keywords" content="..." />
		part.
	
	*/
	function make_keywords ( $text )
	{
		$isUtf8 = false;
		if (gwf_TextUtils::isUtf8($text))	
		{
			$text = gwf_TextUtils::gwfutf8decode($text);
			$isUtf8 = true;
		}
		
		$temp = $text;
		$temp = gwf_TextUtils::replaceWordChars($temp,true);
		$temp = gwf_TextUtils::replaceHtmlEntities($temp);
		$temp = gwf_TextUtils::cleanUpHTML($temp);
		$temp = gwf_TextUtils::html_normal_chars($temp);
		$temp = gwf_TextUtils::strip_whitespaces($temp);
		$temp = trim ( gwf_TextUtils::strip_search_chars($temp) );
		
		$temp = explode (" ", $temp);
		$temp = array_unique($temp);
		
		$temp_array = array();
		
		// only take words with more then 4 characters
		foreach ($temp as $item)
		{
			if (strlen($item) > 4)
				$temp_array[]=$item;	
		}
		
		$wordIndex = array();
		$wordContent = array();
		foreach ( $temp_array as $item )
		{
			$key = array_search ( $item, $wordContent );
			if ( $key === false or $key === NULL )
			{
				$wordKey = count ( $wordContent );
				$wordContent[$wordKey] = $item;
				$wordIndex[$wordKey] = 1;	
			}
			else
			{
				$wordIndex[$key]++;	
			}
		}
		
		asort ( $wordIndex );
		$temp_array = array();
		foreach ( array_keys ( $wordIndex ) as $key )
		{
			$temp_array[] = $wordContent[$key];
		}
		
		$temp = implode(", ",$temp_array);
		
		$temp = wordwrap( $temp, 255, "\n", 0);
		$temp = explode("\n",$temp);
		$temp = trim ( $temp[0], ",");
		$temp = trim ( $temp );
		if ($isUtf8)	
			$temp = gwf_TextUtils::gwfutf8encode($temp);
		return $temp;
	}
	
	/*
		Strips out special characters.
		Used for keywords.
	*/
	function strip_search_chars ($contents)
	{
	    $isUtf8 = false;
		if (gwf_TextUtils::isUtf8($contents))	
		{
			$contents = gwf_TextUtils::gwfutf8decode($contents);
			$isUtf8 = true;
		}
	    
	    $searchArray  = array ( "`", "´", "„", "“", "…",     ",", "'", 
	                            "[", "]", "{", "}", "\n", "\r", "\t", 
	                            "(", ")", ".", ";", "/", "-", "+", "=", 
	                            "<", ">", "~", "_", ":", "?", "!", "\"", 
	                            "\\", "|", "$", "*", "%", "&");
	    $replaceArray = array ( "",  "",  "",  "",  " ... ", "",  " ", 
	                            "",  "",  "",  "",  " ",  " ",  " ",  
	                            " ", " ", " ", " ", " ", "",  "",  " ",  
	                            " ",  " ",  "",  "",  " ", " ", " ", "",   
	                            "",   "",  "",  " ", " ", "");
	    
	    $contents = str_replace ($searchArray, $replaceArray, $contents );
        
        $contents = preg_replace("(\s+)", " ", $contents ); // strip out multiple whitespaces
     	
     	if ($isUtf8)	
			$contents = gwf_TextUtils::gwfutf8encode($contents);
		
		return $contents;
    }
	
	/*
		Helper function to get a clean mail name
		e.g. input:
		&#8220;John Doe&#8222; 
		becomes to 
		„John Doe“
		
	*/
	function convert_mail_line($text)
	{
		// converts unwanted characters
		$temp = $text;
		
		$temp = str_replace ("\t", " ", $temp);
		$temp = gwf_TextUtils::html_normal_chars($temp);
		$temp = gwf_TextUtils::replaceHtmlEntities($temp);
		$temp = gwf_TextUtils::replaceWordChars($temp,true);
		$temp = gwf_TextUtils::strip_whitespaces($temp);
		
		return $temp;	
	}
	
	/*
		If you have html-text and want to send it in an email
		this method add the host to links and images.
		example:
		<a href="/this/is-a-url">Test-URL</a>
		<img src="/design/standard/images/1x1.gif" />
		becomes to
		<a href="http://www.mysite.com/this/is-a-url">Test-URL</a>
		<img src="http://www.mysite.com/design/standard/images/1x1.gif" />
		
		Attention: 
		$include_images have to be FALSE if you want to add the 
		host to image-tags too!!
		
	*/
	function add_host($text, $host = '', $include_images = false)
	{
		include_once( "lib/ezutils/classes/ezsys.php" );

		if ($host == '')
			$host = eZSys::serverVariable( 'HTTP_HOST' );
		
		if ( trim ( $host ) == '' or $host === NULL)
		{
			include_once( "lib/ezutils/classes/ezini.php" );
			$ini =& eZINI::instance();
			if ( $ini->hasVariable ( 'MailSettings', 'MailHost' ) )
				$host = $ini->variable( 'MailSettings', 'MailHost' );
			else
				$host = "www.example.com";
		}
		
		if ($host === false)
			return $temp;
		
		$temp = $text;
		
		// urls
		$temp = preg_replace ("#(href=['|\"])([^'|\"]*)(\s|'|\")#sie", 
							  "gwf_TextUtils::add_host_helper('\\1','\\2','\\3','$host')", $temp ); 
		
		// images
		if (!$include_images)
		{
			$temp = preg_replace ("#(src=['|\"])([^'|\"]*)(\s|'|\")#sie", 
							  "gwf_TextUtils::add_host_helper('\\1','\\2','\\3','$host')", $temp );
		}
		
		return $temp;
	}
	
	/*
		Helper function to convert a email-address for not beeing accessed by spammers
		It uses a javascript.
		
		Example: 
		melker@kuh.at will be convert e.g. to
		<a href="javascript:buildtext('&#69;&#x6e;&#x6e;&#x44;&#x48;&#46;','&#46;&#x61;&#116;','&#x6d;&#97;&#105;&#x6c;&#x74;&#x6f;&#x3a;&#109;&#x65;&#x6c;&#107;&#101;','&#x72;&#x40;&#107;&#117;&#104;','&#97;&#117;&#x59;&#x46;&#x4b;&#105;&#46;');" onMouseOver="displayEmailStatusMsg('&#97;&#117;&#x59;&#x46;&#x4b;&#105;&#46;','&#x6d;&#97;&#105;&#x6c;&#x74;&#x6f;&#x3a;&#109;&#x65;&#x6c;&#107;&#101;','&#69;&#x6e;&#x6e;&#x44;&#x48;&#46;','&#46;&#x61;&#116;','&#x72;&#x40;&#107;&#117;&#104;');return document.MM_returnValue" onmouseout="MM_displayStatusMsg('');"><script type="text/javascript">
<!-- 
	writeMessage('&#x68;&#x70;&#x66;&#117;&#78;&#x59;&#x43;&#x2e;','&#x4d;&#x65;&#x6c;','&#x7a;&#68;&#112;&#x33;&#107;&#103;&#110;&#46;','&#107;&#101;&#114;','&#x65;&#x66;&#x20;','&#x4a;&#111;&#115;'); 
//-->
</script></a>

		Advantages:
		Real visitors are able to click on e-mail links and the mail program opens.
		
		The js works with all browsers except Opera.
		
	*/
	function change_email($email, $link_text='', $first_character='', $last_character = '')
	{
		
		$email = str_replace ("mailto:","",$email);
		
		if ($link_text =='')
		{
			$link_text = $email;
		}
		else
		{
			$link_text = gwf_TextUtils::gwfutf8decode ($link_text);
		}
		
		if (strstr($link_text,'"') or strstr($link_text,"'"))
			$link_text = addslashes($link_text);
		
		$dummy1 = gwf_TextUtils::createPassword (rand(5,10)).".";
		$dummy2 = gwf_TextUtils::createPassword (rand(5,10)).".";
		
		$dummy1 = gwf_TextUtils::encodeEmail ($dummy1);
		$dummy2 = gwf_TextUtils::encodeEmail ($dummy2);
		
		$link_len = ceil( strlen ($link_text) / 4 );
		
		$link_array = chunk_split ($link_text,$link_len,'#ö#');
		$link_array = explode ("#ö#", $link_array);
		
		$link_1 = $link_array[0];
		$link_2 = $link_array[1];
		$link_3 = $link_array[2];
		$link_4 = $link_array[3];
		
		$link_1 = gwf_TextUtils::encodeEmail ($link_1);
		$link_2 = gwf_TextUtils::encodeEmail ($link_2);
		$link_3 = gwf_TextUtils::encodeEmail ($link_3);
		$link_4 = gwf_TextUtils::encodeEmail ($link_4);
		
		$link_text = "<script type=\"text/javascript\">
<!-- 
	writeMessage('$dummy1','$link_3','$dummy2','$link_4','$link_2','$link_1'); 
//-->
</script>";
		
		$dummy1 = gwf_TextUtils::createPassword (rand(5,10)).".";
		$dummy2 = gwf_TextUtils::createPassword (rand(5,10)).".";
		
		$dummy1 = gwf_TextUtils::encodeEmail ($dummy1);
		$dummy2 = gwf_TextUtils::encodeEmail ($dummy2);
		
		$mailto = $email;
		
		$email_len = ceil( strlen ($mailto) /3 );
		
		$email_array = chunk_split ($mailto,$email_len,'#ö#');
		$email_array = explode ("#ö#", $email_array);
		
		$email_1 = "mailto:".$email_array[0];
		$email_2 = $email_array[1];
		$email_3 = $email_array[2];
		
		$email_1 = gwf_TextUtils::encodeEmail ($email_1);
		$email_2 = gwf_TextUtils::encodeEmail ($email_2);
		$email_3 = gwf_TextUtils::encodeEmail ($email_3);
		
		$email_link = $first_character."<a href=\"javascript:buildtext('$dummy1','$email_3','$email_1','$email_2','$dummy2');\" onMouseOver=\"displayEmailStatusMsg('$dummy2','$email_1','$dummy1','$email_3','$email_2');return document.MM_returnValue\" onmouseout=\"MM_displayStatusMsg('');\">$link_text</a>".$last_character;
		
		return $email_link;
	}
	
	/*
		Helper for change_email
	*/
	function encodeEmail ($originalString, $mode = 3) 
	{
		$isUtf8 = false;
		if (gwf_TextUtils::isUtf8($originalString))	
		{
			$isUtf8 = true;
		}
		
		$encodedString = "";
		$nowCodeString = "";
		$randomNumber = -1;
		
		$originalLength = strlen($originalString);
		$encodeMode = $mode;
		
		for ( $i = 0; $i < $originalLength; $i++) 
		{
			if ($mode == 3 )
			{
				if ( !$isUtf8 )
					$encodeMode = rand(1,3);
				else
					$encodeMode = rand(1,2);
			}
			else
			{
				$encodeMode = $mode;
			}
			
			switch ($encodeMode) 
			{
				case 1: // Decimal code
					$nowCodeString = "&#" . ord($originalString{$i}) . ";";
					break;
				case 2: // Hexadecimal code
					$nowCodeString = "&#x" . dechex(ord($originalString{$i})) . ";";
					break;
				case 3: // Normal
					$nowCodeString = $originalString{$i};
					break;
			}
			$encodedString .= $nowCodeString;
		}
		return $encodedString;
	}
	
	/*
		private
		needed by add_host
	*/
	function add_host_helper ($href_begin, $url, $href_end, $host)
	{
		
		//echo "url: $url\n";
		
		if (substr($url,0,4) == "www.")
		{
			$url = "http://" . $url;
		}
		else
		{
			if (substr($url,0,1) == "/")
			{
				$url = "http://" . $host . $url;
			}
			else
			{
				if (strstr($url,"://") === false)
				{
					if (substr($url,0,6) != "mailto" and substr($url,0,4) != "java")
					{
						$url = "http://" . $host . $url;
					}
				}
			}
		}
				
		$text = $href_begin . $url . $href_end;
		return stripslashes ($text);	
	}
	
	/*
		removes all img-tags from html-input
	*/
	function remove_img($text)
	{
		return preg_replace ("#<img([^>]*)>#si", "", $text);
	}
	
	// replace <a href="http://www.kuh.at">kuh.at</a>
	// to
	// kuh.at (http://www.kuh.at)
	// Usefull for plain text emails
	
	function convert_url ($text)
	{
		return preg_replace("#<a\s+([^href=]*)href=[\"|']([^\"|']*)[\"|']([^>]*)>([^<]*)</a>#sie", 
							"gwf_TextUtils::check_url_string('\\2','\\4')", $text ); 
	}
	
	/*
		private
		used by convert_url
	*/
	function check_url_string ($href, $name)
	{
		if (trim($name) == "")
			return "";
		
		if (trim($href) == trim($name))
			return $href;
		elseif (trim($href) == "mailto:".trim($name))
			return $name;
		else
			return "$name ($href)";
	}
	
	/*
		strips out multiple, empty lines
		used by html to plain text conversion for emails
	*/
	function strip_multiple_lines ($text, $line_search = 3, $line_replace = 2)
	{
		$temp = $text;
		$replace = str_repeat("\n",$line_replace);
		$temp = preg_replace ("#(\n{".$line_search.",})#i", $replace, $temp ); // strip out multiple lines
		
		return $temp;	
	}
	
	/*
		strips out whitespaces
	*/
	function strip_whitespaces ($text, $break = false)
	{
		$temp = $text;
		
		$temp = preg_replace("#([\t| ]{1,})#si", " ", $temp ); // strip out multiple whitespaces
		$temp = preg_replace("#\n[ |\t]*#si", "\n", $temp);
		
		return $temp;
	}
	
	/*
		the opposite of htmlspecialchars()
	*/
	function html_normal_chars ($text)
	{
		$temp = ereg_replace('&amp;', '&', ereg_replace('&quot;',
		"\"", ereg_replace('&lt;', '<',	ereg_replace('&gt;', '>', $text))));
		return $text;
	}
	
	/*
		strips out html-code of $text
	*/
	function cleanUpHTML ($text)
	{
		$search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
                 "'<style[^>]*?>.*?</style>'si",
                 "'<noscript>*?</noscript>'si",
                 "'<title([^>]*)>([^<]*)</title>'si",					// Strip html-title
                 "'<[\/\!]*?[^<>]*?>'si",           // Strip out html tags
                 
                 );                    

		$replace = array ("",
                  "",
                  "",
                  "",
                  "",
                 );

		return preg_replace ($search, $replace, $text);	
	}
	
	/*
		Replaces HTML entities
	*/
	function replaceHtmlEntities ($text)
	{
		$search = array (
                 "'&(quot|#34);'i",                 // Replace html entities
                 "'&(amp|#38);'i",
                 "'&(lt|#60);'i",
                 "'&(gt|#62);'i",
                 "'&(euro|#128);'i",
                 "'&(nbsp|#160);'i",
                 "'&(iexcl|#161);'i",
                 "'&(cent|#162);'i",
                 "'&(pound|#163);'i",
                 "'&(yen|#165);'i",
                 "'&(copy|#169);'i",
                 "'&(laquo|#171);'i",
                 "'&(reg|#174);'i",
                 "'&(raquo|#187);'i",
                 "'&(frac14|#188);'i",
                 "'&(frac12|#189);'i",
                 "'&(frac34|#190);'i",
                 "'&(Agrave|#192);'i",
                 "'&(Acirc|#194);'i",
                 "'&(Auml|#196);'i",
                 "'&(AElig|#198);'i",
                 "'&(Ccedil|#199);'i",
                 "'&(Egrave|#200);'i",
                 "'&(Eacute|#201);'i",
                 "'&(Ecirc|#202);'i",
                 "'&(Igrave|#204);'i",
                 "'&(Icirc|#206);'i",
                 "'&(Ograve|#210);'i",
                 "'&(Ocirc|#212);'i",
                 "'&(Ouml|#214);'i",
                 "'&(Ugrave|#217);'i",
                 "'&(Ucirc|#219);'i",
                 "'&(Uuml|#220);'i",
                 "'&(szlig|#223);'i",
                 "'&(agrave|#224);'i",
                 "'&(aacute|#225);'i",
                 "'&(acirc|#226);'i",
                 "'&(auml|#228);'i",
                 "'&(aelig|#230);'i",
                 "'&(ccedil|#231);'i",
                 "'&(egrave|#232);'i",
                 "'&(eacute|#233);'i",
                 "'&(ecirc|#234);'i",
                 "'&(igrave|#236);'i",
                 "'&(icirc|#238);'i",
                 "'&(ograve|#242);'i",
                 "'&(ocirc|#244);'i",
                 "'&(ouml|#246);'i",
                 "'&(ugrave|#249);'i",
                 "'&(ucirc|#251);'i",
                 "'&(uuml|#252);'i"
                 );                    
		
		$replace = array (
                  chr(34),
                  chr(38),
                  chr(60),
                  chr(62),
                  chr(128),
                  chr(160),
                  chr(161),
                  chr(162),
                  chr(163),
                  chr(165),
                  chr(169),
                  chr(171),
                  chr(174),
                  chr(187),
                  chr(188),
                  chr(189),
                  chr(190),
                  chr(192),
                  chr(194),
                  chr(196),
                  chr(198),
                  chr(199),
                  chr(200),
                  chr(201),
                  chr(202),
                  chr(204),
                  chr(206),
                  chr(210),
                  chr(212),
                  chr(214),
                  chr(217),
                  chr(219),
                  chr(220),
				  chr(223),
                  chr(224),
                  chr(225),
                  chr(226),
                  chr(228),
                  chr(230),
                  chr(231),
                  chr(232),
                  chr(233),
                  chr(234),
                  chr(236),
                  chr(238),
                  chr(242),
                  chr(244),
                  chr(246),
                  chr(249),
                  chr(251),
                  chr(252)
                 );
		
		return preg_replace ($search, $replace, $text);	
	}
	
	/*
		replaces characters which come mostly from ms word
		by copy & paste
	*/
	function replaceWordChars ($contents, $stripUnkowns = true)
	{
		
		$searchArray  = array ();
		$searchArray[0] = "&#8364;";
		$searchArray[1] = "&#8230;";
		$searchArray[2] = "&#8222;";
		$searchArray[3] = "&#8221;";
		$searchArray[4] = "&#8220;";
		$searchArray[5] = "&#8218;";
		$searchArray[6] = "&#8217;";
		$searchArray[7] = "&#8216;";
		$searchArray[8] = "&#8211;";
		
		$replaceArray = array ();
		$replaceArray[0] = "€";
		$replaceArray[1] = " ... ";
		$replaceArray[2] = '"';
		$replaceArray[3] = '"';
		$replaceArray[4] = '"';
		$replaceArray[5] = '"';
		$replaceArray[6] = "'";
		$replaceArray[7] = "'";
		$replaceArray[8] = "-";
		
		$contents = str_replace ( $searchArray, $replaceArray, $contents );
		
		foreach ( array_keys ( $searchArray ) as $key )
		{
			$searchArray[$key] = substr ( $searchArray[$key], 1);	
		}
		$contents = str_replace ( $searchArray, $replaceArray, $contents );
		
		if ($stripUnkowns)
		{
			$contents = preg_replace ( "'&#(\d+);'e", "chr(\\1)", $contents);
			$contents = preg_replace ( "'#(\d+);'e", "chr(\\1)", $contents);
		}
		
		return $contents;
	}	
	
	/*
		strtolower() and strtoupper()
		don't work for "special characters"
		So use mb_strtoupper() or mb_strtolower instead - if installed or
		do it manually ...
	*/
	function convertSpecialChars($text, $toLower = true)
	{
		
		if ( function_exists ('mb_strtoupper') and
		     function_exists ('mb_strtolower') )
		{
			if ( $toLower )
				return mb_strtolower ($text);
			else
				return mb_strtoupper ($text);
			
		}
		else
		{
			$specialCharLower  = array ( "à", "á", "â", "ã", "ä", "å", "æ", "è", "é", "ê", 
										 "ë", "Ç", "í", "ì", "ò", "ó", "ô", "õ", "ö", "ù", "ú", "û", "ü");
			$specialCharHigher = array ( "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "È", "É", "Ê", 
										 "Ë", "ç", "Í", "Ì", "Ò", "Ó", "Ô", "Õ", "Ö", "Ù", "Ú", "Û", "Ü");
			
			if ($toLower)
			{
				$search = $specialCharHigher;
				$replace = $specialCharLower;
			}
			else
			{
				$search = $specialCharLower;
				$replace = $specialCharHigher;
			}
			
			$text = str_replace ($search, $replace, $text);
			
			return $text;
		}
	}
	
	/*
		Strip out all urls
	*/
	function stripUrl($text) 
	{
    	$text = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2", $text); 
    	//make sure there is an http: on all URLs

		$text = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i", 
									"", $text); 
		return $text;
	}
	
	function passwordCharacterTable()
	{
	    $table = array();
	    for ( $i = ord( 'a' ); $i <= ord( 'z' ); ++$i )
	    {
	        $char = chr( $i );
	        $table[] = $char;
	        $table[] = strtoupper( $char );
	    }
	    for ( $i = 0; $i <= 9; ++$i )
	    {
	        $table[] = "$i";
	    }
	    $table[] = "@";
	    $table[] = ".";
	    // Remove some characters that are too similar visually
	    $table = array_diff( $table, array( 'I', 'l', 'o', 'O', '0' ) );
	    $tableTmp = $table;
	    $table = array();
	    foreach ( $tableTmp as $item )
	    {
	        $table[] = $item;
	    }
	    return $table;
	}
	
	function createPassword( $passwordLength )
	{
	    $chars = 0;
	    $password = '';
	    if ( $passwordLength < 1 )
	        $passwordLength = 1;
	    $decimal = 0;
	    
	    $characterTable = gwf_TextUtils::passwordCharacterTable();
	    $tableCount = count( $characterTable );
	    
	    for ($i = 0; $i < $passwordLength; $i++)
	    {
	    	$password .= $characterTable [ array_rand ($characterTable,1) ];	
	    }    
	    
	    return $password;
	}
	
}

?>