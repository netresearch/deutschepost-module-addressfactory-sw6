import './mixin/perform-analysis.mixin';
import './mixin/update-address.mixin';
import './components/analysis-details';
import './components/analysis-status';
import './components/suggested-address';

const {Module} = Shopware;

Module.register('postdirekt-addressfactory', {
    type: 'plugin',
    name: 'Postdirekt-Addressfactory',
    title: 'Deutsche Post Direkt Addressfactory',
    version: '1.0.0'
});
