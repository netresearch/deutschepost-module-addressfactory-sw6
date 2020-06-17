const {Application, Mixin} = Shopware;

Mixin.register('postdirekt.addressfactory.perform-analysis', {
    data() {
        return {
            analysisResult: null,
            isLoading: false,
        };
    },
    mixins: [
        Mixin.getByName('notification'),
    ],
    methods: {
        performAnalysis(order) {
            this.isLoading = true;
            const initContainer = Application.getContainer('init');

            return initContainer.httpClient.post(
                'postdirekt/addressfactory/perform-analysis',
                {order_id: order.id}
            )
            .then(response => response.data)
            .then(data => {
                this.analysisResult = data.analysisResult;
                if (!data.message) {
                    return;
                }
                if (data.analysisResult) {
                    this.createNotificationSuccess({
                        title: this.$t('postdirekt-addressfactory.performAnalysis.title'),
                        message: this.$t(data.message, {orderNumber: data.orderNumber}),
                    });
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
        }
    }
});
