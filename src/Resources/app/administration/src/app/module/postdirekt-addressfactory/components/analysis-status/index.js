import template from './analysis-status.html.twig';
import './analysis-status.scss';
import analysisStatus from './../../analysis-status';


Shopware.Component.register('postdirekt.addressfactory.analysis-status', {
    template: template,

    props: {
        status: {
            type: String,
            required: true,
        },
    },
    computed: {
        statusColor() {
            const greenStatus = [
                analysisStatus.DELIVERABLE,
                analysisStatus.ADDRESS_CORRECTED
            ];
            const yellowStatus = [
                analysisStatus.POSSIBLY_DELIVERABLE,
            ]
            const redStatus = [
                analysisStatus.ANALYSIS_FAILED,
                analysisStatus.UNDELIVERABLE,
                analysisStatus.CORRECTION_REQUIRED,
            ]

            if (greenStatus.includes(this.status)) {
                return '#50be03';
            } else if (yellowStatus.includes(this.status)) {
                return '#ffcc01';
            } else if (redStatus.includes(this.status)) {
                return '#ff0000';
            } else {
                return '#dddddd';
            }
        },
        statusLabel() {
            const statusLabels = {
                [analysisStatus.POSSIBLY_DELIVERABLE]: this.$t('postdirekt-addressfactory.analysisStatus.possibleDeliverable'),
                [analysisStatus.DELIVERABLE]: this.$t('postdirekt-addressfactory.analysisStatus.deliverable'),
                [analysisStatus.UNDELIVERABLE]: this.$t('postdirekt-addressfactory.analysisStatus.undeliverable'),
                [analysisStatus.CORRECTION_REQUIRED]: this.$t('postdirekt-addressfactory.analysisStatus.correctionRecommended'),
                [analysisStatus.ADDRESS_CORRECTED]: this.$t('postdirekt-addressfactory.analysisStatus.addressCorrected'),
                [analysisStatus.NOT_ANALYSED]: this.$t('postdirekt-addressfactory.analysisStatus.notAnalysed'),
                [analysisStatus.PENDING]: this.$t('postdirekt-addressfactory.analysisStatus.pending'),
            };

            return statusLabels[this.status] ? statusLabels[this.status] : this.status;
        }
    }
});
