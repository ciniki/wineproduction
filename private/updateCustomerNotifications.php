<?php
//
// Description
// -----------
//
// Info
// ----
// Status:          defined
//
// Arguments
// ---------
// user_id:         The user making the request
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_wineproduction_updateCustomerNotifications($ciniki, $tnid, $customer_id, $subs, $unsubs) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'customerNotifications');
    $rc = ciniki_wineproduction_customerNotifications($ciniki, $tnid, $customer_id);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.66', 'msg'=>'Unable to load customer notifications', 'err'=>$rc['err']));
    }
    $notifications = isset($rc['notifications']) ? $rc['notifications'] : array();

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');

    foreach($notifications as $notification) {
        
        //
        // Check if they are to be unsubscribed from this notification
        //
        if( in_array($notification['ntype'], $unsubs) ) {
            // 
            // No record exists, it needs to be created so they can be marked
            // as removed and will not be readded
            //
            if( $notification['subscription_id'] == 0 ) {
                $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wineproduction.notification_customer', array(
                    'customer_id'=>$customer_id, 'ntype'=>$notification['ntype'], 'flags'=>0x10), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            } 
            //
            // Notification exists, make sure they are marked as removed
            //
            elseif( ($notification['flags']&0x11) != 0x10 ) {
                $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wineproduction.notification_customer', 
                    $notification['subscription_id'], array('flags'=>(($notification['flags']&~0x03)|0x10)), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }

            //
            // Get the list from the queue and delete
            //
            $strsql = "SELECT queue.id, queue.uuid "
                . "FROM ciniki_wineproduction_notification_queue AS queue "
                . "INNER JOIN ciniki_wineproduction_notifications AS notifications ON ("
                    . "queue.notification_id = notifications.id "
                    . "AND notifications.ntype = '" . ciniki_core_dbQuote($ciniki, $notification['ntype']) . "' "
                    . "AND notifications.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                    . ") "
                . "WHERE queue.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "AND queue.customer_id = '" . ciniki_core_dbQuote($ciniki, $customer_id) . "' "
                . "";
            $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'queue');
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.90', 'msg'=>'Unable to load existing queue', 'err'=>$rc['err']));
            }
            $rows = isset($rc['rows']) ? $rc['rows'] : array();
            foreach($rows as $row) {
                $rc = ciniki_core_objectDelete($ciniki, $tnid, 'ciniki.wineproduction.notification_queue', $row['id'], $row['uuid'], 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.67', 'msg'=>'Unable to remove notification queue', 'err'=>$rc['err']));
                }
            }
        }
        //
        // Check if to be subscribed
        //
        elseif( in_array($notification['ntype'], $subs) ) {
            // 
            // No record exists, it needs to be created so they can be marked
            // as removed and will not be readded
            //
            if( $notification['subscription_id'] == 0 ) {
                $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wineproduction.notification_customer', array(
                    'customer_id'=>$customer_id, 'ntype'=>$notification['ntype'], 'flags'=>0x01), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            } 
            //
            // Notification exists, make sure they are marked as removed
            //
            elseif( ($notification['flags']&0x11) != 0x01 ) {
                $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wineproduction.notification_customer', 
                    $notification['subscription_id'], array('flags'=>(($notification['flags']&~0x10)|0x01)), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }

            //
            // Rebuild queues for open orders
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'notificationQueueRebuild');
            $rc = ciniki_wineproduction_notificationQueueRebuild($ciniki, $tnid, array(
                'customer_id'=>$customer_id,
                'ntype' => $notification['ntype'],
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.68', 'msg'=>'Unable to rebuild notification queue', 'err'=>$rc['err']));
            }
        }
    }

    return array('stat'=>'ok');
}
?>
