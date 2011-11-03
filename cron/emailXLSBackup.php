<?php
//
// Description
// -----------
// This cron job will send an email to the business owner with a backup of the
// wineproduction database in excel format.
//
// Info
// ----
// Status: 				beta
//
// Arguments
// ---------
// business_id:			The business ID 
//
// Returns
// -------
//
function ciniki_wineproduction_emailXLSBackup($ciniki, $cronjob) {
	//
	// Check the arguments
	//
	if( !isset($cronjob['business_id']) || $cronjob['business_id'] < 1 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'451', 'msg'=>'No email address specified.'));
	}
	if( !isset($cronjob['args']) || !isset($cronjob['args']['email_address']) || $cronjob['args']['email_address'] == '' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'449', 'msg'=>'No email address specified.'));
	}
	
	//
	// Get the settings for the business to apply the flags and colours
	//
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
	$rc = ciniki_core_dbDetailsQuery($ciniki, 'wineproduction_settings', 'business_id', $cronjob['business_id'], 'wineproductions', 'settings', '');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$settings = $rc['settings'];

	require_once($ciniki['config']['core']['modules_dir'] . '/users/private/dateFormat.php');
	$date_format = ciniki_users_dateFormat($ciniki);

	//
	// Increase memory limits to be able to create entire file
	//
	ini_set('memory_limit', '4192M');

	//
	// Open Excel parsing library
	//
	require($ciniki['config']['core']['lib_dir'] . '/PHPExcel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();

	$strsql = "SELECT wineproductions.id, CONCAT_WS(' ', first, last) AS customer_name, invoice_number, "
		. "products.name AS wine_name, wine_type, kit_length, wineproductions.status, colour_tag, rack_colour, filter_colour, "
		. "order_flags, "
		. "IFNULL(DATE_FORMAT(order_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS order_date, "
		. "IFNULL(DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS start_date, "
		. "IFNULL(DATE_FORMAT(racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS racking_date, "
		. "IFNULL(DATE_FORMAT(rack_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS rack_date, "
		. "sg_reading, "
		. "IFNULL(DATE_FORMAT(filtering_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS filtering_date, "
		. "IFNULL(DATE_FORMAT(filter_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS filter_date, "
		. "bottling_flags, "
		. "IFNULL(DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS bottling_date, "
		. "IFNULL(DATE_FORMAT(bottle_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS bottle_date, "
		. "IFNULL(DATE_FORMAT(IF(rack_date > 0, DATE_ADD(rack_date, INTERVAL (kit_length) DAY), DATE_ADD(start_date, INTERVAL kit_length WEEK)), '" . ciniki_core_dbQuote($ciniki, $date_format) . "'), '') AS approx_filtering_date "
		. ", wineproductions.notes "
		. "FROM wineproductions "
		. "LEFT JOIN customers ON (wineproductions.customer_id = customers.id "
			. "AND customers.business_id = '" . ciniki_core_dbQuote($ciniki, $cronjob['business_id']) . "') "
		. "LEFT JOIN products ON (wineproductions.product_id = products.id "
			. "AND products.business_id = '" . ciniki_core_dbQuote($ciniki, $cronjob['business_id']) . "') "
		. "WHERE wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $cronjob['business_id']) . "' "
		. "AND wineproductions.status > 0 AND wineproductions.status <= 40 "
		. "AND wineproductions.product_id = products.id "
		. "AND products.business_id = '" . ciniki_core_dbQuote($ciniki, $cronjob['business_id']) . "' "
		. "ORDER BY wineproductions.status, wineproductions.invoice_number "
		. "";
	
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuery.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbFetchHashRow.php');
	$rc = ciniki_core_dbQuery($ciniki, $strsql, 'toolbox');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$result_handle = $rc['handle'];

	$sheets = array(
		'10'=>array('count'=>0),
		'20'=>array('count'=>0),
		'25'=>array('count'=>0),
		'30'=>array('count'=>0),
		'40'=>array('count'=>0),
		);
	$sheets[10]['sheet'] = $objPHPExcel->setActiveSheetIndex(0);
	$sheets[10]['sheet']->setTitle('Ordered');
	$sheets[20]['sheet'] = $objPHPExcel->createSheet();
	$sheets[20]['sheet']->setTitle('Started');
	$sheets[25]['sheet'] = $objPHPExcel->createSheet();
	$sheets[25]['sheet']->setTitle('SG REad');
	$sheets[30]['sheet'] = $objPHPExcel->createSheet();
	$sheets[30]['sheet']->setTitle('Racked');
	$sheets[40]['sheet'] = $objPHPExcel->createSheet();
	$sheets[40]['sheet']->setTitle('Filtered');

	$objPHPExcelWorksheet = $objPHPExcel->setActiveSheetIndex(0);
	// Keep track of new row counter, to avoid deleted rows.
	foreach($sheets as $status => $sht ) {
		$i = 0;
		if( $status == 20 || $status == 25 || $status == 30 ) {
			$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, '', false);
		}
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'INV#', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'Customer', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'Wine', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'Type', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'Duration', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'Flags', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'BD', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'Flags', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'OD', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'SD', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'RD', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'Racked', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'FD', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'Filtered', false);
		$sheets[$status]['sheet']->setCellValueByColumnAndRow($i++, 1, 'Notes', false);
		$sheets[$status]['count']++;
		// for($j=0;$j<14;$j++){
			// FIXME: Set bold in headers
		// }
	}

	$result = ciniki_core_dbFetchHashRow($ciniki, $result_handle);
	while( isset($result['row']) ) {
		$order = $result['row'];
		
		$sheets[$order['status']]['count']++;
		$sheet = $sheets[$order['status']];
		$i = 0;
		// $sheet['sheet']->setCellValueByColumnAndRow($i, $sheet['count'], $order['invoice_number'], true);
		$row_range = 'A' . $sheet['count'] . ':' . 'O' . $sheet['count'];
		if( $order['status'] == 20 || $order['status'] == 25) {
			$colour = preg_replace('/\#/', '', $order['rack_colour']);
			$sheet['sheet']->setCellValueByColumnAndRow($i, $sheet['count'], ' ', false);
			$sheet['sheet']->getStyle('A' . $sheet['count'])->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($colour);
			$row_range = 'B' . $sheet['count'] . ':' . 'P' . $sheet['count'];
			$i++;
		} else if( $order['status'] == 30 ) {
			$colour = preg_replace('/\#/', '', $order['filter_colour']);
			$sheet['sheet']->setCellValueByColumnAndRow($i, $sheet['count'], ' ', false);
			$sheet['sheet']->getStyle('A' . $sheet['count'])->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($colour);
			$row_range = 'B' . $sheet['count'] . ':' . 'P' . $sheet['count'];
			$i++;
		}
		$sheet['sheet']->setCellValueByColumnAndRow($i, $sheet['count'], '', false);
		$sheet['sheet']->getCellByColumnAndRow($i, $sheet['count'])->setValueExplicit($order['invoice_number'], PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;

		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['customer_name'], false);
		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['wine_name'], false);
		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['wine_type'], false);
		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['kit_length'] . ' week', false);
		$comma = '';
		$value = '';
		$colour = '';
		for($j=1;$j<=16;$j++) {
			if( isset($settings["order.flags.$i.name"]) && $settings["order.flags.$i.name"] != '' && ($order['order_flags']&pow(2, $j-1)) == pow(2, $j-1) ) {
				$value .= $comma . $settings["order.flags.$j.name"];
				if( isset($settings["order.flags.$j.colour"]) && $settings["order.flags.$j.colour"] != '' && $colour == '' ) {
					$colour = preg_replace('/\#/', '', $settings["order.flags.$j.colour"]);
				}
				$comma = ',';
			}
		}
		if( $value != '' ) {
			$sheet['sheet']->setCellValueByColumnAndRow($i, $sheet['count'], $value, false);
		}
		$i++;
		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['bottling_date'], false);
		$value = '';
		for($j=1;$j<=16;$j++) {
			if( isset($settings["bottling.flags.$i.name"]) && $settings["bottling.flags.$i.name"] != '' && ($order['bottling_flags']&pow(2, $j-1)) == pow(2, $j-1) ) {
				$value .= $comma . $settings["bottling.flags.$j.name"];
				$comma = ',';
			}
		}
		if( $value != '' ) {
			$sheet['sheet']->setCellValueByColumnAndRow($i, $sheet['count'], $value, false);
		}
		$i++;
		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['order_date'], false);
		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['start_date'], false);
		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['racking_date'], false);
		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['rack_date'], false);
		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['filtering_date'], false);
		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['filter_date'], false);
		$sheet['sheet']->setCellValueByColumnAndRow($i++, $sheet['count'], $order['notes'], false);

		if( $colour != '' && $colour != 'ffffff' ) {
			$sheet['sheet']->getStyle($row_range)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($colour);
		}

		$result = ciniki_core_dbFetchHashRow($ciniki, $result_handle);
	}

	// Set status title
	$sheets[10]['sheet']->getHeaderFooter()->setOddHeader('&C&HOrdered');
	$sheets[20]['sheet']->getHeaderFooter()->setOddHeader('&C&HStarted');
	$sheets[25]['sheet']->getHeaderFooter()->setOddHeader('&C&HSG Ready');
	$sheets[30]['sheet']->getHeaderFooter()->setOddHeader('&C&HRacked');
	$sheets[40]['sheet']->getHeaderFooter()->setOddHeader('&C&HFiltered');
	foreach($sheets as $status => $sht ) {
		$sheets[$status]['sheet']->getColumnDimension('A')->setAutoSize(true);
		$sheets[$status]['sheet']->getColumnDimension('C')->setAutoSize(true);
		$sheets[$status]['sheet']->getColumnDimension('B')->setAutoSize(true);
		$sheets[$status]['sheet']->getColumnDimension('G')->setAutoSize(true);
		$sheets[$status]['sheet']->getColumnDimension('H')->setAutoSize(true);
		$sheets[$status]['sheet']->getColumnDimension('I')->setAutoSize(true);
		$sheets[$status]['sheet']->getColumnDimension('J')->setAutoSize(true);
		$sheets[$status]['sheet']->getColumnDimension('K')->setAutoSize(true);
		$sheets[$status]['sheet']->getColumnDimension('L')->setAutoSize(true);
		$sheets[$status]['sheet']->getColumnDimension('M')->setAutoSize(true);
		$sheets[$status]['sheet']->getColumnDimension('N')->setAutoSize(true);
		$sheets[$status]['sheet']->getColumnDimension('O')->setAutoSize(true);
		if( $status == 20 || $status == 25 || $status == 30 ) {
			$sheets[$status]['sheet']->getColumnDimension('D')->setAutoSize(true);
			$sheets[$status]['sheet']->getColumnDimension('P')->setAutoSize(true);
		} else {
			$sheets[$status]['sheet']->getColumnDimension('C')->setAutoSize(true);
			$sheets[$status]['sheet']->getColumnDimension('F')->setAutoSize(true);
		}
		// Set page footer
		$sheets[$status]['sheet']->getHeaderFooter()->setOddFooter('&L&BPage &P of &N');
		$sheets[$status]['sheet']->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$sheets[$status]['sheet']->freezePane('A2');

		// Set printing to fit to one page wide
		$sheets[$status]['sheet']->getPageSetup()->setFitToWidth(1);
		$sheets[$status]['sheet']->getPageSetup()->setFitToHeight(0);

		$sheets[$status]['sheet']->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);
	}


	//
	// Create email message with XLS attached
	//
	if( $cronjob['args']['email_address'] != '' 
		&& isset($ciniki['config']['core']['system.email']) && $ciniki['config']['core']['system.email'] != '' ) {
		$subject = "Ciniki - Wineproduction Backup";
		$msg = "Here is your wineproduction backup.\n"
			. "\n"
			. "\n";
		//
		// The from address can be set in the config file.
		//
		$headers = 'From: "' . $ciniki['config']['core']['system.email.name'] . '" <' . $ciniki['config']['core']['system.email'] . ">\r\n" .
				'Reply-To: "' . $ciniki['config']['core']['system.email.name'] . '" <' . $ciniki['config']['core']['system.email'] . ">\r\n" .
				'X-Mailer: PHP/' . phpversion();


		// boundary 
		$semi_rand = md5(time()); 
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 

		// headers for attachment 
		$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\""; 

		// multipart boundary 
		$msg = "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" .
		"Content-Transfer-Encoding: 7bit\n\n" . $msg . "\n\n"; 

		// Attache the excel file
		$msg .= "--{$mime_boundary}\n";

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$dptr = fopen("php://temp:100000000", "r+");
		$data = $objWriter->save($dptr);
		rewind($dptr);
		$data = stream_get_contents($dptr);
		$data = chunk_split(base64_encode($data));
		$msg .= "Content-Type: application/octet-stream; name=\"wineproduction.xls\"\n" . 
			"Content-Description: wineproduction.xls\n" .
			"Content-Disposition: attachment;\n" . " filename=\"wineproduction.xls\";\n" . 
			"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";

		$msg .= "--{$mime_boundary}--";

		mail($cronjob['args']['email_address'], $subject, $msg, $headers, '-f' . $ciniki['config']['core']['system.email']);
	}
	
	// 
	// Return ok to the cron script
	//
	return array('stat'=>'ok');
}
?>
