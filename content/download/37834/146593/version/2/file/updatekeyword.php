#!/usr/bin/env php
<?php
//

/*! \file updatekeyword.php
*/

include_once( 'lib/ezutils/classes/ezcli.php' );
include_once( 'kernel/classes/ezscript.php' );


$cli =& eZCLI::instance();
$script =& eZScript::instance( array( 'description' => ( "eZ publish keyword update script\n\n" .
                                                         "This script will remove dupblicated keywords which was caused by a bug.\n" .
                                                         "\n" .
                                                         "Note: The script must be run for each siteaccess" .
                                                         "\n" .
                                                         "updatekeyword.php -sSITEACCESS" ),
                                      'use-session' => false,
                                      'use-modules' => false,
                                      'use-extensions' => false,
                                      'min_version'  => '3.4.0',
                                      'max_version' => '3.5.0' ) );

$script->startup();

$options = $script->getOptions( "", "",
                                array() );

$script->initialize();

if ( !$script->validateVersion() )
{
    $cli->output( "Unsuitable eZ publish version: " );
    $cli->output( eZPublishSDK::version() );
    $script->shutdown( 1 );
}

$db =& eZDB::instance();

$sql = "SELECT 
			DISTINCT objectattribute_id 
		FROM ezkeyword_attribute_link 
		ORDER BY objectattribute_id";

$keywordAttributeArray =& $db->arrayQuery( $sql );
$attributeCount = count ( $keywordAttributeArray );

$script->resetIteration($attributeCount);
$cli->output( "Removing keyword dupes from each keyword-attribute" );
$cli->output( "Total attributes: $attributeCount" );

foreach ( $keywordAttributeArray as $attributeItem )
{
	$attributeID = $attributeItem['objectattribute_id'];
	
	// getting the class_id of the current attribute ...
	$sql = "SELECT 
				DISTINCT ezcontentobject.contentclass_id as class_id
			FROM 
				ezcontentobject_attribute, ezcontentobject 
			WHERE
				ezcontentobject_attribute.id = $attributeID AND
				ezcontentobject_attribute.contentobject_id = ezcontentobject.id";
	$classArray =& $db->arrayQuery( $sql );
	if ( isset ( $classArray[0]['class_id'] ) )
	{
		$classID = $classArray[0]['class_id'];
		
		$sql = "SELECT * FROM ezkeyword_attribute_link WHERE objectattribute_id = $attributeID";
		$attributeDataArray =& $db->arrayQuery( $sql );
		$keywordIDArray = array();
		foreach ( $attributeDataArray as $attributeDataItem )
		{
			$keywordIDArray[] = $attributeDataItem['keyword_id'];
		}
		// fetching corresponding keywords
		
		$keywordIDString = implode ( ",", $keywordIDArray );
		
		$sql = "SELECT * FROM ezkeyword WHERE id IN ( $keywordIDString ) AND class_id = $classID";
		$keywordArray =& $db->arrayQuery( $sql );
		
		$dupeKeywordIDArray = array();
		$usedWords = array();
		foreach ( $keywordArray as $keywordItem )
		{
			$keywordID	= $keywordItem['id'];
			$keyword 	= $keywordItem['keyword'];
			
			if ( in_array ( $keyword, $usedWords ) )
			{
				// This is a dupe ...
				$dupeKeywordIDArray[] = $keywordID;
			}	
			else
				$usedWords[] = $keyword;
		}
		
		if ( count ( $dupeKeywordIDArray ) > 0 )
		{
			$script->iterate($cli,true);
			$idString = implode ( ",", $dupeKeywordIDArray );
			// $cli->output ( $idString );
			// $sql = "DELETE FROM ezkeyword WHERE id IN ( $idString ) AND class_id = $classID";
			// $db->query ( $sql );
			$sql = "DELETE FROM ezkeyword_attribute_link 
					WHERE 
						keyword_id IN ( $idString ) 
						AND objectattribute_id = $attributeID";
			$db->query ( $sql );
		}
		else
			$script->iterate($cli,false);
	}
	else
		$script->iterate($cli,false);
}

// checking keyword table for dupes and repair it
$sql = "SELECT * FROM `ezkeyword` GROUP BY keyword, class_id ORDER BY keyword";
$keywordArray =& $db->arrayQuery( $sql );

$keywordCount = count ( $keywordArray );
$cli->output ( "Repairing keyword table, removing dupe keyword entries" );
$cli->output ( "Total keywords: $keywordCount" );
$script->resetIteration($keywordCount);

foreach ( $keywordArray as $keywordItem )
{
	$keyword = $keywordItem['keyword'];
	$keywordSql = $db->escapeString ( $keyword );
	$classID = $keywordItem['class_id'];
	
	$sql = "SELECT id FROM ezkeyword WHERE keyword = '$keywordSql' AND class_id = $classID ORDER BY id";
	$dupeArray =& $db->arrayQuery( $sql );
	if ( count ( $dupeArray ) > 1 )
	{
		// keyword dupes found, removing them	
		$script->iterate($cli,true);
		
		$keywordIDArray = array();
		foreach ( $dupeArray as $dupeItem )
		{
			$keywordIDArray[] = $dupeItem['id'];
		}
		$firstID = array_shift ( $keywordIDArray );
		$idString = implode ( ",", $keywordIDArray );
		
		// changing the keyword id of dupe words
		$sql = "UPDATE ezkeyword_attribute_link SET keyword_id = $firstID WHERE keyword_id IN ( $idString )";
		$db->query ( $sql );
		
		// deleting dupes in keyword table
		$sql = "DELETE FROM ezkeyword WHERE id IN ( $idString )";
		$db->query ( $sql );
	}
	else
		$script->iterate($cli,false);
}

// deleting keywords which aren't used by any attribute

$sql = "SELECT ezkeyword.id as id FROM ezkeyword
        LEFT JOIN ezkeyword_attribute_link ON ezkeyword.id=ezkeyword_attribute_link.keyword_id
        WHERE ezkeyword_attribute_link.keyword_id IS NULL";

$keywordArray =& $db->arrayQuery( $sql );
$keywordIDArray = array();
foreach ( $keywordArray as $keywordItem )
{
	$keywordIDArray[] = $keywordItem['id'];
}

$totalKeywords = count ( $keywordIDArray );
if ( $totalKeywords > 0 )
{
	$cli->output ( "Deleting $totalKeywords keywords which aren't used by any attribute ..." );
	
	$keywordString = implode ( ",", $keywordIDArray );
	$sql = "DELETE FROM ezkeyword WHERE id IN ( $keywordString )";
	$db->query ( $sql );
}
else
	$cli->output ( "No unused keywords found ...");

$script->shutdown();

?>
