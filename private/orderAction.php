<?php
//
// Description
// -----------
// This function will perform an action on an order to update it.
//
// Arguments
// ---------
//
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_wineproduction_orderAction(&$ciniki, $tnid, $args) {

    //
    // Check to make sure action was specified
    //
    if( !isset($args['action']) || $args['action'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.268', 'msg'=>'No action specified', 'err'=>$rc['err']));
    }

    //
    // Check to make sure order_id was specified
    //
    if( !isset($args['order_id']) || $args['order_id'] == '' || $args['order_id'] == 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.269', 'msg'=>'No order specified', 'err'=>$rc['err']));
    }

    //
    // Grab the settings for the tenant from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc =  ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'tnid', $tnid, 'ciniki.wineproduction', 'settings', '');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $settings = $rc['settings'];

    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
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
    // Load the order
    //
    $strsql = "SELECT products.flags, "
        . "orders.product_id, "
        . "products.kit_length, "
        . "products.inventory_current_num "
        . "FROM ciniki_wineproductions AS orders "
        . "INNER JOIN ciniki_wineproduction_products AS products ON ("
            . "orders.product_id = products.id "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE orders.id = '" . ciniki_core_dbQuote($ciniki, $args['order_id']) . "' "
        . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'order');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.270', 'msg'=>'Unable to load order', 'err'=>$rc['err']));
    }
    if( !isset($rc['order']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.271', 'msg'=>'Unable to find requested order'));
    }
    $order = $rc['order'];

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
    if( $args['action'] == 'started' ) {
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
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.wineproduction', 0x0800) && ($order['flags']&0x80) == 0x80 ) {
            $dt = new DateTime('now', new DateTimezone($intl_timezone));
            $dt->add(new DateInterval('P7D'));
            $update_args['transferring_date'] = $dt->format('Y-m-d');
        }
    
        $notification_trigger = 'started';
    } 

    //
    // Bump the status to ready if SG in correct range
    //
    elseif( $args['action'] == 'tsgread' ) {
        $update_args['tsg_reading'] = $args['tsg_reading'];
        if( isset($args['tsg_reading']) && $args['tsg_reading'] <= 1020 ) {
            $update_args['status'] = 22;
        } 
    }

    //
    // The wine was just racked, update the rack_date, and set the filtering date and colour automatically
    //
    elseif( $args['action'] == 'transferred' ) {
        $update_args['status'] = 23;
        $update_args['transfer_date'] = $todays_date;

//        $notification_trigger = 'transferred';
    } 

    //
    // Bump the status to ready if SG in correct range
    //
    elseif( $args['action'] == 'sgread' ) {
        $update_args['sg_reading'] = $args['sg_reading'];
        if( isset($args['sg_reading']) && $args['sg_reading'] >= 992 && $args['sg_reading'] <= 998 ) {
            $update_args['status'] = 25;
        } 
    }

    //
    // The wine was just racked, update the rack_date, and set the filtering date and colour automatically
    //
    elseif( $args['action'] == 'racked' ) {
        $update_args['status'] = 30;
        $update_args['rack_date'] = $todays_date;
        if( isset($order['kit_length']) && $order['kit_length'] > 0 ) {
            $filtering_date = new DateTime($todays_date);
            $filtering_date->modify('+' . ($order['kit_length'] - 2) . ' weeks');
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
    elseif( $args['action'] == 'filtered' ) {
        $update_args['status'] = 40;
        $update_args['bottling_status'] = 128;
        $update_args['filter_date'] = $todays_date;

        $notification_trigger = 'filtered';
    } 

    //
    // Wines can be pulled and filtered early if necessary
    //
    elseif( $args['action'] == 'filtertoday' ) {
        $update_args['filtering_date'] = $todays_date;
    }

    //
    // The wine has been bottled
    //
    elseif( $args['action'] == 'bottled' ) {
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
        $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wineproduction.order', $args['order_id'], $update_args, 0x04);
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
        // Decrement the inventory by 1
        //
        if( isset($order['product_id']) 
            && $order['product_id'] > 0 
            && isset($order['inventory_current_num']) 
            && $order['inventory_current_num'] > 0 
            ) {
            //
            // Update the inventory
            //
            $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wineproduction.product', $order['product_id'], array(
                'inventory_current_num' => ($order['inventory_current_num'] - 1),
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
        $rc = ciniki_wineproduction_notificationTrigger($ciniki, $tnid, $notification_trigger, $args['order_id']);
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
    ciniki_tenants_updateModuleChangeDate($ciniki, $tnid, 'ciniki', 'wineproduction');

    $ciniki['syncqueue'][] = array('push'=>'ciniki.wineproduction.order', 'args'=>array('id'=>$args['order_id']));

    return array('stat'=>'ok');
}
?>
