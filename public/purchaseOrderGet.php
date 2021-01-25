<?php
//
// Description
// ===========
// This method will return all the information about an purchase order.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the purchase order is attached to.
// order_id:          The ID of the purchase order to get the details for.
//
// Returns
// -------
//
function ciniki_wineproduction_purchaseOrderGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'order_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Purchase Order'),
        'supplier_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Supplier'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.purchaseOrderGet');
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

    //
    // Return default for new Purchase Order
    //
    if( $args['order_id'] == 0 ) {
        $strsql = "SELECT MAX(po_number) as po_number "  
            . "FROM ciniki_wineproduction_purchaseorders "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'item');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.170', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
        }
        $po_number = (isset($rc['item']['po_number']) ? ($rc['item']['po_number'] + 1) : '1');

        $dt = new DateTime('now', new DateTimezone($intl_timezone));
        $purchaseorder = array('id'=>0,
            'supplier_id'=>(isset($args['supplier_id']) ? $args['supplier_id'] : 0),
            'po_number'=>$po_number,
            'status'=>10,
            'date_ordered'=>$dt->format("Y-m-d"),
            'date_received'=>'',
            'notes'=>'',
        );


        //
        // Get the list of items for this supplier that need to be ordered
        //
        if( isset($args['supplier_id']) && $args['supplier_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
            $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wineproduction.purchaseorder', $purchaseorder, 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
                return $rc;
            }
            $purchaseorder['id'] = $rc['id'];

            //
            // Update the list of items from inventory/wines to be started
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'purchaseOrderPullItems');
            $rc = ciniki_wineproduction_purchaseOrderPullItems($ciniki, $args['tnid'], $purchaseorder['id']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
        $purchaseorder['date_ordered'] = $dt->format("M d, Y");
    }

    //
    // Get the details for an existing Purchase Order
    //
    else {
        $strsql = "SELECT ciniki_wineproduction_purchaseorders.id, "
            . "ciniki_wineproduction_purchaseorders.supplier_id, "
            . "ciniki_wineproduction_purchaseorders.po_number, "
            . "ciniki_wineproduction_purchaseorders.status, "
            . "ciniki_wineproduction_purchaseorders.date_ordered, "
            . "ciniki_wineproduction_purchaseorders.date_received, "
            . "ciniki_wineproduction_purchaseorders.notes "
            . "FROM ciniki_wineproduction_purchaseorders "
            . "WHERE ciniki_wineproduction_purchaseorders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wineproduction_purchaseorders.id = '" . ciniki_core_dbQuote($ciniki, $args['order_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
            array('container'=>'purchaseorders', 'fname'=>'id', 
                'fields'=>array('id', 'supplier_id', 'po_number', 'status', 'date_ordered', 'date_received', 'notes'),
                'utctotz'=>array('date_ordered'=>array('timezone'=>'UTC', 'format'=>$date_format),
                    'date_received'=>array('timezone'=>'UTC', 'format'=>$date_format)),                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.177', 'msg'=>'Purchase Order not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['purchaseorders'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.178', 'msg'=>'Unable to find Purchase Order'));
        }
        $purchaseorder = $rc['purchaseorders'][0];
    }

    //
    // Load items
    //
    if( $purchaseorder['id'] > 0 ) {
        $strsql = "SELECT items.id, "
            . "items.product_id, "
            . "IF(items.product_id > 0, products.name, items.description) AS description, "
            . "items.quantity_ordered, "
            . "items.quantity_received, "
            . "items.unit_amount, "
            . "products.inventory_current_num "
            . "FROM ciniki_wineproduction_purchaseorder_items AS items "
            . "LEFT JOIN ciniki_wineproduction_products AS products ON ("
                . "items.product_id = products.id "
                . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") "
            . "WHERE items.order_id = '" . ciniki_core_dbQuote($ciniki, $purchaseorder['id']) . "' "
            . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY items.description "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
            array('container'=>'items', 'fname'=>'id', 
                'fields'=>array('id', 'product_id', 'description', 
                    'quantity_ordered', 'quantity_received', 'unit_amount', 'inventory_current_num'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.189', 'msg'=>'Unable to load items', 'err'=>$rc['err']));
        }
        $purchaseorder['items'] = isset($rc['items']) ? $rc['items'] : array();
        foreach($purchaseorder['items'] as $iid => $item) {
            $purchaseorder['items'][$iid]['unit_amount_display'] = '$' . number_format($item['unit_amount'], 2);
            $purchaseorder['items'][$iid]['total_amount_display'] = '$' . number_format(($item['unit_amount']*$item['quantity_ordered']), 2);
        }
    }

    //
    // Get the number of entered orders (unstarted) for each wine
    //
    $strsql = "SELECT products.id, "
        . "products.name, "
        . "COUNT(orders.id) AS num_orders "
        . "FROM ciniki_wineproduction_products AS products "
        . "INNER JOIN ciniki_wineproductions AS orders ON ("
            . "products.id = orders.product_id "
            . "AND orders.status = 10 "
            . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE products.supplier_id = '" . ciniki_core_dbQuote($ciniki, $purchaseorder['supplier_id']) . "' "
        . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND products.status < 60 "
        . "GROUP BY products.id "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'num_orders')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.194', 'msg'=>'Unable to load products', 'err'=>$rc['err']));
    }

    $products = isset($rc['products']) ? $rc['products'] : array();
    foreach($purchaseorder['items'] as $iid => $item) {
        if( isset($products[$item['product_id']]) ) {
            $purchaseorder['items'][$iid]['num_orders'] = $products[$item['product_id']]['num_orders'];
        } else {
            $purchaseorder['items'][$iid]['num_orders'] = '';
        }
    }
    
                

    $rsp = array('stat'=>'ok', 'purchaseorder'=>$purchaseorder);

    //
    // Get the list of suppliers
    //
    $strsql = "SELECT suppliers.id, suppliers.name, suppliers.po_email, suppliers.po_name_address "
        . "FROM ciniki_wineproduction_suppliers AS suppliers "
        . "WHERE suppliers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY suppliers.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'suppliers', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'po_email', 'po_name_address')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.113', 'msg'=>'Unable to load suppliers', 'err'=>$rc['err']));
    }
    $rsp['suppliers'] = isset($rc['suppliers']) ? $rc['suppliers'] : array();

    $rsp['purchaseorder']['supplier_details'] = array(
        array('label'=>'Name/Address', 'details'=>''),
        array('label'=>'Email', 'details'=>''),
        );
    foreach($rsp['suppliers'] as $supplier) {
        if( $supplier['id'] == $purchaseorder['supplier_id'] ) {
            $rsp['purchaseorder']['supplier_details'] = array(
                array('label'=>'Name/Address', 'details'=>$supplier['po_name_address']),
                array('label'=>'Email', 'details'=>$supplier['po_email']),
                );
        }
    }

    return $rsp;
}
?>
