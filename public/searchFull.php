<?php
//
// Description
// -----------
// This method will search all wineproduction orders that are in 
// any status, bottled or not.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to search the orders of.
// search_str:		The string to search the orders for.
// limit:			(optional) The limit of results to return.
// finished:		(optional) If specified 'no' only returned unbottled orders.
// 
// Returns
// -------
//
function ciniki_wineproduction_searchFull($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.searchFull'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);
	
	$strsql = "SELECT ciniki_wineproductions.id, CONCAT_WS(' ', first, last) AS customer_name, invoice_number, "
		. "ciniki_products.name AS wine_name, wine_type, kit_length, ciniki_wineproductions.status, rack_colour, filter_colour, "
		. "DATE_FORMAT(order_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as order_date, "
		. "DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as start_date, "
		. "DATE_FORMAT(racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as racking_date, "
		. "DATE_FORMAT(rack_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as rack_date, "
		. "sg_reading, "
		. "DATE_FORMAT(filtering_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as filtering_date, "
		. "DATE_FORMAT(filter_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as filter_date, "
		. "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as bottling_date, "
		. "DATE_FORMAT(bottle_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as bottle_date "
		. "FROM ciniki_wineproductions "
		. "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "LEFT JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
			. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' ";
	if( is_numeric($args['search_str']) ) {
		$strsql .= "AND invoice_number LIKE '%" . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' "
			. "";
	} else {
		$strsql .= "AND ( ciniki_customers.first LIKE '%" . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' "
			. "OR ciniki_customers.last LIKE '%" . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' "
			. "OR ciniki_products.name LIKE '%" . ciniki_core_dbQuote($ciniki, $args['search_str']) . "%' ) "
			. "";
	}

	if( isset($args['finished']) && $args['finished'] == 'no' ) {
		$strsql . "AND ciniki_wineproductions.status < 60";
	}

	$strsql .= "ORDER BY ciniki_wineproductions.invoice_number ASC ";
	if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";	// is_numeric verified
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
	return ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'orders', 'order', array('stat'=>'ok', 'orders'=>array()));
}
?>
