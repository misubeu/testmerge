Ext.define('Shopware.apps.NfxProfitAnalize.view.main.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.nfxProfitAnalize-main-grid',

    snippets: {
        columnNumber:   '{s namespace=backend/article_list/view/main name=list/column_number}Number{/s}',
        //addArticle:     '{s name=list/add_article}Add article{/s}',
        //deleteArticle:  '{s name=list/delete_article}Delete selected articles{/s}',
        columnName:     '{s namespace=backend/article_list/view/main name=list/column_name}Name{/s}',
        columnSupplier: '{s namespace=backend/article_list/view/main name=list/column_supplier}Supplier{/s}',
        columnActive:   '{s namespace=backend/article_list/view/main name=list/column_active}Active{/s}',
        //columnStock:    '{s name=list/column_stock}Stock{/s}',
        columnPrice:   '{s namespace=backend/article_list/view/main name=list/column_price}Price{/s}',
        columnTax:   '{s namespace=backend/article_list/view/main name=list/column_tax}Tax{/s}',
        columnSaledItems:    '{s name=list/column_saled_items}Verkaufte Artikel{/s}',
        columnSalesPrice:    '{s name=list/column_sales_price}Verkaufspreis{/s}',
        columnTotalProfit:    '{s name=list/column_total_profit}Gesamtprofit{/s}',
        columnInfo:    '{s namespace=backend/article_list/view/main name=list/column_info}Info{/s}',

        tooltipEdit:   '{s namespace=backend/article_list/view/main name=list/tooltip_edit}Edit{/s}',
        emptytextSearch:  '{s namespace=backend/article_list/view/main name=list/emptytext_search}Search ...{/s}',

        tooltipNoImage:         '{s namespace=backend/article_list/view/main name=list/tooltip_noimage}Article has no image{/s}',
        tooltipNoCategories:    '{s namespace=backend/article_list/view/main name=list/tooltip_categories}Article is not assigned to any category{/s}',
        tooltipeHasVariants:    '{s namespace=backend/article_list/view/main name=list/tooltip_hasvariants}Article has variants{/s}',
        tooltipHasConfigurator: '{s namespace=backend/article_list/view/main name=list/tooltip_hasconfigurator}Article has configurator{/s}',

        regexNumberValidation: '{s namespace=backend/article_list/view/main name=list/regex_number_validation}The inserted article number contains illegal characters!{/s}'
    },

    initComponent: function() {
        var me = this;

        me.editor      = me.getRowEditorPlugin();
        me.plugins     = [ me.editor ];
        me.store       = me.articleStore;
        me.selModel    = me.getGridSelModel();
        me.columns     = me.getColumns();
        me.tbar        = me.getToolbar();
        me.bbar        = me.getPagingbar();

        me.addEvents(
            /**
             * Will be fired when the "show variants" checkbox changed
             * @param Ext.form.field.Checkbox
             * @param boolean
            */
            'filterVariantsChange',

            /**
             * Will be fired when user clicks the delete action column
             * @param record
             */
            'deleteArticle',

            /**
             * Will be fired when the user clicks the delete articles button in the toolbar
             * @param records
             */
            'deleteMultipleArticles'
        );

        me.callParent(arguments);
    },

    /**
     * Creates rowEditor Plugin
     *
     * @return [Ext.grid.plugin.RowEditing]
     */
    getRowEditorPlugin: function() {
        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            errorSummary: false,
            pluginId: 'rowEditing'
        });
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel: function () {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the delete button if the user has checked at least one checkbox
                selectionchange: function (sm, selections) {
                    me.deleteButton.setDisabled(selections.length === 0);
                }
            }
        });
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me               = this,
            actionColumItems = [];

        actionColumItems.push({
            action: 'edit',
            cls: 'editBtn',
            iconCls: 'sprite-pencil',
            handler: function(view, rowIndex, colIndex, item, opts, record) {
                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Article',
                    action: 'detail',
                    params: {
                        articleId: record.get('articleId')
                    }
                });
            }
        });

        /*{if {acl_is_allowed resource=article privilege=delete}}*/
        actionColumItems.push({
            iconCls:'sprite-minus-circle-frame',
            action:'delete',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.fireEvent('deleteArticle', record);
            }
        });
        /*{/if}*/

        return [{
            header: me.snippets.columnNumber,
            dataIndex: 'number',
            flex: 2,
            editor: {
                regex: /^[a-zA-Z0-9-_. ]+$/,
                regexText: me.snippets.regexNumberValidation,
                allowBlank: false,
                enableKeyEvents:true,
                checkChangeBuffer:700
            }
        }, {
            header: me.snippets.columnName,
            dataIndex: 'name',
            flex: 4,
            renderer: me.nameColumnRenderer,
            editor: {
                allowBlank: 'false'
            }
        }, {
            header: me.snippets.columnSupplier,
            dataIndex: 'supplier',
            flex: 3
        }, {
            header: me.snippets.columnActive,
            dataIndex: 'active',
            xtype: 'booleancolumn',
            width: 40,
            renderer: me.activeColumnRenderer,
            editor: {
                width: 85,
                xtype: 'checkbox',
                uncheckedValue: false,
                inputValue: true
            }
        }, {
            xtype: 'numbercolumn',
            header: me.snippets.columnPrice,
            dataIndex: 'price',
            align: 'right',
            width: 55,
            editor: {
                width: 85,
                xtype: 'numberfield',
                allowBlank: false,
                hideTrigger: true,
                keyNavEnabled: false,
                mouseWheelEnabled: false
            }
        }, {
            xtype: 'numbercolumn',
            header: me.snippets.columnTax,
            dataIndex: 'tax',
            flex: 1
        }, /*{
            header: me.snippets.columnStock,
            dataIndex: 'inStock',
            flex: 1,
            renderer: me.colorColumnRenderer,
            editor: {
                width: 285,
                xtype: 'numberfield',
                allowBlank: false,
                allowDecimals: false
            }
        },*/
        {
          header: me.snippets.columnSaledItems,
          dataIndex: 'saledItems',
          flex: 1,
          //renderer: me.colorColumnRenderer
        },
        {
          xtype: 'numbercolumn',
          header: me.snippets.columnSalesPrice,
          dataIndex: 'salesPrice',
          flex: 1,
          //renderer: me.colorColumnRenderer
        },
        {
          xtype: 'numbercolumn',
          header: me.snippets.columnTotalProfit,
          dataIndex: 'totalProfit',
          flex: 1,
          //renderer: me.colorColumnRenderer
        }
        , {
            header: me.snippets.columnInfo,
            flex: 1,
            renderer: me.infoColumnRenderer
        }, {
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: 26 * actionColumItems.length,
            items: actionColumItems
        }];
    },

    /**
     * Creates the grid toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this, buttons = [];

        //creates the delete button to remove all selected esds in one request.
        /*me.deleteButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-minus-circle-frame',
            text: me.snippets.deleteArticle,
            disabled: true,
            handler: function() {
                var selectionModel = me.getSelectionModel(),
                           records = selectionModel.getSelection();

                if (records.length > 0) {
                    me.fireEvent('deleteMultipleArticles', records);
                }
            }
        });*/

        /*{if {acl_is_allowed resource=article privilege=delete}}*/
        //buttons.push(me.deleteButton);
        /*{/if}*/

        /*{if {acl_is_allowed resource=article privilege=save}}*/
        /*buttons.push(
                Ext.create('Ext.button.Button', {
                    text: me.snippets.addArticle,
                    iconCls:'sprite-plus-circle-frame',
                    handler: function() {
                        Shopware.app.Application.addSubApplication({
                            name: 'Shopware.apps.Article',
                            action: 'detail'
                        });
                    }
                })
        );*/
        /*{/if}*/

        buttons.push({
            xtype: 'tbfill'
        });

        /*buttons.push({
            xtype: 'checkbox',
            width: 170,
            boxLabel: '{s name=list/Variants}Show variants{/s}',
            uncheckedValue:false,
            inputValue:true,
            listeners: {
                'change': function(field, newValue) {
                    me.fireEvent('filterVariantsChange', field, newValue);
                }
            }
        });*/

        buttons.push({
            xtype : 'textfield',
            name : 'searchfield',
            action : 'search',
            width: 170,
            cls: 'searchfield',
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            emptyText: me.snippets.emptytextSearch,
            listeners: {
                'change': function(field, value) {
                    var store        = me.store,
                        searchString = Ext.String.trim(value);

                    //scroll the store to first page
                    store.currentPage = 1;

                    //If the search-value is empty, reset the filter
                    if (searchString.length === 0 ) {
                        store.clearFilter();
                    } else {
                        //This won't reload the store
                        store.filters.clear();

                        //Loads the store with a special filter
                        store.filter('search', searchString);
                    }
                }
            }
        });

        buttons.push({
            xtype: 'tbspacer',
            width: 6
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: buttons
        });
    },


    /**
     * Renders the name column and concatenates name and additionaltext if its a variant
     * @param value
     * @param metaData
     * @param record
     */
    nameColumnRenderer: function(value, metaData, record) {
        var me = this,
            additionalText = record.get('additionalText'),
            name = record.get('name');
        if(additionalText !== '') {
            return name + ', ' + additionalText;
        }
        return name;
    },

    /**
     * @param [object] - value
     */
    activeColumnRenderer: function(value) {
        if (value) {
            return '<div class="sprite-tick-small"  style="width: 25px; height: 25px">&nbsp;</div>';
        } else {
            return '<div class="sprite-cross-small" style="width: 25px; height: 25px">&nbsp;</div>';
        }
    },

    /**
     * @param [object] - value
     */
    colorColumnRenderer: function(value) {
        if (value > 0){
            return '<span style="color:green;">' + value + '</span>';
        } else {
            return '<span style="color:red;">' + value + '</span>';
        }
    },

    /**
     * @param [object] - value
     * @param [object] - metaData
     * @param [object] - record
     */
    infoColumnRenderer: function(value, metaData, record) {
        var me     = this,
            result = '',
            title;

        var style = 'style="width: 25px; height: 25px; display: inline-block; margin-right: 3px;"';

        if (!record.get('imageSrc')) {
            title = me.snippets.tooltipNoImage;
            result = result + '<div  title="' + title + '" class="sprite-image--exclamation" ' + style + '>&nbsp;</div>';
        }

        if (record.get('hasVariants')) {
            title = me.snippets.tooltipeHasVariants;
            result = result + '<div  title="' + title + '" class="sprite-documents-stack" ' + style + '>&nbsp;</div>';
        }

        if (record.get('hasConfigurator')) {
            title = me.snippets.tooltipHasConfigurator;
            result = result + '<div  title="' + title + '" class="sprite-images-stack" ' + style + '>&nbsp;</div>';
        }

        if (!record.get('hasCategories')) {
            title = me.snippets.tooltipNoCategories;
            // todo@all change icon
            result = result + '<div title="' + title + '" class="sprite-blue-folder--exclamation" ' + style + '>&nbsp;</div>';
        }

        return result;
    },

    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function() {
        var me = this;
        var pageSize = Ext.create('Ext.form.field.ComboBox', {
            labelWidth: 120,
            cls: Ext.baseCSSPrefix + 'page-size',
            queryMode: 'local',
            width: 180,
            listeners: {
                scope: me,
                select: me.onPageSizeChange
            },
            store: Ext.create('Ext.data.Store', {
                fields: [ 'value' ],
                data: [
                    { value: '20' },
                    { value: '40' },
                    { value: '60' },
                    { value: '80' },
                    { value: '100' },
                    { value: '250' },
                ]
            }),
            displayField: 'value',
            valueField: 'value'
        });
        pageSize.setValue(me.articleStore.pageSize);

        var pagingBar = Ext.create('Ext.toolbar.Paging', {
            store: me.articleStore,
            dock:'bottom',
            displayInfo:true
        });

        pagingBar.insert(pagingBar.items.length - 2, [ { xtype: 'tbspacer', width: 6 }, pageSize ]);
        return pagingBar;
    },
    /**
     * Event listener method which fires when the user selects
     * a entry in the "number of orders"-combo box.
     *
     * @event select
     * @param [object] combo - Ext.form.field.ComboBox
     * @param [array] records - Array of selected entries
     * @return void
     */
    onPageSizeChange: function(combo, records) {
        var record = records[0],
            me = this;

        me.articleStore.pageSize = record.get('value');
        me.articleStore.loadPage(1);
    },
    /**
     * Special ExtJS 4 method which will be fired
     * when the component is rendered.
     *
     * Enables the drag zone and collects the neccessary
     * data for the drop item.
     *
     * @private
     * @returm void
     */
    afterRender: function() {
        var me = this, view = me.getView();
        me.callParent(arguments);

        view.dragZone = Ext.create('Ext.dd.DragZone', view.getEl(), {
            ddGroup: 'desktop-article-dd',

            /**
             * Called when a mousedown occurs in this container.
             * Looks in Ext.dd.Registry for a valid target to drag based on the mouse down. Override this method to
             * provide your own lookup logic (e.g. finding a child by class name). Make sure your returned object has
             * a "ddel" attribute (with an HTML Element) for other functions to work.
             *
             * @private
             * @param [object] event - Ext.EventImplObj
             * @return [object] the drag data
             */
            getDragData: function(event) {
                var sourceEl = event.getTarget(view.itemSelector);

                if(sourceEl) {
                    var record = view.getRecord(sourceEl).data,
                        dh = new Ext.dom.Helper(), specs, d, imagePath;

                    if(record.imageSrc) {
                        imagePath = '{link file=""}media/image/thumbnail/' + record.imageSrc;
                    } else {
                        imagePath = '{link file="frontend/_resources/images/no_picture.jpg"}'
                    }
                    // Create a special dd element
                    specs = {
                        tag: 'div',
                        cls: Ext.baseCSSPrefix + 'article-dd',
                        children: [{
                            tag: 'div', cls: 'outer-icon', children: [
                                { tag: 'div', cls: 'inner-icon', children: [{
                                    tag: 'div', cls: 'icon-close'
                                }, {
                                    tag: 'div', cls: 'icon', style: 'background-image: url(' + imagePath + ')'
                                }] }
                            ]
                        }, {
                            tag: 'span', cls: 'desc', html: Ext.String.ellipsis(record.name, 25)
                        }]
                    };
                    d = dh.createDom(specs);

                    view.dragData = {
                        sourceEl: sourceEl,
                        repairXY: Ext.fly(sourceEl).getXY(),
                        ddel: d,
                        record: view.getRecord(sourceEl).data
                    };

                    // return dragData
                    return view.dragData;

                }
            },

            getRepairXY: function() {
                return this.dragData.repairXY;
            }
        });
    }
});
