import template from './sw-order-list.html.twig';
import analysisStatus from '../../app/module/postdirekt-addressfactory/analysis-status';

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
        showAnalyse(order) {
            if (!order.deliveries.length) {
                return false;
            }

            return [
                analysisStatus.NOT_ANALYSED,
                analysisStatus.ANALYSIS_FAILED,
                analysisStatus.PENDING
            ].includes(this.getAnalysisStatus(order));
        },
        showUpdateAddress(order) {
            if (!order.deliveries.length) {
                return false;
            }

            return this.getAnalysisStatus(order) === analysisStatus.CORRECTION_REQUIRED;
        },
        performAnalysisAction(order) {
            this.performAnalysis(order)
                .then(() => {
                    this.reloadAnalysisStatusData([order])
                });
        },
        updateAddressAction(order) {
            this.updateAddress(order.deliveries[0].shippingOrderAddress)
                .then(() => {
                    this.reloadAnalysisStatusData([order])
                });
        }
    }
});

