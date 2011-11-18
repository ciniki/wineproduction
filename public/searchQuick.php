<?php
//
// Description
// -----------
// Search active orders and last updated at the top.
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
function ciniki_wineproduction_searchQuick($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No search specified'), 
        'limit'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No limit specified'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.searchQuick'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Get the number of orders in each status for the business, 
	// if no rows found, then return empty array
	//
	$strsql = "SELECT ciniki_wineproductions.id, ciniki_wineproductions.customer_id, CONCAT_WS(' ', first, last) AS customer_name, invoice_number, "
		. "ciniki_products.name AS wine_name, ciniki_wineproductions.status, ciniki_wineproductions.wine_type, ciniki_wineproductions.kit_length, "
		. "DATE_FORMAT(order_date, '%b %e, %Y') AS order_date, "
		. "DATE_FORMAT(start_date, '%b %e, %Y') AS start_date, "
		. "DATE_FORMAT(racking_date, '%b %e, %Y') AS racking_date, "
		. "DATE_FORMAT(filtering_date, '%b %e, %Y') AS filtering_date, "
		. "DATE_FORMAT(bottling_date, '%b %e, %Y') AS bottling_date "
		. "FROM ciniki_wineproductions "
		. "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "LEFT JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
			. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_wineproductions.status < 60 ";
	if( is_numeric($args['start_needle']) ) {
		$strsql .= "AND invoice_number LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "";
	} else {
		$strsql .= "AND ( ciniki_customers.first LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR ciniki_customers.last LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR ciniki_products.name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR ciniki_products.name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' ) "
			. "";
	}

	$strsql .= "ORDER BY ciniki_wineproductions.last_updated DESC ";
	if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";	// is_numeric verified
	}

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbRspQuery.php');
	return ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'orders', 'order', array('stat'=>'ok', 'orders'=>array()));
}
?>
