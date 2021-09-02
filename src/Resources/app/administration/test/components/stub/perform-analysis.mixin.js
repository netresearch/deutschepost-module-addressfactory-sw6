const {Mixin} = Shopware;

Mixin.register('postdirekt.addressfactory.perform-analysis', {
    data() {
        return {
            analysisResult: null,
            analysisStatus: null,
            isLoading: false
        };
    },
    methods: {
        performAnalysis(order) {
            this.analysisResult = {
                city: 'city',
                street: 'street street2',
                streetNumber: '10',
                firstName: 'firstName',
                lastName: 'lastName',
                postalCode: 'postalCode',
                statusCodes: '123,456',
            };
            this.analysisStatus = 'undeliverable'

            return this.analysisResult;
        },
        getDeliveryAddress(order) {
            if (!order.deliveries[0]) {
                return null;
            }
            return order.deliveries[0].shippingOrderAddress;
        },
    }
});
