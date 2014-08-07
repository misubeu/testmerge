//{namespace name=backend/order/nfx_profit_column/view/position}
//{block name="backend/order/view/list/position" append}
Ext.override(Shopware.apps.Order.view.list.Position, {
    nfx_profit_snippets : {
        profitAmountDetails: '{s name=position/profit_amount}Profit{/s}'
    },
    getColumns: function() {
        var me = this, result;
        result = me.callParent(arguments);
        return Ext.Array.insert(result, 4, me.createNFXProfitColumn());
    },

    createNFXProfitColumn: function() {
        var me = this;
        return [{
            header: me.nfx_profit_snippets.profitAmountDetails,
            dataIndex: 'profitAmountDetails',
            flex: 2,
            renderer: me.profitColumn
        }]
    },
    
    profitColumn: function(value) {
        if ( value === Ext.undefined ) {
            return value;
        }
        return Ext.util.Format.currency(value);
    }

});
//{/block}
