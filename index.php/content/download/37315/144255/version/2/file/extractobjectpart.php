#!/usr/bin/env php
<?php
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: Extract object part for eZ publish
// SOFTWARE RELEASE: 0.1
// COPYRIGHT NOTICE: Copyright (C) 2007 Kristof Coomans <http://blog.kristofcoomans.be>
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

include_once( 'kernel/classes/ezscript.php' );
include_once( 'lib/ezutils/classes/ezcli.php' );

$cli =& eZCLI::instance();

$scriptSettings = array();
$scriptSettings['description'] = 'Create new objects based on data of existing objects';
$scriptSettings['use-session'] = true;
$scriptSettings['use-modules'] = true;
$scriptSettings['use-extensions'] = true;

$script =& eZScript::instance( $scriptSettings );
$script->startup();

$config = '';
$argumentConfig = '[sourceclass][destinationclass][parentnodeid]';
$optionHelp = '';
$arguments = false;
$useStandardOptions = array( 'user' => true );

$options = $script->getOptions( $config, $argumentConfig, $optionHelp, $arguments, $useStandardOptions );
$script->initialize();

if ( count( $options['arguments'] ) < 3 )
{
    $script->shutdown( 1, 'wrong argument count' );
}

$sourceClassID = $options['arguments'][0];
$destinationClassID = $options['arguments'][1];
$nodeID = $options['arguments'][2];

include_once( 'kernel/classes/ezcontentclass.php' );
if ( is_numeric( $sourceClassID ) )
{
    $sourceClass = eZContentClass::fetch( $sourceClassID );
}
else
{
    $sourceClass = eZContentClass::fetchByIdentifier( $sourceClassID );
}

if ( !is_object( $sourceClass ) )
{
    $script->shutdown( 2, 'Could not fetch source class' );
}

if ( is_numeric( $destinationClassID ) )
{
    $destinationClass = eZContentClass::fetch( $destinationClassID );
}
else
{
    $destinationClass = eZContentClass::fetchByIdentifier( $destinationClassID );
}

if ( !is_object( $sourceClass ) )
{
    $script->shutdown( 3, 'Could not fetch destination class' );
}

// calculate fields to copy

$sourceAttribs =& $sourceClass->dataMap();
$destinationAttribs =& $destinationClass->dataMap();

$commonAttribs = array();
$commonIdentifiers = array_intersect( array_keys( $sourceAttribs ), array_keys( $destinationAttribs ) );

foreach ( $commonIdentifiers as $commonID )
{
    if ( $sourceAttribs[$commonID]->attribute( 'data_type_string' ) == $destinationAttribs[$commonID]->attribute( 'data_type_string' ) )
    {
        $commonAttribs[] = $commonID;
    }
}

if ( count( $commonAttribs ) == 0 )
{
    $script->shutdown( 4, 'No common attributes found' );
}

$cli->output( var_export( $commonAttribs, true ) );

$nodes =& eZContentObjectTreeNode::subTree(
    array (
      'ClassFilterType' => 'include',
      'ClassFilterArray' => array ( $sourceClass->attribute( 'id' ) )
    ), 2 );

$cli->output( count( $nodes ) );

foreach ( $nodes as $node )
{
    $sourceAttribs = $node->attribute( 'data_map' );
    $object   = $node->attribute( 'object' );

    if ( $sourceAttribs['poster_presentation']->attribute( 'data_text' ) != '1' )
    {
        continue;
    }

    $ownerID        = $object->attribute( 'owner_id' );
    $sectionID      = $object->attribute( 'section_id' );

    include_once( 'lib/ezdb/classes/ezdb.php' );
    $db =& eZDB::instance();
    $db->begin();

    $newObject =& $destinationClass->instantiate( $ownerID, $sectionID );

    $nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $newObject->attribute( 'id' ),
                                                       'contentobject_version' => $newObject->attribute( 'current_version' ),
                                                       'parent_node' => $nodeID,
                                                       'is_main' => 1 ) );

    $nodeAssignment->store();

    $time = mktime();

    $newObject->setAttribute( 'modified', $time );
    $version = $newObject->currentVersion();

    $attribs =& $version->contentObjectAttributes();
    $attribsCount = count( $attribs );

    for ( $i = 0; $i < $attribsCount; $i++ )
    {
        $classAttrIdentifier = $attribs[$i]->attribute( 'contentclass_attribute_identifier' );
        if ( in_array( $classAttrIdentifier, $commonAttribs ) )
        {
            $attribs[$i]->initialize( $sourceAttribs[$classAttrIdentifier]->attribute( 'version' ), $sourceAttribs[$classAttrIdentifier] );
            $attribs[$i]->sync();
            $attribs[$i]->postInitialize( $sourceAttribs[$classAttrIdentifier]->attribute( 'version' ), $sourceAttribs[$classAttrIdentifier] );
            $attribs[$i]->sync();
        }
    }

    include_once( 'lib/ezutils/classes/ezoperationhandler.php' );

    $operationParams = array();
    $operationParams['object_id']   = $newObject->attribute( 'id' );
    $operationParams['version']     = $newObject->attribute( 'current_version' );

    $operationResult = eZOperationHandler::execute( 'content', 'publish', $operationParams );

    // when preview cache is on, the user is restored but the policy limitations are still wrongly cached
    // see http://ez.no/community/bugs/cache_for_content_read_limitation_list_isn_t_cleared_after_switching_users
    // this is a temporary workaround, until the kernel has been fixed
    if ( isset( $GLOBALS['ezpolicylimitation_list']['content']['read'] ) )
    {
        unset( $GLOBALS['ezpolicylimitation_list']['content']['read'] );
    }

    // get status

    // reload object
    $newObject = eZContentObject::fetch( $newObject->attribute( 'id' ) );

    // replace modified time if no error
    $newObject->setAttribute( 'published', $object->attribute( 'published' ) );
    $newObject->setAttribute( 'modified', $object->attribute( 'modified' ) );
    $newObject->store();

    $version = $newObject->currentVersion();
    $parentVersion = $object->currentVersion();
    $version->setAttribute( 'created', $parentVersion->attribute( 'created' ) );
    $version->setAttribute( 'modified', $parentVersion->attribute( 'modified' ) );
    $version->store();

    //$db->rollback();
    $db->commit();
}

$script->shutdown( 0 );

?>