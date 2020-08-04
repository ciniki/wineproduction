<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_wineproduction_objects($ciniki) {
    $objects = array();
    $objects['order'] = array(
        'name'=>'Wine Production Order',
        'table'=>'ciniki_wineproductions',
        'fields'=>array(
            'customer_id'=>array('ref'=>'ciniki.customers.customer'),
            'invoice_id'=>array(),
            'invoice_number'=>array(),
            'product_id'=>array('ref'=>'ciniki.products.product'),
            'wine_type'=>array(),
            'kit_length'=>array(),
            'status'=>array(),
            'colour_tag'=>array(),
            'rack_colour'=>array(),
            'filter_colour'=>array(),
            'order_flags'=>array(),
            'order_date'=>array(),
            'start_date'=>array(),
            'sg_reading'=>array(),
            'racking_date'=>array(),
            'rack_date'=>array(),
            'filtering_date'=>array(),
            'filter_date'=>array(),
            'bottling_flags'=>array(),
            'bottling_nocolour_flags'=>array(),
            'bottling_duration'=>array(),
            'bottling_date'=>array(),
            'bottling_status'=>array(),
            'bottling_notes'=>array(),
            'bottle_date'=>array(),
            'notes'=>array(),
            'batch_code'=>array(),
            ),
        'history_table'=>'ciniki_wineproduction_history',
        );
    $objects['notification'] = array(
        'name' => 'Notification',
        'sync' => 'yes',
        'o_name' => 'notification',
        'o_container' => 'notifications',
        'table' => 'ciniki_wineproduction_notifications',
        'fields' => array(
            'name' => array('name'=>'Name'),
            'ntype' => array('name'=>'Type'),
            'offset_days' => array('name'=>'Offset Days', 'default'=>'0'),
            'status' => array('name'=>'Status', 'default'=>'10'),
            'email_time' => array('name'=>'Email Time', 'default'=>''),
            'email_subject' => array('name'=>'Email Subject', 'default'=>''),
            'email_content' => array('name'=>'Email Message', 'default'=>''),
            'sms_content' => array('name'=>'SMS Message', 'default'=>''),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['notification_customer'] = array(
        'name' => 'Notification Customer',
        'sync' => 'yes',
        'o_name' => 'customer',
        'o_container' => 'customers',
        'table' => 'ciniki_wineproduction_notification_customers',
        'fields' => array(
            'customer_id' => array('name'=>'Customer', 'ref'=>'ciniki.customers.customer'),
            'ntype' => array('name'=>'Notification Type'),
            'flags' => array('name'=>'Options', 'default'=>'0'),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['notification_queue'] = array(
        'name' => 'Notification Queue',
        'sync' => 'yes',
        'o_name' => 'notification_queue',
        'o_container' => 'notification_queues',
        'table' => 'ciniki_wineproduction_notification_queue',
        'fields' => array(
            'scheduled_dt' => array('name'=>'Scheduled Date/Time'),
            'notification_id' => array('name'=>'Notification', 'ref'=>'ciniki.wineproduction.notification'),
            'customer_id' => array('name'=>'Customer', 'ref'=>'ciniki.customers.customer'),
            'order_id' => array('name'=>'Wine Order', 'ref'=>'ciniki.wineproduction.order'),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['setting'] = array(
        'type'=>'settings',
        'name'=>'Wine Production Setting',
        'table'=>'ciniki_wineproduction_settings',
        'history_table'=>'ciniki_wineproduction_history',
        );
    
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
