
Ext.define('Shopware.apps.NfxProfitAnalize.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.nfxProfitAnalize-main-window',
    layout: 'border',
    width: '90%',
    height: '90%',
    stateful: true,
    stateId: 'shopware-nfxProfitAnalize-main-window',

    snippets: {
      title:         '{s name=list/title}Profit Analyse{/s}',
      categoryTitle: '{s namespace=backend/article_list/view/main name=list/category_title}Categories{/s}',
      filterTitle:   '{s namespace=backend/article_list/view/main name=list/filter_title}Filter{/s}',

      noFilter:      '{s namespace=backend/article_list/view/main name=list/no_filter}No filter{/s}',
      notInStock:    '{s namespace=backend/article_list/view/main name=list/not_in_stock}Not in stock{/s}',
      noCategory:    '{s namespace=backend/article_list/view/main name=list/no_category}No categories{/s}',
      noImage:       '{s namespace=backend/article_list/view/main name=list/no_image}No images{/s}'
    },

    initComponent: function() {
      var me = this;

      me.title = me.snippets.title;

      me.addEvents(
        /**
         * @event
         * @param [Ext.view.View] view - the view that fired the event
         * @param [Ext.data.Model] record
        */
        'categoryChanged'
      );

        me.categoryStore = Ext.create('Shopware.store.CategoryTree');

        me.items = [{
          xtype: 'nfxProfitAnalize-main-grid',
          articleStore: me.articleStore,
          region: 'center'
        }];

        me.items.push({
          xtype: 'container',
          width: 230,
          layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
          },
          region: 'west',
          items: [
            me.createTree(),
            me.createFilterPanel()
          ]
        });

        me.callParent(arguments);
    },

    createFilterPanel: function() {
        var me = this;

        return new Ext.create('Ext.form.Panel', {
            title: me.snippets.filterTitle,
            bodyPadding: 5,
            items: [{
                xtype: 'radiogroup',
                listeners: {
                    change: {
                        fn: function(view, newValue, oldValue) {
                            var me    = this,
                                store =  me.articleStore;

                            store.getProxy().extraParams.filterBy = newValue.filter;
                            store.load();

                        },
                        scope: me
                    }
                },
                columns: 1,
                vertical: true,
                items: [
                    { boxLabel: me.snippets.noFilter, name: 'filter', inputValue: 'none', checked: true  },
                    { boxLabel: me.snippets.notInStock, name: 'filter', inputValue: 'notInStock'  },
                    { boxLabel: me.snippets.noCategory, name: 'filter', inputValue: 'noCategory' },
                    { boxLabel: me.snippets.noImage, name: 'filter', inputValue: 'noImage' }
                ]
            }]


        });
    },

    /**
     * Creates the category tree
     *
     * @return [Ext.tree.Panel]
     */
    createTree: function() {
        var me = this;

        var tree = Ext.create('Ext.tree.Panel', {
            rootVisible: true,
            flex: 1,
            title: me.snippets.categoryTitle,

            expanded: true,
            useArrows: false,
            store: me.categoryStore,
            root: {
                text: me.snippets.categoryTitle,
                expanded: true
            },
            listeners: {
                itemclick: {
                    fn: function(view, record) {
                        var me    = this,
                            store =  me.articleStore;

                        if (record.get('id') === 'root') {
                            store.getProxy().extraParams.categoryId = null;
                        } else {
                            store.getProxy().extraParams.categoryId = record.get('id');
                        }

                        //scroll the store to first page
                        store.currentPage = 1;
                        store.load({
                            callback: function() {
                            }
                        });
                    },
                    scope: me
                }
            }
        });

        return tree;
    }
});
