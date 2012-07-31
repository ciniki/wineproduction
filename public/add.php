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
function ciniki_wineproduction_add($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'customer_id'=>array('required'=>'yes', 'default'=>'0', 'blank'=>'', 'errmsg'=>'No customer specified'), 
        'invoice_number'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'errmsg'=>'No invoice specified'), 
        'product_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No wine specified'), 
        'wine_type'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No wine specified'), 
        'kit_length'=>array('required'=>'no', 'blank'=>'no', 'errmsg'=>'No wine specified'), 
        'status'=>array('required'=>'yes', 'default'=>'10', 'blank'=>'no', 'errmsg'=>'No status specified'), 
        'rack_colour'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'errmsg'=>'No colour specified'), 
        'filter_colour'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'errmsg'=>'No colour specified'), 
        'order_flags'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'errmsg'=>'No order date specified'), 
        'order_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No order date specified'), 
        'start_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No start date specified'), 
        'sg_reading'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'errmsg'=>'No SG reading specified'), 
        'racking_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No racking date specified'), 
        'rack_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No racking date specified'), 
        'filtering_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No filter date specified'), 
        'filter_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No filter date specified'), 
        'bottling_duration'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'errmsg'=>'No bottle duration specified'), 
        'bottling_flags'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'errmsg'=>'No bottle flags specified'), 
        'bottling_nocolour_flags'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'errmsg'=>'No bottle flags specified'), 
        'bottling_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'datetime', 'errmsg'=>'No bottling date specified'), 
        'bottle_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'errmsg'=>'No bottle date specified'), 
        'notes'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'errmsg'=>'No notes specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/wineproduction/private/checkAccess.php');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.add'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//  
	// Turn off autocommit
	//  
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionStart.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionRollback.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionCommit.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbInsert.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbAddModuleHistory.php');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'wineproduction');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Check for duplicate products which will require at A, B C after the invoice number
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
		// Add the order to the database
		//
		$strsql = "INSERT INTO ciniki_wineproductions (uuid, business_id, customer_id, invoice_number, product_id, wine_type, kit_length, "
			. "status, rack_colour, filter_colour, order_flags, "
			. "order_date, start_date, sg_reading, racking_date, rack_date, filtering_date, filter_date, "
			. "bottling_duration, bottling_flags, bottling_nocolour_flags, bottle_date, bottling_date, notes, "
			. "date_added, last_updated) VALUES ("
			. "UUID(), "
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
			. "'" . ciniki_core_dbQuote($ciniki, $args['notes']) . "', "
			. "UTC_TIMESTAMP(), UTC_TIMESTAMP())"
			. "";
		$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.wineproduction');
		if( $rc['stat'] != 'ok' ) { 
			ciniki_core_dbTransactionRollback($ciniki, 'wineproduction');
			return $rc;
		}
		if( !isset($rc['insert_id']) || $rc['insert_id'] < 1 ) {
			ciniki_core_dbTransactionRollback($ciniki, 'wineproduction');
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'363', 'msg'=>'Unable to add order'));
		}
		$wineproduction_id = $rc['insert_id'];

		//
		// Add all the fields to the change log
		//

		$changelog_fields = array(
			'customer_id',
			'invoice_number',
			'product_id' . $ext,
			'wine_type' . $ext,
			'kit_length' . $ext,
			'status',
			'rack_colour',
			'filter_colour',
			'order_flags' . $ext,
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
			'bottle_date',
			'notes',
			);
		foreach($changelog_fields as $field) {
			$insert_name = $field;
			if( $ext != '' && preg_match('/^(product_id|wine_type|kit_length|order_flags)/', $field, $matches) ) {
				$insert_name = $matches[1];
			}
			if( isset($ciniki['request']['args'][$field]) && $ciniki['request']['args'][$field] != '' ) {
				$rc = ciniki_core_dbAddModuleHistory($ciniki, 'wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
					1, 'ciniki_wineproductions', $wineproduction_id, $insert_name, $ciniki['request']['args'][$field]);
			}
		}
	}
	//
	// Commit the database changes
	//
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'wineproduction');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok', 'id'=>$wineproduction_id);
}
?>
