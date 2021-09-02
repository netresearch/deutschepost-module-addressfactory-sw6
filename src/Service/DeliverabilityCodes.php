<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Service;

class DeliverabilityCodes
{
    public const DELIVERABLE = 'deliverable';
    public const UNDELIVERABLE = 'undeliverable';
    public const POSSIBLY_DELIVERABLE = 'possibly_deliverable';
    public const CORRECTION_REQUIRED = 'correction_required';

    private const PERSON_DELIVERABLE = 'PDC050105';
    private const PERSON_NOT_DELIVERABLE = 'PDC050106';
    private const HOUSEHOLD_DELIVERABLE = 'PDC040105';
    private const HOUSEHOLD_UNDELIVERABLE = 'PDC040106';
    private const BUILDING_DELIVERABLE = 'PDC030105';
    private const PERSON_NOT_MATCHED = 'PDC050500';
    private const HOUSEHOLD_NOT_MATCHED = 'PDC040500';
    private const BUILDING_UNDELIVERABLE = 'PDC030106';
    private const NOT_CORRECTABLE = 'BAC000111';
    private const HOUSE_NUMBER_NOT_FILLED = 'FNC030501';

    private const STATUS_CODES_SIGNIFICANTLY_CORRECTED = ['103', '108'];

    /**
     * @param string[] $codes
     */
    public function computeScore(array $codes, bool $wasAlreadyUpdated = false): string
    {
        $codes = $this->filterInapplicable($codes);

        if (!$wasAlreadyUpdated) {
            foreach ($codes as $code) {
                $statusCode = \mb_substr($code, -3, 3);
                if (\in_array($statusCode, self::STATUS_CODES_SIGNIFICANTLY_CORRECTED, true)) {
                    return self::CORRECTION_REQUIRED;
                }
            }
        }

        if (\in_array(self::HOUSE_NUMBER_NOT_FILLED, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        if (\in_array(self::NOT_CORRECTABLE, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        if (\in_array(self::PERSON_DELIVERABLE, $codes, true)) {
            return self::DELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_DELIVERABLE, $codes, true)
            && \in_array(self::HOUSEHOLD_DELIVERABLE, $codes, true)) {
            return self::DELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_DELIVERABLE, $codes, true)
            && \in_array(self::HOUSEHOLD_UNDELIVERABLE, $codes, true)
            && !\in_array(self::BUILDING_DELIVERABLE, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_MATCHED, $codes, true)
            && \in_array(self::HOUSEHOLD_DELIVERABLE, $codes, true)) {
            return self::DELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_MATCHED, $codes, true)
            && \in_array(self::HOUSEHOLD_UNDELIVERABLE, $codes, true)
            && \in_array(self::BUILDING_DELIVERABLE, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_MATCHED, $codes, true)
            && \in_array(self::HOUSEHOLD_NOT_MATCHED, $codes, true)
            && \in_array(self::BUILDING_DELIVERABLE, $codes, true)) {
            return self::POSSIBLY_DELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_MATCHED, $codes, true)
            && \in_array(self::HOUSEHOLD_NOT_MATCHED, $codes, true)
            && \in_array(self::BUILDING_UNDELIVERABLE, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        if (\in_array(self::PERSON_NOT_DELIVERABLE, $codes, true)) {
            return self::UNDELIVERABLE;
        }

        return self::POSSIBLY_DELIVERABLE;
    }

    /**
     * @param string[] $codes
     *
     * @return string[][]
     */
    public function getLabels(array $codes): array
    {
        $mappedCodes = [
            self::NOT_CORRECTABLE => [
                'icon' => 'icon-alert',
                'label' => 'Address not correctable',
                'code' => self::NOT_CORRECTABLE,
            ],
            'FNC000500' => [
                'icon' => 'icon-alert',
                'label' => 'Receiver not found in Post reference data',
                'code' => 'FNC000500',
            ],
        ];

        $mappedModuleCodes = [
            'BAC' => '',
            'FNC' => '',
        ];

        $mappedFieldCodes = [
            '000' => '',
            '010' => 'House address',
            '012' => 'Bulk recipient address',
            '020' => 'Street',
            '030' => 'Building',
            '040' => 'Household',
            '050' => 'Person',
            '060' => 'Postal code',
            '100' => 'Postal code',
            '101' => 'City',
            '102' => 'Street',
            '103' => 'City addition',
            '105' => 'City',
            '106' => 'Street',
            '110' => 'Postal code',
            '111' => 'City',
            '113' => 'City addition',
            '115' => 'City',
            '120' => 'Postal code',
            '121' => 'City',
            '122' => 'Bulk receiver name',
            '123' => 'City addition',
            '125' => 'City',
            '130' => 'Postal code',
            '131' => 'City',
            '133' => 'City addition',
            '135' => 'City',
            '144' => 'Country',
            '170' => 'Postal code',
            '171' => 'City',
            '173' => 'City addition',
            //            '011' => 'Post box address',
            //            '013' => 'Parcel station address',
            //            '017' => 'Post office address',
            //            '104' => 'District',
            //            '112' => 'Post box number',
            //            '132' => 'Post box number',
            //            '140' => 'Political information',
            //            '145' => 'Route code',
            //            '150' => 'Political information',
            //            '154' => 'Route code',
            //            '160' => 'Political information',
            //            '164' => 'Route code',
            //            '172' => 'Post office number',
            //            '200' => 'House number',
            //            '201' => 'House number addition',
        ];

        $mappedStatusCodes = [
            '000' => '',
            '103' => 'significantly corrected',
            '104' => 'marginally corrected',
            '106' => 'undeliverable',
            '108' => 'incorporated or renamed',
            '111' => 'different',
            '112' => 'moved',
            '113' => 'address type changed',
            '120' => 'receiver deceased',
            '121' => 'reportedly deceased',
            '140' => 'matched in Robinson list',
            '141' => 'matched in fake-name list',
            '500' => 'not matched',
            '501' => 'not filled',
            '503' => 'ambigouus',
            '504' => 'is foreign address',
            '505' => 'incorporated',
            '506' => 'is company address',
            //            '107' => 'enriched',
            //            '105' => 'deliverable',
            //            '509' => 'not queried',
            //            '102' => 'correct',
            //            '110' => 'separated from original data',
            //            '130' => 'doublet',
            //            '131' => 'head doublet',
            //            '132' => 'followed doublet',
            //            '135' => 'followed doublet in negative list',
        ];

        $labels = [];
        // remove redundant codes
        $codes = $this->filterInapplicable($codes);

        foreach ($codes as $code) {
            if (isset($mappedCodes[$code])) {
                $labels[] = $mappedCodes[$code];

                continue;
            }

            $moduleCode = \mb_substr($code, 0, 3);
            $fieldCode = \mb_substr($code, -6, 3);
            $statusCode = \mb_substr($code, -3, 3);

            if (
            isset(
                $mappedModuleCodes[$moduleCode],
                $mappedFieldCodes[$fieldCode],
                $mappedStatusCodes[$statusCode]
            )
            ) {
                $iconCode = $this->mapToIcon($fieldCode);
                $label = \ucfirst(
                    \trim(
                        $mappedModuleCodes[$moduleCode] . ' '
                        . $mappedFieldCodes[$fieldCode] . ' '
                        . $mappedStatusCodes[$statusCode]
                    )
                );
                $labels[] = [
                    'icon' => $iconCode,
                    'label' => $label,
                    'code' => $code,
                ];
            }
        }

        return $labels;
    }

    /**
     * @param string[] $codes
     *
     * @return string[]
     */
    private function filterInapplicable(array $codes): array
    {
        /**
         * BAC201110 - House numbers can be separated by the API, but Magento cannot take advantage of this
         * BAC010103, BAC010104 - These are always explained in more detail by another code.
         * FNC201103 - Street number addition corrected: This is a false positive in connection with BAC201110
         */
        $removals = ['BAC201110', 'BAC010103', 'BAC010104', 'FNC201103'];

        $codes = \array_diff($codes, $removals);

        if (\in_array(self::NOT_CORRECTABLE, $codes, true)) {
            /**
             * If self::NOT_CORRECTABLE is in codes, all other codes from BAC module become irrelevant
             */
            $codes = \array_filter(
                $codes,
                static function ($entry) {
                    return \mb_strpos($entry, 'BAC') !== false;
                }
            );
        }

        return $codes;
    }

    private function mapToIcon(string $fieldCode): string
    {
        $inHouse = ['010', '012', '030'];

        switch ($fieldCode) {
            case '000':
                $iconCode = 'icon-alert';

                break;
            case \in_array($fieldCode, $inHouse, true):
                $iconCode = 'icon-house';

                break;
            case '050':
                $iconCode = 'icon-user-account';

                break;
            case '040':
                $iconCode = 'icon-user-group';

                break;
            default:
                $iconCode = 'icon-info';
        }

        return $iconCode;
    }
}
