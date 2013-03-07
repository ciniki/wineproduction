<?php
//
// Description
// -----------
// This method will update an existing wineproduction order.
//
// Info
// ----
// Status: 			defined
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_wineproduction_update(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'wineproduction_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No order specified'), 
        'customer_id'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No customer specified'), 
        'invoice_number'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No invoice specified'), 
        'product_id'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No wine specified'), 
        'wine_type'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No type specified'), 
        'kit_length'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No racking length specified'), 
        'status'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No status specified'), 
        'colour_tag'=>array('required'=>'no', 'blank'=>'yes', 'null'=>'', 'errmsg'=>'No colour tag specified'), 
        'rack_colour'=>array('required'=>'no', 'blank'=>'yes', 'null'=>'', 'errmsg'=>'No colour specified'), 
        'filter_colour'=>array('required'=>'no', 'blank'=>'yes', 'null'=>'', 'errmsg'=>'No colour specified'), 
        'order_flags'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No order date specified'), 
        'order_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No order date specified'), 
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No start date specified'), 
        'sg_reading'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No SG reading specified'), 
        'racking_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No racking date specified'), 
        'rack_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No racking date specified'), 
        'filtering_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No filter date specified'), 
        'filter_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No filter date specified'), 
        'bottling_duration'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No bottling duration specified'), 
        'bottling_flags'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No bottling flags specified'), 
        'bottling_nocolour_flags'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No bottling flags specified'), 
        'bottling_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetime', 'errmsg'=>'No bottling date specified'), 
        'bottling_status'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No bottling status specified'), 
        'bottle_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No bottle date specified'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No notes specified'), 
        'batch_code'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No batch code specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.update'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//  
	// Turn off autocommit
	//  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.wineproduction');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Add the order to the database
	//
	$strsql = "UPDATE ciniki_wineproductions SET last_updated = UTC_TIMESTAMP()";

	//
	// Add all the fields to the change log
	//

	$changelog_fields = array(
		'customer_id',
		'invoice_number',
		'product_id',
		'wine_type',
		'kit_length',
		'status',
		'colour_tag',
		'rack_colour',
		'filter_colour',
		'order_flags',
		'order_date',
		'start_date',
		'sg_reading',
		'racking_date',
		'rack_date',
		'filtering_date',
		'filter_date',
		'bottling_duration',
		'bottling_flags',
		'bottling_nocolour_flags',
		'bottling_date',
		'bottling_status',
		'bottle_date',
		'notes',
		'batch_code',
		);
	foreach($changelog_fields as $field) {
		if( isset($args[$field]) ) {
			$strsql .= ", $field = '" . ciniki_core_dbQuote($ciniki, $args[$field]) . "' ";
			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
				2, 'ciniki_wineproductions', $args['wineproduction_id'], $field, $args[$field]);
		}
	}
	$strsql .= "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
	$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.wineproduction');
	if( $rc['stat'] != 'ok' ) { 
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
		return $rc;
	}
	if( !isset($rc['num_affected_rows']) || $rc['num_affected_rows'] != 1 ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'367', 'msg'=>'Unable to add order'));
	}

	//
	// Commit the database changes
	//
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.wineproduction');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'wineproduction');

	$ciniki['syncqueue'][] = array('push'=>'ciniki.wineproduction.order',
		'args'=>array('id'=>$args['wineproduction_id']));

	return array('stat'=>'ok');
}
?>
