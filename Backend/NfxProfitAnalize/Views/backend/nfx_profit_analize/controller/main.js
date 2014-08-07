
Ext.define('Shopware.apps.NfxProfitAnalize.controller.Main', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * @array
     */
    refs: [
        { ref: 'articleGrid', selector: 'nfxProfitAnalize-main-grid' }
    ],

    /**
     * Contains all snippets for the component.
     * @object
     */
    snippets: {
        growlMessage: '{s name=growl_message}Article{/s}',
        messages: {
            successTitle: '{s name=messages/success}Success{/s}',
            deleteSuccess: '{s name=messages/delete_success}The selected articles have been removed{/s}',
            deleteArticleTitle: '{s name=messages/delete_article_title}Delete selected Article(s)?{/s}',
            deleteArticle: '{s name=messages/delete_article}Are you sure you want to delete the selected Article(s)?{/s}'
        }
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'nfxProfitAnalize-main-window': {
                categoryChanged: me.onCategoryChanged
            },

            'nfxProfitAnalize-main-grid': {
                filterVariantsChange: me.onFilterVariantsChanged,
                deleteArticle: me.onDeleteArticle,
                deleteMultipleArticles: me.onDeleteMultipleArticles,
                edit: me.onEdit
            }
        });
        store = me.getStore('List');
        store.getProxy().extraParams.showVariants = 1;
        me.mainWindow = me.getView('main.Window').create({
          articleStore: store.load()
        });

        me.mainWindow.show();
        me.callParent(arguments);
    },

    /**
     * Fired after a row is edited and passes validation. This event is fired
     * after the store's update event is fired with this edit.
     *
     * @event edit
     * @param [Ext.grid.plugin.Editing]
     * @param [object] An edit event
     *
     * @return void
     */
    onEdit: function(editor, event) {
        var me     = this,
            store  = me.getStore('List'),
            record = event.record;

        if (!record.dirty) {
            return;
        }

        me.getArticleGrid().setLoading(true);
        record.save({
            success: function() {
                store.load({
                    callback: function() {
                        me.getArticleGrid().setLoading(false);
                    }
                });
            },
            failure: function() {
                me.getArticleGrid().setLoading(false);
            },
        });
    },

    /**
     * @param record
     */
    onDeleteArticle: function(record) {
        var me    = this,
            store = me.getStore('List');

        Ext.MessageBox.confirm(me.snippets.messages.deleteArticleTitle, me.snippets.messages.deleteArticle, function (response) {
            if (response !== 'yes') {
                return false;
            }
            record.destroy({
                callback: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.messages.successTitle, me.snippets.messages.deleteSuccess, me.snippets.growlMessage);
                    store.load();
                }
            });
        });
    },

    /**
     * @param records
     */
    onDeleteMultipleArticles: function(records) {
        var me    = this,
            store = me.getStore('List');

        if (records.length > 0) {
            // we do not just delete - we are polite and ask the user if he is sure.
            Ext.MessageBox.confirm(me.snippets.messages.deleteArticleTitle, me.snippets.messages.deleteArticle, function (response) {
                if ( response !== 'yes' ) {
                    return;
                }
                store.getProxy().batchActions = false;
                store.remove(records);
                store.sync({
                    callback: function() {
                        Shopware.Notification.createGrowlMessage(me.snippets.messages.successTitle, me.snippets.messages.deleteSuccess, me.snippets.growlMessage);
                        //store.currentPage = 1;
                        store.load();
                    }
                });
            });
        }
    },

    /**
     * @event filterVariantsChange
     * @param field
     * @param newValue
     */
    onFilterVariantsChanged: function(field, newValue) {
        var me = this,
            store = me.getStore('List');

        if (newValue) {
            store.getProxy().extraParams.showVariants = 1;
        } else {
            store.getProxy().extraParams.showVariants = 0;
        }

        store.load();
    },

    /**
     * @event categoryChanged
     * @param [Ext.view.View] view - the view that fired the event
     * @param [Ext.data.Model] record
     *
     * @return void
     */
    onCategoryChanged: function(view, record) {
        var me    = this,
            store = this.getStore('List'),
            grid  = me.getArticleGrid();

        if (record.get('id') === 'root') {
            store.getProxy().extraParams.categoryId = null;
        } else {
            store.getProxy().extraParams.categoryId = record.get('id');
        }

        //scroll the store to first page
        store.currentPage = 1;
        grid.setLoading(true);
        store.load({
            callback: function() {
                grid.setLoading(false);
            }
        });
    }
});

