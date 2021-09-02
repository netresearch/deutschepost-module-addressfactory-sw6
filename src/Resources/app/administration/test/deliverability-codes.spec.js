import deliverabilityCodes from '../src/app/module/postdirekt-addressfactory/deliverability-codes';

// Mock global translation function
Shopware.Application.view = {root: {$t: (string) => string}};

describe('app/module/postdirekt-addressfactory/deliverability-codes', () => {
    it('should calculate DELIVERABLE score', () => {
        let testData = [
            {
                codes: ['PDC050105'],
                alreadyUpdated: false,
                expectedScore: deliverabilityCodes.DELIVERABLE,
            },
            {
                codes: ['PDC050106', 'PDC040105'],
                alreadyUpdated: false,
                expectedScore: deliverabilityCodes.DELIVERABLE,
            },
            {
                codes: ['PDC050500', 'PDC040105'],
                alreadyUpdated: false,
                expectedScore: deliverabilityCodes.DELIVERABLE,
            },
        ];
        testData.forEach((data) => {
            expect(deliverabilityCodes.computeScore(data.codes, data.alreadyUpdated))
                .toBe(data.expectedScore);
        });
    });
    it('should calculate UNDELIVERABLE score', () => {
        let testData = [
            {
                codes: ['PDC050500', 'PDC040106', 'PDC030105'],
                alreadyUpdated: false,
                expectedScore: deliverabilityCodes.UNDELIVERABLE,
            },
            {
                codes: ['PDC050500', 'PDC040500', 'PDC030106'],
                alreadyUpdated: false,
                expectedScore: deliverabilityCodes.UNDELIVERABLE,
            },
            {
                codes: ['PDC050106', 'PDC040106'],
                alreadyUpdated: false,
                expectedScore: deliverabilityCodes.UNDELIVERABLE,
            },
            {
                codes: ['PDC050106', 'PDC040106', 'PDC030105'],
                alreadyUpdated: false,
                expectedScore: deliverabilityCodes.UNDELIVERABLE,
            },
        ];
        testData.forEach((data) => {
            expect(deliverabilityCodes.computeScore(data.codes, data.alreadyUpdated))
                .toBe(data.expectedScore);
        });
    });
    it('should calculate POSSIBLY_DELIVERABLE score', () => {
        let testData = [
            {
                codes: ['PDC050500', 'PDC040106'],
                alreadyUpdated: false,
                expectedScore: deliverabilityCodes.POSSIBLY_DELIVERABLE,
            },
            {
                codes: ['PDC050500', 'PDC040500', 'PDC030105'],
                alreadyUpdated: false,
                expectedScore: deliverabilityCodes.POSSIBLY_DELIVERABLE,
            },
        ];
        testData.forEach((data) => {
            expect(deliverabilityCodes.computeScore(data.codes, data.alreadyUpdated))
                .toBe(data.expectedScore);
        });
    });
    it('should calculate CORRECTION_REQUIRED score', () => {
        let testData = [
            {
                codes: ['XXXXXX103'],
                alreadyUpdated: false,
                expectedScore: deliverabilityCodes.CORRECTION_REQUIRED,
            },
            {
                codes: ['XXXXXX108'],
                alreadyUpdated: false,
                expectedScore: deliverabilityCodes.CORRECTION_REQUIRED,
            },
        ];
        testData.forEach((data) => {
            expect(deliverabilityCodes.computeScore(data.codes, data.alreadyUpdated))
                .toBe(data.expectedScore);
        });
    });
    it('should return label objects', () => {
        let testData = [
            {
                code: 'FNC000500',
                expectedLabel: 'postdirekt-addressfactory.deliverabilityCodes.mappedCodes.receiverNotFoundInPostReferenceData',
                expectedIcon: 'default-badge-warning',
            },
        ];
        testData.forEach((data) => {
            expect(deliverabilityCodes.getLabels([data.code]))
                .toStrictEqual([{
                    label: data.expectedLabel,
                    icon: data.expectedIcon,
                    code: data.code,
                }]);
        });
    });
});
