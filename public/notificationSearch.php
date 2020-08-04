<?php
//
// Description
// -----------
// This method searchs for a Notifications for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Notification for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
// Returns
// -------
//
function ciniki_wineproduction_notificationSearch($ciniki) {
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.notificationSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of notifications
    //
    $strsql = "SELECT ciniki_wineproduction_notifications.id, "
        . "ciniki_wineproduction_notifications.name, "
        . "ciniki_wineproduction_notifications.ntype, "
        . "ciniki_wineproduction_notifications.offset_days, "
        . "ciniki_wineproduction_notifications.status, "
        . "ciniki_wineproduction_notifications.email_time, "
        . "ciniki_wineproduction_notifications.email_subject "
        . "FROM ciniki_wineproduction_notifications "
        . "WHERE ciniki_wineproduction_notifications.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
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
        array('container'=>'notifications', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'ntype', 'offset_days', 'status', 'email_time', 'email_subject')),
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
