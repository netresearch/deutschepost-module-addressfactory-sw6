import template from './sw-order-detail-details.twig';

const {State} = Shopware;

Shopware.Component.override('sw-order-detail-details', {
    template,

    methods: {
        createdComponent() {
            this.$super('createdComponent');

            State.watch(
                (state) => state.swOrderDetail.savedSuccessful,
                (newValue, oldValue) => {
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
