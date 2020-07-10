const {Mixin} = Shopware;

Mixin.register('postdirekt.addressfactory.update-address', {
    data() {
        return {
            isLoading: false,
        };
    },
    inject: [
        'postdirektUpdateAddressService'
    ],
    mixins: [
        Mixin.getByName('notification'),
    ],
    methods: {
        updateAddress(deliveryAddress) {
            this.isLoading = true;

            return this.postdirektUpdateAddressService.updateAddress(deliveryAddress.orderId)
                .then(data => {
                    if (!!data.success && !!data.orderAddress) {
                        if (data.orderAddress.id !== deliveryAddress.id) {
                            throw this.$t('postdirekt-addressfactory.updateAddress.idMismatch');
                        }
                        deliveryAddress.firstName = data.orderAddress.firstName;
                        deliveryAddress.lastName = data.orderAddress.lastName;
                        deliveryAddress.street = data.orderAddress.street;
                        deliveryAddress.zipcode = data.orderAddress.zipcode;
                        deliveryAddress.city = data.orderAddress.city;

                        this.createNotificationSuccess({
                            title: this.$t('postdirekt-addressfactory.updateAddress.title'),
                            message: this.$t(data.message)
                        });
                    } else {
                        throw this.$t(data.message);
                    }
                }).catch((error) => {
                    this.createNotificationError({
                        title: this.$t('postdirekt-addressfactory.updateAddress.errorTitle'),
                        message: error,
                    });
                }).finally(() => {
                    this.isLoading = false;
                });
        }
    }
});
