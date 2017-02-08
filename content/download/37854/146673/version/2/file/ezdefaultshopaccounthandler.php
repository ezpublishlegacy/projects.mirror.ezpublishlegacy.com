<?php
//
// Definition of eZDefaultShopAccountHandler class
//
// Created on: <13-Feb-2003 08:58:14 bf>
//
// Copyright (C) 1999-2004 eZ systems as. All rights reserved.
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
include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );
include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/classes/ezcontentobjectattribute.php' );
include_once( 'kernel/classes/ezcontentclassattribute.php' );

class eZDefaultShopAccountHandler
{
    /*!
    */
    function eZDefaultShopAccountHandler()
    {

    }

    /*!
     Will verify that the user has supplied the correct user information.
     Returns true if we have all the information needed about the user.
    */
    function verifyAccountInformation()
    {
        // Check login
        $user =& eZUser::currentUser();

        if ( !$user->isLoggedIn() )
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /*!
     Redirectes to the user registration page.
    */
    function email( $order = false )
    {
        $user =& eZUser::currentUser();
        return $user->attribute( 'email' );
    }
    function fetchAccountInformation( &$module )
    {
        eZHTTPTool::setSessionVariable( 'RedirectAfterUserRegister', '/shop/basket/' );
        eZHTTPTool::setSessionVariable( 'DoCheckoutAutomatically', true );
        $module->redirectTo( '/user/login/' );
    }
    
    function accountInformation( $order )
    {
    	$first_name = "";
    	$last_name = "";
    	$email = "";
    	
        $user =& $order->user();

        $object =& $user->attribute( "contentobject" );
        //$object =& $object->currentVersion();
        $contentObjectAttributes =& $object->contentObjectAttributes();
        
        $first_name = $contentObjectAttributes[0]->content();
        $last_name = $contentObjectAttributes[1]->content();
        $email = $user->attribute( "email" );
    	
        return array( 'first_name' => $first_name,
                      'last_name' => $last_name,
                      'email' => $email
                      );
    }
}

?>
