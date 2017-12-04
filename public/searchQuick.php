<?php
//
// Description
// -----------
// This method will search unbottled orders.
//
// Info
// ----
// Status:          defined
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to search the order of.
// start_needle:    The string to search the invoice_numbers, customer names or product names.
// limit:           The maximum number of results to return.
// 
// Returns
// -------
//
function ciniki_wineproduction_searchQuick($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'No tenant specified'), 
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'No search specified'), 
        'limit'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'No limit specified'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.searchQuick'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get the number of orders in each status for the tenant, 
    // if no rows found, then return empty array
    //
    $strsql = "SELECT ciniki_wineproductions.id, ciniki_wineproductions.customer_id, "
        . "ciniki_customers.display_name AS customer_name, invoice_number, "
        . "ciniki_products.name AS wine_name, ciniki_wineproductions.status, ciniki_wineproductions.wine_type, ciniki_wineproductions.kit_length, "
        . "DATE_FORMAT(ciniki_wineproductions.order_date, '%b %e, %Y') AS order_date, "
        . "DATE_FORMAT(ciniki_wineproductions.start_date, '%b %e, %Y') AS start_date, "
        . "DATE_FORMAT(ciniki_wineproductions.racking_date, '%b %e, %Y') AS racking_date, "
        . "DATE_FORMAT(ciniki_wineproductions.filtering_date, '%b %e, %Y') AS filtering_date, "
        . "DATE_FORMAT(ciniki_wineproductions.bottling_date, '%b %e, %Y') AS bottling_date "
        . "FROM ciniki_wineproductions "
        . "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
            . "AND ciniki_customers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "') "
        . "LEFT JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
            . "AND ciniki_products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "') "
        . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_wineproductions.status < 60 ";
    if( is_numeric($args['start_needle']) ) {
        $strsql .= "AND invoice_number LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "";
    } else {
        $strsql .= "AND ( ciniki_customers.first LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_customers.first LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_customers.last LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_customers.last LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_customers.company LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_customers.company LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_products.name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_products.name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' ) "
            . "";
    }

    $strsql .= "ORDER BY ciniki_wineproductions.last_updated DESC ";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";   // is_numeric verified
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
    return ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'orders', 'order', array('stat'=>'ok', 'orders'=>array()));
}
?>
