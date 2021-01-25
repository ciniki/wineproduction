<?php
//
// Description
// ===========
// This method will return all the information about an purchase order item.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the purchase order item is attached to.
// item_id:          The ID of the purchase order item to get the details for.
//
// Returns
// -------
//
function ciniki_wineproduction_purchaseOrderItemGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'item_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Purchase Order Item'),
        'order_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Order'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.purchaseOrderItemGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    $strsql = "SELECT id, supplier_id "
        . "FROM ciniki_wineproduction_purchaseorders "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['order_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'order');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.191', 'msg'=>'Unable to load order', 'err'=>$rc['err']));
    }
    if( !isset($rc['order']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.192', 'msg'=>'Unable to find order'));
    }
    $order = isset($rc['order']) ? $rc['order'] : array();

    //
    // Return default for new Purchase Order Item
    //
    if( $args['item_id'] == 0 ) {
        $item = array('id'=>0,
            'product_id'=>'0',
            'description'=>'',
            'quantity_ordered'=>'',
            'quantity_received'=>'',
            'unit_amount'=>'',
            'taxtype_id'=>'0',
        );
    }

    //
    // Get the details for an existing Purchase Order Item
    //
    else {
        $strsql = "SELECT ciniki_wineproduction_purchaseorder_items.id, "
            . "ciniki_wineproduction_purchaseorder_items.order_id, "
            . "ciniki_wineproduction_purchaseorder_items.product_id, "
            . "ciniki_wineproduction_purchaseorder_items.description, "
            . "ciniki_wineproduction_purchaseorder_items.quantity_ordered, "
            . "ciniki_wineproduction_purchaseorder_items.quantity_received, "
            . "ciniki_wineproduction_purchaseorder_items.unit_amount, "
            . "ciniki_wineproduction_purchaseorder_items.taxtype_id "
            . "FROM ciniki_wineproduction_purchaseorder_items "
            . "WHERE ciniki_wineproduction_purchaseorder_items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wineproduction_purchaseorder_items.id = '" . ciniki_core_dbQuote($ciniki, $args['item_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
            array('container'=>'items', 'fname'=>'id', 
                'fields'=>array('order_id', 'product_id', 'description', 'quantity_ordered', 'quantity_received', 'unit_amount', 'taxtype_id'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.182', 'msg'=>'Purchase Order Item not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['items'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.183', 'msg'=>'Unable to find Purchase Order Item'));
        }
        $item = $rc['items'][0];
        $item['unit_amount'] = '$' . number_format($item['unit_amount'], 2);
    }

    //
    // Get the list of active products for this supplier
    //
    $strsql = "SELECT id, name "
        . "FROM ciniki_wineproduction_products "
        . "WHERE supplier_id = '" . ciniki_core_dbQuote($ciniki, $order['supplier_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 60 "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'id', 'fields'=>array('id', 'name')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.190', 'msg'=>'Unable to load products', 'err'=>$rc['err']));
    }
    $products = isset($rc['products']) ? $rc['products'] : array();
    array_unshift($products, array('id'=>0, 'name'=>'Unlisted Product'));


    return array('stat'=>'ok', 'item'=>$item, 'products'=>$products);
}
?>
