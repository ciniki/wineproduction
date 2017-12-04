<?php
//
// Description
// -----------
// This function will validate the user making the request has the 
// proper permissions to access or change the data.  This function
// must be called by all public API functions to ensure security.
//
// Info
// ----
// Status:          beta
//
// Arguments
// ---------
// tnid:         The ID of the tenant the request is for.
// 
// Returns
// -------
//
function ciniki_wineproduction_checkAccess($ciniki, $tnid, $method) {
    //
    // Check if the tenant is active and the module is enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'checkModuleAccess');
    $rc = ciniki_tenants_checkModuleAccess($ciniki, $tnid, 'ciniki', 'wineproduction');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( !isset($rc['ruleset']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.6', 'msg'=>'No permissions granted'));
    }
    $modules = $rc['modules'];

    //
    // Load the rulesets for this module
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'getRulesets');
    $rulesets = ciniki_wineproduction_getRuleSets($ciniki);

    //
    // Sysadmins are allowed full access
    //
    if( ($ciniki['session']['user']['perms'] & 0x01) == 0x01 ) {
        return array('stat'=>'ok');
    }

    //
    // Check to see if the ruleset is valid
    //
    if( !isset($rulesets[$rc['ruleset']]) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.7', 'msg'=>'Access denied.'));
    }
    $ruleset = $rc['ruleset'];

    // 
    // Get the rules for the specified method
    //
    $rules = array();
    if( isset($rulesets[$ruleset]['methods']) && isset($rulesets[$ruleset]['methods'][$method]) ) {
        $rules = $rulesets[$ruleset]['methods'][$method];
    } elseif( isset($rulesets[$ruleset]['default']) ) {
        $rules = $rulesets[$ruleset]['default'];
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.8', 'msg'=>'Access denied.'));
    }

    //
    // Apply the rules.  Any matching rule will allow access.
    //

    //
    // If tenant_group specified, check the session user in the tenant_users table.
    //
    if( isset($rules['permission_groups']) && $rules['permission_groups'] > 0 ) {
        //
        // If the user is attached to the tenant AND in the one of the accepted permissions group, they will be granted access
        //
        $strsql = "SELECT tnid, user_id FROM ciniki_tenant_users "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND user_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['session']['user']['id']) . "' "
            . "AND status = 10 "
            . "AND CONCAT_WS('.', package, permission_group) IN ('" . implode("','", $rules['permission_groups']) . "') "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'user');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.9', 'msg'=>'Access denied.', 'err'=>$rc['err']));
        }
        
        //
        // If the user has permission, return ok
        //
        if( isset($rc['rows']) && isset($rc['rows'][0]) 
            && $rc['rows'][0]['user_id'] > 0 && $rc['rows'][0]['user_id'] == $ciniki['session']['user']['id'] ) {
            return array('stat'=>'ok');
        }
    }

    //
    // If all tests passed, then return ok
    //
    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.10', 'msg'=>'Access denied.'));
}
?>
