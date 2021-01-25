<?php
//
// Description
// -----------
// This method will return the list of Purchase Orders for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Purchase Order for.
//
// Returns
// -------
//
function ciniki_wineproduction_purchaseOrderList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.purchaseOrderList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    
    //
    // Load the date format strings for the user
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');
    
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
        . "ORDER BY po_number DESC "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'purchaseorders', 'fname'=>'id', 
            'fields'=>array('id', 'supplier_id', 'supplier_name', 'po_number', 'status', 'status_text', 'date_ordered', 'date_received'),
            'maps'=>array('status_text'=>$maps['purchaseorder']['status']),
            'utctotz'=>array('date_ordered'=>array('format'=>$date_format, 'timezone'=>$intl_timezone),
                'date_received'=>array('format'=>$date_format, 'timezone'=>$intl_timezone),
                ),
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
