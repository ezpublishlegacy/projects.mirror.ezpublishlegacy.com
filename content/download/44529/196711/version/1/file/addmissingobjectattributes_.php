#!/usr/bin/env php
<?php
//
// Created on: <10-Aug-2004 15:47:14 pk>
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

/*! \file addmissingobjectattributes.php
*/

set_time_limit( 0 );

include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'lib/ezutils/classes/ezcli.php' );
include_once( 'kernel/classes/ezscript.php' );


function updateClass( $classId )
{
    $class =& eZContentClass::fetch( $classId, true, EZ_CLASS_VERSION_STATUS_TEMPORARY );
    $attributes =& $class->fetchAttributes();

    $id = $class->attribute( 'id' );
    $oldClassAttributes = $class->fetchAttributes( $id, true, EZ_CLASS_VERSION_STATUS_DEFINED );
    $newClassAttributes = $class->fetchAttributes( );
    //echo "oldClassAttributes: " . count( $oldClassAttributes ) . "\n";
    //echo "newClassAttributes: " . count( $newClassAttributes ) . "\n";
    $objects = null;
    $objectCount =& eZContentObject::fetchSameClassListCount( $classId );
    //echo "objects count: " . $objectCount  . "\n";

    if ( $objectCount > 0 )
    {
        // Delete object attributes which have been removed.
        foreach ( $oldClassAttributes as $oldClassAttribute )
        {
            //echo "old class attribute: " . $oldClassAttribute->attribute( 'name' ) . "\n";
            $attributeExist = false;
            $oldClassAttributeID = $oldClassAttribute->attribute( 'id' );
            foreach ( $newClassAttributes as $newClassAttribute )
            {
                //echo "|----->comparing to new attribute: " . $newClassAttribute->attribute( 'name' ) . "\n";
                $newClassAttributeID = $newClassAttribute->attribute( 'id' );
                if ( $oldClassAttributeID == $newClassAttributeID )
                {
                    // echo "seting attributeExist to true\n";
                    $attributeExist = true;
                }
            }

            //echo "attributeExist value: $attributeExist \n";

            if ( !$attributeExist )
            {
                //echo "removing old attributes\n";
                $objectAttributes =& eZContentObjectAttribute::fetchSameClassAttributeIDList( $oldClassAttributeID );
                foreach ( $objectAttributes as $objectAttribute )
                {
                    //echo "removing attribute: " . $objectAttribute->attribute( 'name' ) . "\n";
                    $objectAttributeID = $objectAttribute->attribute( 'id' );
                    $objectAttribute->remove( $objectAttributeID );
                }
            }
        }

        $class->storeDefined( $attributes );


        // Add object attributes which have been added.
        foreach ( $newClassAttributes as $newClassAttribute )
        {
            $attributeExist = false;
            $newClassAttributeID = $newClassAttribute->attribute( 'id' );
            foreach ( $oldClassAttributes as $oldClassAttribute )
            {
                $oldClassAttributeID = $oldClassAttribute->attribute( 'id' );
                if ( $oldClassAttributeID == $newClassAttributeID )
                    $attributeExist = true;
            }

            if ( !$attributeExist )
            {
				echo "adding new attributes \n";
                if ( $objects == null )
                {
                    $objects =& eZContentObject::fetchSameClassList( $classId );
		    		echo "Number of objects to be processed: " . count( $objects ) . "\n";
                }

                // add new attributes for all versions and translations of all objects
                foreach ( $objects as $object )
                {
					echo "#";
                    $contentobjectID = $object->attribute( 'id' );
                    $objectVersions =& $object->versions();

					foreach ( $objectVersions as $objectVersion )
                    {
                        $translations = $objectVersion->translations( false );
                        $version = $objectVersion->attribute( 'version' );
                        foreach ( $translations as $translation )
                        {
                            //echo "creating new object attribute: classAttributeId=$newClassAttributeID, contentObjectId=$contentobjectID, version=$version \n";
                            $objectAttribute =& eZContentObjectAttribute::create( $newClassAttributeID, $contentobjectID, $version );
                            //echo "|--> object attribute created\n";
                            //print_r( $objectAttribute );
                            $objectAttribute->setAttribute( 'language_code', $translation );
                            //echo "|--> language code set\n";
                            $objectAttribute->initialize( ); //initialize attribute value
                            //echo "|--> attribute initialized\n";
                            $objectAttribute->store();
                        }
                    }
                }
            }
        }
    }

    // Set the object name to the first attribute, if not set
    $classAttributes = $class->fetchAttributes();

    // Fetch the first attribute
    if ( count( $classAttributes ) > 0 && trim( $class->attribute( 'contentobject_name' ) ) == '' )
    {
        $identifier = $classAttributes[0]->attribute( 'identifier' );
        $identifier = '<' . $identifier . '>';
        $class->setAttribute( 'contentobject_name', $identifier );
        $class->store();
    }

    // Remove old version 0 first
    eZContentClassClassGroup::removeClassMembers( $classId, EZ_CLASS_VERSION_STATUS_DEFINED );

    $classgroups =& eZContentClassClassGroup::fetchGroupList( $classId, EZ_CLASS_VERSION_STATUS_TEMPORARY );
    for ( $i=0;$i<count(  $classgroups );$i++ )
    {
        $classgroup =& $classgroups[$i];
        $classgroup->setAttribute('contentclass_version', EZ_CLASS_VERSION_STATUS_DEFINED );
        $classgroup->store();
    }

    // Remove version 1
    eZContentClassClassGroup::removeClassMembers( $classId, EZ_CLASS_VERSION_STATUS_TEMPORARY );
}


// TODO init ez script

set_time_limit( 0 );

$cli =& eZCLI::instance();
$endl = $cli->endlineString();

$script =& eZScript::instance( array( 'description' => ( "CLI script.\n\n" .
                                                         "Will add missing content object attributes for a given class.\n" .
                                                         "\n" .
                                                         "addmissingobjectattributes.php -s admin"),
                                      'use-session' => false,
                                      'use-modules' => true,
                                      'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( "[db-user:][db-password:][db-database:][db-driver:][sql]",
                                "[name]",
                                array( 'db-host' => "Database host",
                                       'db-user' => "Database user",
                                       'db-password' => "Database password",
                                       'db-database' => "Database name",
                                       'db-driver' => "Database driver",
                                       'sql' => "Display sql queries"
                                       ) );
$script->initialize();

//print_r( $options );

//SET CLASS ID TO UPDATE HERE:
updateClass( 18 );

$script->shutdown();
?>
