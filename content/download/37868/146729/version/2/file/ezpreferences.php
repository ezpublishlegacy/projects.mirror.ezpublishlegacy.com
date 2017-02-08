<?php
//
// Definition of eZPreferences class
//
// Created on: <11-Aug-2003 13:23:55 bf>
//
// Copyright (C) 1999-2003 eZ systems as. All rights reserved.
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

/*! \file ezpreferences.php
*/

/*!
  \class eZPreferences ezpreferences.php
  \brief The class eZPreferences handles user/session preferences

  Preferences can be either pr user or pr session. eZPreferences will automatically
  set a session preference if the user is not logged in, if not a user preference will be set.

*/

include_once( 'kernel/classes/datatypes/ezuser/ezuser.php' );

class eZPreferences
{
    function eZPreferences()
    {

    }

    /*!
     Sets a preference value for the current user. If
     the user is anonymous the value is only stored in session.
    */
    function setValue( $name, $value )
    {
        $db =& eZDB::instance();
        $name = $db->escapeString( $name );
        $value = $db->escapeString( $value );

        $user =& eZUser::currentUser();
        if ( $user->isLoggedIn() )
        {
            // Only store in DB is user is logged in
            $userID = $user->attribute( 'contentobject_id' );
            $existingRes = $db->arrayQuery( "SELECT * FROM ezpreferences WHERE user_id = $userID and name = '$name'" );

            if ( count( $existingRes ) > 0 )
            {
                $prefID = $existingRes[0]['id'];
                $query = "UPDATE ezpreferences SET value='$value' WHERE id = $prefID AND name='$name'";
                $db->query( $query );
            }
            else
            {
                $query = "INSERT INTO ezpreferences ( user_id, name, value ) VALUES ( $userID, '$name', '$value' )";
                $db->query( $query );
            }
        }

        // Set session variable
        $http =& eZHTTPTool::instance();
        $http->setSessionVariable( 'Preferences-' . $name, $value );
    }

    /*!
     \return the session variable for the current user/session. If no variable is found
     false is returned. The preferences variable is stored in session after fetching.
    */
    function value( $name )
    {
        //PBo: always fetch from database to cope with different sessions open (eg with two browsers)
	//$value = false;
	$valueSession = false;
	$valueDB = false;
        // Check session

        $http =& eZHTTPTool::instance();
        if ( $http->hasSessionVariable( 'Preferences-' . $name ) )
        {
            $valueSession = $http->sessionVariable( 'Preferences-' . $name );
            //return $value;
        }


        $db =& eZDB::instance();
        $name = $db->escapeString( $name );
        $user =& eZUser::currentUser();
        $userID = $user->attribute( 'contentobject_id' );
        $existingRes = $db->arrayQuery( "SELECT value FROM ezpreferences WHERE user_id = $userID AND name = '$name'" );


        if ( count( $existingRes ) == 1 )
        {
            $valueDB = $existingRes[0]['value'];
            //$http->setSessionVariable( 'Preferences-' . $name, $value );
        }
        /*else
        {
            //$http->setSessionVariable( 'Preferences-' . $name, false );
        }
	*/
        if ( $user->isLoggedIn() )
	// not for anonymous users
        {
            //update the sesssion to reflect always the DB value for logged in users, multiple browser safe
	    $http->setSessionVariable( 'Preferences-' . $name, $valueDB );
	    return $valueDB;
	}
	else
	// for anonymous users
	{
            return $valueSession;
	}
    }
}


?>
