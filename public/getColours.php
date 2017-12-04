<?php
//
// Description
// -----------
// This function will return the list of available colours for this tenant.
//
// Info
// ----
// Status:          started
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get the colours for.
// 
// Returns
// -------
//
function ciniki_wineproduction_getColours($ciniki) {
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
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.getColours'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

//  return array('stat'=>'ok', 'colours'=>array(
//      array('colour'=>array('id'=>'*', 'name'=>'Unknown', 'code'=>'ffffff')),
//      array('colour'=>array('id'=>'yellow', 'name'=>'Yellow', 'code'=>'ffff00')),
//      array('colour'=>array('id'=>'darkblue', 'name'=>'Dark Blue', 'code'=>'0033ff')),
//      array('colour'=>array('id'=>'darkgreen', 'name'=>'Dark Green', 'code'=>'006600')),
//      array('colour'=>array('id'=>'brightred', 'name'=>'Bright Red', 'code'=>'ff0000')),
//      array('colour'=>array('id'=>'tan', 'name'=>'Tan', 'code'=>'ffcc88')),
//      array('colour'=>array('id'=>'lightred', 'name'=>'Light red', 'code'=>'660000')),
//      array('colour'=>array('id'=>'lightblue', 'name'=>'Light Blue', 'code'=>'00ccff')),
//      array('colour'=>array('id'=>'orange', 'name'=>'Orange', 'code'=>'ff8800')),
//      array('colour'=>array('id'=>'black', 'name'=>'Black', 'code'=>'000000')),
//      array('colour'=>array('id'=>'purple', 'name'=>'Purple', 'code'=>'993399')),
//      array('colour'=>array('id'=>'brightgren', 'name'=>'Bright Green', 'code'=>'00ff00')),
//      array('colour'=>array('id'=>'brightpink', 'name'=>'Bright Pink', 'code'=>'f660ab')),
//      ));

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'getColours');
    $colours = ciniki_wineproduction__getColours($ciniki, $args['tnid']);
    return array('stat'=>'ok', 'colours'=>$colours);
}
?>
