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
// tnid:         The ID of the tenant the order belongs to.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
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
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.actionOrder'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Grab the settings for the tenant from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc =  ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'tnid', $args['tnid'], 'ciniki.wineproduction', 'settings', '');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $settings = $rc['settings'];

    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    
    //
    // Setup todays date
    //
    $dt = new DateTime('now', new DateTimezone($intl_timezone));
    $todays_date = $dt->format('Y-m-d');

    //  
    // Turn off autocommit
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.wineproduction');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // The wine was just started, setup the racking date automatically
    //
    $strsql = "";
    $update_args = array();
    if( $args['action'] == 'Started' ) {
        $update_args['status'] = 20;
        $update_args['start_date'] = $todays_date;
        $update_args['batch_code'] = $args['batch_code'];
        $racking_autoschedule = "racking.autoschedule.madeon" . strtolower(date('D', strtotime($todays_date)));
        if( isset($settings[$racking_autoschedule]) && $settings[$racking_autoschedule] > 0 ) {
            $racking_date = new DateTime($todays_date);
            $racking_date->modify('+' . $settings[$racking_autoschedule] . ' days');
            $update_args['racking_date'] = $racking_date->format('Y-m-d');
            
            //
            // Check the day of week, and setup colour based on day
            //
            $rack_week = ((date_format($racking_date, 'U') - 1468800)/604800)%3;
            $rack_dayofweek = strtolower(date_format($racking_date, 'D'));
            $racking_autocolour = "racking.autocolour.week" . $rack_week . $rack_dayofweek;
            if( isset($settings[$racking_autocolour]) && $settings[$racking_autocolour] != '' ) {
                $update_args['rack_colour'] = $settings[$racking_autocolour];
            }
        }
        
        //
        // Check if transfer date should be setup
        //
        // FIXME: CHange to option from products
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.wineproduction', 0x0800) ) {
            $dt = new DateTime('now', new DateTimezone($intl_timezone));
            $dt->add(new DateInterval('P7D'));
            $update_args['transferring_date'] = $dt->format('Y-m-d');
        }
    
        $notification_trigger = 'started';
    } 

    //
    // Bump the status to ready if SG in correct range
    //
    elseif( $args['action'] == 'SGRead' ) {
        $update_args['sg_reading'] = $args['sg_reading'];
        if( isset($args['sg_reading']) && $args['sg_reading'] >= 992 && $args['sg_reading'] <= 998 ) {
            $update_args['status'] = 25;
        } 
    }

    //
    // The wine was just racked, update the rack_date, and set the filtering date and colour automatically
    //
    elseif( $args['action'] == 'Transferred' ) {
        $update_args['status'] = 23;
        $update_args['transfer_date'] = $todays_date;

//        $notification_trigger = 'transferred';
    } 

    //
    // The wine was just racked, update the rack_date, and set the filtering date and colour automatically
    //
    elseif( $args['action'] == 'Racked' ) {
        $update_args['status'] = 30;
        $update_args['rack_date'] = $todays_date;
        if( isset($args['kit_length']) && $args['kit_length'] > 0 ) {
            $filtering_date = new DateTime($todays_date);
            $filtering_date->modify('+' . ($args['kit_length'] - 2) . ' weeks');
            $update_args['filtering_date'] = $filtering_date->format('Y-m-d');

            $filter_week = ((date_format($filtering_date, 'U') - 1468800)/604800)%7;
            $filter_dayofweek = strtolower(date_format($filtering_date, 'D'));
            $filtering_autocolour = "filtering.autocolour.week" . $filter_week . $filter_dayofweek;
            if( isset($settings[$filtering_autocolour]) && $settings[$filtering_autocolour] != '' ) {
                $update_args['filter_colour'] = $settings[$filtering_autocolour];
            }
        }

        $notification_trigger = 'racked';
    } 

    //
    // The wine was filtered
    //
    elseif( $args['action'] == 'Filtered' ) {
        $update_args['status'] = 40;
        $update_args['filter_date'] = $todays_date;

        $notification_trigger = 'filtered';
    } 

    //
    // Wines can be pulled and filtered early if necessary
    //
    elseif( $args['action'] == 'Filter Today' ) {
        $update_args['filtering_date'] = $todays_date;
    }

    //
    // The wine has been bottled
    //
    elseif( $args['action'] == 'Bottled' ) {
        $update_args['status'] = 60;
        $update_args['bottle_date'] = $todays_date;

        $notification_trigger = 'bottled';
    }

//    if( $strsql != "" ) {
    if( count($update_args) > 0 ) {
        //
        // Update the order
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
        $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.wineproduction.order', $args['wineproduction_id'], $update_args, 0x04);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.261', 'msg'=>'Unable to update the order', 'err'=>$rc['err']));
        }
    }

    //
    // When the wine is started, decrease inventory
    //
    if( isset($notification_trigger) && $notification_trigger == 'started' ) {
        //
        // Get the current number in inventory and decrement by 1
        //
        $strsql_inventory = "SELECT orders.id, "
            . "IFNULL(products.id, 0) AS product_id, "
            . "IFNULL(products.inventory_current_num, 0) AS inventory_current_num "
            . "FROM ciniki_wineproductions AS orders "
            . "LEFT JOIN ciniki_wineproduction_products AS products ON ("
                . "orders.product_id = products.id "
                . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") "
            . "WHERE orders.id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' "
            . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql_inventory, 'ciniki.wineproduction', 'product');
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.199', 'msg'=>'Unable to load product', 'err'=>$rc['err']));
        }
        $product = isset($rc['product']) ? $rc['product'] : array();
        if( isset($product['product_id']) 
            && $product['product_id'] > 0 
            && isset($product['inventory_current_num']) 
            && $product['inventory_current_num'] > 0 
            ) {
            //
            // Update the inventory
            //
            $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.wineproduction.product', $product['product_id'], array(
                'inventory_current_num' => ($product['inventory_current_num'] - 1),
                ), 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.200', 'msg'=>'Unable to update the inventory'));
            }
        }
    }

    //
    // Trigger customer notifications
    //
    if( isset($notification_trigger) && $notification_trigger != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'notificationTrigger');
        $rc = ciniki_wineproduction_notificationTrigger($ciniki, $args['tnid'], $notification_trigger, $args['wineproduction_id']);
        if( $rc['stat'] != 'ok' ) {
            // FIXME: Find way to warn user without return full error
            error_log('WINEPRODUCTION[actionOrder:' . __LINE__ . ']: ' . print_r($rc['err'], true));
        }
    }

    //
    // Commit the database changes
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.wineproduction');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'wineproduction');

    $ciniki['syncqueue'][] = array('push'=>'ciniki.wineproduction.order', 'args'=>array('id'=>$args['wineproduction_id']));

    return array('stat'=>'ok');
}
?>
