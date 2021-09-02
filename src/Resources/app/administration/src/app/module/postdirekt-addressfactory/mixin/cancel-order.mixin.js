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
        cancelOrder() {
            this.$emit('order-state-change', 'cancel');
        }
    }
});
