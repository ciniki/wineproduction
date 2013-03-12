<?php
//
// Description
// -----------
// This function will clean up the history for wineproduction.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_wineproduction_dbIntegrityCheck($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'fix'=>array('required'=>'no', 'default'=>'no', 'name'=>'Fix Problems'),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
	$rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.historyFix', 0);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbFixTableHistory');

	if( $args['fix'] == 'yes' ) {
		//
		// Update the history for ciniki_wineproductions
		//
		$rc = ciniki_core_dbFixTableHistory($ciniki, 'ciniki.wineproduction', $args['business_id'],
			'ciniki_wineproductions', 'ciniki_wineproduction_history', 
			array('uuid', 'customer_id', 'invoice_id', 'invoice_number', 'product_id', 'wine_type',
				'kit_length', 'status', 'colour_tag', 'rack_colour', 'filter_colour',
				'order_flags', 'order_date', 'start_date', 'sg_reading',
				'racking_date', 'rack_date', 'filtering_date', 'filter_date',
				'bottling_flags', 'bottling_nocolour_flags', 'bottling_duration', 
				'bottling_date', 'bottling_status', 'bottling_notes',
				'bottle_date', 'notes', 'batch_code'));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		//
		// Check for items missing a UUID
		//
		$strsql = "UPDATE ciniki_wineproduction_history SET uuid = UUID() WHERE uuid = ''";
		$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.wineproduction');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		//
		// Remote any entries with blank table_key, they are useless we don't know what they were attached to
		//
		$strsql = "DELETE FROM ciniki_wineproduction_history WHERE table_key = ''";
		$rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.wineproduction');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
	}

	return array('stat'=>'ok');
}
?>
