//{namespace name=backend/order/nfx_profit_column/view/list}
//{block name="backend/order/view/list/list" append}
Ext.override(Shopware.apps.Order.view.list.List, {
    nfx_profit_snippets : {
        profitAmountDetails: '{s name=column/profit}Profit{/s}'
    },
    getColumns: function() {
        var me = this, result;
        result = me.callParent(arguments);
        return Ext.Array.insert(result, 3, me.createNFXProfitColumn());
    },

    createNFXProfitColumn: function() {
        var me = this;
        return [{
            header: me.nfx_profit_snippets.profitAmountDetails,
            dataIndex: 'profitAmount',
            flex:1,
            renderer:me.colorColumnRenderer
        }]
    },
    
    colorColumnRenderer: function(value) {
      if ( value === Ext.undefined ) {
            return value;
        }
      if (value > 0){
          return '<span style="color:green;">' + Ext.util.Format.currency(value) + '</span>';
      } else {
          return '<span style="color:red;">' + Ext.util.Format.currency(value) + '</span>';
      }
    }

});
//{/block}
