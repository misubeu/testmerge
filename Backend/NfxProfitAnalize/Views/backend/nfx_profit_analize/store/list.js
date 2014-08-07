
Ext.define('Shopware.apps.NfxProfitAnalize.store.List', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    model : 'Shopware.apps.NfxProfitAnalize.model.List',
    remoteSort: true,
    remoteFilter: true,
    pageSize: 40
});

