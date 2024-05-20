import template from './sw-order-list.html.twig';
import analysisStatus from '../../module/postdirekt-addressfactory/analysis-status';

const {Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Shopware.Component.override('sw-order-list', {
    template,

    mixins: [
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
        orderCriteria() {
            let criteria = this.$super('orderCriteria');
            criteria.addAssociation('analysisStatus');
            return criteria;
        },
    },
    methods: {
        getAnalysisStatus(order) {
            if (order?.extensions?.analysisStatus?.status) {
                return order.extensions.analysisStatus.status;
            }

            return analysisStatus.NOT_ANALYSED;
        },
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
        performAnalysisAction(order) {
            this.performAnalysis(order).then(() => {
                this.reloadAnalysisStatusData([order])
            });
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
        updateAddressAction(order) {
            const deliveryAddress = this.getDeliveryAddress(order);

            if (!deliveryAddress) {
                return;
            }

            this.updateAddress(deliveryAddress).then(() => {
                this.reloadAnalysisStatusData([order])
            });
        },
        async reloadAnalysisStatusData(orders) {
            this.isLoading = true;

            let criteria = await Shopware.Service('filterService')
                .mergeWithStoredFilters(this.storeKey, this.orderCriteria);

            criteria = await this.addQueryScores(this.term, criteria);
            criteria.addFilter(Criteria.equalsAny('id', orders.map(order => order.id)));

            try {
                const response = await this.orderRepository.search(criteria);
                this.orders.forEach((order, index) => {
                    const updatedOrder = response.get(order.id);

                    if (updatedOrder) {
                        this.$set(this.orders, index, updatedOrder);
                    }
                });
            } finally {
                this.isLoading = false;
            }
        }
    }
});
