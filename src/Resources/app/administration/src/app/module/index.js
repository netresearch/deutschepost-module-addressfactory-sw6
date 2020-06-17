import './postdirekt-addressfactory/mixin/analysis-status-loader.mixin';
import './postdirekt-addressfactory/mixin/perform-analysis.mixin';
import './postdirekt-addressfactory/mixin/update-address.mixin';
import './postdirekt-addressfactory/components/infobox';
import './postdirekt-addressfactory/components/analysis-details';
import './postdirekt-addressfactory/components/analysis-status';
import './postdirekt-addressfactory/components/suggested-address';
import './postdirekt-addressfactory/components/api-test-button';

import deDe from '../snippet/de-DE';
import enGB from '../snippet/en-GB';

const { Module } = Shopware;

Module.register('postdirekt-addressfactory', {
    type: 'plugin',
    name: 'Postdirekt-Addressfactory',
    title: 'Deutsche Post Direkt Addressfactory',
    version: '1.0.0',
    snippets: {
        'de-DE': deDe,
        'en-GB': enGB
    }
});
