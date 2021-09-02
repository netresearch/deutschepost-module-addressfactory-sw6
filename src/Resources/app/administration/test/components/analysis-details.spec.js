import {shallowMount, createLocalVue} from '@vue/test-utils';
import flushPromises from 'flush-promises'
import "babel-polyfill"
import './stub/cancel-order.mixin'
import './stub/perform-analysis.mixin'
import '../../src/app/module/postdirekt-addressfactory/components/analysis-details/index'

// Mock global translation function
Shopware.Application.view = {root: {$t: (string) => string}};

describe('postdirekt.addressfactory.analysis-details', () => {
    let localVue = createLocalVue()
    localVue.filter('asset', val => val)

    let mockResultRepository = {
        searchResult: [],
        search: function () {
            return Promise.resolve(this.searchResult);
        }
    }
    let mockScoreRepository = {
        searchResult: [],
        search: function () {
            return Promise.resolve(this.searchResult);
        }
    }

    let subject = Shopware.Component.build('postdirekt.addressfactory.analysis-details');

    let mountOptions = {
        localVue,
        propsData: {
            order: {
                id: 'testOrderId',
                deliveries: [
                    {
                        shippingOrderAddress: {
                            id: 'testShippingAddressId',
                            city: 'city',
                            street: ['street', 'street2 10'],
                            firstName: 'firstName',
                            lastName: 'lastName',
                            zipcode: 'zipcode',
                            country: {
                                iso: 'DE',
                            },
                        }
                    }
                ],
                stateMachineState: {technicalName: 'someState'}
            },
            sync: false
        },
        mocks: {
            $t: key => key,
        },
        stubs: {
            'sw-loader': {template: '<div></div>'},
            'sw-icon': {template: '<div></div>'},
            'postdirekt.addressfactory.suggested-address': {template: '<div class="suggested-address-box"></div>'},
        },
        provide: {
            repositoryFactory: {
                create: (name) => {
                    if (name === 'postdirekt_addressfactory_analysis_result') {
                        return mockResultRepository;
                    } else {
                        return mockScoreRepository;
                    }
                }
            }
        }
    };

    it('displays an analyse button by default', () => {
        let wrapper = shallowMount(subject, mountOptions);
        expect(wrapper.find('button').text()).toContain('performButtonLabel');
    });

    it('can perform an analysis', () => {
        let wrapper = shallowMount(subject, mountOptions);
        wrapper.find('button.sw-button--primary').trigger('click');
        wrapper.vm.$nextTick(() => {
            expect(wrapper.find('.deliverability-score').text()).toContain('deliverabilityCodes.');
            expect(wrapper.find('.detected-issues').exists()).toBe(true);
            expect(wrapper.find('.suggested-address-box').exists()).toBe(true);
        })
    });

    it('can load existing analysis from repository', async() => {
        mockResultRepository.searchResult = [{
            city: 'city',
            street: 'street street2',
            streetNumber: '10',
            firstName: 'firstName',
            lastName: 'lastName',
            postalCode: 'postalCode',
            statusCodes: '123,456',
        }];
        mockScoreRepository.searchResult = [{
            name: 'not_analysed',
        }]

        let wrapper = shallowMount(subject, mountOptions);

        await flushPromises();

        expect(wrapper.find('.deliverability-score').text()).toContain('deliverabilityCodes.');
        expect(wrapper.find('.detected-issues').exists()).toBe(true);
        expect(wrapper.find('.suggested-address-box').exists()).toBe(true);
    });
});
