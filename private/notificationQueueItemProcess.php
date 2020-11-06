<?php
//
// Description
// -----------
// Process and send the notification for a queued item
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wineproduction_notificationQueueItemProcess(&$ciniki, $tnid, $queue_id) {

    //
    // Setup the action to perform based on checks and balances
    //
    $action = 'exec';

    //
    // Load the queue item
    //
    $strsql = "SELECT queue.id, "
        . "queue.uuid, "
        . "queue.scheduled_dt, "
        . "queue.notification_id, "
        . "queue.customer_id, "
        . "queue.order_id, "
        . "notifications.name, "
        . "notifications.ntype, "
        . "notifications.status, "
        . "notifications.min_days_from_last, "
        . "notifications.email_time, "
        . "notifications.email_subject, "
        . "notifications.email_content, "
        . "notifications.sms_content, "
        . "orders.product_id, "
        . "orders.order_date, "
        . "orders.start_date, "
        . "orders.rack_date, "
        . "orders.filter_date, "
        . "orders.bottling_date, "
        . "orders.bottle_date, "
        . "products.name AS product_name "
        . "FROM ciniki_wineproduction_notification_queue AS queue "
        . "LEFT JOIN ciniki_wineproduction_notifications AS notifications ON ("
            . "queue.notification_id = notifications.id "
            . "AND notifications.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "LEFT JOIN ciniki_wineproductions AS orders ON ("
            . "queue.order_id = orders.id "
            . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "LEFT JOIN ciniki_products AS products ON ("
            . "orders.product_id = products.id "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE queue.id = '" . ciniki_core_dbQuote($ciniki, $queue_id) . "' "
        . "AND queue.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'item');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.92', 'msg'=>'Unable to load queued notification', 'err'=>$rc['err']));
    }
    if( !isset($rc['item']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.93', 'msg'=>'Unable to find requested queue notification'));
    }
    $notification = $rc['item'];

    //
    // Check to make sure notification still exists
    //
    if( !isset($notification['name']) || $notification['name'] == null ) {
        error_log('WINEPRODUCTION[notification queue:' . $notification['id'] . ',' . $notification['notification_id'] . ',' . $notification['customer_id'] . ']: missing notification');
        goto deletefromqueue;
    }

    //
    // Check to make sure the order still exists
    //
    if( !isset($notification['order_date']) || $notification['order_date'] == null ) {
        error_log('WINEPRODUCTION[notification queue:' . $notification['id'] . ',' . $notification['notification_id'] . ',' . $notification['customer_id'] . ']: missing order');
        goto deletefromqueue;
    }

    //
    // Check to make sure the product still exists
    //
    if( !isset($notification['product_name']) || $notification['product_name'] == null ) {
        error_log('WINEPRODUCTION[notification queue:' . $notification['id'] . ',' . $notification['notification_id'] . ',' . $notification['customer_id'] . ']: missing product ');
        goto deletefromqueue;
    }

    //
    // Check to make sure the subject and content are specified
    //
    if( !isset($notification['email_subject']) || trim($notification['email_subject']) == '' ) {
        error_log('WINEPRODUCTION[notification queue:' . $notification['id'] . ',' . $notification['notification_id'] . ',' . $notification['customer_id'] . ']: missing email subject ');
        goto deletefromqueue;
    }
    if( !isset($notification['email_content']) || trim($notification['email_content']) == '' ) {
        error_log('WINEPRODUCTION[notification queue:' . $notification['id'] . ',' . $notification['notification_id'] . ',' . $notification['customer_id'] . ']: missing email content ');
        goto deletefromqueue;
    }

    //
    // Check to make sure notification is to be processed
    //
    if( $notification['status'] < 10 ) {
        error_log('WINEPRODUCTION[notification queue:' . $notification['id'] . ',' . $notification['notification_id'] . ',' . $notification['customer_id'] . ']: notification inactive');
        goto deletefromqueue;
    }

    //
    // Load the customer emails
    //
    $strsql = "SELECT email "
        . "FROM ciniki_customer_emails "
        . "WHERE customer_id = '" . ciniki_core_dbQuote($ciniki, $notification['customer_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND (flags&0x10) = 0 "       // Make sure they have not blocked all emails
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
    $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'ciniki.wineproduction', 'emails', 'email');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.94', 'msg'=>'Unable to load the list of ', 'err'=>$rc['err']));
    }
    $emails = isset($rc['emails']) ? $rc['emails'] : array();

    //
    // Load the customer name
    //
    $strsql = "SELECT first, last, display_name, company "
        . "FROM ciniki_customers "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $notification['customer_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'customer');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.101', 'msg'=>'Unable to load customer', 'err'=>$rc['err']));
    }
    if( !isset($rc['customer']) ) {
        error_log('WINEPRODUCTION[notification queue:' . $notification['id'] . ',' . $notification['notification_id'] . ',' . $notification['customer_id'] . ']: customer missing');
        goto deletefromqueue;
    }
    $customer = $rc['customer'];
    
    //
    // Load the list of emails already sent for this notification to this customer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'mail', 'hooks', 'objectMessages');
    $rc = ciniki_mail_hooks_objectMessages($ciniki, $tnid, array(
        'object' => 'ciniki.wineproduction.notification',
        'object_id' => $notification['notification_id'],
        'customer_id' => $notification['customer_id'],
        'xml' => 'no',
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.95', 'msg'=>'Unable to check for last message sent', 'err'=>$rc['err']));
    }
    $existing_messages = isset($rc['messages']) ? $rc['messages'] : array();
   
    //
    // Check existing messages
    //
    foreach($existing_messages as $message) {
        //
        // A blank date_sent means it's in the queue, don't add another
        //
        if( $message['date_sent'] == '' ) {
            $days_from_last = 0;
            break;
        } else {
            $date_sent = new DateTime($rc['messages'][0]['date_sent'], new DateTimezone('UTC'));
            $dt = new DateTime('now', new DateTimezone('UTC'));
            $interval = $dt->diff($date_sent, true);
            if( !isset($days_from_last) || $interval->format("%a") < $days_from_last ) {
                $days_from_last = $interval->format("%a");
            }
        }
    }

    //
    // Check if any there needs to be a minimum number of days between notifications to same customer
    //
    if( isset($notification['min_days_from_last']) && $notification['min_days_from_last'] > 0 ) {
        //
        // Check if there has been a message sent and what the date of it was
        //
        if( isset($days_from_last) && $days_from_last < $notification['min_days_from_last'] ) {
            error_log('WINEPRODUCTION[notification queue:' . $notification['id'] . ',' . $notification['notification_id'] . ',' . $notification['customer_id'] . ']: too soon not exceeded min days from last');
            goto deletefromqueue;
        }
    }

    //
    // Double check there wasn't already one sent in the last 24 hours
    //
    if( isset($existing_messages[0]) && isset($days_from_last) && $days_from_last == 0 ) {
        error_log('WINEPRODUCTION[notification queue:' . $notification['id'] . ',' . $notification['notification_id'] . ',' . $notification['customer_id'] . ']: already sent in last 24 hours');
        goto deletefromqueue;
    }

    //
    // Check if there are any more of ntype for today
    // FIXME: Do we need this?  Previous check should catch it in existing messages???
    //

    //
    // Setup variables used in substitutions
    //
    $num_orders = 1;
    $order_list = $notification['product_name'];

    //
    // Check if this should include multiple wine orders
    //
    if( $notification['ntype'] > 10 ) {
        $strsql = "SELECT orders.id, "
            . "orders.product_id, "
            . "orders.order_date, "
            . "orders.start_date, "
            . "orders.rack_date, "
            . "orders.filter_date, "
            . "orders.bottling_date, "
            . "orders.bottle_date, "
            . "products.name AS product_name "
            . "FROM ciniki_wineproductions AS orders "
            . "LEFT JOIN ciniki_products AS products ON ("
                ."orders.product_id = products.id "
                . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "WHERE orders.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND orders.id <> '" . ciniki_core_dbQuote($ciniki, $notification['order_id']) . "' "
            . "AND orders.customer_id = '" . ciniki_core_dbQuote($ciniki, $notification['customer_id']) . "' " 
            . "";
        if( $notification['ntype'] == 20 || $notification['ntype'] == 25 ) {
            $strsql .= "AND orders.start_date = '" . ciniki_core_dbQuote($ciniki, $notification['start_date']) . "' ";
        } elseif( $notification['ntype'] == 50 || $notification['ntype'] == 55 ) {
            $strsql .= "AND orders.rack_date = '" . ciniki_core_dbQuote($ciniki, $notification['rack_date']) . "' ";
        } elseif( $notification['ntype'] == 60 || $notification['ntype'] == 65 || $notification['ntype'] == 70 ) {
            $strsql .= "AND orders.filter_date = '" . ciniki_core_dbQuote($ciniki, $notification['filter_date']) . "' ";
        } elseif( $notification['ntype'] == 80 ) {
            $strsql .= "AND orders.bottling_date = '" . ciniki_core_dbQuote($ciniki, $notification['bottling_date']) . "' ";
        } elseif( $notification['ntype'] >= 100 ) {
            $strsql .= "AND orders.bottle_date = '" . ciniki_core_dbQuote($ciniki, $notification['bottle_date']) . "' ";
        } else {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.99', 'msg'=>'Invalid ntype for sending email'));
        }
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'item');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.100', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
        }
        $orders = isset($rc['rows']) ? $rc['rows'] : array();
        foreach($orders as $order) {
            $num_orders++;
            $order_list .= "\n" . $order['product_name'];
        }
    }
   
    //
    // Create the email
    //
    $subject = $notification['email_subject'];
    $content = $notification['email_content'];

    //
    // Run the substitutions
    //
    $subject = str_replace('{_firstname_}', $customer['first'], $subject);
    $content = str_replace('{_firstname_}', $customer['first'], $content);
    $subject = str_replace('{_orders_}', $order_list, $subject);
    $content = str_replace('{_orders_}', $order_list, $content);

    $subject = str_replace('{_numorders_}', ($num_orders > 1 ? 's' : ''), $subject);
    $content = str_replace('{_numorders_}', ($num_orders > 1 ? 's' : ''), $content);

    //
    // Send the email
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'mail', 'hooks', 'addMessage');
    foreach($emails as $email) {
        $rc = ciniki_mail_hooks_addMessage($ciniki, $tnid, array(
            'object' => 'ciniki.wineproduction.notification',
            'object_id' => $notification['notification_id'],
            'customer_id' => $notification['customer_id'],
            'customer_email' => $email,
            'customer_name' => $customer['display_name'],
            'status' => ($notification['status'] == 20 ? 10 : 7),   // Put in pending if not setup for auto send
            'subject' => $subject,
            'html_content' => $content,
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.102', 'msg'=>'Unable to add', 'err'=>$rc['err']));
        }
    }

    //
    // Goto point when no message sent, but queue should be removed
    //
    deletefromqueue:

    //
    // Delete the queue item
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    $rc = ciniki_core_objectDelete($ciniki, $tnid, 'ciniki.wineproduction.notification_queue', $notification['id'], $notification['uuid'], 0x04);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.96', 'msg'=>'Unable to remove notification queue', 'err'=>$rc['err']));
    } 

    return array('stat'=>'ok');
}
?>
