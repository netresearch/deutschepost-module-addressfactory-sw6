const {Mixin} = Shopware;


Mixin.register('postdirekt.addressfactory.perform-analysis', {
    data() {
        return {
            analysisResult: null,
            analysisStatus: null,
            isLoading: false
        };
    },
    inject: [
        'postdirektPerformAnalysisService'
    ],
    mixins: [
        Mixin.getByName('notification'),
    ],
    methods: {
        performAnalysis(order) {
            this.isLoading = true;

            const deliveryAddress = this.getDeliveryAddress(order);

            return this.postdirektPerformAnalysisService.performAnalysis(order.id)
                .then(data => {
                    this.analysisResult = data.analysisResult;

                    if (data.order) {
                        // Update order state (in case order was cancelled)
                        order.stateMachineState = data.order.stateMachineState;
                        if (deliveryAddress) {
                            // Update delivery address (in case address was updated)
                            deliveryAddress.firstName = data.order.deliveries[0].shippingOrderAddress.firstName;
                            deliveryAddress.lastName = data.order.deliveries[0].shippingOrderAddress.lastName;
                            deliveryAddress.street = data.order.deliveries[0].shippingOrderAddress.street;
                            deliveryAddress.zipcode = data.order.deliveries[0].shippingOrderAddress.zipcode;
                            deliveryAddress.city = data.order.deliveries[0].shippingOrderAddress.city;
                        }
                    }
                    if (data.analysisStatus) {
                        // Update analysis status (in case address was updated and status is now ADDRESS_CORRECTED)
                        this.analysisStatus = data.analysisStatus
                    }
                    if (data.analysisResult) {
                        if (data.message) {
                            this.createNotificationSuccess({
                                title: this.$t('postdirekt-addressfactory.performAnalysis.title'),
                                message: this.$t(data.message, {orderNumber: data.orderNumber}),
                            });
                        }
                    } else {
                        this.createNotificationError({
                            title: this.$t('postdirekt-addressfactory.performAnalysis.errorTitle'),
                            message: this.$t(data.message, {orderNumber: data.orderNumber}),
                        });
                    }
                }).catch((error) => {
                    this.createNotificationError({
                        title: this.$t('postdirekt-addressfactory.performAnalysis.errorTitle'),
                        message: this.$t('postdirekt-addressfactory.performAnalysis.genericError'),
                    });
                }).finally(() => {
                    this.isLoading = false;
                });
        },
        getDeliveryAddress(order) {
            if (!order.deliveries[0]) {
                return null;
            }
            return order.deliveries[0].shippingOrderAddress;
        },
    }
});
