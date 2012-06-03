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

	$strsql = "SELECT ciniki_wineproductions.id, ciniki_wineproductions.customer_id, CONCAT_WS(' ', first, last) AS customer_name, invoice_number, "
		. "ciniki_products.id as product_id, ciniki_products.name AS wine_name, wine_type, kit_length, "
		. "ciniki_wineproductions.status, colour_tag, order_flags, rack_colour, filter_colour, "
		. "DATE_FORMAT(order_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as order_date, "
		. "DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as start_date, "
		. "DATE_FORMAT(racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as racking_date, "
		. "DATE_FORMAT(rack_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as rack_date, "
		. "sg_reading, "
		. "DATE_FORMAT(filtering_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as filtering_date, "
		. "DATE_FORMAT(filter_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as filter_date, "
		. "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $datetime_format) . "') as bottling_date, "
		. "DATE_FORMAT(bottle_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as bottle_date, "
		. "bottling_flags, bottling_status, bottling_duration, "
		. "ciniki_wineproductions.notes, "
		. "ciniki_wineproductions.batch_code "
		. "FROM ciniki_wineproductions "
		. "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "LEFT JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
			. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_wineproductions.id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' "
		. "";
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQuery.php');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'wineproduction', 'order');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['order']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'366', 'msg'=>'Invalid order'));
	}

	$rc['order']['bottling_date'] = preg_replace('/ 12:00 AM/', '', $rc['order']['bottling_date']);
	return array('stat'=>'ok', 'order'=>$rc['order']);
}
?>
