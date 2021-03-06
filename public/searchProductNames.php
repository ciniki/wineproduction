<?php
//
// Description
// -----------
// This method will search the wineproduction order products.
//
// Info
// ----
// Status:          defined
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to search the products for.
// start_needle:    The string to search the product names for.
// limit:           The number to limit the results.
// customer_id:     (optional) The ID of customer to search for orders.  If specified,
//                  the search will be restricted to orders attached to this order.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
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
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.searchProductNames'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // If a customer is specified, search through past orders to find preferences for customer
    //
    if( $args['start_needle'] == '' && isset($args['customer_id']) && $args['customer_id'] > 0 ) {
        $strsql = "SELECT ciniki_wineproduction_products.id, "
            . "ciniki_wineproduction_products.name AS wine_name, "
            . "ciniki_wineproductions.wine_type, "
            . "ciniki_wineproductions.kit_length, "
            . "order_flags "
            . "FROM ciniki_wineproductions, ciniki_wineproduction_products "
            . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wineproductions.customer_id = '" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "' "
            . "AND ciniki_wineproductions.product_id = ciniki_wineproduction_products.id "
            . "AND ciniki_wineproduction_products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wineproduction_products.status = 10 ";
    } 

    //
    // If no customer specified, but a search string is, then search through products for 
    // matching names
    //
    else if( $args['start_needle'] != '' ) {
        $strsql = "SELECT ciniki_wineproduction_products.id, ciniki_wineproduction_products.name AS wine_name, "
            . "ciniki_wineproduction_products.wine_type, "
            . "ciniki_wineproduction_products.kit_length, "
            . "0 AS order_flags "
            . "FROM ciniki_wineproduction_products "
            . "WHERE ciniki_wineproduction_products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND (ciniki_wineproduction_products.name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "  
                . "OR ciniki_wineproduction_products.name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "  
                . ") "
            . "AND ciniki_wineproduction_products.status = 10 ";
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.19', 'msg'=>'No search specified'));
    }

    $strsql .= "GROUP BY ciniki_wineproduction_products.id "
        . "ORDER BY ciniki_wineproduction_products.name "
//      . "ORDER BY COUNT(ciniki_wineproductions.id) DESC "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";   // is_numeric verified
    } else {
        $strsql .= "LIMIT 25";
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
    return ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'names', 'name', array('stat'=>'ok', 'names'=>array()));
}
?>
