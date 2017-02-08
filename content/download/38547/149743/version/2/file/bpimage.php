<?php
//
// BpImageOperator
//
// Copyright (C) 1999-2005 eZ systems as. All rights reserved.
// Copyright (C) 2005 Ben Peter <ben.peter@gmx.net> All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// Licencees holding valid "eZ publish professional licences" may use this
// file in accordance with the "eZ publish professional licence" Agreement
// provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" is available at
// http://ez.no/products/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*!
  \class   BpImageOperator bpimage.php
  \ingroup eZTemplateOperators
  \brief   bpimage works just like ezimage, except that it will generate a width and height attribute, if the image is found. The quote_val parameter is respected, and you can set the third parameter to false() if you want to suppress width and height attribute output.
  \version 1.0
  \date    14.03.2005
  \author  Ben Peter (ben.peter@gmx.net)

  

  Example:
\code
{"home/logo.gif"|bpimage}
\endcode
  Creates, depending on image size and location:
\code
"design/plain/home/logo.gif" width="100" height="50"
\endcode

\code
{"home/logo.gif"|bpimage('single')}
\endcode
Creates the following:
\code
'design/plain/home/logo.gif' width='100' height='50'
\endcode

If you set the quote style to 'no', no width and height attributes will be generated.

  
*/
include_once('kernel/common/ezurloperator.php');

class BpImageOperator
{
    function BpImageOperator()
    {
    }

    function operatorList()
    {
        return array( 'bpimage' );
    }

    function namedParameterPerOperator()
    {
        return false;
    }
    
    function namedParameterList()
    {
        return array( 
        'quote_val' => array( 	'type' => 'string',
	                            'required' => false,
	                            'default' => 'double' ),
        'skip_slash' => array( 	'type' => 'boolean',
	                            'required' => false,
	                            'default' => false ),
        'show_dimensions' => array( 	'type' => 'boolean',
	                            	'required' => false,
	                            	'default' => true ),
                                            );                                            
    }

    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
    	$debug = '';
    	
        $urlOperator = new eZURLOperator();
        $param = 'ezimage';
        
        $urlOperator->modify($tpl, $param, $operatorParameters, $rootNamespace, $currentNamespace, $operatorValue, $namedParameters );
                
        if ($namedParameters['quote_val'] == 'no') {
        	$debug .= "\nQuote val is no";
        	$this->_setValue($operatorValue, $debug);
        	return;
        }
        
        $skipslash = $namedParameters['skip_slash'] ? '/' : '';
        $showdimensions = $namedParameters['show_dimensions'];
        $quote_char = $namedParameters['quote_val'] == 'single' ? "'" : '"';
    	$imagename = substr($operatorValue, 1, -1);
        
    	$debug .= "
    	
    	skipslash: $skipslash
    	showdimensions: $showdimensions
    	quote_char: $quote_char
    	imagename: $imagename
    	
    	";
    	
        $ezSys =& eZSys::instance();
        $filename = $ezSys->rootDir() . $skipslash . $imagename;
        
        if (!$showdimensions) {
        	$debug .= "
        	showdimensions not set
        	";
        	$this->_setValue($operatorValue, $debug);
        	return;        	
        }
        
        if (   ! file_exists($filename)
        	|| ! is_file($filename)
        	|| ! is_readable($filename) )  {
	        	$debug .= "
	        	problem accessing $filename
	        	";
	        	$this->_setValue($operatorValue, $debug);
	        	return;        	
        	}
        	
        if (!function_exists('getimagesize')) return;
        
        $size = @getimagesize($filename);
        
        if ($size === false) {
        	$debug .= "
        	Size is FALSE
        	";
        	$this->_setValue($operatorValue, $debug);
        	return;
        }

        $this->_setValue($operatorValue, $debug);
        
        list($width, $height, $type, $attr) = $size;		        
        $attribs = " width={$quote_char}{$width}{$quote_char} height={$quote_char}{$height}{$quote_char}";		
        $operatorValue .= $attribs;
    }
    
    function _setValue(&$operatorValue, $debug) {    	
    	return;
    	$operatorValue = $operatorValue . '<pre>' . $debug . '</pre>';
    }
}
