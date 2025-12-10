const {Mixin} = Shopware;

Mixin.register('postdirekt.addressfactory.update-address', {
    data() {
        return {
            isLoading: false,
        };
    },
    methods: {
        updateAddress() {
            return 'updateAddress called';
        }
    }
});
