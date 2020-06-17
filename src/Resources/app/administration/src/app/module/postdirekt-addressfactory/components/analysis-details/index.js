import template from './analysis-details.html.twig';
import './analysis-details.scss';
import deliverabilityCodes from './../../deliverability-codes';

const {Context, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Shopware.Component.register('postdirekt.addressfactory.analysis-details', {
    template,
    inject: [
        'repositoryFactory'
    ],
    mixins: [
        Mixin.getByName('postdirekt.addressfactory.perform-analysis'),
    ],
    created() {
        this.getExistingAnalysis();
    },
    data() {
        return {
            analysisResult: null,
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
        deliveryAddress() {
            if (!this.order.deliveries[0]) {
                return null;
            }
            return this.order.deliveries[0].shippingOrderAddress;
        },
        isCancellable() {
            return this.order.stateMachineState.technicalName !== 'cancelled';
        }
    },
    methods: {
        isEnabled() {
            return !!this.deliveryAddress && this.deliveryAddress.country.iso === 'DE';
        },
        getLogoPath() {
            return "nrlejpostdirektaddressfactory/static/assets/images/addressfactory-logo.png"
        },
        getExistingAnalysis() {
            this.isLoading = true;
            const criteria = new Criteria();
            criteria.addFilter(
                Criteria.equals('orderAddressId', this.deliveryAddress.id)
            );
            this.analysisResultRepository.search(criteria, Context.api)
            .then((result) => {
                if (result.first()) {
                    this.analysisResult = result.first();
                }
            }).finally(() => {
                this.isLoading = false;
            });
        },
        getHumanReadableScore() {
            const scores = {
                [deliverabilityCodes.POSSIBLY_DELIVERABLE]: this.$t('postdirekt-addressfactory.deliverabilityCodes.possibleDeliverable'),
                [deliverabilityCodes.DELIVERABLE]: this.$t('postdirekt-addressfactory.deliverabilityCodes.deliverable'),
                [deliverabilityCodes.UNDELIVERABLE]: this.$t('postdirekt-addressfactory.deliverabilityCodes.undeliverable'),
                [deliverabilityCodes.CORRECTION_REQUIRED]: this.$t('postdirekt-addressfactory.deliverabilityCodes.correctionRecommended'),
            };


            return scores[this.getScore()];
        },
        getScore() {
            if (!this.analysisResult) {
                return deliverabilityCodes.POSSIBLY_DELIVERABLE;
            }

            /** @TODO: Derive value from the analysis status */
            const wasAlreadyUpdated = false;

            return deliverabilityCodes.computeScore(
                this.analysisResult.statusCodes.split(','),
                wasAlreadyUpdated
            );
        },
        getScoreColor() {
            const score = this.getScore();

            if (score === deliverabilityCodes.DELIVERABLE) {
                return '#50be03';
            } else if (score === deliverabilityCodes.POSSIBLY_DELIVERABLE) {
                return '#ffcc01';
            } else {
                return '#ff0000';
            }
        },
        getDetectedIssues() {
            return deliverabilityCodes.getLabels(
                this.analysisResult.statusCodes.split(',')
            )
        }
    }
});
