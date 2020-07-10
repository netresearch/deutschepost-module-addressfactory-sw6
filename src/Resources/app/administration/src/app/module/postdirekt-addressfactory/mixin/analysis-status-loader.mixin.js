import analysisStatus from '../analysis-status';

const {Context, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Mixin.register('postdirekt.addressfactory.analysis-status-loader', {
    inject: [
        'repositoryFactory',
    ],
    data() {
        return {
            orders: [],
            analysisStatusList: {},
        }
    },
    watch: {
        orders: function (orders) {
            if (!orders.length) {
                return;
            }

            this.reloadAnalysisStatusData(orders);
        }
    },
    computed: {
        analysisStatusRepository() {
            return this.repositoryFactory.create('postdirekt_addressfactory_analysis_status');
        },
    },
    methods: {
        getAnalysisStatus(order) {
            if (this.analysisStatusList[order.id]) {
                return this.analysisStatusList[order.id];
            }
            return analysisStatus.NOT_ANALYSED;
        },
        reloadAnalysisStatusData(orders) {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equalsAny('orderId', orders.map(order => order.id)));
            this.analysisStatusRepository.search(criteria, Context.api)
                .then((result) => {
                    const newStatusList = result.reduce(
                        (statusList, status) => {
                            statusList[status.orderId] = status.status;
                            return statusList;
                        },
                        {}
                    );
                    this.analysisStatusList = {...this.analysisStatusList, ...newStatusList};
                });
        }
    },
});

