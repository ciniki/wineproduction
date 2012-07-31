<?php
//
// Description
// -----------
// This function will return the list of colours available to the wine production module.
// In the future, these values can be pulled from the database.
//
// Info
// ----
// Status: 			beta
//
// Arguments
// ---------
// ciniki:
// business_id:		The ID of the business to get the colours for.
// 
// Returns
// -------
//
function ciniki_wineproduction__getColours($ciniki, $business_id) {
	
	return array(
		'racking_colours'=>array(
			'*'=>array('name'=>'Unknown', 'code'=>'ffffff'),
			'yellow'=>array('name'=>'Yellow', 'code'=>'ffff00'),
			'darkblue'=>array('name'=>'', 'code'=>'0033ff'),
			'darkgreen'=>array('name'=>'', 'code'=>'006600'),
			'brightred'=>array('name'=>'', 'code'=>'ff0000'),
			'tan'=>array('name'=>'', 'code'=>'ffcc88'),
			'lightred'=>array('name'=>'Light red', 'code'=>'660000'),
			),
		'filtering_colours'=>array(
			'*'=>array('name'=>'Unknown', 'code'=>'ffffff'),
			'lightblue'=>array('name'=>'Light Blue', 'code'=>'00ccff'),
			'orange'=>array('name'=>'Orange', 'code'=>'ff8800'),
			'black'=>array('name'=>'Black', 'code'=>'000000'),
			'purple'=>array('name'=>'Purple', 'code'=>'993399'),
			'brightgreen'=>array('name'=>'Bright Green', 'code'=>'00ff00'),
			'brightpink'=>array('name'=>'Bright Pink', 'code'=>'f660ab'),
			),
		);
}
