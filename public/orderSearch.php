<?php
//
// Description
// -----------
// This method searchs for a Wine Production Orders for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Wine Production Order for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
// Returns
// -------
//
function ciniki_wineproduction_orderSearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.orderSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
        
    //
    // Load maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'maps');
    $rc = ciniki_wineproduction_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Get the number of orders in each status for the tenant, 
    // if no rows found, then return empty array
    //
    $strsql = "SELECT orders.id, "
        . "orders.customer_id, "
        . "customers.display_name AS customer_name, "
        . "orders.invoice_id, "
        . "orders.invoice_number, "
        . "orders.batch_letter, "
        . "orders.flags, "
        . "orders.order_flags, "
        . "orders.status, "
        . "orders.status AS status_text, "
        . "orders.location, "
        . "products.name AS wine_name, "
        . "products.wine_type, "
        . "products.kit_length, "
        . "IFNULL(DATE_FORMAT(orders.order_date, '%b %e, %Y'), '') AS order_date, "
        . "IFNULL(DATE_FORMAT(orders.start_date, '%b %e, %Y'), '') AS start_date, "
        . "IFNULL(DATE_FORMAT(orders.racking_date, '%b %e, %Y'), '') AS racking_date, "
        . "IFNULL(DATE_FORMAT(orders.filtering_date, '%b %e, %Y'), '') AS filtering_date, "
        . "IFNULL(DATE_FORMAT(orders.bottling_date, '%b %e, %Y'), '') AS bottling_date "
        . "FROM ciniki_wineproductions AS orders "
        . "LEFT JOIN ciniki_customers AS customers ON ("
            . "orders.customer_id = customers.id "
            . "AND customers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "LEFT JOIN ciniki_wineproduction_products AS products ON ("
            . "orders.product_id = products.id "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND orders.status < 60 ";
    if( is_numeric($args['start_needle']) ) {
        $strsql .= "AND ( "
            . "invoice_number LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR invoice_number LIKE '0" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR invoice_number LIKE '00" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . ") ";
    } else {
        $strsql .= "AND ( "
            . "customers.first LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR customers.first LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR customers.last LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR customers.last LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR customers.company LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR customers.company LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR products.name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR products.name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . ") "
            . "";
    }

    $strsql .= "ORDER BY orders.last_updated DESC ";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";   // is_numeric verified
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'orders', 'fname'=>'id', 
            'fields'=>array('id', 'wine_name', 'customer_id', 'customer_name', 
                'invoice_id', 'invoice_number', 'batch_letter', 
                'wine_type', 'kit_length', 'status', 'status_text',
                'location', 'flags', 'order_flags', 
                'order_date', 'start_date', 'racking_date', 'filtering_date', 'bottling_date',
                ),
            'maps'=>array('status_text'=>$maps['wineproduction']['status']),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $orders = isset($rc['orders']) ? $rc['orders'] : array();

    return array('stat'=>'ok', 'orders'=>$orders);
}
?>
