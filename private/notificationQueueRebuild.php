<?php
//
// Description
// -----------
// Rebuild the queue for a customer based on changes to the notification settings
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wineproduction_notificationQueueRebuild(&$ciniki, $tnid, $args) {

    //
    // Must pass in customer id and ntype, may change in the future
    //
    if( !isset($args['customer_id']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.69', 'msg'=>'No customer specified.'));
    }
    if( !isset($args['ntype']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.84', 'msg'=>'Missing notification type.'));
    }

    if( $args['ntype'] < 100 ) {
        //
        // Load the open orders
        //
        $strsql = "SELECT orders.id, "
            . "orders.status "
            . "FROM ciniki_wineproductions AS orders "
            . "WHERE orders.customer_id = '" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "' "
            . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND orders.status < 60 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'item');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.85', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
        }
        $orders = isset($rc['rows']) ? $rc['rows'] : array();
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'notificationTrigger');
        foreach($orders as $order) {
            $trigger = '';
            if( $order['status'] == 10 ) {
                $trigger = 'entered';
            }
            elseif( $order['status'] == 20 ) {
                $trigger = 'started';
            }
            elseif( $order['status'] == 30 ) {
                $trigger = 'racked';
            }
            elseif( $order['status'] == 40 ) {
                $trigger = 'filtered';
            }
            if( $trigger != '' ) {
                $rc = ciniki_wineproduction_notificationTrigger($ciniki, $tnid, $trigger, $order['id']);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.91', 'msg'=>'Unable to update notifications', 'err'=>$rc['err']));
                }
            }
        }
    }
    else {
        //
        // Load the closed orders in the last year
        //
        $strsql = "SELECT orders.id, "
            . "orders.status "
            . "FROM ciniki_wineproductions AS orders "
            . "WHERE orders.customer_id = '" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "' "
            . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND orders.status = 60 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'item');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.70', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
        }
        $orders = isset($rc['rows']) ? $rc['rows'] : array();

        //
        // Trigger "bottled" on the orders
        //
        foreach($orders as $order) {
            $rc = ciniki_wineproduction_notificationTrigger($ciniki, $tnid, 'bottled', $order['id']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.86', 'msg'=>'Unable to update notifications', 'err'=>$rc['err']));
            }
        }
    }

    return array('stat'=>'ok');
}
?>
