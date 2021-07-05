<?php
//
// Description
// ===========
// This function will produce a PDF of half page tags
//
// Arguments
// ---------
// 
// Returns
// -------
//
function ciniki_wineproduction_templates_halfpageTags(&$ciniki, $tnid, $args) {

    //
    // Load tenant details
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'tenantDetails');
    $rc = ciniki_tenants_tenantDetails($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['details']) && is_array($rc['details']) ) {    
        $tenant_details = $rc['details'];
    } else {
        $tenant_details = array();
    }

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
    // Load the settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_wineproduction_settings', 'tnid', $tnid, 'ciniki.wineproduction', 'settings', 'purchaseorder');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.195', 'msg'=>'Unable to load settings', 'err'=>$rc['err']));
    }
    $settings = isset($rc['settings']) ? $rc['settings'] : array();
   
    //
    // Load the orders
    //
    $strsql = "SELECT orders.id, "
        . "orders.invoice_number, "
        . "orders.batch_letter, "
        . "products.kit_length, "
        . "DATE_FORMAT(orders.start_date, '%W %b %e, %Y') AS start_date, "
        . "DATE_FORMAT(orders.transferring_date, '%W %b %e, %Y') AS transferring_date, "
        . "DATE_FORMAT(orders.racking_date, '%W %b %e, %Y') AS racking_date, "
        . "DATE_FORMAT(orders.filtering_date, '%W %b %e, %Y') AS filtering_date, "
        . "DATE_FORMAT(orders.bottling_date, '%W %b %e, %Y') AS bottling_date, "
        . "products.name, "
        . "customers.display_name "
        . "FROM ciniki_wineproductions AS orders "
        . "LEFT JOIN ciniki_wineproduction_products AS products ON ("
            . "orders.product_id = products.id "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "LEFT JOIN ciniki_customers AS customers ON ("
            . "orders.customer_id = customers.id "
            . "AND customers.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "";
    if( isset($args['order_id']) ) {
        $strsql .= "WHERE orders.id = '" . ciniki_core_dbQuote($ciniki, $args['order_id']) . "' ";
    } elseif( isset($args['order_ids']) && is_array($args['order_ids']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');
        $strsql .= "WHERE orders.id IN (" . ciniki_core_dbQuoteIDs($ciniki, $args['order_ids']) . ") ";
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.278', 'msg'=>'No order specified'));
    }
    $strsql .= "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'orders', 'fname'=>'id', 
            'fields'=>array('id', 'invoice_number', 'batch_letter', 'name', 'display_name', 'kit_length',
                'start_date', 'transferring_date', 'racking_date', 'filtering_date', 'bottling_date', 
                ),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.274', 'msg'=>'Unable to load ', 'err'=>$rc['err']));
    }
    $orders = isset($rc['orders']) ? $rc['orders'] : array();


    //
    // Load TCPDF library
    //
    require_once($ciniki['config']['ciniki.core']['lib_dir'] . '/tcpdf/tcpdf.php');

    class MYPDF extends TCPDF {
        //Page header
        public function Header() {
        }
        public function Footer() {
        }
    }

    //
    // Start a new document
    //
    $pdf = new MYPDF('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);

    //
    // Setup the PDF basics
    //
    $pdf->SetCreator('Ciniki');
    $pdf->SetAuthor($tenant_details['name']);
    $pdf->SetTitle('Wine Tag');
    $pdf->SetSubject('');
    $pdf->SetKeywords('');

    // set margins
    $pdf->SetMargins(12, 0, 12);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    foreach($orders as $order) {
        // add a page
        $pdf->AddPage();
        $pdf->Ln(7);
        $pdf->SetFillColor(255);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(128);
        $pdf->SetLineWidth(0.15);
        // set font
        $pdf->SetCellPadding(2);

        //
        // Calculate filter date if not specified
        //
        if( $order['filtering_date'] == '' && $order['start_date'] != '' ) {
            $dt = new DateTime($order['start_date'], new DateTimezone($intl_timezone));
            $dt->add(new DateInterval('P' . $order['kit_length'] . 'W'));
            $order['filtering_date'] = '~ ' . $dt->format('l M j, Y');
        }

        $w = array(85, 90);
        $invoice_number = $order['invoice_number'];
        if( $order['batch_letter'] != '' && $order['batch_letter'] != 'A' ) {
            $invoice_number .= $order['batch_letter'];
        }
        $pdf->SetFont('times', 'B', 18);
        $pdf->Cell($w[0], 18, 'Invoice #' . $invoice_number, 0, 1, 'L', 1);
        $pdf->SetFont('times', 'B', 12);
        $pdf->Cell($w[0], 8, $order['display_name'], 0, 1, 'L', 1);
        $pdf->SetFont('times', '', 12);
        $pdf->Cell($w[0], 8, $order['name'], 0, 1, 'L', 1);
        $y = $pdf->GetY();
        $pdf->Ln(15);

        $tw = array(30, 50);
        $pdf->SetFont('times', '', 12);
        $pdf->Cell($tw[0], 12, 'Start', 'TB', 0, 'L', 1);
        $pdf->Cell($tw[1], 12, $order['start_date'], 'TB', 1, 'R', 1);
        $pdf->Cell($tw[0], 12, 'Transfer', 'TB', 0, 'L', 1);
        $pdf->Cell($tw[1], 12, $order['transferring_date'], 'TB', 1, 'R', 1);
        $pdf->Cell($tw[0], 12, 'Rack', 'TB', 0, 'L', 1);
        $pdf->Cell($tw[1], 12, $order['racking_date'], 'TB', 1, 'R', 1);
        $pdf->Cell($tw[0], 12, 'Filter', 'TB', 0, 'L', 1);
        $pdf->Cell($tw[1], 12, $order['filtering_date'], 'TB', 1, 'R', 1);
        $pdf->Cell($tw[0], 12, 'Bottle', 'TB', 0, 'L', 1);
        $pdf->Cell($tw[1], 12, $order['bottling_date'], 'TB', 1, 'R', 1);
        
        //
        // Add legal message
        //
        $legal_message = "I, ______________________________________, have purchased "
            . "the ingredients and started the fermentation, in order to produce this "
            . "product for my personal or family use, not for resale or commercial purposes."
            . "\n\n"
            . "I authorize the operator to hold this product, under contract of bailment, until my return to "
            . "bottle and remove the said product from this facility.\n\n";
        $pdf->SetY($y);
        $pdf->SetX($w[0] + 22);
        $pdf->MultiCell($w[1], 6, $legal_message, 0, 'L', 0, 1);
        $pdf->SetX($w[0] + 22);
        $pdf->MultiCell($w[1], 6, "Signature:  ______________________________", 0, 'L', 0, 1);
        $pdf->SetX($w[0] + 22);
        $pdf->MultiCell($w[1], 6, "Date:          ______________________________", 0, 'L', 0, 1);
    }

    $filename = 'tag_' . $invoice_number . '.pdf';

    return array('stat'=>'ok', 'pdf'=>$pdf, 'filename'=>$filename);
}
?>
