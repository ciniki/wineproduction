<?php
//
// Description
// ===========
// 
//
// Arguments
// =========
// 
// Returns
// =======
// <rsp stat="ok" />
//
function ciniki_wineproduction_cron_jobs(&$ciniki) {
    ciniki_cron_logMsg($ciniki, 0, array('code'=>'0', 'msg'=>'Checking for wine production jobs', 'severity'=>'5'));

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'private', 'invoiceAddFromRecurring');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'checkModuleAccess');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'notificationQueueItemProcess');

    //
    // Check for notification queue items that are ready to be sent
    //
    $strsql = "SELECT id, tnid "
        . "FROM ciniki_wineproduction_notification_queue "
        . "WHERE scheduled_dt <= UTC_TIMESTAMP() "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'item');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.73', 'msg'=>'Unable to check notification queue', 'err'=>$rc['err']));
    }
    $rows = isset($rc['rows']) ? $rc['rows'] : array();

    foreach($rows as $row) {
        $rc = ciniki_wineproduction_notificationQueueItemProcess($ciniki, $row['tnid'], $row['id']);
        if( $rc['stat'] != 'ok' ) {
            ciniki_cron_logMsg($ciniki, $row['tnid'], array('code'=>'ciniki.wineproduction.74', 'msg'=>'Unable to process notification queue item',
                'cron_id'=>0, 'severity'=>50, 'err'=>$rc['err'],
                ));
        }

    }
    

    return array('stat'=>'ok');
}
?>
