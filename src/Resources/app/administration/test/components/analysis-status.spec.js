import {shallowMount} from '@vue/test-utils';
import AnalysisStatus from '../../src/app/module/postdirekt-addressfactory/analysis-status'
import '../../src/app/module/postdirekt-addressfactory/components/analysis-status/index'

describe('postdirekt.addressfactory.analysis-status', () => {
    let wrapperOptionsTemplate = {
        propsData: {status: AnalysisStatus.UNDELIVERABLE},
        mocks: {
            $t: key => key
        },
        stubs: {
            'sw-icon': {template: '<div class="icon"></div>'}
        },
    };

    it('prints a package icon', () => {
        let wrapper = shallowMount(
            Shopware.Component.build('postdirekt.addressfactory.analysis-status'),
            wrapperOptionsTemplate
        );
        expect(wrapper.find('.icon').attributes().name).toBe('default-package-closed');
    });

    it('prints a grey indicator on not analysed', () => {
        wrapperOptionsTemplate.propsData.status = AnalysisStatus.NOT_ANALYSED;
        let wrapper = shallowMount(
            Shopware.Component.build('postdirekt.addressfactory.analysis-status'),
            wrapperOptionsTemplate
        );
        expect(wrapper.find('.icon').attributes().color).toBe('#dddddd');
        expect(wrapper.find('.score').text()).toContain('notAnalysed');
    });

    it('prints a red indicator on undeliverability', () => {
        wrapperOptionsTemplate.propsData.status = AnalysisStatus.UNDELIVERABLE;
        let wrapper = shallowMount(
            Shopware.Component.build('postdirekt.addressfactory.analysis-status'),
            wrapperOptionsTemplate
        );
        expect(wrapper.find('.icon').attributes().color).toBe('#ff0000');
        expect(wrapper.find('.score').text()).toContain('undeliverable');
    });

    it('prints a red indicator on required correction', () => {
        wrapperOptionsTemplate.propsData.status = AnalysisStatus.CORRECTION_REQUIRED;
        let wrapper = shallowMount(
            Shopware.Component.build('postdirekt.addressfactory.analysis-status'),
            wrapperOptionsTemplate
        );
        expect(wrapper.find('.icon').attributes().color).toBe('#ff0000');
        expect(wrapper.find('.score').text()).toContain('correctionRecommended');
    });

    it('prints a yellow indicator on possible deliverability', () => {
        wrapperOptionsTemplate.propsData.status = AnalysisStatus.POSSIBLY_DELIVERABLE;
        let wrapper = shallowMount(
            Shopware.Component.build('postdirekt.addressfactory.analysis-status'),
            wrapperOptionsTemplate
        );
        expect(wrapper.find('.icon').attributes().color).toBe('#ffcc01');
        expect(wrapper.find('.score').text()).toContain('possibleDeliverable');
    });
});
