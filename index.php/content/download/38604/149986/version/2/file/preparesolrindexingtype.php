<?php

/**
 * Prepare Solr indexing workflow
 * 
 * After publishing eZContentObject, insert into "ezpending_actions" table
 * values for cronjob do Solr indexing.
 * 
 * @author Nicolas Jessel <njessel@interact.lu> 
 */

class prepareSolrIndexingType extends eZWorkflowEventType {
	
	const EZ_WORKFLOW_TYPE_PREPARE_SOLR_INDEXING = 'preparesolrindexing';
	
/**
 * Construct
 */

	public function __construct(){
		$this->eZWorkflowEventType( self::EZ_WORKFLOW_TYPE_PREPARE_SOLR_INDEXING, "Prepare Solr indexing" );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array ( 'after' ) ) ) );
	}
	
/**
 * prepareSolrIndexingType::execute()
 *
 * @param mixed $process
 * @param mixed $event
 * @return (int) statut
 */
	
	public function execute( $process, $event ){
		
		// Parameters list
		$parameters = $process->attribute( 'parameter_list' );
		
        // Doesn't doing recursive execution
		if ( isset( $parameters['from_workflow'] ) ) {
            return eZWorkflowType::STATUS_ACCEPTED;
        }
        
        eZDebug::addTimingPoint( 'Workflow event start prepareSolrIndexingType' );
		
        // Current object
		$object = eZContentObject::fetch( $parameters['object_id'] );
		
		if ( $object instanceof eZContentObject ){
			
			// db instance
			$db = eZDB::instance();
			
			// ObjectId
			$objectID = (int) $object->ID;
			
			// Start db interaction
			$db->begin();
			
			// already indexable?
			$query = "SELECT * FROM ezpending_actions WHERE action = 'index_object' AND param = '$objectID'";
			$res = $db->arrayQuery( $query );
			
			if ( !count( $res ) ){
				
				// insert query
				$query = "INSERT INTO ezpending_actions( action, param ) VALUES( 'index_object', '$objectID' )";
				
				// Insert
				$db->query( $query );
			
			}
			
			// Stop db interaction
			$db->commit();
			
			eZDebug::addTimingPoint( "ending prepareSolrIndexingType => prepare eZContententObject #$objectID to Solr indexing" );
			
			return eZWorkflowType::STATUS_ACCEPTED;
			
		}
		else return eZWorkflowType::STATUS_REJECTED;
		
	}
	
}

eZWorkflowEventType::registerEventType( prepareSolrIndexingType::EZ_WORKFLOW_TYPE_PREPARE_SOLR_INDEXING, "prepareSolrIndexingType" );

?>