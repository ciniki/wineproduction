<?php
//
// Description
// -----------
// This function will add a new order to the wine production module.
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_wineproduction_add(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'customer_id'=>array('required'=>'yes', 'default'=>'0', 'blank'=>'', 'name'=>'Customer'), 
        'invoice_number'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Invoice Number'), 
        'product_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Wine'), 
        'batch_count'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'name'=>'Batch Count'), 
        'wine_type'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Wine Type'), 
        'kit_length'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Kit Length'), 
        'status'=>array('required'=>'yes', 'default'=>'10', 'blank'=>'no', 'name'=>'Status'), 
        'rack_colour'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Rack Colour'), 
        'filter_colour'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Filter Colour'), 
        'order_flags'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'name'=>'Order Flags'), 
        'order_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'name'=>'Order Date'), 
        'start_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'name'=>'Start Date'), 
        'sg_reading'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'SG Reading'), 
        'racking_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'name'=>'Racking Date'), 
        'rack_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'name'=>'Rack Date'), 
        'filtering_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'name'=>'Filtering Date'), 
        'filter_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'name'=>'Filter Date'), 
        'bottling_duration'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'name'=>'Bottling Duration'), 
        'bottling_flags'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'name'=>'Bottling Flags'), 
        'bottling_nocolour_flags'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'name'=>'Bottling Colour Flags'), 
        'bottling_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'datetime', 'name'=>'Bttling Date'), 
        'bottle_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'name'=>'Bottle Date'), 
		'bottling_status'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'name'=>'Bottling Status'),
        'notes'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Notes'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.add'); 
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.wineproduction');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Check for duplicate products which will require at A, B C after the invoice number
	// FIXME: No longer needed
	//
	$pcount = array(); 
	for($i=0;$i<25;$i++) {
		$ext = '';
		if( $i > 0 ) {
			$ext = '_' . $i;
		}
		if( isset($ciniki['request']['args']['product_id' . $ext]) && $ciniki['request']['args']['product_id' . $ext] != '' && $ciniki['request']['args']['product_id' . $ext] != '0' ) {
			if( !isset($pcount[$ciniki['request']['args']['product_id' . $ext]]) ) {
				$pcount[$ciniki['request']['args']['product_id' . $ext]] = array('cur'=>0, 'total'=>0);
			} 
			$pcount[$ciniki['request']['args']['product_id' . $ext]]['total']++;
		} else {
			break;
		}
	}

	for($i=0;$i<25;$i++) {
		$ext = '';
		if( $i > 0 ) {
			$ext = '_' . $i;
			// Check if more than one wine was passed to be added
			if( !isset($ciniki['request']['args']['product_id' . $ext]) || $ciniki['request']['args']['product_id' . $ext] == '') {
				break;
			}
		}
		$invoice_number = $args['invoice_number'];
		if( $pcount[$ciniki['request']['args']['product_id' . $ext]]['total'] > 1 ) {
			$invoice_number .= '-' . chr($pcount[$ciniki['request']['args']['product_id' . $ext]]['cur'] + 65);
			$pcount[$ciniki['request']['args']['product_id' . $ext]]['cur']++;
		}

		//
		// Get a new UUID
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUUID');
		$rc = ciniki_core_dbUUID($ciniki, 'ciniki.services');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$args['uuid'] = $rc['uuid'];

		if( isset($args['batch_count']) && $args['batch_count'] > 0 ) {
			$invoice_number .= '-' . chr($args['batch_count'] + 64);
		}

		//
		// Add the order to the database
		//
		$strsql = "INSERT INTO ciniki_wineproductions (uuid, business_id, customer_id, invoice_number, "
			. "product_id, wine_type, kit_length, "
			. "status, rack_colour, filter_colour, order_flags, "
			. "order_date, start_date, sg_reading, racking_date, rack_date, filtering_date, filter_date, "
			. "bottling_duration, bottling_flags, bottling_nocolour_flags, bottle_date, bottling_date, bottling_status, notes, "
			. "date_added, last_updated) VALUES ("
			. "'" . ciniki_core_dbQuote($ciniki, $args['uuid']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $invoice_number) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['product_id' . $ext]) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['wine_type' . $ext]) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['kit_length' . $ext]) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['status']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['rack_colour']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['filter_colour']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['order_flags' . $ext]) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['order_date']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['sg_reading']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['racking_date']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['rack_date']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['filtering_date']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['filter_date']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['bottling_duration']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['bottling_flags']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['bottling_nocolour_flags']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['bottle_date']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['bottling_date']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['bottling_status']) . "', "
			. "'" . ciniki_core_dbQuote($ciniki, $args['notes']) . "', "
			. "UTC_TIMESTAMP(), UTC_TIMESTAMP())"
			. "";
		$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.wineproduction');
		if( $rc['stat'] != 'ok' ) { 
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
			return $rc;
		}
		if( !isset($rc['insert_id']) || $rc['insert_id'] < 1 ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'363', 'msg'=>'Unable to add order'));
		}
		$wineproduction_id = $rc['insert_id'];

		//
		// Add the base fields which are the same for all orders to the history
		//
		$changelog_fields = array(
			'uuid',
			'customer_id',
			'invoice_number',
			'status',
			'rack_colour',
			'filter_colour',
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
			);
		foreach($changelog_fields as $field) {
			$insert_name = $field;
			if( isset($args[$field]) && $args[$field] != '' ) {
				$rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', 
					$args['business_id'], 1, 'ciniki_wineproductions', $wineproduction_id, $insert_name, $args[$field]);
			}
		}

		//
		// Add the order specific fields to the orders history
		//
		$changelog_fields = array(
			'product_id' . $ext,
			'wine_type' . $ext,
			'kit_length' . $ext,
			'order_flags' . $ext,
			);
		foreach($changelog_fields as $field) {
			$insert_name = $field;
			if( $ext != '' && preg_match('/^(product_id|wine_type|kit_length|order_flags)/', $field, $matches) ) {
				$insert_name = $matches[1];
			}
			if( isset($ciniki['request']['args'][$field]) && $ciniki['request']['args'][$field] != '' ) {
				$rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', 
					$args['business_id'], 1, 'ciniki_wineproductions', $wineproduction_id, $insert_name, $ciniki['request']['args'][$field]);
			}
		}
		//
		// Add the order to the sync queue
		//
		$ciniki['syncqueue'][] = array('push'=>'ciniki.wineproduction.order',
			'args'=>array('id'=>$wineproduction_id));
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

	return array('stat'=>'ok', 'id'=>$wineproduction_id);
}
?>
