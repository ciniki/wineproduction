<?php
//
// Description
// ===========
// This method will return all the information about an notification.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the notification is attached to.
// notification_id:          The ID of the notification to get the details for.
//
// Returns
// -------
//
function ciniki_wineproduction_notificationGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'notification_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Notification'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.notificationGet');
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
    // Return default for new Notification
    //
    if( $args['notification_id'] == 0 ) {
        $notification = array('id'=>0,
            'name'=>'',
            'ntype'=>'',
            'offset_days'=>'0',
            'min_days_from_last'=>'1',
            'status'=>'10',
            'email_time'=>'',
            'email_subject'=>'',
            'email_content'=>'',
            'sms_content'=>'',
        );
    }

    //
    // Get the details for an existing Notification
    //
    else {
        $strsql = "SELECT ciniki_wineproduction_notifications.id, "
            . "ciniki_wineproduction_notifications.name, "
            . "ciniki_wineproduction_notifications.ntype, "
            . "ciniki_wineproduction_notifications.offset_days, "
            . "ciniki_wineproduction_notifications.min_days_from_last, "
            . "ciniki_wineproduction_notifications.status, "
            . "TIME_FORMAT(ciniki_wineproduction_notifications.email_time, '%l:%i %p') AS email_time, "
            . "ciniki_wineproduction_notifications.email_subject, "
            . "ciniki_wineproduction_notifications.email_content, "
            . "ciniki_wineproduction_notifications.sms_content "
            . "FROM ciniki_wineproduction_notifications "
            . "WHERE ciniki_wineproduction_notifications.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wineproduction_notifications.id = '" . ciniki_core_dbQuote($ciniki, $args['notification_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
            array('container'=>'notifications', 'fname'=>'id', 
                'fields'=>array('name', 'ntype', 'offset_days', 'min_days_from_last', 'status', 
                    'email_time', 'email_subject', 'email_content', 'sms_content',
                    ),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.45', 'msg'=>'Notification not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['notifications'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.46', 'msg'=>'Unable to find Notification'));
        }
        $notification = $rc['notifications'][0];
    }

    return array('stat'=>'ok', 'notification'=>$notification);
}
?>
