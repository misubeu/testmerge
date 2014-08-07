Ext.define('Shopware.apps.NfxProfitAnalize.model.List', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        { name: 'id', type: 'int' },
        { name: 'articleId', type: 'int' },

        { name: 'number',   type: 'string' },
        { name: 'name',     type: 'string' },
        { name: 'supplier', type: 'string' },
        { name: 'additionalText', type: 'string' },

        { name: 'tax',      type: 'string' },
        { name: 'price',    type: 'string' },

        { name: 'active',   type: 'boolean' },
       // { name: 'inStock',  type: 'int' },
        { name: 'saledItems',  type: 'int' },
        { name: 'salesPrice',  type: 'float' },
        { name: 'totalProfit',  type: 'float' },
        { name: 'imageSrc', type: 'string' },

        { name: 'hasVariants',      type: 'boolean' },
        { name: 'hasConfigurator',  type: 'boolean' },
        { name: 'hasCategories',    type: 'boolean' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read:    '{url action="list"}',
            update:  '{url action="update"}',
            destroy: '{url action="delete"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
