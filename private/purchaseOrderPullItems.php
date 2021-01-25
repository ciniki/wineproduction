<?php
//
// Description
// -----------
// This function will compare whats been order with what is in inventory 
// and add to purchase order if required.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wineproduction_purchaseOrderPullItems(&$ciniki, $tnid, $order_id) {

    //
    // Load the purchase order and existing items
    //
    $strsql = "SELECT po.id, "
        . "po.status, "
        . "po.supplier_id, "
        . "items.product_id, "
        . "items.id AS item_id, "
        . "items.quantity_ordered "
        . "FROM ciniki_wineproduction_purchaseorders AS po "
        . "LEFT JOIN ciniki_wineproduction_purchaseorder_items AS items ON ("
            . "po.id = items.order_id "
            . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE po.id = '" . ciniki_core_dbQuote($ciniki, $order_id) . "' "
        . "AND po.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'po', 'fname'=>'id', 'fields'=>array('id', 'status', 'supplier_id')),
        array('container'=>'items', 'fname'=>'product_id', 'fields'=>array('id'=>'item_id', 'quantity_ordered')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.175', 'msg'=>'Unable to load order', 'err'=>$rc['err']));
    }
    if( !isset($rc['po'][$order_id]) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.176', 'msg'=>'Order not found'));
    }

    //
    // FIXME: Restrict on add/update only 1 product on each PO
        // - update purchaseOrderAdd, purchaseOrderUpdate
    //
    $purchaseorder = $rc['po'][$order_id];
    $ordered = isset($purchaseorder['items']) ? $purchaseorder['items'] : array();

    //
    // Load the ordered wines for the supplier and current inventory
    //
    $strsql = "SELECT products.id, "
        . "products.name, "
        . "products.cost, "
        . "products.taxtype_id, "
        . "products.inventory_current_num, "
        . "COUNT(orders.id) AS num_required "
        . "FROM ciniki_wineproduction_products AS products "
        . "INNER JOIN ciniki_wineproductions AS orders ON ("
            . "products.id = orders.product_id "
            . "AND orders.status = 10 "
            . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE products.supplier_id = '" . ciniki_core_dbQuote($ciniki, $purchaseorder['supplier_id']) . "' "
        . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "GROUP BY products.id "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'cost', 'taxtype_id', 'inventory_current_num', 'num_required')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.174', 'msg'=>'Unable to load products', 'err'=>$rc['err']));
    }
    $required = isset($rc['products']) ? $rc['products'] : array();


    //
    // Check existing order items if anything needs to be updated
    //
    foreach($ordered as $product_id => $item) {
        //
        // Check if order item is in the required list
        //
        if( isset($required[$product_id]) ) {
            if( $required[$product_id]['inventory_current_num'] < 0 ) {
                $qty_required = $required[$product_id]['num_required'];
            } else {
                $qty_required = ($required[$product_id]['num_required'] - $required[$product_id]['inventory_current_num']);
            }
            if( $qty_required > $item['quantity_ordered'] ) {
                //
                // Update the quantity ordered
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
                $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wineproduction.purchaseorderitem', $item['id'], array(
                    'quantity_ordered' => $qty_required
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.187', 'msg'=>'Unable to update the purchaseorderitem'));
                }
            }
        }
    }

    //
    // Check for products that don't exist in the order yet
    //
    foreach($required as $product_id => $item) {
        if( !isset($ordered[$product_id]) ) {
            if( $item['inventory_current_num'] < 0 ) {
                $qty_required = $item['num_required'];
            } else {
                $qty_required = ($item['num_required'] - $item['inventory_current_num']);
            }
            //
            // Add item to order
            //
            error_log("Add: " . $product_id);
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
            $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wineproduction.purchaseorderitem', array(
                'order_id' => $order_id,
                'product_id' => $product_id,
                'description' => $item['name'],
                'quantity_ordered' => $qty_required,
                'quantity_received' => 0,
                'unit_amount' => $item['cost'],
                'taxtype_id' => $item['taxtype_id'],
                ), 0x04);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.188', 'msg'=>'Unable to add the purchaseorderitem'));
            }
            
        }

    }

    return array('stat'=>'ok');
}
?>
