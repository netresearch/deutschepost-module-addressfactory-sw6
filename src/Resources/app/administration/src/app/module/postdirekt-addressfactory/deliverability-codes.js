const {Application} = Shopware;

const DELIVERABLE = 'deliverable';
const UNDELIVERABLE = 'undeliverable';
const POSSIBLY_DELIVERABLE = 'possibly_deliverable';
const CORRECTION_REQUIRED = 'correction_required';

const PERSON_DELIVERABLE = 'PDC050105';
const PERSON_NOT_DELIVERABLE = 'PDC050106';
const HOUSEHOLD_DELIVERABLE = 'PDC040105';
const HOUSEHOLD_UNDELIVERABLE = 'PDC040106';
const BUILDING_DELIVERABLE = 'PDC030105';
const PERSON_NOT_MATCHED = 'PDC050500';
const HOUSEHOLD_NOT_MATCHED = 'PDC040500';
const BUILDING_UNDELIVERABLE = 'PDC030106';
const NOT_CORRECTABLE = 'BAC000111';
const HOUSE_NUMBER_NOT_FILLED = 'FNC030501';

const STATUS_CODES_SIGNIFICANTLY_CORRECTED = ['103', '108'];

const mapToIcon = (fieldCode) => {
    if (fieldCode === '000') {
        return 'default-badge-warning';
    } else if (['010', '012', '030'].includes(fieldCode)) {
        return 'default-building-home';
    } else if (fieldCode === '050') {
        return 'default-avatar-single';
    } else if (fieldCode === '040') {
        return 'default-avatar-multiple';
    } else {
        return 'default-badge-info';
    }
};

const filterInapplicable = (codes) => {
    /**
     * BAC201110 - House numbers can be separated by the API, but Magento cannot take advantage of this
     * BAC010103, BAC010104 - These are always explained in more detail by another code.
     * FNC201103 - Street number addition corrected: This is a false positive in connection with BAC201110
     */
    const removals = ['BAC201110', 'BAC010103', 'BAC010104', 'FNC201103'];

    codes = codes.filter(code => !removals.includes(code));
    if (codes.includes(NOT_CORRECTABLE)) {
        /**
         * if NOT_CORRECTABLE is part of the codes, all codes from the BAC module become irrelevant
         */
        codes = codes.filter(code => code.indexOf('BAC') !== -1)
    }
    return codes;
};

const $t = (string) => {
    return Application.view.root.$t(string);
};

const computeScore = (codes, wasAlreadyUpdated) => {
    codes = filterInapplicable(codes);

    if (!wasAlreadyUpdated) {
        for (const code of codes) {
            const statusCode = code.substring(6, 9);
            if (STATUS_CODES_SIGNIFICANTLY_CORRECTED.includes(statusCode)) {
                return CORRECTION_REQUIRED;
            }
        }
    }

    if (codes.includes(HOUSE_NUMBER_NOT_FILLED)) {
        return UNDELIVERABLE;
    }

    if (codes.includes(NOT_CORRECTABLE)) {
        return UNDELIVERABLE;
    }

    if (codes.includes(PERSON_DELIVERABLE)) {
        return DELIVERABLE;
    }

    if (codes.includes(PERSON_NOT_DELIVERABLE) &&
        codes.includes(HOUSEHOLD_DELIVERABLE)) {
        return DELIVERABLE;
    }

    if (codes.includes(PERSON_NOT_DELIVERABLE) &&
        codes.includes(HOUSEHOLD_UNDELIVERABLE) &&
        !codes.includes(BUILDING_DELIVERABLE)) {
        return UNDELIVERABLE;
    }

    if (codes.includes(PERSON_NOT_MATCHED) &&
        codes.includes(HOUSEHOLD_DELIVERABLE)) {
        return DELIVERABLE;
    }

    if (codes.includes(PERSON_NOT_MATCHED) &&
        codes.includes(HOUSEHOLD_UNDELIVERABLE) &&
        codes.includes(BUILDING_DELIVERABLE)) {
        return UNDELIVERABLE;
    }

    if (codes.includes(PERSON_NOT_MATCHED) &&
        codes.includes(HOUSEHOLD_NOT_MATCHED) &&
        codes.includes(BUILDING_DELIVERABLE)) {
        return POSSIBLY_DELIVERABLE;
    }

    if (codes.includes(PERSON_NOT_MATCHED) &&
        codes.includes(HOUSEHOLD_NOT_MATCHED) &&
        codes.includes(BUILDING_UNDELIVERABLE)) {
        return UNDELIVERABLE;
    }

    if (codes.includes(PERSON_NOT_DELIVERABLE)) {
        return UNDELIVERABLE;
    }

    return POSSIBLY_DELIVERABLE;
};

const getLabels = (codes) => {
    const mappedCodes = {
        [NOT_CORRECTABLE]: {
            'icon': 'default-badge-warning',
            'label': $t('postdirekt-addressfactory.deliverabilityCodes.mappedCodes.addressNotCorrectable'),
            'code': NOT_CORRECTABLE,
        },
        'FNC000500': {
            'icon': 'default-badge-warning',
            'label': $t('postdirekt-addressfactory.deliverabilityCodes.mappedCodes.receiverNotFoundInPostReferenceData'),
            'code': 'FNC000500',
        }
    };

    const mappedModuleCodes = {
        'BAC': '',
        'FNC': '',
    };

    const mappedFieldCodes = {
        '000': '',
        '010': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.houseAddress'),
        '012': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.bulkRecipientAddress'),
        '020': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.street'),
        '030': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.building'),
        '040': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.household'),
        '050': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.person'),
        '060': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.postalCode'),
        '100': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.postalCode'),
        '101': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.city'),
        '102': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.street'),
        '103': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.cityAddition'),
        '105': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.city'),
        '106': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.street'),
        '110': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.postalCode'),
        '111': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.city'),
        '113': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.cityAddition'),
        '115': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.city'),
        '120': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.postalCode'),
        '121': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.city'),
        '122': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.bulkReceiverName'),
        '123': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.cityAddition'),
        '125': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.city'),
        '130': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.postalCode'),
        '131': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.city'),
        '133': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.cityAddition'),
        '135': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.city'),
        '144': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.country'),
        '170': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.postalCode'),
        '171': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.city'),
        '173': $t('postdirekt-addressfactory.deliverabilityCodes.fieldCodes.cityAddition'),
        //            '011': $t('Post box address'),
        //            '013': $t('Parcel station address'),
        //            '017': $t('Post office address'),
        //            '104': $t('District'),
        //            '112': $t('Post box number'),
        //            '132': $t('Post box number'),
        //            '140': $t('Political information'),
        //            '145': $t('Route code'),
        //            '150': $t('Political information'),
        //            '154': $t('Route code'),
        //            '160': $t('Political information'),
        //            '164': $t('Route code'),
        //            '172': $t('Post office number'),
        //            '200': $t('House number'),
        //            '201': $t('House number addition'),
    };

    const mappedStatusCodes = {
        '103': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.significantlyCorrected'),
        '104': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.marginallyCorrected'),
        '106': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.undeliverable'),
        '108': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.incorporatedOrRenamed'),
        '111': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.different'),
        '112': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.moved'),
        '113': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.addressTypeChanged'),
        '120': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.receiverDeceased'),
        '121': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.reportedlyDeceased'),
        '140': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.matchedInRobinsonList'),
        '141': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.matchedInFakenameList'),
        '500': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.notMatched'),
        '501': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.notFilled'),
        '503': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.ambigouus'),
        '504': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.isForeignAddress'),
        '505': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.incorporated'),
        '506': $t('postdirekt-addressfactory.deliverabilityCodes.statusCodes.isCompanyAddress'),
        //            '107': $t('enriched'),
        //            '105': $t('deliverable'),
        //            '509': $t('not queried'),
        //            '102': $t('correct'),
        //            '110': $t('separated from original data'),
        //            '130': $t('doublet'),
        //            '131': $t('head doublet'),
        //            '132': $t('followed doublet'),
        //            '135': $t('followed doublet in negative list'),
    };

    // remove redundant codes
    codes = filterInapplicable(codes);

    return codes.map((code) => {
        if (code in mappedCodes) {
            return mappedCodes[code];
        }

        const moduleCode = code.substring(0, 3);
        const fieldCode = code.substring(3, 6);
        const statusCode = code.substring(6, 9);

        if (
            moduleCode in mappedModuleCodes
            && fieldCode in mappedFieldCodes
            && statusCode in mappedStatusCodes
        ) {
            return {
                'icon': mapToIcon(fieldCode),
                'label': [
                    mappedModuleCodes[moduleCode],
                    mappedFieldCodes[fieldCode],
                    mappedStatusCodes[statusCode]
                ].join(' ').trim(),
                'code': code,
            };
        }

        return null;
    }).filter(item => !!item);
}

export default {
    computeScore: computeScore,
    getLabels: getLabels,
    DELIVERABLE: DELIVERABLE,
    UNDELIVERABLE: UNDELIVERABLE,
    POSSIBLY_DELIVERABLE: POSSIBLY_DELIVERABLE,
    CORRECTION_REQUIRED: CORRECTION_REQUIRED
};
