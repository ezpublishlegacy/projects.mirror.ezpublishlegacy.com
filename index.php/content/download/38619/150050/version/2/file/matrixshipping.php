
			$classID = eZContentObjectTreeNode::classIDByIdentifier( 'common_ini_settings' );
			$chosenNodeList    = eZContentObject::fetchFilteredList( array( 'contentclass_id' => $classID ), 0, 1 );
			$chosenNode = $chosenNodeList[0];

			$dataMap = $chosenNode->dataMap();
			$matrix=$dataMap['shipping1']->content();

			$matrixHeaderArray = array();
			// Fetch the current defined columns in the matrix
			$columns = $matrix->attribute( "columns" );

			foreach ( $columns['sequential'] as $column )
			{
				$matrixHeaderArray[] = strtoupper($column['name']);
			}
			$originalHeaderCount = count( $matrixHeaderArray );

			$rows = $matrix->attribute( "rows" );

			$costArray = array();
			$thresholdArray = array();

			$cost = -1.0;
			$askvendor = true;

			// let's say we have to ship to Fr:
			$country = strtoupper("fr");

			$country_column = array_search( $country, $matrixHeaderArray ); 

			$rowcount = count( $rows['sequential'] );
			
			// we found the country column.
			if ( $country_column ) {
				
				foreach ( $rows['sequential'] as $row )
				{
					$col=0;
					  foreach ( $row['columns'] as $cell )
					  {
						if ($col==0) $thresholdArray[]=$cell; 
						if ($col==$country_column) $costArray[]=$cell; 	
						$col++;
					  }
				}
				$cost = $costArray[$rowcount-1];  // set price as found in the 'above' line...

				$askvendor = false;

				for ( $i = $rowcount-2; $i >= 0 ; $i-- ) {
					if ($totalweight < $thresholdArray[$i]) { 
						$cost = $costArray[$i];
						$askvendor = false;
					} else break;
				}

				if ($cost < 0.0 ) askvendor = true ;
			} 
		
