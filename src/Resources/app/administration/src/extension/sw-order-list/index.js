import template from './sw-order-list.html.twig';
import analysisStatus from '../../module/postdirekt-addressfactory/analysis-status';

const {Mixin} = Shopware;

Shopware.Component.override('sw-order-list', {
    template,

    mixins: [
        Mixin.getByName('postdirekt.addressfactory.analysis-status-loader'),
        Mixin.getByName('postdirekt.addressfactory.perform-analysis'),
        Mixin.getByName('postdirekt.addressfactory.update-address'),
    ],

    computed: {
        orderColumns() {
            let columns = this.getOrderColumns();

            /* Add analysis status column. */
            columns.push({
                property: 'analysisStatus',
                label: this.$tc('postdirekt-addressfactory.orderList.columnAnalysisStatus'),
                allowResize: true,
                sortable: false,
                align: 'left'
            });
            return columns;
        },
    },

    methods: {
        getDeliveryAddress(order) {
            if (!order.deliveries[0]) {
                return null;
            }
            return order.deliveries[0].shippingOrderAddress;
        },
        showAnalyse(order) {
            if (!this.getDeliveryAddress(order)) {
                return false;
            }

            return [
                analysisStatus.NOT_ANALYSED,
                analysisStatus.ANALYSIS_FAILED,
                analysisStatus.PENDING
            ].includes(this.getAnalysisStatus(order));
        },
        showUpdateAddress(order) {
            const deliveryAddress = this.getDeliveryAddress(order);

            if (!deliveryAddress) {
                return false;
            }

            return [
                analysisStatus.CORRECTION_REQUIRED,
                analysisStatus.POSSIBLY_DELIVERABLE,
            ].includes(this.getAnalysisStatus(order));
        },
        performAnalysisAction(order) {
            this.performAnalysis(order)
                .then(() => {
                    this.reloadAnalysisStatusData([order])
                });
        },
        updateAddressAction(order) {
            const deliveryAddress = this.getDeliveryAddress(order);

            if (!deliveryAddress) {
                return;
            }

            this.updateAddress(deliveryAddress)
                .then(() => {
                    this.reloadAnalysisStatusData([order])
                });
        }
    }
});

