import template from './analysis-details.html.twig';
import './analysis-details.scss';
import deliverabilityCodes from './../../deliverability-codes';
import analysisStatus from './../../analysis-status';

const {Context, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Shopware.Component.register('postdirekt.addressfactory.analysis-details', {
    template,
    inject: [
        'repositoryFactory'
    ],
    mixins: [
        Mixin.getByName('postdirekt.addressfactory.perform-analysis'),
        Mixin.getByName('postdirekt.addressfactory.cancel-order'),
    ],
    created() {
        this.getExistingAnalysis();
    },
    data() {
        return {
            analysisResult: null,
            analysisStatus: null,
            isLoading: false,
        };
    },
    props: {
        order: {
            type: Object,
            required: true,
        },
    },
    computed: {
        analysisResultRepository() {
            return this.repositoryFactory.create('postdirekt_addressfactory_analysis_result');
        },
        analysisStatusRepository() {
            return this.repositoryFactory.create('postdirekt_addressfactory_analysis_status');
        },
        deliveryAddress() {
            if (!this.order.deliveries[0]) {
                return null;
            }
            return this.order.deliveries[0].shippingOrderAddress;
        },
        isCancellable() {
            return this.order.stateMachineState.technicalName !== 'cancelled';
        },
        score() {
            if (!this.analysisResult) {
                return deliverabilityCodes.POSSIBLY_DELIVERABLE;
            }

            const wasAlreadyUpdated = (() => {
                if (!this.analysisStatus) {
                    return false;
                }
                return this.analysisStatus === analysisStatus.ADDRESS_CORRECTED
            })();

            return deliverabilityCodes.computeScore(
                this.analysisResult.statusCodes.split(','),
                wasAlreadyUpdated
            );
        },
        scoreColor() {
            if (this.score === deliverabilityCodes.DELIVERABLE) {
                return '#50be03';
            } else if (this.score === deliverabilityCodes.POSSIBLY_DELIVERABLE) {
                return '#ffcc01';
            } else {
                return '#ff0000';
            }
        },
        humanReadableScore() {
            const scores = {
                [deliverabilityCodes.POSSIBLY_DELIVERABLE]: this.$t('postdirekt-addressfactory.deliverabilityCodes.possibleDeliverable'),
                [deliverabilityCodes.DELIVERABLE]: this.$t('postdirekt-addressfactory.deliverabilityCodes.deliverable'),
                [deliverabilityCodes.UNDELIVERABLE]: this.$t('postdirekt-addressfactory.deliverabilityCodes.undeliverable'),
                [deliverabilityCodes.CORRECTION_REQUIRED]: this.$t('postdirekt-addressfactory.deliverabilityCodes.correctionRecommended'),
            };

            return scores[this.score];
        },
    },
    methods: {
        isEnabled() {
            return !!this.deliveryAddress && this.deliveryAddress.country.iso === 'DE';
        },
        getLogoPath() {
            return "nrlejpostdirektaddressfactory/static/assets/images/addressfactory-logo.png"
        },
        getExistingAnalysis() {
            const resultCriteria = new Criteria();
            const statusCriteria = new Criteria();

            this.isLoading = true;

            resultCriteria.addFilter(Criteria.equals('orderAddressId', this.deliveryAddress.id));
            statusCriteria.addFilter(Criteria.equals('orderId', this.order.id));

            Promise.all([
                this.analysisResultRepository.search(resultCriteria, Context.api),
                this.analysisStatusRepository.search(statusCriteria, Context.api)
            ]).then(([result, status]) => {
                if (result[0]) {
                    this.analysisResult = result[0];
                }
                if (status[0]) {
                    this.analysisStatus = status[0].status;
                }
            }).finally(() => {
                this.isLoading = false;
            })
        },
        getDetectedIssues() {
            return deliverabilityCodes.getLabels(
                this.analysisResult.statusCodes.split(',')
            )
        },
        updateAnalysisStatus(value) {
            this.analysisStatus = value;
        }
    }
});
