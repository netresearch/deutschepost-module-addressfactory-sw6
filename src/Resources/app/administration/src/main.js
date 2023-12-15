import './init/api-service.init';

import './component/postdirekt-addressfactory/infobox';
import './component/postdirekt-addressfactory/api-test-button';

import './module/postdirekt-addressfactory';

import './extension/sw-order-list';
import './extension/sw-order-detail-details';
import './extension/sw-order-detail';

import localeDE from './snippet/de-DE.json';
import localeEN from './snippet/en-GB.json';

Shopware.Locale.extend('de-DE', localeDE);
Shopware.Locale.extend('en-GB', localeEN);
