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
	$objects['setting'] = array(
		'type'=>'settings',
		'name'=>'Wine Production Setting',
		'table'=>'ciniki_wineproduction_settings',
		'history_table'=>'ciniki_wineproduction_history',
		);
	
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
