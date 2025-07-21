import template from './sw-order-detail-details.twig';

Shopware.Component.override('sw-order-detail-details', {
    template,

    methods: {
        createdComponent() {
            this.$super('createdComponent');

            Shopware.Vue.watch(
                () => Shopware.Store.get('swOrderDetail').savedSuccessful,
                (newValue) => {
                    if (newValue === true) {
                        this.resetAnalysisResult();
                    }
                }
            );
        },
        resetAnalysisResult() {
            this.$refs.postdirektAnalysisDetails.getExistingAnalysis();
        },
        orderChanges() {
            return this.$parent.$parent.$parent.isOrderEditing;
        }
    }
});
