<?php
//
// Description
// -----------
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wineproduction_importPrices(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.importPrices'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    //
    // Check to see if an image was uploaded
    //
    if( isset($_FILES['uploadfile']['error']) && $_FILES['uploadfile']['error'] == UPLOAD_ERR_INI_SIZE ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.202', 'msg'=>'Upload failed, file too large.'));
    }
    // FIXME: Add other checkes for $_FILES['uploadfile']['error']

    //
    // Make sure a file was submitted
    //
    if( !isset($_FILES) || !isset($_FILES['uploadfile']) || $_FILES['uploadfile']['tmp_name'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.203', 'msg'=>'No file specified.'));
    }

    ini_set('memory_limit', '4096M');
    require('/ciniki/ciniki-lib/PHPExcel/PHPExcel.php');

    $start = 1;
    $size = 1000;
    $products = array();

    /**  Define a Read Filter class implementing PHPExcel_Reader_IReadFilter  */ 
    try {
        class MyReadFilter implements PHPExcel_Reader_IReadFilter 
        { 
            // Defaults for start and size
            public $_start = 1;
            public $_size = 1000;
            public function readCell($column, $row, $worksheetName = '') { 
                if( $row >= $this->_start && $row < ($this->_start + $this->_size)) {
                    return true;
                }
                return false; 
            } 
        } 
        /**  Create an Instance of our Read Filter  **/ 
        $filterSubset = new MyReadFilter(); 
        $filterSubset->_start = $start;
        $filterSubset->_size = $size;

        $inputFileType = PHPExcel_IOFactory::identify($_FILES['uploadfile']['tmp_name']);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadFilter($filterSubset);
//        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($_FILES['uploadfile']['tmp_name']);

        $objWorksheet = $objPHPExcel->getActiveSheet();
        $numRows = $objWorksheet->getHighestRow(); // e.g. 10
        $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
        $numCols = PHPExcel_Cell::columnIndexFromString($highestColumn); 

        //
        // Find all the wines in the spreadsheet
        //
        $skucols = array();
        $namecols = array();
        $pricecols = array();
        $qtycols = array();
        $ptype = 10;
        for($row = $start; $row <= ($start + ($size-1)) && $row <= $numRows; $row++) {
            if( count($skucols) == 0 ) {
                for($col = 0; $col < $numCols; $col++) {
                    $v = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                    if( preg_match("/sku/", strtolower($v)) ) {
                        $skucols[] = $col;
                    } elseif( preg_match("/(^name|^description|white wines|red wines|products)/", strtolower($v)) ) {
                        $namecols[] = $col;
                    } elseif( preg_match("/(price|list)/", strtolower($v)) ) {
                        $pricecols[] = $col;
                    } elseif( preg_match("/(qty|quantity)/", strtolower($v)) ) {
                        $qtycols[] = $col;
                    }
                }
            } else {    
                $v = $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();
                if( $v != null && preg_match("/(equipment|accessories)/", strtolower($v)) ) {
                    $ptype = 90;
                }
                for($i = 0; $i < count($skucols); $i++) {
                    $sku = $objWorksheet->getCellByColumnAndRow($skucols[$i], $row)->getValue();
                    if( $sku != null && is_numeric($sku) ) {
                        $name = $objWorksheet->getCellByColumnAndRow($namecols[$i], $row)->getValue();
                        $name = preg_replace("/\\xe2\\x80\\x93/", "-", $name);
//                        $name = preg_replace("/\\xc3\\xa9/","e", $name);
//                        $name = preg_replace("/\\xc3\\xa8/","e", $name);
//                        $encoding = mb_detect_encoding($name);
//                        if( $encoding != 'ASCII' ) { 
//                            $name = mb_convert_encoding($name, 'ASCII', $encoding);
//                        }
                        $price = $objWorksheet->getCellByColumnAndRow($pricecols[$i], $row)->getValue();
                        $products[$sku] = array(
                            'name' => $name,
                            'permalink' => ciniki_core_makePermalink($ciniki, $name),
                            'ptype' => $ptype, 
                            'flags' => 0,
                            'status' => 10,
                            'supplier_id' => 0,
                            'supplier_item_number' => $sku,
                            'list_price' => $price,
                            );
                    }
                }
            }
        }
    } catch(Exception $e) {
        error_log('Error: Unable to understand spreadsheet');
        exit;
    }

//    foreach($products as $product) {
//        error_log($product['supplier_item_number'] . ', ' . $product['name'] . ' -- ' . $product['list_price']);
//    }
//    return array('stat'=>'ok');

    //
    // Load current wines
    //
    $strsql = "SELECT id, name, supplier_item_number, list_price "
        . "FROM ciniki_wineproduction_products "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status = 10 "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'skus', 'fname'=>'supplier_item_number', 'fields'=>array('supplier_item_number')),
        array('container'=>'products', 'fname'=>'id', 'fields'=>array('id', 'name', 'supplier_item_number', 'list_price')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.206', 'msg'=>'Unable to load products', 'err'=>$rc['err']));
    }
    $skus = isset($rc['skus']) ? $rc['skus'] : array();

    foreach($products as $product) {
        //
        // Check if sku exists, and update if required
        //
        if( isset($skus[$product['supplier_item_number']]['products']) ) {
            foreach($skus[$product['supplier_item_number']]['products'] as $dbproduct) {
                $update_args = array();
                if( $product['name'] != $dbproduct['name'] ) {
                    $update_args['name'] = $product['name'];
                }
                if( $product['list_price'] != $dbproduct['list_price'] ) {
                    $update_args['list_price'] = $product['list_price'];
                }
                if( count($update_args) > 0 ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
                    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.wineproduction.product', $dbproduct['id'], $update_args, 0x04);
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.207', 'msg'=>'Unable to update the product', 'err'=>$rc['err']));
                    }
                }
            }
        } 
        //
        // Add product if is doesn't exist
        //
        else {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
            $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wineproduction.product', $product, 0x04);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.208', 'msg'=>'Unable to add the product', 'err'=>$rc['err']));
            }
        
        }
    }



    return array('stat'=>'ok');
}
?>
