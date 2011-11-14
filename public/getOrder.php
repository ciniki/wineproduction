<?php
//
// Description
// -----------
// This function will return a list of orders
//
// Info
// ----
// Status: 			started
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
//
function ciniki_wineproduction_getOrder($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'wineproduction_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No order specified'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.getOrder'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
    require_once($ciniki['config']['core']['modules_dir'] . '/users/private/dateFormat.php');
    require_once($ciniki['config']['core']['modules_dir'] . '/users/private/datetimeFormat.php');
	$date_format = ciniki_users_dateFormat($ciniki);
	$datetime_format = ciniki_users_datetimeFormat($ciniki);

	$strsql = "SELECT wineproductions.id, wineproductions.customer_id, CONCAT_WS(' ', first, last) AS customer_name, invoice_number, "
		. "products.id as product_id, products.name AS wine_name, wine_type, kit_length, "
		. "wineproductions.status, colour_tag, order_flags, rack_colour, filter_colour, "
		. "DATE_FORMAT(order_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as order_date, "
		. "DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as start_date, "
		. "DATE_FORMAT(racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as racking_date, "
		. "DATE_FORMAT(rack_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as rack_date, "
		. "sg_reading, "
		. "DATE_FORMAT(filtering_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as filtering_date, "
		. "DATE_FORMAT(filter_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as filter_date, "
		. "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $datetime_format) . "') as bottling_date, "
		. "DATE_FORMAT(bottle_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as bottle_date, "
		. "bottling_flags, "
		. "wineproductions.notes, "
		. "wineproductions.batch_code "
		. "FROM wineproductions "
		. "LEFT JOIN customers ON (wineproductions.customer_id = customers.id "
			. "AND customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "LEFT JOIN products ON (wineproductions.product_id = products.id "
			. "AND products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "WHERE wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND wineproductions.id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' "
		. "";
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQuery.php');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'wineproduction', 'order');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['order']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'366', 'msg'=>'Invalid order'));
	}
	return array('stat'=>'ok', 'order'=>$rc['order']);
}
?>
