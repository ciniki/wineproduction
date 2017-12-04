<?php
//
// Description
// -----------
// This function will return the list of colours available to the wine production module.
// In the future, these values can be pulled from the database.
//
// Info
// ----
// Status:          beta
//
// Arguments
// ---------
// ciniki:
// tnid:     The ID of the tenant to get the settings for.
// 
// Returns
// -------
//
function ciniki_wineproduction__getSettings($ciniki, $tnid) {

    date_default_timezone_set('America/Toronto');

    //
    // Grab the settings for the tenant from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    return ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'tnid', $args['tnid'], 'ciniki.wineproduction', 'settings', '');
}
