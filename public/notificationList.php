<?php
//
// Description
// -----------
// This method will return the list of Notifications for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Notification for.
//
// Returns
// -------
//
function ciniki_wineproduction_notificationList($ciniki) {
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.notificationList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
        
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
    // Get the list of notifications
    //
    $strsql = "SELECT ciniki_wineproduction_notifications.id, "
        . "ciniki_wineproduction_notifications.name, "
        . "ciniki_wineproduction_notifications.ntype, "
        . "ciniki_wineproduction_notifications.ntype AS ntype_text, "
        . "ciniki_wineproduction_notifications.offset_days, "
        . "ciniki_wineproduction_notifications.status, "
        . "ciniki_wineproduction_notifications.status AS status_text, "
        . "TIME_FORMAT(ciniki_wineproduction_notifications.email_time, '%l:%i %p') AS email_time, "
        . "ciniki_wineproduction_notifications.email_subject "
        . "FROM ciniki_wineproduction_notifications "
        . "WHERE ciniki_wineproduction_notifications.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY ntype, offset_days "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'notifications', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'ntype', 'ntype_text', 'offset_days', 
                'status', 'status_text', 'email_time', 'email_subject',
                ),
            'maps'=>array(
                'ntype_text'=>$maps['notification']['ntype'],
                'status_text'=>$maps['notification']['status'],
                ),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['notifications']) ) {
        $notifications = $rc['notifications'];
        $notification_ids = array();
        foreach($notifications as $iid => $notification) {
            $notification_ids[] = $notification['id'];
        }
    } else {
        $notifications = array();
        $notification_ids = array();
    }

    return array('stat'=>'ok', 'notifications'=>$notifications, 'nplist'=>$notification_ids);
}
?>
