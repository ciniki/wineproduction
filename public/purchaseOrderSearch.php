<?php
//
// Description
// -----------
// This method searchs for a Purchase Orders for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Purchase Order for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
// Returns
// -------
//
function ciniki_wineproduction_purchaseOrderSearch($ciniki) {
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.purchaseOrderSearch');
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
    // Get the list of purchaseorders
    //
    $strsql = "SELECT orders.id, "
        . "orders.supplier_id, "
        . "suppliers.name AS supplier_name, "
        . "orders.po_number, "
        . "orders.status, "
        . "orders.status AS status_text, "
        . "orders.date_ordered, "
        . "orders.date_received "
        . "FROM ciniki_wineproduction_purchaseorders AS orders "
        . "LEFT JOIN ciniki_wineproduction_suppliers AS suppliers ON ("
            . "orders.supplier_id = suppliers.id "
            . "AND suppliers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "suppliers.name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR suppliers.name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR orders.po_number LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR orders.po_number LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "ORDER BY po_number DESC "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'purchaseorders', 'fname'=>'id', 
            'fields'=>array('id', 'supplier_id', 'supplier_name', 'po_number', 'status', 'date_ordered', 'date_received'),
            'maps'=>array('status_text'=>$maps['purchaseorder']['status']),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['purchaseorders']) ) {
        $purchaseorders = $rc['purchaseorders'];
        $purchaseorder_ids = array();
        foreach($purchaseorders as $iid => $purchaseorder) {
            $purchaseorder_ids[] = $purchaseorder['id'];
        }
    } else {
        $purchaseorders = array();
        $purchaseorder_ids = array();
    }

    return array('stat'=>'ok', 'purchaseorders'=>$purchaseorders, 'nplist'=>$purchaseorder_ids);
}
?>
