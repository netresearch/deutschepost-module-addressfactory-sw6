import template from './infobox.html.twig';
import './infobox.scss';

Shopware.Component.register('postdirekt-addressfactory-infobox', {
    template: template,
    created() {
        console.log('postdirekt-addressfactory-infobox')
    },
    props: {
        logo: {
            type: String,
            required: true,
        },
        version: {
            type: String,
            required: true,
        },
        headerColor: {
            type: String,
            required: true,
        },
        bodyColor: {
            type: String,
            required: true,
        },
    },
});
