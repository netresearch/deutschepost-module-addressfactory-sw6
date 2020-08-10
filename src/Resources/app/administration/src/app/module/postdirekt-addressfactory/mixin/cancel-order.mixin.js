const {Mixin} = Shopware;
const ApiService = Shopware.Classes.ApiService;

Mixin.register('postdirekt.addressfactory.cancel-order', {

    inject: [
        'orderStateMachineService'
    ],
    mixins: [
        Mixin.getByName('notification'),
    ],
    methods: {
        cancelOrder(order) {
            this.isLoading = true;

            this.orderStateMachineService.transitionOrderState(
                order.id,
                'cancel'
            ).then(ApiService.handleResponse
            ).then((state) => {
                order.stateMachineState = state;
                this.$emit('order-state-change');
            }).catch((error) => {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('sw-order.stateCard.labelErrorStateChange') + error
                });
            }).finally(() => {
                this.isLoading = false;
            });
        }
    }
});
