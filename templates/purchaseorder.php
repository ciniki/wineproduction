<?php
//
// Description
// ===========
// This function will create the purchase order, similar to invoices in ciniki.sapos.
//
// Arguments
// ---------
// 
// Returns
// -------
//
function ciniki_wineproduction_templates_purchaseorder(&$ciniki, $tnid, $order_id) {

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
    // Load the settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_wineproduction_settings', 'tnid', $tnid, 'ciniki.wineproduction', 'settings', 'purchaseorder');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.195', 'msg'=>'Unable to load settings', 'err'=>$rc['err']));
    }
    $po_settings = isset($rc['settings']) ? $rc['settings'] : array();
    
    //
    // Load the order and items
    //
    $strsql = "SELECT ciniki_wineproduction_purchaseorders.id, "
        . "ciniki_wineproduction_purchaseorders.supplier_id, "
        . "ciniki_wineproduction_purchaseorders.po_number, "
        . "ciniki_wineproduction_purchaseorders.status, "
        . "DATE_FORMAT(ciniki_wineproduction_purchaseorders.date_ordered, '%b %e, %Y') AS date_ordered, "
        . "ciniki_wineproduction_purchaseorders.date_received, "
        . "ciniki_wineproduction_purchaseorders.notes "
        . "FROM ciniki_wineproduction_purchaseorders "
        . "WHERE ciniki_wineproduction_purchaseorders.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproduction_purchaseorders.id = '" . ciniki_core_dbQuote($ciniki, $order_id) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'purchaseorders', 'fname'=>'id', 
            'fields'=>array('id', 'supplier_id', 'po_number', 'status', 'date_ordered', 'date_received', 'notes')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.177', 'msg'=>'Purchase Order not found', 'err'=>$rc['err']));
    }
    if( !isset($rc['purchaseorders'][0]) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.178', 'msg'=>'Unable to find Purchase Order'));
    }
    $po = $rc['purchaseorders'][0];

    //
    // Load the items
    //
    $strsql = "SELECT items.id, "
        . "items.product_id, "
        . "IF(items.product_id > 0, products.supplier_item_number, items.sku) AS sku, "
        . "IF(items.product_id > 0, products.name, items.description) AS description, "
        . "items.quantity_ordered, "
        . "items.quantity_received, "
        . "items.unit_amount, "
        . "products.inventory_current_num "
        . "FROM ciniki_wineproduction_purchaseorder_items AS items "
        . "LEFT JOIN ciniki_wineproduction_products AS products ON ("
            . "items.product_id = products.id "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE items.order_id = '" . ciniki_core_dbQuote($ciniki, $po['id']) . "' "
        . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'items', 'fname'=>'id', 
            'fields'=>array('id', 'product_id', 'sku', 'description', 
                'quantity_ordered', 'quantity_received', 'unit_amount', 'inventory_current_num'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.189', 'msg'=>'Unable to load items', 'err'=>$rc['err']));
    }
    $items = isset($rc['items']) ? $rc['items'] : array();

    //
    // Load the supplier
    //
    $strsql = "SELECT ciniki_wineproduction_suppliers.id, "
        . "ciniki_wineproduction_suppliers.name, "
        . "ciniki_wineproduction_suppliers.supplier_tnid, "
        . "ciniki_wineproduction_suppliers.po_name_address, "
        . "ciniki_wineproduction_suppliers.po_email "
        . "FROM ciniki_wineproduction_suppliers "
        . "WHERE ciniki_wineproduction_suppliers.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproduction_suppliers.id = '" . ciniki_core_dbQuote($ciniki, $po['supplier_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'supplier');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.197', 'msg'=>'Unable to load supplier', 'err'=>$rc['err']));
    }
    if( !isset($rc['supplier']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.198', 'msg'=>'Unable to find requested supplier'));
    }
    $supplier = $rc['supplier'];
    
    
    //
    // Load TCPDF library
    //
    require_once($ciniki['config']['ciniki.core']['lib_dir'] . '/tcpdf/tcpdf.php');

    class MYPDF extends TCPDF {
        //Page header
        public $header_image = null;
        public $header_height = 0;      // The height of the image and address
        public $po_date = '';
        public $po_number = '';
        public $po_settings = array();

        public function Header() {
            //
            // Check if there is an image to be output in the header.   The image
            // will be displayed in a narrow box if the contact information is to
            // be displayed as well.  Otherwise, image is scaled to be 100% page width
            // but only to a maximum height of the header_height (set far below).
            //
            $img_width = 0;
            if( $this->header_image != null ) {
                $height = $this->header_image->getImageHeight();
                $width = $this->header_image->getImageWidth();
                $image_ratio = $width/$height;
                $img_width = 110;
                $available_ratio = $img_width/$this->header_height;
                // Check if the ratio of the image will make it too large for the height,
                // and scaled based on either height or width.
                if( $available_ratio < $image_ratio ) {
                    $this->Image('@'.$this->header_image->getImageBlob(), 15, 12, 
                        $img_width, 0, 'JPEG', '', 'L', 2, '150');
                } else {
                    $this->Image('@'.$this->header_image->getImageBlob(), 15, 12, 
                        0, $this->header_height-5, 'JPEG', '', 'L', 2, '150');
                }
            }

            //
            // Add the contact information
            //
            $align = 'R';
            $this->Ln(8);
            $this->SetX(135);
            $this->SetFont('times', 'B', 24);
            $this->Cell(60, 10, 'Purchase Order', 0, 1, 'R', 0, '', 0, false, 'M', 'M');
            $this->Ln(5);
            $this->SetFont('times', '', 10);
            $this->SetX(135);
            $this->SetFillColor(224);
            $this->SetDrawColor(128);
            $this->SetLineWidth(0.15);
            $this->SetFont('', 'B');
            $this->Cell(30, 8, 'Date', 1, 0, 'C', 1);
            $this->Cell(30, 8, 'P.O. #', 1, 1, 'C', 1);
            $this->SetX(135);
            $this->SetFont('', '');
            $this->Cell(30, 8, $this->po_date, 1, 0, 'C');
            $this->Cell(30, 8, $this->po_number, 1, 1, 'C');
            $this->Ln();
        }

        // Page footer
        public function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('helvetica', 'I', 8);
            if( isset($this->po_settings['purchaseorder-footer-message']) 
                && $this->po_settings['purchaseorder-footer-message'] != '' ) {
                $this->Cell(90, 10, $this->po_settings['purchaseorder-footer-message'],
                    0, false, 'L', 0, '', 0, false, 'T', 'M');
                $this->Cell(90, 10, 'Page ' . $this->pageNo().'/'.$this->getAliasNbPages(), 
                    0, false, 'R', 0, '', 0, false, 'T', 'M');
            } else {
                // Center the page number if no footer message.
                $this->Cell(0, 10, 'Page ' . $this->pageNo().'/'.$this->getAliasNbPages(), 
                    0, false, 'C', 0, '', 0, false, 'T', 'M');
            }
        }
    }

    //
    // Start a new document
    //
    $pdf = new MYPDF('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);

    //
    // Figure out the header tenant name and address information
    //
    $pdf->header_height = 30;

    //
    // Load the header image
    //
    if( isset($po_settings['purchaseorder-header-image']) && $po_settings['purchaseorder-header-image'] > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');
        $rc = ciniki_images_loadImage($ciniki, $tnid, 
            $po_settings['purchaseorder-header-image'], 'original');
        if( $rc['stat'] == 'ok' ) {
            $pdf->header_image = $rc['image'];
        }
    }

    $pdf->po_settings = $po_settings;
    $pdf->po_date = $po['date_ordered'];
    $pdf->po_number = $po['po_number'];

    //
    // Setup the PDF basics
    //
    $pdf->SetCreator('Ciniki');
    $pdf->SetAuthor($tenant_details['name']);
    $pdf->SetTitle('Purchase Order #' . $po['po_number']);
    $pdf->SetSubject('');
    $pdf->SetKeywords('');

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, $pdf->header_height+15, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


    // set font
    $pdf->SetFont('times', 'BI', 10);
    $pdf->SetCellPadding(2);

    // add a page
    $pdf->AddPage();
    $pdf->SetFillColor(255);
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(128);
    $pdf->SetLineWidth(0.15);

    //
    // Determine the billing address information
    //
    $supplier_name_address = $supplier['po_name_address'];
    $supplier_name_address .= ($supplier_name_address != '' ? "\n" : '') . $supplier['po_email'];

    $tenant_name_address = $po_settings['purchaseorder-name-address'];

    $w = array(90, 90);
    $lh = 6;
    $s_height = $pdf->getStringHeight($w[0], $supplier_name_address);
    $t_height = $pdf->getStringHeight($w[1], $tenant_name_address);
    $lh = $s_height < $t_height ? $t_height : $s_height;
    $pdf->SetFillColor(224);
    $pdf->setCellPadding(2);
    $pdf->SetFont('', 'B');
    $pdf->Cell($w[0], 6, 'Vendor', 1, 0, 'L', 1);
    $pdf->Cell($w[1], 6, 'Ship To', 1, 1, 'L', 1);
    $pdf->SetFont('', '');
    $pdf->MultiCell($w[0], $lh, $supplier_name_address, 1, 'L', 0, 0, '', '', true, 0, false, true, 0, 'T', false);
    $pdf->MultiCell($w[1], $lh, $tenant_name_address, 1, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);

    $pdf->Ln(5);

    //
    // Add the invoice items
    //
    $w = array(25, 89, 22, 22, 22);
    $pdf->SetFillColor(224);
    $pdf->SetFont('', 'B');
    $pdf->SetCellPadding(2);
    $pdf->Cell($w[0], 6, 'SKU', 1, 0, 'C', 1);
    $pdf->Cell($w[1], 6, 'Item', 1, 0, 'C', 1);
    $pdf->Cell($w[2], 6, 'Quantity', 1, 0, 'C', 1);
    $pdf->Cell($w[3], 6, 'Rate', 1, 0, 'C', 1);
    $pdf->Cell($w[4], 6, 'Amount', 1, 0, 'C', 1);
    $pdf->Ln();
    $pdf->SetFillColor(236);
    $pdf->SetTextColor(0);
    $pdf->SetFont('');

    $fill=0;
    $total_amount = 0;
    foreach($items as $item) {
        $height = $pdf->getStringHeight($w[1], $item['description']);
        // Check if we need a page break
        if( $pdf->getY() > ($pdf->getPageHeight() - 30 - $height) ) {
            $pdf->AddPage();
            $pdf->SetFillColor(224);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, 'SKU', 1, 0, 'C', 1);
            $pdf->Cell($w[1], 6, 'Item', 1, 0, 'C', 1);
            $pdf->Cell($w[2], 6, 'Quantity', 1, 0, 'C', 1);
            $pdf->Cell($w[3], 6, 'Rate', 1, 0, 'C', 1);
            $pdf->Cell($w[4], 6, 'Amount', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(236);
            $pdf->SetTextColor(0);
            $pdf->SetFont('');
        }
        $pdf->MultiCell($w[0], $height, $item['sku'], 1, 'L', $fill, 
            0, '', '', true, 0, false, true, 0, 'T', false);
        $pdf->MultiCell($w[1], $height, $item['description'], 1, 'L', $fill, 
            0, '', '', true, 0, false, true, 0, 'T', false);
        $pdf->MultiCell($w[2], $height, $item['quantity_ordered'], 1, 'R', $fill, 
            0, '', '', true, 0, false, true, 0, 'T', false);
        $pdf->MultiCell($w[3], $height, '$' . number_format($item['unit_amount'], 2), 1, 'R', $fill, 
            0, '', '', true, 0, false, true, 0, 'T', false);
        $item_total = $item['quantity_ordered'] * $item['unit_amount'];
        $total_amount += $item_total;
        $pdf->MultiCell($w[4], $height, '$' . number_format($item_total, 2), 1, 'R', $fill, 
            1, '', '', true, 0, false, true, 0, 'T', false);
        $fill=!$fill;
    }
    $pdf->SetFont('', 'B');
    $pdf->Cell($w[0] + $w[1], 6, '', 0, 0, 'C', 0);
    $pdf->Cell($w[2] + $w[3], 6, 'Total', 1, 0, 'R', 1);
    $pdf->Cell($w[4], 6, '$' . number_format($total_amount, 2), 1, 0, 'R', 1);

    //
    // Check if there is a notes to be displayed
    //
    if( isset($po['notes']) 
        && $po['notes'] != '' ) {
        $pdf->Ln();
        $pdf->SetFont('');
        $pdf->MultiCell(180, 5, $po['notes'], 0, 'L');
    }

    //
    // Check if there is a message to be displayed
    //
    if( isset($po_settings['purchaseorder-bottom-message']) && $po_settings['purchaseorder-bottom-message'] != '' ) {
        $pdf->Ln();
        $pdf->SetFont('');
        $pdf->MultiCell(180, 5, $po_settings['purchaseorder-bottom-message'], 0, 'L');
    }

    $filename = 'po_' . $po['po_number'] . '.pdf';

    return array('stat'=>'ok', 'pdf'=>$pdf, 'filename'=>$filename);
}
?>
