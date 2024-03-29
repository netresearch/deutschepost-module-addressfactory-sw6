import {shallowMount} from '@vue/test-utils';
import 'babel-polyfill'
import './stub/update-address.mixin'
import '../../src/app/module/postdirekt-addressfactory/components/suggested-address/index'

describe('postdirekt.addressfactory.suggested-address with identical addresses', () => {
    let wrapper = shallowMount(Shopware.Component.build('postdirekt.addressfactory.suggested-address'), {
        propsData: {
            deliveryAddress: {
                city: 'city',
                street: ['street', 'street2 10'],
                firstName: 'firstName',
                lastName: 'lastName',
                zipcode: 'zipcode'
            },
            analysisResult: {
                city: 'city',
                street: 'street street2',
                streetNumber: '10',
                firstName: 'firstName',
                lastName: 'lastName',
                postalCode: 'postalCode',
            }
        },
        mocks: {
            $t: key => key,
            isInEditingContext: () => false,
        }
    });
    it('does not display a box, address, or update button', () => {
        expect(wrapper.find('.address-parts').exists()).toBe(true);
        expect(wrapper.find('button').exists()).toBe(true);
    });

    it('can update the address using a mixin', () => {
        let updateAddressSpy = jest.spyOn(wrapper.vm, 'updateAddress')
        wrapper.find('button').trigger('click');
        expect(updateAddressSpy).toHaveBeenCalled()
    });
});

describe('postdirekt.addressfactory.suggested-address with edit mode', () => {
    let wrapper = shallowMount(Shopware.Component.build('postdirekt.addressfactory.suggested-address'), {
        propsData: {
            deliveryAddress: {
                city: 'city',
                street: ['street', 'street2 10'],
                firstName: 'firstName',
                lastName: 'lastName',
                zipcode: 'zipcode'
            },
            analysisResult: {
                city: 'city',
                street: 'street street2',
                streetNumber: '10',
                firstName: 'firstName',
                lastName: 'lastName',
                postalCode: 'postalCode',
            }
        },
        mocks: {
            $t: key => key,
            isEditingContext: () => true,
        }
    });
    it('does not display a box, address, or update button', () => {
        expect(wrapper.find('.address-parts').exists()).toBe(true);
        expect(wrapper.find('button').exists()).toBe(true);
    });

    it('can not update the address using a mixin as button is disabled', () => {
        let updateAddressSpy = jest.spyOn(wrapper.vm, 'updateAddress')
        wrapper.find('button').trigger('click');
        expect(updateAddressSpy).toHaveReturnedTimes(0)
    });
});

describe('postdirekt.addressfactory.suggested-address with different addresses', () => {
    let wrapper = shallowMount(Shopware.Component.build('postdirekt.addressfactory.suggested-address'), {
        propsData: {
            deliveryAddress: {
                city: 'city',
                street: ['street', 'street2 10'],
                firstName: 'firstName',
                lastName: 'lastName',
                zipcode: 'zipcode'
            },
            analysisResult: {
                city: 'city',
                street: 'street street2',
                streetNumber: '10',
                firstName: 'firstName',
                lastName: 'lastName',
                postalCode: 'postalCode',
            }
        },
        mocks: {
            $t: key => key,
            isEditingContext: () => false,
        }
    });
    it('displays an address and update button', async () => {
        await wrapper.setProps({
            deliveryAddress: {
                city: 'city',
                street: ['street', 'street2 10'],
                firstName: 'firstName',
                lastName: 'lastName',
                zipcode: 'zipcode'
            },
            analysisResult: {
                city: 'city',
                street: 'streetUpdated',
                streetNumber: '10',
                firstName: 'firstName',
                lastName: 'lastName',
                postalCode: 'postalCode',
            },
        });
        expect(wrapper.findAll('.address-parts span').length).toBe(5);
        expect(wrapper.get('button').text()).toContain('Autocorrect');
    });

    it('highlights the changed address parts', async () => {
        await wrapper.setProps({
            deliveryAddress: {
                city: 'city',
                street: ['street', 'street2 10'],
                firstName: 'firstName',
                lastName: 'lastName',
                zipcode: 'zipcode'
            },
            analysisResult: {
                city: 'city',
                street: 'streetUpdated',
                streetNumber: '10',
                firstName: 'firstName',
                lastName: 'lastName',
                postalCode: 'postalCode',
            },
        });
        expect(wrapper.find('.different').text()).toContain('streetUpdated');
    });
});
