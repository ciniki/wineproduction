<?php
//
// Description
// -----------
// This method searchs for a Purchase Order Items for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Purchase Order Item for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
// Returns
// -------
//
function ciniki_wineproduction_purchaseOrderItemSearch($ciniki) {
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.purchaseOrderItemSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of items
    //
    $strsql = "SELECT ciniki_wineproduction_purchaseorder_items.id, "
        . "ciniki_wineproduction_purchaseorder_items.order_id, "
        . "ciniki_wineproduction_purchaseorder_items.product_id, "
        . "ciniki_wineproduction_purchaseorder_items.sku, "
        . "ciniki_wineproduction_purchaseorder_items.description, "
        . "ciniki_wineproduction_purchaseorder_items.quantity_ordered, "
        . "ciniki_wineproduction_purchaseorder_items.quantity_received, "
        . "ciniki_wineproduction_purchaseorder_items.unit_amount, "
        . "ciniki_wineproduction_purchaseorder_items.taxtype_id "
        . "FROM ciniki_wineproduction_purchaseorder_items "
        . "WHERE ciniki_wineproduction_purchaseorder_items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'items', 'fname'=>'id', 
            'fields'=>array('id', 'order_id', 'product_id', 'sku', 'description', 'quantity_ordered', 'quantity_received', 'unit_amount', 'taxtype_id')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['items']) ) {
        $items = $rc['items'];
        $item_ids = array();
        foreach($items as $iid => $item) {
            $item_ids[] = $item['id'];
        }
    } else {
        $items = array();
        $item_ids = array();
    }

    return array('stat'=>'ok', 'items'=>$items, 'nplist'=>$item_ids);
}
?>
