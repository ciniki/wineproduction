<?php
//
// Description
// -----------
// Get the notifications the customer is subscribed to
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wineproduction_customerNotifications(&$ciniki, $tnid, $customer_id) {
   
    //
    // Setup the notification array
    //
    $notifications = array(
        10 => array('ntype' => 10, 'label' => 'Welcome Email'),
        20 => array('ntype' => 20, 'label' => 'Started Notifications'),
        25 => array('ntype' => 25, 'label' => 'Fermenting Information'),
        50 => array('ntype' => 50, 'label' => 'Racked Notifications'),
        55 => array('ntype' => 55, 'label' => 'Racking Information'),
        60 => array('ntype' => 60, 'label' => 'Filtered Notifications'),
        65 => array('ntype' => 65, 'label' => 'Filtering Information'),
        80 => array('ntype' => 80, 'label' => 'Bottling Appointment Reminders'),
        100 => array('ntype' => 100, 'label' => 'Care & Storage Reminders'),
        120 => array('ntype' => 120, 'label' => 'Wine Information'),
        130 => array('ntype' => 130, 'label' => 'Wine Recipes'),
        150 => array('ntype' => 150, 'label' => 'Wine Deals & Coupons'),
        );

    //
    // Setup defaults
    //
    foreach($notifications as $nid => $notification) {
        $notifications[$nid]['flags'] = 0;
        $notifications[$nid]['subscription_id'] = 0;
        $notifications[$nid]['status_text'] = 'Unsubscribed';
    }

    //
    // Load the notifications settings for the customer
    //
    $strsql = "SELECT id, ntype, flags "
        . "FROM ciniki_wineproduction_notification_customers "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND customer_id = '" . ciniki_core_dbQuote($ciniki, $customer_id) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'customer');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.63', 'msg'=>'Unable to load subs', 'err'=>$rc['err']));
    }
    foreach($rc['rows'] as $row) {
        $notifications[$row['ntype']]['flags'] = $row['flags'];
        $notifications[$row['ntype']]['subscription_id'] = $row['id'];
        if( ($row['flags']&0x10) == 0x10 ) {
            $notifications[$row['ntype']]['status_text'] = 'Removed';
        } elseif( ($row['flags']&0x01) == 0x01 ) {
            $notifications[$row['ntype']]['status_text'] = 'Subscribed';
        }
    }

    return array('stat'=>'ok', 'notifications'=>$notifications);
}
?>
