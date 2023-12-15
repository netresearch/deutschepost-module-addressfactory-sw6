const {State} = Shopware;

Shopware.Component.override('sw-order-detail', {
    methods: {
        async onSaveEdits() {
            State.commit('swOrderDetail/setSavedSuccessful', false);
            await this.$super('onSaveEdits');
        }
    }
});
