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
    
    return array('stat'=>'ok', 'maps'=>$maps);
}
?>
