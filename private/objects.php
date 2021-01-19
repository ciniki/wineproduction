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
    $objects['product'] = array(
        'name' => 'Product',
        'sync' => 'yes',
        'o_name' => 'product',
        'o_container' => 'products',
        'table' => 'ciniki_wineproduction_products',
        'fields' => array(
            'name' => array('name'=>'Name'),
            'permalink' => array('name'=>'Permalink', 'default'=>''),
            'ptype' => array('name'=>'Type', 'default'=>'10'),
            'flags' => array('name'=>'Options', 'default'=>'0'),
            'status' => array('name'=>'Status', 'default'=>'10'),
            'start_date' => array('name'=>'Start Date', 'default'=>''),
            'end_date' => array('name'=>'End Date', 'default'=>''),
            'supplier_id' => array('name'=>'Supplier', 'ref'=>'ciniki.wineproduction.'),
            'supplier_item_number' => array('name'=>'Supplier Item Number', 'default'=>''),
            'wine_type' => array('name'=>'Wine Type', 'default'=>''),
            'kit_length' => array('name'=>'Kit Length', 'default'=>''),
            'cost' => array('name'=>'Cost', 'default'=>''),
            'unit_amount' => array('name'=>'Unit Amount', 'default'=>''),
            'unit_discount_amount' => array('name'=>'Discount Amount', 'default'=>''),
            'unit_discount_percentage' => array('name'=>'Discount Percent', 'default'=>''),
            'taxtype_id' => array('name'=>'Taxes', 'ref'=>'ciniki.taxes.type'),
            'inventory_current_num' => array('name'=>'Inventory', 'default'=>''),
            'primary_image_id' => array('name'=>'Primary Image', 'default'=>'0', 'ref'=>'ciniki.images.image'),
            'synopsis' => array('name'=>'Synopsis', 'default'=>''),
            'description' => array('name'=>'Synopsis', 'default'=>''),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['productimage'] = array(
        'name' => 'Product Image',
        'sync' => 'yes',
        'o_name' => 'image',
        'o_container' => 'images',
        'table' => 'ciniki_wineproduction_product_images',
        'fields' => array(
            'product_id' => array('name'=>'Product', 'ref'=>'ciniki.wineproduction.product'),
            'name' => array('name'=>'Name'),
            'permalink' => array('name'=>'Permalink', 'default'=>''),
            'webflags' => array('name'=>'Options', 'default'=>''),
            'sequence' => array('name'=>'Order', 'default'=>''),
            'image_id' => array('name'=>'Image', 'ref'=>'ciniki.images.image'),
            'description' => array('name'=>'Description', 'default'=>''),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['producttag'] = array(
        'name' => 'Product Tag',
        'sync' => 'yes',
        'o_name' => 'tag',
        'o_container' => 'tags',
        'table' => 'ciniki_wineproduction_product_tags',
        'fields' => array(
            'product_id' => array('name'=>'Product', 'ref'=>'ciniki.wineproduction.product'),
            'tag_type' => array('name'=>'Type', 'default'=>''),
            'tag_name' => array('name'=>'Name', 'default'=>''),
            'permalink' => array('name'=>'Permalink', 'default'=>''),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['producttagdetail'] = array(
        'name' => 'Product Tag Details',
        'sync' => 'yes',
        'o_name' => 'tag',
        'o_container' => 'tags',
        'table' => 'ciniki_wineproduction_product_tagdetails',
        'fields' => array(
            'tag_type' => array('name'=>'Type', 'default'=>''),
            'permalink' => array('name'=>'Permalink', 'default'=>''),
            'name' => array('name'=>'Name'),
            'sequence' => array('name'=>'Order', 'default'=>''),
            'display' => array('name'=>'Category Format', 'default'=>''),
            'primary_image_id' => array('name'=>'Image', 'default'=>'0', 'ref'=>'ciniki.images.image'),
            'synopsis' => array('name'=>'Synopsis', 'default'=>''),
            'description' => array('name'=>'Description', 'default'=>''),
            'flags' => array('name'=>'Options', 'default'=>''),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['productfile'] = array(
        'name' => 'Product File',
        'sync' => 'yes',
        'o_name' => 'file',
        'o_container' => 'files',
        'table' => 'ciniki_wineproduction_product_files',
        'fields' => array(
            'product_id' => array('name'=>'', 'ref'=>'ciniki.wineproduction.product'),
            'extension' => array('name'=>'', 'default'=>''),
            'name' => array('name'=>'Name'),
            'permalink' => array('name'=>'', 'default'=>''),
            'webflags' => array('name'=>'', 'default'=>''),
            'description' => array('name'=>'Description', 'default'=>''),
            'org_filename' => array('name'=>'', 'default'=>''),
            'publish_date' => array('name'=>'', 'default'=>''),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['supplier'] = array(
        'name' => 'Supplier',
        'sync' => 'yes',
        'o_name' => 'supplier',
        'o_container' => 'suppliers',
        'table' => 'ciniki_wineproduction_suppliers',
        'fields' => array(
            'name' => array('name'=>'Name'),
            'supplier_tnid' => array('name'=>'', 'default'=>''),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
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
            'min_days_from_last' => array('name'=>'Min Days From Last', 'default'=>'0'),
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
