<?php
//
// Description
// -----------
// This function returns the array of status text for ciniki_wineproductions.status.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_wineproduction_maps(&$ciniki) {
    
    //
    // Build the maps object
    //
    $maps = array();
    $maps['wineproduction'] = array(
        'status'=>array(
            '10'=>'Ordered',
            '20'=>'Started',
            '25'=>'SG Ready',
            '30'=>'Racked',
            '40'=>'Filtered',
            '50'=>'Shared',
            '60'=>'Bottled',
            '100'=>'Removed',
            ),
        'bottling_status'=>array(
            '0'=>'',
            '1'=>'Reschedule',
            '2'=>'Pull',
            '128'=>'Ready',
            ),
    );
    $maps['notification'] = array(
        'ntype'=>array(
            '10'=>'New Customer',
            '20'=>'Started',
            '25'=>'Post Started Education',
            '40'=>'SG Reading',
            '50'=>'Racked',
            '55'=>'Post Racked Education',
            '60'=>'Filtered',
            '65'=>'Post Filtered Education',
            '70'=>'No Bottling Appointment',
            '80'=>'Upcoming Bottling Reminder',
            '100'=>'Post Bottling Reminder',
            '120'=>'Post Bottling Education',
            '130'=>'Post Bottling Recipes',
            '150'=>'Post Bottling No Order Deals',
            ),
        'status'=>array(
            '0'=>'Inactive',
            '10'=>'Require Approval',
            '20'=>'Auto Send',
            ),
    );
    
    return array('stat'=>'ok', 'maps'=>$maps);
}
?>
