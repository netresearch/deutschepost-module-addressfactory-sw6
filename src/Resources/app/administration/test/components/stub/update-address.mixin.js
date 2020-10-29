const {Mixin} = Shopware;

Mixin.register('postdirekt.addressfactory.update-address', {
    data() {
        return {
            isLoading: false,
        };
    },
    methods: {
        updateAddress(deliveryAddress) {
            return 'updateAddress called';
        }
    }
});
