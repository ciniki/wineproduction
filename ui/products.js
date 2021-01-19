function ciniki_wineproduction_products() {
    //
    // The panel to list the product
    //
    this.menu = new M.panel('product', 'ciniki_wineproduction_products', 'menu', 'mc', 'xlarge narrowaside', 'sectioned', 'ciniki.wineproduction.products.menu');
    this.tag10 = '';
    this.tag11 = '';
    this.tag12 = '';
    this.tag13 = '';
    this.tag14 = '';
    this.tag15 = '';
    this.supplier_id = '';
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.sections = {
        '_tabs':{'label':'', 'type':'paneltabs', 'selected':'categories', 'aside':'yes', 'tabs':{
            'categories':{'label':'Categories', 'fn':'M.ciniki_wineproduction_products.menu.switchTab("categories");'},
            'varietals':{'label':'Varietals', 'fn':'M.ciniki_wineproduction_products.menu.switchTab("varietals");'},
            'obs':{'label':'O/B/S', 'fn':'M.ciniki_wineproduction_products.menu.switchTab("obs");'},
            'suppliers':{'label':'Supplier', 'fn':'M.ciniki_wineproduction_products.menu.switchTab("suppliers");'},
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
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':3,
            'headerValues':['Name', 'Status', 'Visible'],
            'cellClasses':[''],
            'hint':'Search product',
            'noData':'No product found',
            },
        'products':{'label':'Product', 'type':'simplegrid', 'num_cols':7,
            'headerValues':['Name', 'Categories', 'SubCategories', 'Visible', 'Price', 'Supplier', 'Inv'],
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
        if( s == 'products' ) {
            switch(j) {
                case 0: return d.name;
                case 1: return d.categories;
                case 2: return d.subcategories;
                case 3: return d.visible;
                case 4: return d.unit_amount_display;
                case 5: return d.supplier_name;
                case 6: return d.inventory_current_num;
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
        this.showHideSections(['tags10', 'tags11', 'tags12', 'tags13', 'tags14', 'tags15', 'suppliers']);
    }

    this.menu.open = function(cb) {
        M.api.getJSONCb('ciniki.wineproduction.products', {'tnid':M.curTenantID, 'tag10':this.tag10, 'tag11':this.tag11, 'tag12':this.tag12, 'tag13':this.tag13, 'tag14':this.tag14, 'tag15':this.tag15, 'supplier_id':this.supplier_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_products.menu;
            p.data = rsp;
            p.nplist = (rsp.nplist != null ? rsp.nplist : null);
            p.refresh();
            p.show(cb);
        });
    }
    this.menu.addClose('Back');

    //
    // The panel to edit Product
    //
    this.product = new M.panel('Product', 'ciniki_wineproduction_products', 'product', 'mc', 'large mediumaside columns', 'sectioned', 'ciniki.wineproduction.main.product');
    this.product.data = null;
    this.product.product_id = 0;
    this.product.nplist = [];
    this.product.sections = {
        '_primary_image_id':{'label':'Primary Image', 'type':'imageform', 'aside':'yes', 'fields':{
            'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no',
                'addDropImage':function(iid) {
                    M.ciniki_wineproduction_products.product.setFieldValue('primary_image_id', iid);
                    return true;
                    },
                'addDropImageRefresh':'',
             },
        }},
        'general':{'label':'Product', 'aside':'yes', 'fields':{
            'name':{'label':'Name', 'required':'yes', 'type':'text'},
            'ptype':{'label':'Type', 'type':'toggle', 'toggles':{'10':'Wine', '90':'Other'},
                'onchange':'M.ciniki_wineproduction_products.product.updateForm();',
                },
            'flags':{'label':'Options', 'type':'flags', 'flags':{'1':{'name':'Visible'}}},
            'status':{'label':'Status', 'type':'toggle', 'toggles':{'10':'Active', '60':'Discontinued'}},
            'inventory_current_num':{'label':'Inventory', 'type':'text', 'size':'small'},
            }},
        'supplier':{'label':'', 'aside':'yes', 'fields':{
            'supplier_id':{'label':'Supplier', 'type':'select', 'options':{}, 'complex_options':{'name':'label', 'value':'value'}},
            'supplier_item_number':{'label':'Supplier Item Number', 'type':'text'},
            'list_price':{'label':'List Price', 'type':'text', 'size':'small',
                'onchange':'M.ciniki_wineproduction_products.product.updateForm();',
                },
            'list_discount_percent':{'label':'Discount %', 'type':'text', 'size':'small', 
                'onchange':'M.ciniki_wineproduction_products.product.updateForm();',
                },
            'cost':{'label':'Cost', 'type':'text', 'size':'small', 'editable':'no'},
            'wine_type':{'label':'Wine Type', 'type':'multitoggle', 'none':'yes', 
                'toggles':{'Red':'Red', 'White':'White', 'Specialty':'Specialty'},
                },
            'kit_length':{'label':'# Weeks', 'type':'text'},
            }},
        'price':{'label':'', 'aside':'yes', 'fields':{
            // Only visible for Wine types
            'kit_price_id':{'label':'Kit Price', 'type':'select', 'options':{}},
            'processing_price_id':{'label':'Processing Price', 'type':'select', 'options':{}},
            // Visible for Suppliers/Other
            'unit_amount':{'label':'', 'type':'text', 'size':'small', 'editable':'no'},
            'unit_discount_amount':{'label':'Discount Amount', 'type':'text', 'size':'small'},
            'unit_discount_percentage':{'label':'Discount Percent', 'type':'text', 'size':'small'},
            'taxtype_id':{'label':'Taxes', 'type':'select', 'options':{}, 'complex_options':{'name':'name', 'value':'id'}},
            }},
        '_tags10':{'label':'Categories', 'aside':'yes', 'panelcolumn':1, 'fields':{
            'tags10':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'New Category:'},
            }},
        '_tags11':{'label':'Sub Categories', 'aside':'yes', 'panelcolumn':1, 'fields':{
            'tags11':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'New Sub Category:'},
            }},
        '_tags12':{'label':'Varietal', 'aside':'yes', 'panelcolumn':1, 'fields':{
            'tags12':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'New Varietal:'},
            }},
        '_tags13':{'label':'Oak', 'aside':'yes', 'panelcolumn':1, 'fields':{
            'tags13':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Oak:'},
            }},
        '_tags14':{'label':'Body', 'aside':'yes', 'panelcolumn':1, 'fields':{
            'tags14':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'Body:'},
            }},
        '_tags15':{'label':'Sweetness', 'aside':'yes', 'panelcolumn':1, 'fields':{
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
        console.log('cost');
    }
    this.product.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.productHistory', 'args':{'tnid':M.curTenantID, 'product_id':this.product_id, 'field':i}};
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
            p.sections.price.fields.taxtype_id.options = rsp.taxtypes;
            p.refresh();
            p.show(cb);
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
    this.supplier = new M.panel('Supplier', 'ciniki_wineproduction_products', 'supplier', 'mc', 'medium', 'sectioned', 'ciniki.wineproduction.main.supplier');
    this.supplier.data = null;
    this.supplier.supplier_id = 0;
    this.supplier.nplist = [];
    this.supplier.sections = {
        'general':{'label':'', 'fields':{
            'name':{'label':'Name', 'required':'yes', 'type':'text'},
//            'supplier_tnid':{'label':'', 'type':'text'},
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
                console.log(rsp);
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
