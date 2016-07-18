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
function ciniki_wineproduction_statusMaps($ciniki) {
    
    $status_maps = array(
        '10'=>'Ordered',
        '20'=>'Started',
        '25'=>'SG Ready',
        '30'=>'Racked',
        '40'=>'Filtered',
        '60'=>'Bottled',
        '100'=>'Removed',
        );
    
    return array('stat'=>'ok', 'maps'=>$status_maps);
}
?>
