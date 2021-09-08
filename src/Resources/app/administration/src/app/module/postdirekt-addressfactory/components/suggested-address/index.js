import template from './suggested-address.html.twig';
import './suggested-address.scss';

const {Mixin} = Shopware;

Shopware.Component.register('postdirekt.addressfactory.suggested-address', {
    template,

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('postdirekt.addressfactory.update-address'),
    ],
    props: {
        deliveryAddress: {
            type: Object,
            required: true,
        },
        analysisResult: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            isLoading: false,
        };
    },
    methods: {
        isEditingContext() {
          return this.$parent.isEditingContext();
        },
        isCityDifferent() {
            return this.deliveryAddress.city !== this.analysisResult.city;
        },
        isStreetDifferent() {
            return this.deliveryAddress.street !== [
                this.analysisResult.street,
                this.analysisResult.streetNumber
            ].join(' ').trim();
        },
        isFirstNameDifferent() {
            return this.deliveryAddress.firstName !== this.analysisResult.firstName;
        },
        isLastNameDifferent() {
            return this.deliveryAddress.lastName !== this.analysisResult.lastName;
        },
        isZipcodeDifferent() {
            return this.deliveryAddress.zipcode !== this.analysisResult.postalCode;
        },
        shouldShowAddress() {
            return this.isCityDifferent()
                || this.isStreetDifferent()
                || this.isFirstNameDifferent()
                || this.isLastNameDifferent()
                || this.isZipcodeDifferent();
        },
    }
});
