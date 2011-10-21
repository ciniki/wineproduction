<?php
//
// Description
// -----------
// The search return a list of results, with the most probable at the top.
//
// Info
// ----
// Status: 			defined
//
// Arguments
// ---------
// user_id: 		The user making the request
// search_str:		The search string provided by the user.
// 
// Returns
// -------
//
function ciniki_wineproduction_searchFull($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'search_str'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No search specified'), 
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No limit specified'), 
        'finished'=>array('required'=>'no', 'default'=>'yes', 'blank'=>'yes', 'errmsg'=>'No finished flag specified'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.searchFull'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	require_once($ciniki['config']['core']['modules_dir'] . '/users/private/dateFormat.php');
	$date_format = ciniki_users_dateFormat($ciniki);
	
	$strsql = "SELECT wineproductions.id, CONCAT_WS(' ', first, last) AS customer_name, invoice_number, "
		. "products.name AS wine_name, wine_type, kit_length, wineproductions.status, rack_colour, filter_colour, "
		. "DATE_FORMAT(order_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as order_date, "
		. "DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as start_date, "
		. "DATE_FORMAT(racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as racking_date, "
		. "DATE_FORMAT(rack_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as rack_date, "
		. "sg_reading, "
		. "DATE_FORMAT(filtering_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as filtering_date, "
		. "DATE_FORMAT(filter_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as filter_date, "
		. "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as bottling_date, "
		. "DATE_FORMAT(bottle_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as bottle_date "
		. "FROM wineproductions "
		. "LEFT JOIN customers ON (wineproductions.customer_id = customers.id "
			. "AND customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "LEFT JOIN products ON (wineproductions.product_id = products.id "
			. "AND products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "WHERE wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' ";
	if( is_numeric($args['search_str']) ) {
		$strsql .= "AND invoice_number LIKE '%" . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' "
			. "";
	} else {
		$strsql .= "AND ( customers.first LIKE '%" . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' "
			. "OR customers.last LIKE '%" . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' "
			. "OR products.name LIKE '%" . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' ) "
			. "";
	}

	if( isset($args['finished']) && $args['finished'] == 'no' ) {
		$strsql . "AND wineproductions.status < 60";
	}

	$strsql .= "ORDER BY wineproductions.invoice_number ASC ";
	if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";	// is_numeric verified
	}

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbRspQuery.php');
	return ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'orders', 'order', array('stat'=>'ok', 'orders'=>array()));
}
?>
