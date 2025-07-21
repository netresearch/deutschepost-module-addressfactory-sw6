const {State} = Shopware;

Shopware.Component.override('sw-order-detail', {
    methods: {
        async onSaveEdits() {
            Shopware.Store.get('swOrderDetail').savedSuccessful = false;
            await this.$super('onSaveEdits');
        }
    }
});
