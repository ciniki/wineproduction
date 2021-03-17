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
            'supplier_id' => array('name'=>'Supplier', 'ref'=>'ciniki.wineproduction.supplier'),
            'supplier_item_number' => array('name'=>'Supplier Item Number', 'default'=>''),
            'package_qty' => array('name'=>'Package Quantity', 'default'=>'1'),
            'wine_type' => array('name'=>'Wine Type', 'default'=>''),
            'kit_length' => array('name'=>'Kit Length', 'default'=>''),
            'list_price' => array('name'=>'List Price', 'default'=>''),
            'list_discount_percent' => array('name'=>'List Discount', 'default'=>''),
            'cost' => array('name'=>'Cost', 'default'=>''),
            'kit_price_id' => array('name'=>'Kit Price', 'ref'=>'ciniki.wineproduction.productprice', 'default'=>''),
            'processing_price_id' => array('name'=>'Processing Price', 'ref'=>'ciniki.wineproduction.productprice', 'default'=>''),
            'unit_amount' => array('name'=>'Unit Amount', 'default'=>''),
            'unit_discount_amount' => array('name'=>'Discount Amount', 'default'=>''),
            'unit_discount_percentage' => array('name'=>'Discount Percent', 'default'=>''),
            'taxtype_id' => array('name'=>'Taxes', 'ref'=>'ciniki.taxes.type', 'default'=>'0'),
            'inventory_current_num' => array('name'=>'Inventory', 'default'=>''),
            'primary_image_id' => array('name'=>'Primary Image', 'default'=>'0', 'ref'=>'ciniki.images.image'),
            'synopsis' => array('name'=>'Synopsis', 'default'=>''),
            'description' => array('name'=>'Synopsis', 'default'=>''),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['productprice'] = array(
        'name' => 'Price',
        'sync' => 'yes',
        'o_name' => 'price',
        'o_container' => 'prices',
        'table' => 'ciniki_wineproduction_product_pricing',
        'fields' => array(
            'price_type' => array('name'=>'Type'),
            'name' => array('name'=>'Name'),
            'invoice_description' => array('name'=>'Name', 'default'=>''),
            'sequence' => array('name'=>'Order', 'default'=>''),
            'unit_amount' => array('name'=>'Price', 'default'=>''),
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
            'product_id' => array('name'=>'Product', 'ref'=>'ciniki.wineproduction.product'),
            'extension' => array('name'=>'Extension', 'default'=>''),
            'name' => array('name'=>'Name'),
            'permalink' => array('name'=>'Permalink', 'default'=>''),
            'webflags' => array('name'=>'Options', 'default'=>''),
            'description' => array('name'=>'Description', 'default'=>''),
            'org_filename' => array('name'=>'Original Filename', 'default'=>''),
            'publish_date' => array('name'=>'Publish Date', 'default'=>''),
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
            'supplier_tnid' => array('name'=>'Supplier Tenant ID', 'default'=>''),
            'po_name_address' => array('name'=>'Purchase Order Name/Address', 'default'=>''),
            'po_email' => array('name'=>'Purchase Order Email Address', 'default'=>''),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['purchaseorder'] = array(
        'name' => 'Purchase Order',
        'sync' => 'yes',
        'o_name' => 'purchaseorder',
        'o_container' => 'purchaseorders',
        'table' => 'ciniki_wineproduction_purchaseorders',
        'fields' => array(
            'supplier_id' => array('name'=>'Supplier', 'ref'=>'ciniki.wineproduction.supplier'),
            'po_number' => array('name'=>'PO Number'),
            'status' => array('name'=>'Status', 'default'=>'10'),
            'date_ordered' => array('name'=>'Date Ordered', 'default'=>''),
            'date_received' => array('name'=>'Date Received', 'default'=>''),
            'notes' => array('name'=>'Notes', 'default'=>''),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['purchaseorderitem'] = array(
        'name' => 'Purchase Order Item',
        'sync' => 'yes',
        'o_name' => 'item',
        'o_container' => 'items',
        'table' => 'ciniki_wineproduction_purchaseorder_items',
        'fields' => array(
            'order_id' => array('name'=>'Order', 'ref'=>'ciniki.wineproduction.purchaseorder'),
            'product_id' => array('name'=>'Product', 'ref'=>'ciniki.wineproduction.product', 'default'=>'0'),
            'sku' => array('name'=>'Sku', 'default'=>''),
            'description' => array('name'=>'Description', 'default'=>''),
            'quantity_ordered' => array('name'=>'Quantity Ordered', 'default'=>''),
            'quantity_received' => array('name'=>'Quantity Received', 'default'=>''),
            'unit_amount' => array('name'=>'Price', 'default'=>''),
            'taxtype_id' => array('name'=>'Tax Type', 'ref'=>'ciniki.taxes.type', 'default'=>'0'),
            ),
        'history_table' => 'ciniki_wineproduction_history',
        );
    $objects['order'] = array(
        'name'=>'Wine Production Order',
        'sync'=>'yes',
        'o_name'=>'order',
        'o_container'=>'orders',
        'table'=>'ciniki_wineproductions',
        'fields'=>array(
            'parent_id'=>array('name'=>'Primary Order', 'ref'=>'ciniki.wineproduction.order', 'default'=>'0'),
            'customer_id'=>array('name'=>'Customer', 'ref'=>'ciniki.customers.customer'),
            'invoice_id'=>array('name'=>'Invoice', 'ref'=>'ciniki.sapos.invoice', 'default'=>'0'),
            'invoice_number'=>array('name'=>'Invoice Number', 'default'=>''),
            'batch_letter'=>array('name'=>'Batch', 'default'=>''),
            'product_id'=>array('name'=>'Product', 'ref'=>'ciniki.products.product'),
            'wine_type'=>array('name'=>'Wine Type', 'default'=>''),
            'kit_length'=>array('name'=>'Kit Length', 'default'=>''),
            'status'=>array('name'=>'Status', 'default'=>'10'),
            'rack_colour'=>array('name'=>'Rack Colour', 'default'=>''),
            'filter_colour'=>array('name'=>'Filter Colour', 'default'=>''),
            'location'=>array('name'=>'Location', 'default'=>''),
            'flags'=>array('name'=>'Options', 'default'=>'0'),
            'order_flags'=>array('name'=>'Order Flags', 'default'=>'0'),
            'order_date'=>array('name'=>'Order Date', 'default'=>''),
            'start_date'=>array('name'=>'Start Date', 'default'=>''),
            'tsg_reading'=>array('name'=>'Transfer SG Reading', 'default'=>''),
            'transferring_date'=>array('name'=>'Transferring Date', 'default'=>''),
            'transfer_date'=>array('name'=>'Transferred Date', 'default'=>''),
            'sg_reading'=>array('name'=>'SG Reading', 'default'=>''),
            'racking_date'=>array('name'=>'Racking Date', 'default'=>''),
            'rack_date'=>array('name'=>'Racked Date', 'default'=>''),
            'filtering_date'=>array('name'=>'Filtering Date', 'default'=>''),
            'filter_date'=>array('name'=>'Filtered Date', 'default'=>''),
            'bottling_flags'=>array('name'=>'Bottling Options', 'default'=>'0'),
            'bottling_nocolour_flags'=>array('name'=>'Bottling Options 2', 'default'=>'0'),
            'bottling_duration'=>array('name'=>'Bottling Duration', 'default'=>'0'),
            'bottling_date'=>array('name'=>'Bottling Date', 'default'=>''),
            'bottling_status'=>array('name'=>'Bottling Status', 'default'=>''),
            'bottling_notes'=>array('name'=>'Bottling Notes', 'default'=>''),
            'bottle_date'=>array('name'=>'Bottled Date', 'default'=>''),
            'notes'=>array('name'=>'Notes', 'default'=>''),
            'batch_code'=>array('name'=>'Batch Code', 'default'=>''),
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
