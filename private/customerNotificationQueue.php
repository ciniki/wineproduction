<?php
//
// Description
// -----------
// Get the list of queued notifications for the customer.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wineproduction_customerNotificationQueue(&$ciniki, $tnid, $customer_id) {
  
    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    //
    // Get the list of upcoming notifications for the customer
    //
    $strsql = "SELECT queue.id, "
        . "queue.uuid, "
        . "queue.scheduled_dt AS scheduled_date, "
        . "queue.scheduled_dt AS scheduled_time, "
        . "notifications.name, "
        . "notifications.email_subject, "
        . "orders.order_date, "
        . "IFNULL(products.name, '') AS product_name "
        . "FROM ciniki_wineproduction_notification_queue AS queue "
        . "INNER JOIN ciniki_wineproduction_notifications AS notifications ON ("
            . "queue.notification_id = notifications.id "
            . "AND notifications.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "INNER JOIN ciniki_wineproductions AS orders ON ("
            . "queue.order_id = orders.id "
            . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "LEFT JOIN ciniki_products AS products ON ("
            . "orders.product_id = products.id "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE queue.customer_id = '" . ciniki_core_dbQuote($ciniki, $customer_id) . "' "
        . "AND queue.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "ORDER BY queue.scheduled_dt, notifications.ntype "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'queue', 'fname'=>'id', 
            'fields'=>array( 'id', 'uuid', 'scheduled_date', 'scheduled_time', 'name', 'email_subject', 'order_date', 'name'),
            'utctotz'=>array(
                'scheduled_date'=>array('timezone'=>$intl_timezone, 'format'=>'M j, Y'),
                'scheduled_time'=>array('timezone'=>$intl_timezone, 'format'=>'g:i a'),
                'order_date'=>array('timezone'=>'UTC', 'format'=>'M j, Y'),
                ),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.71', 'msg'=>'Unable to load notification_queue', 'err'=>$rc['err']));
    }
    $queue = isset($rc['queue']) ? $rc['queue'] : array();

    return array('stat'=>'ok', 'queue'=>$queue);
}
?>
