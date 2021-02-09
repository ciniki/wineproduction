function ciniki_wineproduction_products() {
    //
    // The panel to list the product
    //
    this.menu = new M.panel('Products', 'ciniki_wineproduction_products', 'menu', 'mc', 'xlarge narrowaside', 'sectioned', 'ciniki.wineproduction.products.menu');
    this.menu.tag10 = '';
    this.menu.tag11 = '';
    this.menu.tag12 = '';
    this.menu.tag13 = '';
    this.menu.tag14 = '';
    this.menu.tag15 = '';
    this.menu.supplier_id = '';
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.sections = {
        '_tabs':{'label':'', 'type':'paneltabs', 'selected':'suppliers', 'aside':'yes', 'tabs':{
            'suppliers':{'label':'Suppliers', 'fn':'M.ciniki_wineproduction_products.menu.switchTab("suppliers");'},
            'categories':{'label':'Categories', 'fn':'M.ciniki_wineproduction_products.menu.switchTab("categories");'},
            'varietals':{'label':'Varietals', 'fn':'M.ciniki_wineproduction_products.menu.switchTab("varietals");'},
            'obs':{'label':'O/B/S', 'fn':'M.ciniki_wineproduction_products.menu.switchTab("obs");'},
            }},
        'tags10':{'label':'Categories', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { return M.ciniki_wineproduction_products.menu.sections._tabs.selected == 'categories' ? 'yes' : 'hidden';},
            'editFn':function(s, i, d) {
                return 'M.ciniki_wineproduction_products.tagdetail.open(\'M.ciniki_wineproduction_products.menu.open();\',10,\'' + d.permalink + '\',\'\');';
                },
            },
        'tags11':{'label':'Sub Categories', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { return M.ciniki_wineproduction_products.menu.sections._tabs.selected == 'categories' ? 'yes' : 'hidden';},
            'editFn':function(s, i, d) {
                return 'M.ciniki_wineproduction_products.tagdetail.open(\'M.ciniki_wineproduction_products.menu.open();\',11,\'' + d.permalink + '\',\'\');';
                },
            },
        'tags12':{'label':'Varietals', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { return M.ciniki_wineproduction_products.menu.sections._tabs.selected == 'varietals' ? 'yes' : 'hidden';},
            'editFn':function(s, i, d) {
                return 'M.ciniki_wineproduction_products.tagdetail.open(\'M.ciniki_wineproduction_products.menu.open();\',12,\'' + d.permalink + '\',\'\');';
                },
            },
        'tags13':{'label':'Oak', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { return M.ciniki_wineproduction_products.menu.sections._tabs.selected == 'obs' ? 'yes' : 'hidden';},
            'editFn':function(s, i, d) {
                return 'M.ciniki_wineproduction_products.tagdetail.open(\'M.ciniki_wineproduction_products.menu.open();\',13,\'' + d.permalink + '\',\'\');';
                },
            },
        'tags14':{'label':'Body', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { return M.ciniki_wineproduction_products.menu.sections._tabs.selected == 'obs' ? 'yes' : 'hidden';},
            'editFn':function(s, i, d) {
                return 'M.ciniki_wineproduction_products.tagdetail.open(\'M.ciniki_wineproduction_products.menu.open();\',14,\'' + d.permalink + '\',\'\');';
                },
            },
        'tags15':{'label':'Sweetness', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { return M.ciniki_wineproduction_products.menu.sections._tabs.selected == 'obs' ? 'yes' : 'hidden';},
            'editFn':function(s, i, d) {
                return 'M.ciniki_wineproduction_products.tagdetail.open(\'M.ciniki_wineproduction_products.menu.open();\',15,\'' + d.permalink + '\',null);';
                },
            },
        'suppliers':{'label':'Suppliers', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { return M.ciniki_wineproduction_products.menu.sections._tabs.selected == 'suppliers' ? 'yes' : 'hidden';},
            'editFn':function(s, i, d) {
                return 'M.ciniki_wineproduction_products.supplier.open(\'M.ciniki_wineproduction_products.menu.open();\',\'' + d.id + '\',null);';
                },
            'addTxt':'Add Supplier',
            'addFn':'M.ciniki_wineproduction_products.supplier.open(\'M.ciniki_wineproduction_products.menu.open();\',0,null);'
            },
        'purchaseorders':{'label':'Open Purchase Orders', 'type':'simplegrid', 'num_cols':2, 'aside':'yes',
            'visible':function() { return M.ciniki_wineproduction_products.menu.sections._tabs.selected == 'suppliers' ? 'yes' : 'hidden';},
//            'editFn':function(s, i, d) {
//                return 'M.ciniki_wineproduction_products.supplier.open(\'M.ciniki_wineproduction_products.menu.open();\',\'' + d.id + '\',null);';
//                },
            'noData':'No Open Purchase Orders',
            'addTxt':'Add Purchase Order',
            'addFn':'M.ciniki_wineproduction_products.purchaseorder.open(\'M.ciniki_wineproduction_products.menu.open();\',0,M.ciniki_wineproduction_products.menu.supplier_id,null);'
            },
        '_buttons':{'label':'', 'aside':'yes', 
           'visible':function() { return M.ciniki_wineproduction_products.menu.sections._tabs.selected == 'suppliers' ? 'yes' : 'hidden';},
           'buttons':{ 
                'orders':{'label':'All Purchase Orders', 'fn':'M.ciniki_wineproduction_products.purchaseorders.open(\'M.ciniki_wineproduction_products.menu.open();\');'},
                'import':{'label':'Import Prices', 'fn':'M.ciniki_wineproduction_products.menu.uploadPrices();',
                    'visible':function() { return M.modFlagSet('ciniki.wineproduction', 0x02); },
                    },
                'updater':{'label':'Supplier Updater', 'fn':'M.ciniki_wineproduction_products.updater.open(\'M.ciniki_wineproduction_products.menu.open();\',M.ciniki_wineproduction_products.menu.supplier_id);',
                    'visible':function() { return M.modFlagSet('ciniki.wineproduction', 0x0100); },
                    },
            }},
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':3,
            'headerValues':['Name', 'Status', 'Visible'],
            'cellClasses':[''],
            'hint':'Search product',
            'noData':'No product found',
            },
        '_ptabs':{'label':'', 'type':'paneltabs', 'selected':'overview', 'tabs':{
            'overview':{'label':'Overview', 'fn':'M.ciniki_wineproduction_products.menu.switchPTab("overview");'},
            'pricing':{'label':'Pricing', 'fn':'M.ciniki_wineproduction_products.menu.switchPTab("pricing");',
                'visible':function() { return M.modFlagSet('ciniki.wineproduction', 0x0100); },
                },
            'website':{'label':'Website', 'fn':'M.ciniki_wineproduction_products.menu.switchPTab("website");',
                'visible':function() { return M.modFlagSet('ciniki.wineproduction', 0x0100); },
                },
            'discontinued':{'label':'Discontinued', 'fn':'M.ciniki_wineproduction_products.menu.switchPTab("discontinued");'},
            }},
        'products':{'label':'Product', 'type':'simplegrid', 'num_cols':7,
            'headerValues':['Name', 'Categories', 'SubCategories', 'Visible', 'Price', 'Supplier', 'Inv'],
            'cellClasses':[],
            'sortable':'yes',
            'sortTypes':['text', 'text', 'text', 'text', 'number', 'number'],
            'noData':'No product',
            'addTxt':'Add Product',
            'addFn':'M.ciniki_wineproduction_products.product.open(\'M.ciniki_wineproduction_products.menu.open();\',0,null);'
            },
    }
    this.menu.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            M.api.getJSONBgCb('ciniki.wineproduction.productSearch', {'tnid':M.curTenantID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                M.ciniki_wineproduction_products.menu.liveSearchShow('search',null,M.gE(M.ciniki_wineproduction_products.menu.panelUID + '_' + s), rsp.products);
                });
        }
    }
    this.menu.liveSearchResultValue = function(s, f, i, j, d) {
        switch(j) {
            case 0: return d.name;
            case 1: return d.status_text;
            case 2: return d.visible;
        }

    }
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'M.ciniki_wineproduction_products.product.open(\'M.ciniki_wineproduction_products.menu.open();\',\'' + d.id + '\');';
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'tags10' || s == 'tags11' || s == 'tags12' || s == 'tags13' || s == 'tags14' || s == 'tags15' ) {
            return d.tag_name + '<span class="count">' + d.num_items + '</span>';
        }
        if( s == 'suppliers' ) {
            return d.name + '<span class="count">' + d.num_items + '</span>';
        }
        if( s == 'purchaseorders' ) {
            switch(j) {
                case 0: return d.po_number;
                case 1: return d.name;
            }
        }
        // Suppliers view of products, different from wine store
        if( s == 'products' && !M.modFlagOn('ciniki.wineproduction', 0x0100) ) {
            switch(j) {
                case 0: return d.supplier_item_number;
                case 1: return d.name;
                case 2: return d.categories;
                case 3: return d.subcategories;
                case 4: return d.list_price_display;
            }
        }
        if( s == 'products' && (this.sections._ptabs.selected == 'overview' || this.sections._ptabs.selected == 'discontinued') ) {
            switch(j) {
                case 0: return d.name;
                case 1: return d.categories;
                case 2: return d.subcategories;
                case 3: return d.visible;
                case 4: return d.total_display;
                case 5: return d.supplier_name;
                case 6: return d.inventory_current_num;
            }
        }
        if( s == 'products' && this.sections._ptabs.selected == 'pricing' ) {
            switch(j) {
                case 0: return d.supplier_item_number;
                case 1: return d.name;
                case 2: return (d.list_price != 0 ? d.list_price_display : '');
                case 3: return (d.list_discount_percent != 0 ? d.list_discount_percent_display : '');
                case 4: return (d.cost != 0 ? d.cost_display : '');
                case 5: return (d.kit_unit_amount != 0 ? d.kit_price_display : '');
                case 6: return (d.processing_unit_amount != 0 ? d.processing_price_display : '');
                case 7: return (d.unit_amount != 0 ? d.unit_amount_display : '');
                case 8: return (d.unit_discount_amount != 0 ? d.unit_discount_amount_display : '');
                case 9: return (d.unit_discount_percentage != 0 ? d.unit_discount_percentage_display : '');
                case 10: return (d.tax_amount != 0 ? d.tax_amount_display : '');
                case 11: return d.total_display;
            }
        }
        if( s == 'products' && this.sections._ptabs.selected == 'website' ) {
            if( j == 0 ) {
                if( d.primary_image_id > 0 && d.image != null && d.image != '' ) {
                    return '<img width="75px" height="75px" src=\'' + d.image + '\' />'; 
                } else {
                    return '<img width="75px" height="75px" src=\'/ciniki-mods/core/ui/themes/default/img/noimage_75.jpg\' />';
                }
            }
            switch(j) {
                case 1: return d.name;
                case 2: return d.categories;
                case 3: return d.subcategories;
                case 4: return d.visible;
                case 5: return d.total_display;
            }
        }
    }
    this.menu.rowClass = function(s, i, d) {
        if( s == 'tags10' && this.tag10 == d.permalink ) {
            return 'highlight';
        }
        if( s == 'tags11' && this.tag11 == d.permalink ) {
            return 'highlight';
        }
        if( s == 'tags12' && this.tag12 == d.permalink ) {
            return 'highlight';
        }
        if( s == 'tags13' && this.tag13 == d.permalink ) {
            return 'highlight';
        }
        if( s == 'tags14' && this.tag14 == d.permalink ) {
            return 'highlight';
        }
        if( s == 'tags15' && this.tag15 == d.permalink ) {
            return 'highlight';
        }
        if( s == 'suppliers' && this.supplier_id == d.id ) {
            return 'highlight';
        }
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'tags10' ) {
            return 'M.ciniki_wineproduction_products.menu.openTag(\'tag10\', \'' + d.permalink + '\');';
        }
        if( s == 'tags11' ) {
            return 'M.ciniki_wineproduction_products.menu.openTag(\'tag11\', \'' + d.permalink + '\');';
        }
        if( s == 'tags12' ) {
            return 'M.ciniki_wineproduction_products.menu.openTag(\'tag12\', \'' + d.permalink + '\');';
        }
        if( s == 'tags13' ) {
            return 'M.ciniki_wineproduction_products.menu.openTag(\'tag13\', \'' + d.permalink + '\');';
        }
        if( s == 'tags14' ) {
            return 'M.ciniki_wineproduction_products.menu.openTag(\'tag14\', \'' + d.permalink + '\');';
        }
        if( s == 'tags15' ) {
            return 'M.ciniki_wineproduction_products.menu.openTag(\'tag15\', \'' + d.permalink + '\');';
        }
        if( s == 'suppliers' ) {
            return 'M.ciniki_wineproduction_products.menu.openTag(\'supplier_id\', \'' + d.id + '\');';
        }
        if( s == 'purchaseorders' ) {
            return 'M.ciniki_wineproduction_products.purchaseorder.open(\'M.ciniki_wineproduction_products.menu.open();\',\'' + d.id + '\');';
        }
        if( s == 'products' ) {
            return 'M.ciniki_wineproduction_products.product.open(\'M.ciniki_wineproduction_products.menu.open();\',\'' + d.id + '\',M.ciniki_wineproduction_products.product.nplist);';
        }
    }
    this.menu.openTag = function(t, p) {
        this.tag10 = '';
        this.tag11 = '';
        this.tag12 = '';
        this.tag13 = '';
        this.tag14 = '';
        this.tag15 = '';
        this.supplier_id = '';
        this[t] = p;
        this.open();
    }
    this.menu.switchTab = function(t) {
        this.sections._tabs.selected = t;
        this.refreshSection('_tabs');
        this.showHideSections(['tags10', 'tags11', 'tags12', 'tags13', 'tags14', 'tags15', 'suppliers', 'purchaseorders', '_buttons']);
    }
    this.menu.switchPTab = function(t) {
        this.sections._ptabs.selected = t;
        this.open();
    }

    this.menu.open = function(cb) {
        M.api.getJSONCb('ciniki.wineproduction.products', {'tnid':M.curTenantID, 'tag10':this.tag10, 'tag11':this.tag11, 'tag12':this.tag12, 'tag13':this.tag13, 'tag14':this.tag14, 'tag15':this.tag15, 'supplier_id':this.supplier_id, 'list':this.sections._ptabs.selected}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_products.menu;
            p.data = rsp;
            p.nplist = (rsp.nplist != null ? rsp.nplist : null);
            if( p.sections._ptabs.selected == 'pricing' ) {
                p.sections.products.num_cols = 12;
                p.sections.products.sortTypes = ['text', 'text', 'number', 'number', 'number', 'number', 'number', 'number', 'number', 'number', 'number', 'number'];
                p.sections.products.headerValues = ['Sku', 'Name', 'List', 'Disc', 'Cost', 'Kit', 'Proc', 'Price', 'Disc$', 'Disc%', 'Tax', 'Total'];
                p.sections.products.headerClasses = ['','', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright'];
                p.sections.products.cellClasses = ['','', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright', 'alignright'];
            } else if( p.sections._ptabs.selected == 'website' ) {
                p.sections.products.num_cols = 6;
                p.sections.products.sortTypes = ['', 'text', 'text', 'text', 'text', 'number'];
                p.sections.products.headerValues = ['Image', 'Name', 'Categories', 'SubCategories', 'Visible', 'Price'];
                p.sections.products.headerClasses = ['', '', '', '', '', 'alignright'];
                p.sections.products.cellClasses = ['thumbnail', '', '', '', '', 'alignright'];
            } else if( !M.modFlagOn('ciniki.wineproduction', 0x0100) ) {
                p.sections.products.num_cols = 5;
                p.sections.products.sortTypes = ['text', 'text', 'text', 'text', 'number'];
                p.sections.products.headerValues = ['Sku', 'Name', 'Categories', 'SubCategories', 'Price'];
                p.sections.products.headerClasses = ['', '', '', '', 'alignright'];
                p.sections.products.cellClasses = ['', '', '', '', 'alignright'];
            } else { // overview or discontinued
                p.sections.products.num_cols = 7;
                p.sections.products.sortTypes = ['text', 'text', 'text', 'text', 'number', 'text', 'number'];
                p.sections.products.headerValues = ['Name', 'Categories', 'SubCategories', 'Visible', 'Price', 'Supplier', 'Inv'];
                p.sections.products.headerClasses = ['', '', '', '', 'alignright', '', 'alignright'];
                p.sections.products.cellClasses = ['', '', '', '', 'alignright', '', 'alignright'];
            }
            p.sections.purchaseorders.addTxt = (p.supplier_id > 0 ? 'Add Order' : '');
            p.refresh();
            p.show(cb);
        });
    }
    this.menu.uploadPrices = function() {
        if( this.upload == null ) {
            this.upload = M.aE('input', this.panelUID + '_prices_orgfilename_upload', 'image_uploader');
            this.upload.setAttribute('name', 'prices_orgfilename');
            this.upload.setAttribute('type', 'file');
            this.upload.setAttribute('onchange', this.panelRef + '.uploadFile();');
        }
        this.upload.value = '';
        this.upload.click();
    }
    this.menu.uploadFile = function() {
        var f = this.upload;
        M.api.postJSONFile('ciniki.wineproduction.importPrices', 
            {'tnid':M.curTenantID}, 
            f.files[0], 
            function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.menu.open();
            });
    }
    this.menu.addButton('settings', 'Pricing', 'M.ciniki_wineproduction_products.prices.open(\'M.ciniki_wineproduction_products.menu.open();\');');
    this.menu.addClose('Back');

    //
    // The panel to edit Product
    //
    this.product = new M.panel('Product', 'ciniki_wineproduction_products', 'product', 'mc', 'large mediumaside columns', 'sectioned', 'ciniki.wineproduction.products.product');
    this.product.data = null;
    this.product.product_id = 0;
    this.product.nplist = [];
    this.product.sections = {
        'general':{'label':'Product', 'aside':'yes', 'fields':{
            'name':{'label':'Name', 'required':'yes', 'type':'text'},
            'ptype':{'label':'Type', 'type':'toggle', 'toggles':{'10':'Wine', '90':'Other'},
                'onchange':'M.ciniki_wineproduction_products.product.updateForm();',
                },
            'flags':{'label':'Options', 'type':'flags', 'flags':{'1':{'name':'Visible'}}},
            'status':{'label':'Status', 'type':'toggle', 'toggles':{'10':'Active', '60':'Discontinued'}},
            'wine_type':{'label':'Wine Type', 'type':'multitoggle', 'none':'yes', 'visible':'yes',
                'toggles':{'Red':'Red', 'White':'White', 'Specialty':'Specialty'},
                },
            'kit_length':{'label':'# Weeks', 'type':'text', 'visible':'yes', 'size':'small'},
            'inventory_current_num':{'label':'Inventory', 'type':'text', 'size':'small'},
            }},
        // Only show some fields if no wine production and supplier
        'supplier':{'label':'', 'aside':'yes', 'fields':{
            'supplier_id':{'label':'Supplier', 'type':'select', 'options':{}, 
                'visible':function() { return M.modFlagSet('ciniki.wineproduction', 0x0100); },
                'complex_options':{'name':'label', 'value':'value'},
                },
            'supplier_item_number':{'label':'Supplier Item Number', 'type':'text'},
            'list_price':{'label':'List Price', 'type':'text', 'size':'small',
                'onkeyupFn':'M.ciniki_wineproduction_products.product.updateForm',
                },
            'list_discount_percent':{'label':'Discount %', 'type':'text', 'size':'small', 
                'visible':function() { return M.modFlagSet('ciniki.wineproduction', 0x0100); },
                'onkeyupFn':'M.ciniki_wineproduction_products.product.updateForm',
                },
            'cost':{'label':'Cost', 'type':'text', 'size':'small', 'editable':'no',
                'visible':function() { return M.modFlagSet('ciniki.wineproduction', 0x0100); },
                },
            }},
        'price':{'label':'', 'aside':'yes', 
            // visible on when wineproduction on
            'visible':function() { return M.modFlagSet('ciniki.wineproduction', 0x0100); },
            'fields':{
                // Only visible for Wine types
                'kit_price_id':{'label':'Kit Price', 'type':'select', 'options':{}, 
                    'complex_options':{'name':'label', 'value':'id'},
                    'onchangeFn':'M.ciniki_wineproduction_products.product.updateForm',
                    },
                'processing_price_id':{'label':'Processing Price', 'type':'select', 'options':{}, 
                    'complex_options':{'name':'label', 'value':'id'},
                    'onchangeFn':'M.ciniki_wineproduction_products.product.updateForm',
                    },
                // Visible for Suppliers/Other
                'unit_amount':{'label':'Price', 'type':'text', 'size':'small', 'editable':'no'},
                'unit_discount_amount':{'label':'Discount Amount', 'type':'text', 'size':'small'},
                'unit_discount_percentage':{'label':'Discount Percent', 'type':'text', 'size':'small'},
                'taxtype_id':{'label':'Taxes', 'type':'select', 'options':{}, 'complex_options':{'name':'name', 'value':'id'}},
            }},
        '_primary_image_id':{'label':'Primary Image', 'type':'imageform', 'aside':'yes', 'fields':{
            'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'delete':'yes', 'history':'no',
                'addDropImage':function(iid) {
                    M.ciniki_wineproduction_products.product.setFieldValue('primary_image_id', iid);
                    return true;
                    },
                'deleteImage':function(iid) {
                    M.ciniki_wineproduction_products.product.setFieldValue('primary_image_id', 0);
                    return true;
                    },
                'addDropImageRefresh':'',
             },
        }},
        '_tags10':{'label':'Categories', 'aside':'yes', 'panelcolumn':1, 'fields':{
            'tags10':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'New Category:'},
            }},
        '_tags11':{'label':'Sub Categories', 'aside':'yes', 'panelcolumn':1, 'fields':{
            'tags11':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'New Sub Category:'},
            }},
        '_tags12':{'label':'Varietal', 'aside':'yes', 'panelcolumn':1, 'visible':'yes', 'fields':{
            'tags12':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'New Varietal:'},
            }},
        '_tags13':{'label':'Oak', 'aside':'yes', 'panelcolumn':1, 'visible':'yes', 'fields':{
            'tags13':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Oak:'},
            }},
        '_tags14':{'label':'Body', 'aside':'yes', 'panelcolumn':1, 'visible':'yes', 'fields':{
            'tags14':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Body:'},
            }},
        '_tags15':{'label':'Sweetness', 'aside':'yes', 'panelcolumn':1, 'visible':'yes', 'fields':{
            'tags15':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Sweetness:'},
            }},
        '_synopsis':{'label':'Synopsis', 'panelcolumn':2, 'fields':{
            'synopsis':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
            }},
        '_description':{'label':'Description', 'panelcolumn':2, 'fields':{
            'description':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
            }},
        'images':{'label':'Additional Images', 'panelcolumn':2, 'type':'simplethumbs'},
        '_images':{'label':'', 'type':'simplegrid', 'panelcolumn':2, 'num_cols':1,
            'addTxt':'Add Image',
            'addFn':'M.ciniki_wineproduction_products.product.save("M.ciniki_wineproduction_products.image.open(\'M.ciniki_wineproduction_products.product.open();\',0,M.ciniki_wineproduction_products.product.product_id);");',
            },
        'files':{'label':'Files', 'type':'simplegrid', 'num_cols':1, 'panelcolumn':2,
            'headerValues':null,
            'cellClasses':['multiline'],
            'addTxt':'Add File',
            'addFn':'M.ciniki_wineproduction_products.product.save("M.ciniki_wineproduction_products.addfile.open(\'M.ciniki_wineproduction_products.product.open();\',M.ciniki_wineproduction_products.product.product_id);");',
            },
        '_buttons':{'label':'', 'panelcolumn':2, 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_products.product.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.ciniki_wineproduction_products.product.product_id > 0 ? 'yes' : 'no'; },
                'fn':'M.ciniki_wineproduction_products.product.remove();'},
            }},
        };
    this.product.fieldValue = function(s, i, d) { return this.data[i]; }
    this.product.thumbFn = function(s, i, d) {
             return 'M.ciniki_wineproduction_products.product.save("M.ciniki_wineproduction_products.image.open(\'M.ciniki_wineproduction_products.product.open();\',' + d.id + ',M.ciniki_wineproduction_products.product.product_id);");';
    }
    this.product.updateForm = function() {
        if( !M.modFlagOn('ciniki.wineproduction', 0x0100) ) {
            return true;
        }
        if( this.formValue('ptype') == 10 ) {
            this.sections.general.fields.wine_type.visible = 'yes';
            this.sections.general.fields.kit_length.visible = 'yes';
            this.showHideFormField('general', 'wine_type');
            this.showHideFormField('general', 'kit_length');
            this.sections.price.fields.kit_price_id.visible = 'yes';
            this.sections.price.fields.processing_price_id.visible = 'yes';
            this.showHideFormField('price', 'kit_price_id');
            this.showHideFormField('price', 'processing_price_id');
            this.sections.price.fields.unit_amount.editable = 'no';
            this.refreshFormField('price', 'unit_amount');
            var list_price = parseFloat(this.formValue('list_price').replace(/[^0-9\.]/, ""));
            var list_discount_percent = parseFloat(this.formValue('list_discount_percent').replace(/[^0-9\.]/, ""));
            if( list_discount_percent > 0 ) {
                var cost = list_price - (list_price * (list_discount_percent/100));
            } else {
                var cost = list_price;
            }
            this.setFieldValue('cost', '$' + cost.toFixed(2));
            var kit_price_id = this.formValue('kit_price_id');
            var kit_unit_amount = 0;
            var processing_price_id = this.formValue('processing_price_id');
            var processing_unit_amount = 0;
            for(var i in this.sections.price.fields.kit_price_id.options) {
                if( this.sections.price.fields.kit_price_id.options[i].id == kit_price_id ) {
                    kit_unit_amount = parseFloat(this.sections.price.fields.kit_price_id.options[i].unit_amount);
                }
            }
            for(var i in this.sections.price.fields.processing_price_id.options) {
                if( this.sections.price.fields.processing_price_id.options[i].id == processing_price_id ) {
                    processing_unit_amount = parseFloat(this.sections.price.fields.processing_price_id.options[i].unit_amount);
                }
            }
            var unit_amount = kit_unit_amount + processing_unit_amount;
            this.setFieldValue('unit_amount', '$' + unit_amount.toFixed(2));
            this.sections._tags12.visible = 'yes';
            this.sections._tags13.visible = 'yes';
            this.sections._tags14.visible = 'yes';
            this.sections._tags15.visible = 'yes';
            this.showHideSections(['_tags12', '_tags13', '_tags14', '_tags15']);
        } else {
            this.sections.general.fields.wine_type.visible = 'no';
            this.sections.general.fields.kit_length.visible = 'no';
            this.showHideFormField('general', 'wine_type');
            this.showHideFormField('general', 'kit_length');
            this.sections.price.fields.kit_price_id.visible = 'no';
            this.sections.price.fields.processing_price_id.visible = 'no';
            this.showHideFormField('price', 'kit_price_id');
            this.showHideFormField('price', 'processing_price_id');
            this.sections.price.fields.unit_amount.editable = 'yes';
            this.refreshFormField('price', 'unit_amount');
            this.sections._tags12.visible = 'hidden';
            this.sections._tags13.visible = 'hidden';
            this.sections._tags14.visible = 'hidden';
            this.sections._tags15.visible = 'hidden';
            this.showHideSections(['_tags12', '_tags13', '_tags14', '_tags15']);
        }
        
    }
    this.product.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.productHistory', 'args':{'tnid':M.curTenantID, 'product_id':this.product_id, 'field':i}};
    }
    this.product.cellValue = function(s, i, j, d) {
        return d.name;
    }
    this.product.rowFn = function(s, i, d) {
        return 'M.ciniki_wineproduction_products.product.save("M.ciniki_wineproduction_products.editfile.open(\'M.ciniki_wineproduction_products.product.open();\',\'' + d.id + '\',M.ciniki_wineproduction_products.product.product_id);");';
    }
    this.product.open = function(cb, pid, list) {
        if( pid != null ) { this.product_id = pid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('ciniki.wineproduction.productGet', {'tnid':M.curTenantID, 'product_id':this.product_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_products.product;
            p.data = rsp.product;
            p.sections._tags10.fields.tags10.tags = rsp.tags10;
            p.sections._tags11.fields.tags11.tags = rsp.tags11;
            p.sections._tags12.fields.tags12.tags = rsp.tags12;
            p.sections._tags13.fields.tags13.tags = rsp.tags13;
            p.sections._tags14.fields.tags14.tags = rsp.tags14;
            p.sections._tags15.fields.tags15.tags = rsp.tags15;
            p.sections.supplier.fields.supplier_id.options = rsp.suppliers;
            p.sections.price.fields.kit_price_id.options = rsp.kit_prices;
            p.sections.price.fields.processing_price_id.options = rsp.processing_prices;
            p.sections.price.fields.taxtype_id.options = rsp.taxtypes;
            p.refresh();
            p.show(cb);
            p.updateForm();
        });
    }
    this.product.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wineproduction_products.product.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.product_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wineproduction.productUpdate', {'tnid':M.curTenantID, 'product_id':this.product_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.wineproduction.productAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.product.product_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.product.remove = function() {
        if( confirm('Are you sure you want to remove product?') ) {
            M.api.getJSONCb('ciniki.wineproduction.productDelete', {'tnid':M.curTenantID, 'product_id':this.product_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.product.close();
            });
        }
    }
    this.product.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.product_id) < (this.nplist.length - 1) ) {
            return 'M.ciniki_wineproduction_products.product.save(\'M.ciniki_wineproduction_products.product.open(null,' + this.nplist[this.nplist.indexOf('' + this.product_id) + 1] + ');\');';
        }
        return null;
    }
    this.product.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.product_id) > 0 ) {
            return 'M.ciniki_wineproduction_products.product.save(\'M.ciniki_wineproduction_products.product.open(null,' + this.nplist[this.nplist.indexOf('' + this.product_id) - 1] + ');\');';
        }
        return null;
    }
    this.product.addButton('save', 'Save', 'M.ciniki_wineproduction_products.product.save();');
    this.product.addClose('Cancel');
    this.product.addButton('next', 'Next');
    this.product.addLeftButton('prev', 'Prev');

    //
    // The panel to edit Item Image
    //
    this.image = new M.panel('Product Image', 'ciniki_wineproduction_products', 'image', 'mc', 'medium', 'sectioned', 'ciniki.wineproduction.products.image');
    this.image.data = null;
    this.image.product_id = 0;
    this.image.productimage_id = 0;
    this.image.nplist = [];
    this.image.sections = {
        '_image_id':{'label':'Image', 'type':'imageform', 'aside':'yes', 'fields':{
            'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no',
                'addDropImage':function(iid) {
                    M.ciniki_wineproduction_products.image.setFieldValue('image_id', iid);
                    return true;
                    },
                'addDropImageRefresh':'',
             },
        }},
        'general':{'label':'', 'fields':{
            'name':{'label':'Name', 'type':'text'},
            'webflags':{'label':'Options', 'type':'flags', 'flags':{'1':{'name':'Visible'}}},
            }},
        '_description':{'label':'Description', 'fields':{
            'description':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_products.image.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.ciniki_wineproduction_products.image.productimage_id > 0 ? 'yes' : 'no'; },
                'fn':'M.ciniki_wineproduction_products.image.remove();'},
            }},
        };
    this.image.fieldValue = function(s, i, d) { return this.data[i]; }
    this.image.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.productImageHistory', 'args':{'tnid':M.curTenantID, 'productimage_id':this.productimage_id, 'field':i}};
    }
    this.image.open = function(cb, iid, product_id, list) {
        if( iid != null ) { this.productimage_id = iid; }
        if( product_id != null ) { this.product_id = product_id; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('ciniki.wineproduction.productImageGet', {'tnid':M.curTenantID, 'productimage_id':this.productimage_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_products.image;
            p.data = rsp.image;
            p.refresh();
            p.show(cb);
        });
    }
    this.image.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wineproduction_products.image.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.productimage_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wineproduction.productImageUpdate', {'tnid':M.curTenantID, 'productimage_id':this.productimage_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.wineproduction.productImageAdd', {'tnid':M.curTenantID, 'product_id':this.product_id}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.image.productimage_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.image.remove = function() {
        M.confirm('Are you sure you want to remove item image?',null,function() {
            M.api.getJSONCb('ciniki.wineproduction.productImageDelete', {'tnid':M.curTenantID, 'productimage_id':M.ciniki_wineproduction_products.image.productimage_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.image.close();
            });
        });
    }
    this.image.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.productimage_id) < (this.nplist.length - 1) ) {
            return 'M.ciniki_wineproduction_products.image.save(\'M.ciniki_wineproduction_products.image.open(null,' + this.nplist[this.nplist.indexOf('' + this.productimage_id) + 1] + ');\');';
        }
        return null;
    }
    this.image.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.productimage_id) > 0 ) {
            return 'M.ciniki_wineproduction_products.image.save(\'M.ciniki_wineproduction_products.image.open(null,' + this.nplist[this.nplist.indexOf('' + this.productimage_id) - 1] + ');\');';
        }
        return null;
    }
    this.image.addButton('save', 'Save', 'M.ciniki_wineproduction_products.image.save();');
    this.image.addClose('Cancel');
    this.image.addButton('next', 'Next');
    this.image.addLeftButton('prev', 'Prev');

    //
    // The panel to edit Supplier
    //
    this.supplier = new M.panel('Supplier', 'ciniki_wineproduction_products', 'supplier', 'mc', 'medium', 'sectioned', 'ciniki.wineproduction.products.supplier');
    this.supplier.data = null;
    this.supplier.supplier_id = 0;
    this.supplier.nplist = [];
    this.supplier.sections = {
        'general':{'label':'', 'fields':{
            'name':{'label':'Name', 'required':'yes', 'type':'text'},
            'supplier_tnid':{'label':'Ciniki Tenant', 'type':'select', 'options':{}, 
                'active':'no',
                'complex_options':{'value':'id', 'name':'name'},
                },
            'po_email':{'label':'PO Email', 'type':'text'},
            }},
        '_po_name_address':{'label':'Purchase Order Name & Address', 'fields':{
            'po_name_address':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_products.supplier.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.ciniki_wineproduction_products.supplier.supplier_id > 0 ? 'yes' : 'no'; },
                'fn':'M.ciniki_wineproduction_products.supplier.remove();'},
            }},
        };
    this.supplier.fieldValue = function(s, i, d) { return this.data[i]; }
    this.supplier.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.supplierHistory', 'args':{'tnid':M.curTenantID, 'supplier_id':this.supplier_id, 'field':i}};
    }
    this.supplier.open = function(cb, sid, list) {
        if( sid != null ) { this.supplier_id = sid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('ciniki.wineproduction.supplierGet', {'tnid':M.curTenantID, 'supplier_id':this.supplier_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_products.supplier;
            p.data = rsp.supplier;
            p.sections.general.fields.supplier_tnid.active = 'no';
            p.sections.general.fields.supplier_tnid.options = {};
            // Only sysadmins are allowed to join a wine tenant to supplier tenant
            if( (M.userPerms&0x01) == 0x01 ) {
                p.sections.general.fields.supplier_tnid.active = 'yes';
                p.sections.general.fields.supplier_tnid.options = rsp.suppliers;
            }
            p.refresh();
            p.show(cb);
        });
    }
    this.supplier.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wineproduction_products.supplier.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.supplier_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wineproduction.supplierUpdate', {'tnid':M.curTenantID, 'supplier_id':this.supplier_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.wineproduction.supplierAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.supplier.supplier_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.supplier.remove = function() {
        if( confirm('Are you sure you want to remove supplier?') ) {
            M.api.getJSONCb('ciniki.wineproduction.supplierDelete', {'tnid':M.curTenantID, 'supplier_id':this.supplier_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.supplier.close();
            });
        }
    }
    this.supplier.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.supplier_id) < (this.nplist.length - 1) ) {
            return 'M.ciniki_wineproduction_products.supplier.save(\'M.ciniki_wineproduction_products.supplier.open(null,' + this.nplist[this.nplist.indexOf('' + this.supplier_id) + 1] + ');\');';
        }
        return null;
    }
    this.supplier.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.supplier_id) > 0 ) {
            return 'M.ciniki_wineproduction_products.supplier.save(\'M.ciniki_wineproduction_products.supplier.open(null,' + this.nplist[this.nplist.indexOf('' + this.supplier_id) - 1] + ');\');';
        }
        return null;
    }
    this.supplier.addButton('save', 'Save', 'M.ciniki_wineproduction_products.supplier.save();');
    this.supplier.addClose('Cancel');
    this.supplier.addButton('next', 'Next');
    this.supplier.addLeftButton('prev', 'Prev');

    //
    // The edit panel
    //
    this.tagdetail = new M.panel('Detail',
        'ciniki_wineproduction_products', 'tagdetail',
        'mc', 'medium mediumaside', 'sectioned', 'ciniki.wineproduction.products.tagdetail');
    this.tagdetail.data = {};
    this.tagdetail.detail_id = 0;
    this.tagdetail.tag_type = '';
    this.tagdetail.tag_permalink = '';
    this.tagdetail.sections = {
        '_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
            'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 
                'controls':'all', 'history':'no'},
        }},
        '_name':{'label':'', 'aside':'yes', 'fields':{
            'name':{'label':'Name', 'type':'text'},
            'sequence':{'label':'Sequence', 'type':'text', 'size':'small'},
//            'tag_type':{'label':'Sub Category', 'type':'select', 
//                'visible':function() { return M.ciniki_products_category.edit.subcategory_permalink==''?'yes':'no'; },
//                'options':{'':'All'},
//                },
        }},
//        '_formats':{'label':'Display Formats', 
//            'active':function() { return M.ciniki_products_category.edit.subcategory_permalink==''?'yes':'no'; },
//            'aside':'yes', 'fields':{
//                'display':{'label':'Category', 'type':'select', 
//                    'options':{
//                        'default':'Default', 
//                        'tradingcards':'Trading Cards'},
//                    },
//        }},
        '_synopsis':{'label':'Synopsis', 'fields':{
            'synopsis':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
        }},
        '_description':{'label':'Description', 'fields':{
            'description':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
        }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_products.tagdetail.save();'},
        }},
    }
    this.tagdetail.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.productTagDetailHistory', 'args':{'tnid':M.curTenantID,
            'detail_id':this.detail_id, 'field':i}};
    }
    this.tagdetail.addDropImage = function(iid) {
        M.ciniki_wineproduction_products.tagdetail.setFieldValue('primary_image_id', iid, null, null);
        return true;
    }
    this.tagdetail.deleteImage = function(fid) {
        this.setFieldValue(fid, 0, null, null);
        return true;
    }
    this.tagdetail.sectionData = function(s) { 
        return this.data[s];
    }
    this.tagdetail.fieldValue = function(s, i, j, d) {
        return this.data[i];
    }
    this.tagdetail.open = function(cb, tagtype, permalink) {
        this.reset();
        if( tagtype != null ) { 
            this.tag_type = tagtype; 
        }
        if( permalink != null ) { 
            this.tag_permalink = permalink; 
        }
        M.api.getJSONCb('ciniki.wineproduction.productTagDetailGet', {'tnid':M.curTenantID,
            'tag_type':this.tag_type, 'permalink':this.tag_permalink}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_wineproduction_products.tagdetail;
                p.data = rsp.detail;
                p.detail_id = rsp.detail.id;
//                p.sections._name.fields.tag_type.options = {'0':'All'};
//                if( rsp.tag_types != null ) {
//                    for(var i in rsp.tag_types) {
//                        p.sections._name.fields.tag_type.options[i] = rsp.tag_types[i];
//                    }
//                }
                p.refresh();
                p.show(cb);
            });
    }
    this.tagdetail.save = function() {
        if( this.detail_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) { 
                M.api.postJSONCb('ciniki.wineproduction.productTagDetailUpdate', {'tnid':M.curTenantID,
                    'detail_id':this.detail_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        M.ciniki_wineproduction_products.tagdetail.close();
                    });
            } else {
                this.close();
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.wineproduction.productTagDetailAdd', {'tnid':M.curTenantID,
                'tag_type':this.tag_type, 'permalink':this.tag_permalink}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_wineproduction_products.tagdetail.close();
                });
        }
    }
    this.tagdetail.addButton('save', 'Save', 'M.ciniki_wineproduction_products.tagdetail.save();');
    this.tagdetail.addClose('Cancel');

    //
    // The panel to list the productPrice
    //
    this.prices = new M.panel('Product Pricing', 'ciniki_wineproduction_products', 'prices', 'mc', 'medium', 'sectioned', 'ciniki.wineproduction.products.prices');
    this.prices.data = {};
    this.prices.nplist = [];
    this.prices.sections = {
        'kit_prices':{'label':'Kit Prices', 'type':'simplegrid', 'num_cols':2,
            'cellClasses':['', 'alignright'],
            'noData':'No kit prices',
            'addTxt':'Add Price',
            'addFn':'M.ciniki_wineproduction_products.price.open(\'M.ciniki_wineproduction_products.prices.open();\',0,10,null);'
            },
        'processing_prices':{'label':'Processing Prices', 'type':'simplegrid', 'num_cols':2,
            'cellClasses':['', 'alignright'],
            'noData':'No processing prices',
            'addTxt':'Add Price',
            'addFn':'M.ciniki_wineproduction_products.price.open(\'M.ciniki_wineproduction_products.prices.open();\',0,20,null);'
            },
    }
    this.prices.cellValue = function(s, i, j, d) {
        switch(j) {
            case 0: return d.name;
            case 1: return d.unit_amount_display;
        }
    }
    this.prices.rowFn = function(s, i, d) {
        return 'M.ciniki_wineproduction_products.price.open(\'M.ciniki_wineproduction_products.prices.open();\',\'' + d.id + '\',null,M.ciniki_wineproduction_products.price.nplist);';
    }
    this.prices.open = function(cb) {
        M.api.getJSONCb('ciniki.wineproduction.productPriceList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_products.prices;
            p.data = rsp;
            p.nplist = (rsp.nplist != null ? rsp.nplist : null);
            p.refresh();
            p.show(cb);
        });
    }
    this.prices.addClose('Back');

    //
    // The panel to edit Price
    //
    this.price = new M.panel('Price', 'ciniki_wineproduction_products', 'price', 'mc', 'narrow', 'sectioned', 'ciniki.wineproduction.products.price');
    this.price.data = null;
    this.price.price_id = 0;
    this.price.price_type = 10;
    this.price.nplist = [];
    this.price.sections = {
        'general':{'label':'', 'fields':{
            'name':{'label':'Name', 'required':'yes', 'type':'text'},
            'sequence':{'label':'Order', 'type':'text', 'size':'small'},
            'unit_amount':{'label':'Price', 'type':'text', 'size':'small'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_products.price.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.ciniki_wineproduction_products.price.price_id > 0 ? 'yes' : 'no'; },
                'fn':'M.ciniki_wineproduction_products.price.remove();'},
            }},
        };
    this.price.fieldValue = function(s, i, d) { return this.data[i]; }
    this.price.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.productPriceHistory', 'args':{'tnid':M.curTenantID, 'price_id':this.price_id, 'field':i}};
    }
    this.price.open = function(cb, pid, ptype, list) {
        if( pid != null ) { this.price_id = pid; }
        if( ptype != null ) { this.price_type = ptype; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('ciniki.wineproduction.productPriceGet', {'tnid':M.curTenantID, 'price_id':this.price_id, 'price_type':this.price_type}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_products.price;
            p.data = rsp.price;
            if( rsp.price.price_type == 10 || (rsp.price.price_type == 0 && p.price_type == 10) ) {
                p.title = 'Kit Price';
            } else if( rsp.price.price_type == 20 || (rsp.price.price_type == 0 && p.price_type == 20) ) {
                p.title = 'Processing Price';
            }
            p.refresh();
            p.show(cb);
        });
    }
    this.price.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wineproduction_products.price.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.price_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wineproduction.productPriceUpdate', {'tnid':M.curTenantID, 'price_id':this.price_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.wineproduction.productPriceAdd', {'tnid':M.curTenantID, 'price_type':this.price_type}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.price.price_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.price.remove = function() {
        if( confirm('Are you sure you want to remove productPrice?') ) {
            M.api.getJSONCb('ciniki.wineproduction.productPriceDelete', {'tnid':M.curTenantID, 'price_id':this.price_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.price.close();
            });
        }
    }
    this.price.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.price_id) < (this.nplist.length - 1) ) {
            return 'M.ciniki_wineproduction_products.price.save(\'M.ciniki_wineproduction_products.price.open(null,' + this.nplist[this.nplist.indexOf('' + this.price_id) + 1] + ');\');';
        }
        return null;
    }
    this.price.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.price_id) > 0 ) {
            return 'M.ciniki_wineproduction_products.price.save(\'M.ciniki_wineproduction_products.price.open(null,' + this.nplist[this.nplist.indexOf('' + this.price_id) - 1] + ');\');';
        }
        return null;
    }
    this.price.addButton('save', 'Save', 'M.ciniki_wineproduction_products.price.save();');
    this.price.addClose('Cancel');
    this.price.addButton('next', 'Next');
    this.price.addLeftButton('prev', 'Prev');

    //
    // The panel to display the add form
    //
    this.addfile = new M.panel('Add File',
        'ciniki_wineproduction_products', 'addfile',
        'mc', 'medium', 'sectioned', 'ciniki.wineproduction.products.files');
    this.addfile.data = {}; 
    this.addfile.sections = {
        '_file':{'label':'File', 'fields':{
            'uploadfile':{'label':'', 'type':'file', 'hidelabel':'yes'},
        }},
        'info':{'label':'Information', 'type':'simpleform', 'fields':{
            'name':{'label':'Title', 'type':'text'},
            'webflags':{'label':'Website', 'type':'flags', 'default':'1', 'flags':{'1':{'name':'Visible'}}},
        }},
        '_save':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_products.addfile.save();'},
        }},
    };
//    this.addfile.fieldValue = function(s, i, d) { 
//        if( this.data[i] != null ) {
//            return this.data[i]; 
//        } 
//        return ''; 
//    };
    this.addfile.open = function(cb, pid) {
        this.product_id = pid;
        this.reset();
        this.file_id = 0;
        this.data = {'name':''};
        this.refresh();
        this.show(cb);
    }
    this.addfile.save = function() {
        var c = this.serializeFormData('yes');

        if( c != '' ) {
            M.api.postJSONFormData('ciniki.wineproduction.productFileAdd', {'tnid':M.curTenantID, 'product_id':this.product_id}, c,
                function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_wineproduction_products.addfile.close();
                });
        } else {
            M.ciniki_wineproduction_products.addfile.close();
        }
    };
    this.addfile.addButton('save', 'Save', 'M.ciniki_wineproduction_products.addfile.save();');
    this.addfile.addClose('Cancel');

    //
    // The panel to display the edit form
    //
    this.editfile = new M.panel('File',
        'ciniki_wineproduction_products', 'editfile',
        'mc', 'medium', 'sectioned', 'ciniki.wineproduction.products.editfile');
    this.editfile.file_id = 0;
    this.editfile.data = null;
    this.editfile.sections = {
        'info':{'label':'Details', 'type':'simpleform', 'fields':{
            'name':{'label':'Title', 'type':'text'},
            'webflags':{'label':'Website', 'type':'flags', 'default':'1', 'flags':{'1':{'name':'Visible'}}},
        }},
        '_save':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_products.editfile.save();'},
            'download':{'label':'Download', 'fn':'M.ciniki_wineproduction_products.editfile.download();'},
            'delete':{'label':'Delete', 'fn':'M.ciniki_wineproduction_products.editfile.remove();'},
        }},
    };
//    this.editfile.fieldValue = function(s, i, d) { 
//        return this.data[i]; 
//    }
    this.editfile.sectionData = function(s) {
        return this.data[s];
    };
    this.editfile.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.productFileHistory', 'args':{'tnid':M.curTenantID, 
            'file_id':this.file_id, 'field':i}};
    };
    this.editfile.open = function(cb, fid) {
        if( fid != null ) { this.file_id = fid; }
        var rsp = M.api.getJSONCb('ciniki.wineproduction.productFileGet', {'tnid':M.curTenantID, 
            'file_id':this.file_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.editfile.data = rsp.file;
                M.ciniki_wineproduction_products.editfile.refresh();
                M.ciniki_wineproduction_products.editfile.show(cb);
            });
    };
    this.editfile.save = function() {
        var c = this.serializeFormData('no');
        if( c != '' ) {
            M.api.postJSONFormData('ciniki.wineproduction.productFileUpdate', {'tnid':M.curTenantID, 'file_id':this.file_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_wineproduction_products.editfile.close();
                        }
                    });
        }
    };
    this.editfile.remove = function() {
        M.confirm('Are you sure you want to delete \'' + this.data.name + '\'?  All information about it will be removed and unrecoverable.',null,function() {
            M.api.getJSONCb('ciniki.wineproduction.productFileDelete', {'tnid':M.curTenantID, 
                'file_id':M.ciniki_wineproduction_products.editfile.file_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_wineproduction_products.editfile.close();
                });
        });
    };
    this.editfile.download = function() {
        M.api.openFile('ciniki.wineproduction.productFileDownload', {'tnid':M.curTenantID, 'file_id':this.file_id});
    };
    this.editfile.addButton('save', 'Save', 'M.ciniki_wineproduction_products.editfile.save();');
    this.editfile.addClose('Cancel');

    //
    // The panel to list the purchaseOrder
    //
    this.purchaseorders = new M.panel('purchaseOrder', 'ciniki_wineproduction_products', 'purchaseorders', 'mc', 'medium', 'sectioned', 'ciniki.wineproduction.products.purchaseorders');
    this.purchaseorders.data = {};
    this.purchaseorders.nplist = [];
    this.purchaseorders.sections = {
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':5,
            'cellClasses':[''],
            'hint':'Search purchaseOrder',
            'noData':'No purchaseOrder found',
            },
        'purchaseorders':{'label':'Purchase Orders', 'type':'simplegrid', 'num_cols':5,
            'headerValues':['PO #', 'Status', 'Supplier', 'Ordered', 'Received'],
            'noData':'No purchaseOrder',
            'addTxt':'Add Purchase Order',
            'addFn':'M.ciniki_wineproduction_products.purchaseorder.open(\'M.ciniki_wineproduction_products.purchaseorders.open();\',0,null);'
            },
    }
    this.purchaseorders.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            M.api.getJSONBgCb('ciniki.wineproduction.purchaseOrderSearch', {'tnid':M.curTenantID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                M.ciniki_wineproduction_products.purchaseorders.liveSearchShow('search',null,M.gE(M.ciniki_wineproduction_products.purchaseorders.panelUID + '_' + s), rsp.purchaseorders);
                });
        }
    }
    this.purchaseorders.liveSearchResultValue = function(s, f, i, j, d) {
        switch(j) {
            case 0: return d.po_number;
            case 1: return d.status_text;
            case 2: return d.supplier_name;
            case 3: return d.date_ordered;
            case 4: return d.date_received;
        }
    }
    this.purchaseorders.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'M.ciniki_wineproduction_products.purchaseorder.open(\'M.ciniki_wineproduction_products.purchaseorders.open();\',\'' + d.id + '\');';
    }
    this.purchaseorders.cellValue = function(s, i, j, d) {
        if( s == 'purchaseorders' ) {
            switch(j) {
                case 0: return d.po_number;
                case 1: return d.status_text;
                case 2: return d.supplier_name;
                case 3: return d.date_ordered;
                case 4: return d.date_received;
            }
        }
    }
    this.purchaseorders.rowFn = function(s, i, d) {
        if( s == 'purchaseorders' ) {
            return 'M.ciniki_wineproduction_products.purchaseorder.open(\'M.ciniki_wineproduction_products.purchaseorders.open();\',\'' + d.id + '\',M.ciniki_wineproduction_products.purchaseorder.nplist);';
        }
    }
    this.purchaseorders.open = function(cb) {
        M.api.getJSONCb('ciniki.wineproduction.purchaseOrderList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_products.purchaseorders;
            p.data = rsp;
            p.nplist = (rsp.nplist != null ? rsp.nplist : null);
            p.refresh();
            p.show(cb);
        });
    }
    this.purchaseorders.addClose('Back');

    //
    // The panel to edit Purchase Order
    //
    this.purchaseorder = new M.panel('Purchase Order', 'ciniki_wineproduction_products', 'purchaseorder', 'mc', 'large mediumaside', 'sectioned', 'ciniki.wineproduction.products.purchaseorder');
    this.purchaseorder.data = null;
    this.purchaseorder.order_id = 0;
    this.purchaseorder.nplist = [];
    this.purchaseorder.sections = {
        'supplier_details':{'label':'Supplier', 'type':'simplegrid', 'aside':'yes', 'num_cols':2,
            'cellClasses':['label', ''],
            },
        'general':{'label':'Purchase Order', 'aside':'yes', 'fields':{
//            'supplier_id':{'label':'Supplier', 'type':'select', 'options':{}, 'complex_options':{'name':'name', 'value':'id'}},
            'po_number':{'label':'PO #', 'required':'yes', 'type':'text', 'size':'small'},
            'status':{'label':'Status', 'type':'toggle', 'toggles':{'10':'Draft', '30':'Sent', '50':'Received', '90':'Closed'}},
            'date_ordered':{'label':'Date Ordered', 'type':'date'},
            'date_received':{'label':'Date Received', 'type':'date'},
            }},
        '_notes':{'label':'Notes', 'aside':'yes', 'fields':{
            'notes':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
            }},
        'items':{'label':'Items', 'type':'simplegrid', 'num_cols':7,
            'headerValues':['SKU', 'Item', 'Qty', 'Rate', 'Amount', 'Inventory', 'Ordered'],
            'headerClasses':['', '', 'aligncenter', 'alignright', 'alignright', 'alignright', 'alignright'],
            'cellClasses':['', '', 'aligncenter', 'alignright', 'alignright', 'alignright', 'alignright'],
            'sortable':'yes',
            'sortTypes':['number', 'text', 'number', 'number', 'number', 'number', 'number'],
            'noData':'No Items Added',
            'addTxt':'Add Item',
            'addFn':'M.ciniki_wineproduction_products.purchaseorder.save("M.ciniki_wineproduction_products.poitem.open(\'M.ciniki_wineproduction_products.purchaseorder.open();\',0,M.ciniki_wineproduction_products.purchaseorder.order_id);");',
            },
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_products.purchaseorder.save();'},
            'pullitems':{'label':'Auto Generate', 'fn':'M.ciniki_wineproduction_products.purchaseorder.save("M.ciniki_wineproduction_products.purchaseorder.pullitems();");'},
            'download':{'label':'Download', 'fn':'M.ciniki_wineproduction_products.purchaseorder.save("M.ciniki_wineproduction_products.purchaseorder.download();");'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.ciniki_wineproduction_products.purchaseorder.order_id > 0 ? 'yes' : 'no'; },
                'fn':'M.ciniki_wineproduction_products.purchaseorder.remove();'},
            }},
        };
    this.purchaseorder.fieldValue = function(s, i, d) { return this.data[i]; }
    this.purchaseorder.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.purchaseOrderHistory', 'args':{'tnid':M.curTenantID, 'order_id':this.order_id, 'field':i}};
    }
    this.purchaseorder.download = function() {
        M.api.openFile('ciniki.wineproduction.purchaseOrderDownload', {'tnid':M.curTenantID, 'order_id':this.order_id});
    }
    this.purchaseorder.cellValue = function(s, i, j, d) {
        if( s == 'supplier_details' ) {
            switch(j) {
                case 0: return d.label;
                case 1: return d.details.replace(/\n/g, '<br/>');
            }
        }
        if( s == 'items' ) {
            switch(j) {
                case 0: return d.sku;
                case 1: return d.description;
                case 2: return d.quantity_ordered;
                case 3: return d.unit_amount_display;
                case 4: return d.total_amount_display;
                case 5: return d.inventory_current_num;
                case 6: return d.num_orders;
            }
        }
    }
    this.purchaseorder.rowFn = function(s, i, d) {
        if( s == 'supplier_details' ) {
            return 'M.ciniki_wineproduction_products.purchaseorder.save("M.ciniki_wineproduction_products.supplier.open(\'M.ciniki_wineproduction_products.purchaseorder.open();\',\'' + this.data.supplier_id + '\',null);");';
        }
        if( s == 'items' ) {
            return 'M.ciniki_wineproduction_products.purchaseorder.save("M.ciniki_wineproduction_products.poitem.open(\'M.ciniki_wineproduction_products.purchaseorder.open();\',\'' + d.id + '\',M.ciniki_wineproduction_products.purchaseorder.order_id,null);");';
        }
    }
    this.purchaseorder.pullitems = function() {
        M.api.getJSONCb('ciniki.wineproduction.purchaseOrderImport', {'tnid':M.curTenantID, 'order_id':this.order_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_products.purchaseorder;
            p.data = rsp.purchaseorder;
            p.order_id = rsp.purchaseorder.id;
            p.refresh();
            p.show(cb);
        });
        
    }
    this.purchaseorder.open = function(cb, oid, sid, list) {
        if( oid != null ) { this.order_id = oid; }
        if( sid != null ) { this.supplier_id = sid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('ciniki.wineproduction.purchaseOrderGet', {'tnid':M.curTenantID, 'order_id':this.order_id, 'supplier_id':this.supplier_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_products.purchaseorder;
            p.data = rsp.purchaseorder;
//            p.sections.general.fields.supplier_id.options = rsp.suppliers;
            p.order_id = rsp.purchaseorder.id;
            p.refresh();
            p.show(cb);
        });
    }
    this.purchaseorder.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wineproduction_products.purchaseorder.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.order_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wineproduction.purchaseOrderUpdate', {'tnid':M.curTenantID, 'order_id':this.order_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.wineproduction.purchaseOrderAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.purchaseorder.order_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.purchaseorder.remove = function() {
        if( confirm('Are you sure you want to remove purchaseOrder?') ) {
            M.api.getJSONCb('ciniki.wineproduction.purchaseOrderDelete', {'tnid':M.curTenantID, 'order_id':this.order_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.purchaseorder.close();
            });
        }
    }
    this.purchaseorder.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.order_id) < (this.nplist.length - 1) ) {
            return 'M.ciniki_wineproduction_products.purchaseorder.save(\'M.ciniki_wineproduction_products.purchaseorder.open(null,' + this.nplist[this.nplist.indexOf('' + this.order_id) + 1] + ');\');';
        }
        return null;
    }
    this.purchaseorder.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.order_id) > 0 ) {
            return 'M.ciniki_wineproduction_products.purchaseorder.save(\'M.ciniki_wineproduction_products.purchaseorder.open(null,' + this.nplist[this.nplist.indexOf('' + this.order_id) - 1] + ');\');';
        }
        return null;
    }
    this.purchaseorder.addButton('save', 'Save', 'M.ciniki_wineproduction_products.purchaseorder.save();');
    this.purchaseorder.addClose('Cancel');
    this.purchaseorder.addButton('next', 'Next');
    this.purchaseorder.addLeftButton('prev', 'Prev');

    //
    // The panel to edit Purchase Order Item
    //
    this.poitem = new M.panel('Purchase Order Item', 'ciniki_wineproduction_products', 'poitem', 'mc', 'medium', 'sectioned', 'ciniki.wineproduction.products.poitem');
    this.poitem.data = null;
    this.poitem.item_id = 0;
    this.poitem.nplist = [];
    this.poitem.sections = {
        'general':{'label':'', 'fields':{
            'product_id':{'label':'Product', 'type':'select', 'options':{}, 
                'complex_options':{'name':'name', 'value':'id'},
                'onchange':'M.ciniki_wineproduction_products.poitem.updateForm();',
                },
            'description':{'label':'Description', 'type':'text'},
            'quantity_ordered':{'label':'Quantity Ordered', 'type':'text', 'size':'small'},
            'quantity_received':{'label':'Quantity Received', 'type':'text', 'size':'small'},
            'unit_amount':{'label':'Unit Amount', 'type':'text', 'size':'small'},
//            'taxtype_id':{'label':'Tax Type', 'type':'text'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_products.poitem.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.ciniki_wineproduction_products.poitem.item_id > 0 ? 'yes' : 'no'; },
                'fn':'M.ciniki_wineproduction_products.poitem.remove();'},
            }},
        };
    this.poitem.fieldValue = function(s, i, d) { return this.data[i]; }
    this.poitem.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.purchaseOrderItemHistory', 'args':{'tnid':M.curTenantID, 'item_id':this.item_id, 'field':i}};
    }
    this.poitem.updateForm = function() {
        if( this.formValue('product_id') == 0 ) {
            this.sections.general.fields.description.visible = 'yes';
        } else {
            this.sections.general.fields.description.visible = 'no';
        }
        this.showHideFormField('general', 'description');
    }
    this.poitem.open = function(cb, iid, oid, list) {
        if( iid != null ) { this.item_id = iid; }
        if( oid != null ) { this.order_id = oid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('ciniki.wineproduction.purchaseOrderItemGet', {'tnid':M.curTenantID, 'item_id':this.item_id, 'order_id':this.order_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_products.poitem;
            p.data = rsp.item;
            p.sections.general.fields.product_id.options = rsp.products;
            p.refresh();
            p.show(cb);
            p.updateForm();
        });
    }
    this.poitem.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wineproduction_products.poitem.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.item_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wineproduction.purchaseOrderItemUpdate', {'tnid':M.curTenantID, 'item_id':this.item_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.wineproduction.purchaseOrderItemAdd', {'tnid':M.curTenantID, 'order_id':this.order_id}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.poitem.item_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.poitem.remove = function() {
        if( confirm('Are you sure you want to remove purchaseOrderItem?') ) {
            M.api.getJSONCb('ciniki.wineproduction.purchaseOrderItemDelete', {'tnid':M.curTenantID, 'item_id':this.item_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_products.poitem.close();
            });
        }
    }
    this.poitem.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.item_id) < (this.nplist.length - 1) ) {
            return 'M.ciniki_wineproduction_products.poitem.save(\'M.ciniki_wineproduction_products.poitem.open(null,' + this.nplist[this.nplist.indexOf('' + this.item_id) + 1] + ');\');';
        }
        return null;
    }
    this.poitem.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.item_id) > 0 ) {
            return 'M.ciniki_wineproduction_products.poitem.save(\'M.ciniki_wineproduction_products.poitem.open(null,' + this.nplist[this.nplist.indexOf('' + this.item_id) - 1] + ');\');';
        }
        return null;
    }
    this.poitem.addButton('save', 'Save', 'M.ciniki_wineproduction_products.poitem.save();');
    this.poitem.addClose('Cancel');
    this.poitem.addButton('next', 'Next');
    this.poitem.addLeftButton('prev', 'Prev');


    this.updater = new M.panel('Supplier Product Update', 'ciniki_wineproduction_products', 'updater', 'mc', 'xlarge narrowaside', 'sectioned', 'ciniki.wineproduction.products.updater');
    this.updater.data = null;
    this.updater.supplier_id = 0;
    this.updater.compare_field = '';
    this.updater.sections = {
        'supplier':{'label':'Supplier', 'aside':'yes', 'fields':{
            'supplier_id':{'label':'', 'hidelabel':'yes', 'type':'select', 'options':{}, 'idnames':'yes',
                'onchangeFn':'M.ciniki_wineproduction_products.updater.changeSupplier();',
                },
            }},
        'fields':{'label':'Product Fields', 'type':'simplegrid', 'aside':'yes', 'num_cols':1,
            },
        'products':{'label':'Products', 'type':'simplegrid', 'num_cols':4,
            'headerValues':['Name', 'Current', '', 'Supplier'],
            'cellClasses':['', 'alignright', '', ''],
            'headerClasses':['', 'alignright', '', ''],
            'sortable':'yes',
            'sortTypes':['text', 'text', '', 'text'],
            'noData':'No differences found',
            },
        '_buttons':{'label':'', 'buttons':{
            // 'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_products.poitem.save();'},
            }},
        };
    this.updater.fieldValue = function(s, i, d) { return this.supplier_id; }
    this.updater.changeSupplier = function() {
        this.supplier_id = this.formValue('supplier_id');
        this.compare_field = '';
        this.open();
    }
    this.updater.changeField = function(f) {
        this.compare_field = f;
        this.open();
    }
    this.updater.cellValue = function(s, i, j, d) {
        if( s == 'fields' ) {   
            return M.textCount(d.name, d.num_diffs);
        }
        if( s == 'products' && this.compare_field == 'new' ) {
            switch(j) {
                case 0: return d.supplier_item_number;
                case 1: return d.name;
                case 2: return d.wine_type;
                case 3: return d.kit_length;
                case 4: return M.formatDollar(d.list_price);
                case 5: return '<button onclick="M.ciniki_wineproduction_products.updater.importProduct(\'' + d.supplier_item_number + '\');">Import</button>';
            }
        }
        if( s == 'products' && this.compare_field == 'name' ) {
            switch(j) {
                case 0: return d.tenant_name;
                case 1: return '<button onclick="M.ciniki_wineproduction_products.updater.updateField(\'' + d.id + '\');"> &lt;&lt; </button>';
                case 2: return d.supplier_name;
            }
        }
        if( s == 'products' && this.compare_field == 'primary_image_checksum' ) {
            switch(j) {
                case 0: return d.tenant_name;
                case 1: 
                    if( d.tenant_value != '' ) {
                        return '<img width="75px" height="75px" src=\'' + d.tenant_image + '\' />'; 
                    }
                    return '<img width="75px" height="75px" src=\'/ciniki-mods/core/ui/themes/default/img/noimage_75.jpg\' />';
                case 2: return '<button onclick="M.ciniki_wineproduction_products.updater.updateField(\'' + d.id + '\');"> &lt;&lt; </button>';
                case 3: 
                    if( d.supplier_value != '' ) {
                        return '<img width="75px" height="75px" src=\'' + d.supplier_image + '\' />'; 
                    }
                    return '<img width="75px" height="75px" src=\'/ciniki-mods/core/ui/themes/default/img/noimage_75.jpg\' />';
            }
        }
        if( s == 'products' ) {
            switch(j) {
                case 0: return d.tenant_name;
                case 1: return d.tenant_value;
                case 2: return '<button onclick="M.ciniki_wineproduction_products.updater.updateField(\'' + d.id + '\');"> &lt;&lt; </button>';
                case 3: return d.supplier_value;
            }
/*            if( j == 0 ) { return d.tenant_name; }
            if( j == 1 ) {
                switch(this.compare_field) {
                    case 'status': return d.tenant_status_text;
                    case 'wine_type': return d.tenant_wine_type;
                    case 'kit_length': return d.tenant_kit_length;
                    case 'list_price': return d.tenant_list_price;
                    case 'synopsis': return d.tenant_synopsis.replace(/\n/g, '<br/>');
                    case 'description': return d.tenant_description.replace(/\n/g, '<br/>');
                }
            }
            if( j == 2 ) {
                return '<';
            }
            if( j == 3 ) {
                switch(this.compare_field) {
                    case 'status': return d.supplier_status_text;
                    case 'wine_type': return d.supplier_wine_type;
                    case 'kit_length': return d.supplier_kit_length;
                    case 'list_price': return d.supplier_list_price;
                    case 'synopsis': return d.supplier_synopsis.replace(/\n/g, '<br/>');
                    case 'description': return d.supplier_description.replace(/\n/g, '<br/>');
                }
            } */
        }
    }
    this.updater.rowClass = function(s, i, d) {
        if( s == 'fields' && this.compare_field == d.field ) {
            return 'highlight';
        }
        return '';
    }
    this.updater.rowFn = function(s, i, d) {
        if( s == 'fields' ) {
            return 'M.ciniki_wineproduction_products.updater.changeField(\'' + d.field + '\');';
        }
        return '';
    }
    this.updater.importProduct = function(sku) {
        M.api.getJSONCb('ciniki.wineproduction.supplierUpdates', {'tnid':M.curTenantID, 'supplier_id':this.supplier_id, 'field':this.compare_field, 'import_sku':sku}, this.openFinish);
    }
    this.updater.updateField = function(id) {
        M.api.getJSONCb('ciniki.wineproduction.supplierUpdates', {'tnid':M.curTenantID, 'supplier_id':this.supplier_id, 'field':this.compare_field, 'update_product_id':id}, this.openFinish);
    }
    this.updater.open = function(cb, sid) {
        if( sid != null ) { this.supplier_id = sid; }
        if( cb != null ) { this.cb = cb; }
        M.api.getJSONCb('ciniki.wineproduction.supplierUpdates', {'tnid':M.curTenantID, 'supplier_id':this.supplier_id, 'field':this.compare_field}, this.openFinish);
    }
    this.updater.openFinish = function(rsp) {
        if( rsp.stat != 'ok' ) {
            M.api.err(rsp);
            return false;
        }
        var p = M.ciniki_wineproduction_products.updater;
        p.data = rsp;
        p.sections.supplier.fields.supplier_id.options = rsp.suppliers;
        if( p.compare_field == 'new' ) {
            p.sections.products.num_cols = 6;
            p.sections.products.headerValues = ['Sku', 'Name', 'Type', 'Length', 'Price', ''];
            p.sections.products.headerClasses = ['', '', '', '', 'alignright', 'aligncenter'];
            p.sections.products.cellClasses = ['', '', '', '', 'alignright', 'aligncenter'];
            p.sections.products.sortTypes = ['text', 'text', 'text', 'number', 'number', ''];
        } else if( p.compare_field == 'name' ) {
            p.sections.products.num_cols = 3;
            p.sections.products.headerValues = ['Current', '', 'Supplier'];
            p.sections.products.cellClasses = ['alignright', 'aligncenter', ''];
            p.sections.products.headerClasses = ['alignright', 'aligncenter', ''];
            p.sections.products.sortTypes = ['text', '', 'text'];
        } else if( p.compare_field == 'primary_image_checksum' ) {
            p.sections.products.num_cols = 4;
            p.sections.products.headerValues = ['Product', 'Current', '', 'Supplier'];
            p.sections.products.cellClasses = ['', 'alignright thumbnail', 'aligncenter', 'thumbnail'];
            p.sections.products.headerClasses = ['', 'alignright', 'aligncenter', ''];
            p.sections.products.sortTypes = ['text', 'image', '', 'image'];
        } else {
            p.sections.products.num_cols = 4;
            p.sections.products.headerValues = ['Product', 'Current', '', 'Supplier'];
            p.sections.products.cellClasses = ['', 'alignright', 'aligncenter', ''];
            p.sections.products.headerClasses = ['', 'alignright', 'aligncenter', ''];
            p.sections.products.sortTypes = ['text', 'text', '', 'text'];
        }
        p.refresh();
        p.show();
    }
    this.updater.addClose('Back');

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) {
            args = eval(aG);
        }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_wineproduction_products', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 

        if( args.product_id != null ) {
            this.product.open(cb, args.product_id);
        } else {
            this.menu.open(cb);
        }
    };
}
