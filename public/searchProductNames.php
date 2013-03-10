<?php
//
// Description
// -----------
// This method will search the wineproduction order products.
//
// Info
// ----
// Status: 			defined
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to search the products for.
// start_needle:	The string to search the product names for.
// limit:			The number to limit the results.
// customer_id:		(optional) The ID of customer to search for orders.  If specified,
//					the search will be restricted to orders attached to this order.
// 
// Returns
// -------
//
function ciniki_wineproduction_searchProductNames($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'start_needle'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Search'), 
        'limit'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Limit'), 
        'customer_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Customer'), 
		'category_id'=>array('required'=>'no', 'default'=>'0', 'blank'=>'yes', 'name'=>'Category'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.searchProductNames'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// If a customer is specified, search through past orders to find preferences for customer
	//
	if( $args['start_needle'] == '' && isset($args['customer_id']) && $args['customer_id'] > 0 ) {
		$strsql = "SELECT ciniki_products.id, ciniki_products.name AS wine_name, "
			. "wine_type, kit_length, order_flags "
			. "FROM ciniki_wineproductions, ciniki_products "
			. "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_wineproductions.customer_id = '" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "' "
			. "AND ciniki_wineproductions.product_id = ciniki_products.id "
			. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_products.status = 1 ";
	} 

	//
	// If no customer specified, but a search string is, then search through products for 
	// matching names
	//
	else if( $args['start_needle'] != '' ) {
		$strsql = "SELECT ciniki_products.id, ciniki_products.name AS wine_name, "
			. "IFNULL(d1.detail_value, '') AS wine_type, "
			. "IFNULL(d2.detail_value, '') AS kit_length, "
			. "0 AS order_flags "
			. "FROM ciniki_products "
			. "LEFT JOIN ciniki_product_details AS d1 ON (ciniki_products.id = d1.product_id AND d1.detail_key = 'wine_type') "
			. "LEFT JOIN ciniki_product_details AS d2 ON (ciniki_products.id = d2.product_id AND d2.detail_key = 'kit_length') "
//			. "LEFT JOIN ciniki_wineproductions ON (ciniki_products.id = ciniki_wineproductions.product_id "
//				. "AND ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
			. "WHERE ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND (ciniki_products.name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "	
				. "OR ciniki_products.name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "	
				. ") "
			. "AND ciniki_products.status = 1 ";
	} else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'372', 'msg'=>'No search specified'));
	}

	$strsql .= "GROUP BY ciniki_products.id "
		. "ORDER BY ciniki_products.name "
//		. "ORDER BY COUNT(ciniki_wineproductions.id) DESC "
		. "";
	if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";	// is_numeric verified
	} else {
		$strsql .= "LIMIT 25";
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
	return ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'names', 'name', array('stat'=>'ok', 'names'=>array()));
}
?>
