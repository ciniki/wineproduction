<?php
//
// Description
// -----------
// This method will apply one of multiple actions to a wineproduction order.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the order belongs to.
// wineproduction_id:   The ID of the wineproduction order to take action on.
// action:              The action to be performed.
//
//                      - Started - Change the status to 20
//                      - SGRead - Change the status to 25
//                      - Racked - Change the status to 30
//                      - Filtered - Change the status to 40
//                      - Filter Today - Change the filter_date to today
//                      - Bottled - Change the status to 60
//
// sg_reading:          (optional) The value for the SG Reading if action is SGRead.
// kit_length:          (optional) The number of days the wine needs to be racked for, must be specified if action is Racked.
// batch_code:          (optional) The batch code from the kit box.  This should be specified if the action is Started.
//
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_wineproduction_actionOrder(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'wineproduction_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Order'), 
        'action'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Action'), 
        'sg_reading'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'SG Reading'), 
        'kit_length'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Racking Length'), 
        'batch_code'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Batch Code'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.actionOrder'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Grab the settings for the business from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc =  ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'business_id', $args['business_id'], 'ciniki.wineproduction', 'settings', '');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $settings = $rc['settings'];

    //
    // FIXME: Add timezone information
    //
    date_default_timezone_set('America/Toronto');
    $todays_date = strftime("%Y-%m-%d");

    //  
    // Turn off autocommit
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.wineproduction');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // The wine was just started, setup the racking date automatically
    //
    $strsql = "";
    if( $args['action'] == 'Started' ) {
        $strsql = "UPDATE ciniki_wineproductions SET start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "', "
            . "batch_code = '" . ciniki_core_dbQuote($ciniki, $args['batch_code']) . "', "
            . "";
        $racking_autoschedule = "racking.autoschedule.madeon" . strtolower(date('D', strtotime($todays_date)));
        if( isset($settings[$racking_autoschedule]) && $settings[$racking_autoschedule] > 0 ) {
            // FIXME: Replace following with commented line when rackspace updated to php 5.3.x
            // $racking_date = date_format(date_add(date_create($todays_date), date_interval_create_from_date_string($settings[$racking_autoschedule] . " days")), 'Y-m-d');
            $racking_date = new DateTime($todays_date);
            $racking_date->modify('+' . $settings[$racking_autoschedule] . ' days');
            $strsql .= "racking_date = '" . ciniki_core_dbQuote($ciniki, date_format($racking_date, 'Y-m-d')) . "', ";
            $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
                2, 'ciniki_wineproductions', $args['wineproduction_id'], 'racking_date', date_format($racking_date, 'Y-m-d'));
            $rack_week = ((date_format($racking_date, 'U') - 1468800)/604800)%3;
            // $rack_week = (date_format($racking_date, 'W'))%3;
            $rack_dayofweek = strtolower(date_format($racking_date, 'D'));
            $racking_autocolour = "racking.autocolour.week" . $rack_week . $rack_dayofweek;
            if( isset($settings[$racking_autocolour]) && $settings[$racking_autocolour] != '' ) {
                $strsql .= "rack_colour = '" . ciniki_core_dbQuote($ciniki, $settings[$racking_autocolour]) . "', ";
                $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
                    2, 'ciniki_wineproductions', $args['wineproduction_id'], 'rack_colour', $settings[$racking_autocolour]);
            }
        }
        $strsql .= "status = 20 "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
        $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
            2, 'ciniki_wineproductions', $args['wineproduction_id'], 'start_date', $todays_date);
        $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
            2, 'ciniki_wineproductions', $args['wineproduction_id'], 'status', '20');
        $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
            2, 'ciniki_wineproductions', $args['wineproduction_id'], 'batch_code', $args['batch_code']);
    } 

    //
    // Bump the status to ready if SG in correct range
    //
    elseif( $args['action'] == 'SGRead' ) {
        if( isset($args['sg_reading']) && $args['sg_reading'] >= 992 && $args['sg_reading'] <= 998 ) {
            $strsql = "UPDATE ciniki_wineproductions SET status = 25 "
                . ", sg_reading = '" . ciniki_core_dbQuote($ciniki, $args['sg_reading']) . "' "
                . ", last_updated = UTC_TIMESTAMP() "
                . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
                . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
            $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
                2, 'ciniki_wineproductions', $args['wineproduction_id'], 'status', '25');
            $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
                2, 'ciniki_wineproductions', $args['wineproduction_id'], 'sg_reading', $args['sg_reading']);
        } else {
            $strsql = "UPDATE ciniki_wineproductions SET "
                . "sg_reading = '" . ciniki_core_dbQuote($ciniki, $args['sg_reading']) . "' "
                . ", last_updated = UTC_TIMESTAMP() "
                . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
                . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
            $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
                2, 'ciniki_wineproductions', $args['wineproduction_id'], 'sg_reading', $args['sg_reading']);
        }
    }

    //
    // The wine was just racked, update the rack_date, and set the filtering date and colour automatically
    //
    elseif( $args['action'] == 'Racked' ) {
        // FIXME: Check for SG reading first, must be filled in
        $strsql = "UPDATE ciniki_wineproductions SET rack_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "', ";
        if( isset($args['kit_length']) && $args['kit_length'] > 0 ) {
            // FIXME: Replace following with commented line when rackspace updated to php 5.3.x
            //$filtering_date = date_format(date_add(date_create($todays_date), date_interval_create_from_date_string($args['kit_length'] . " weeks")), 'Y-m-d');
            // $filtering_date = date_format(date_modify(date_create($todays_date), "+" . $args['weeks'] . " weeks"), 'Y-m-d');
            $filtering_date = new DateTime($todays_date);
            $filtering_date->modify('+' . ($args['kit_length'] - 2) . ' weeks');
            $strsql .= "filtering_date = '" . ciniki_core_dbQuote($ciniki, date_format($filtering_date, 'Y-m-d')) . "', ";
            $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
                2, 'ciniki_wineproductions', $args['wineproduction_id'], 'filtering_date', date_format($filtering_date, 'Y-m-d'));
            $filter_week = ((date_format($filtering_date, 'U') - 1468800)/604800)%7;
            // $filter_week = date_format($filtering_date, 'W')%7;
            $filter_dayofweek = strtolower(date_format($filtering_date, 'D'));
            $filtering_autocolour = "filtering.autocolour.week" . $filter_week . $filter_dayofweek;
            if( isset($settings[$filtering_autocolour]) && $settings[$filtering_autocolour] != '' ) {
                $strsql .= "filter_colour = '" . ciniki_core_dbQuote($ciniki, $settings[$filtering_autocolour]) . "', ";
                $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
                    2, 'ciniki_wineproductions', $args['wineproduction_id'], 'filter_colour', $settings[$filtering_autocolour]);
            }
        }
        $strsql .= "status = 30 "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
        $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
            2, 'ciniki_wineproductions', $args['wineproduction_id'], 'rack_date', $todays_date);
        $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
            2, 'ciniki_wineproductions', $args['wineproduction_id'], 'status', '30');
    } 

    //
    // The wine was filtered
    //
    elseif( $args['action'] == 'Filtered' ) {
        $strsql = "UPDATE ciniki_wineproductions SET filter_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "', status = 40, bottling_status = 128 "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
        $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
            2, 'ciniki_wineproductions', $args['wineproduction_id'], 'filter_date', $todays_date);
        $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
            2, 'ciniki_wineproductions', $args['wineproduction_id'], 'status', '40');
    } 

    //
    // Wines can be pulled and filtered early if necessary
    //
    elseif( $args['action'] == 'Filter Today' ) {
        $strsql = "UPDATE ciniki_wineproductions SET filtering_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
        $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
            2, 'ciniki_wineproductions', $args['wineproduction_id'], 'filtering_date', $todays_date);
    }

    //
    // The wine has been bottled
    //
    elseif( $args['action'] == 'Bottled' ) {
        $strsql = "UPDATE ciniki_wineproductions SET bottle_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "', status = 60 "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
        $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
            2, 'ciniki_wineproductions', $args['wineproduction_id'], 'bottle_date', $todays_date);
        $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
            2, 'ciniki_wineproductions', $args['wineproduction_id'], 'status', '60');
    }

    if( $strsql != "" ) {
        $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.wineproduction');
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
    }

//  if( $rc['num_affected_rows'] != 1 ) {
//      ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
//      return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.11', 'msg'=>'Invalid order'));
//  }

    //
    // Commit the database changes
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.wineproduction');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the business modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
    ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'wineproduction');

    $ciniki['syncqueue'][] = array('push'=>'ciniki.wineproduction.order',
        'args'=>array('id'=>$args['wineproduction_id']));

    return array('stat'=>'ok');
}
?>
