//{namespace name=backend/order/nfx_profit_column/view/detail}
//{block name="backend/order/view/detail/position" append}
Ext.override(Shopware.apps.Order.view.detail.Position, {
    getColumns: function(grid) {
        var me = this, result;
        result = me.callParent(arguments);
        return Ext.Array.insert(result, 5, me.createNFXProfitColumn(grid));
    },

    createNFXProfitColumn: function(grid) {
        var me = this;
        return [{
            header: grid.nfx_profit_snippets.profitAmountDetails,
            dataIndex: 'profitAmountDetails',
            flex:2,
            renderer: grid.profitColumn
        }]
    }

});
//{/block}
