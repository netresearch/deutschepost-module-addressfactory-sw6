import template from './sw-order-user-card.html.twig';

Shopware.Component.override('sw-order-user-card', {
    template,
    methods: {
        bubbleOrderChange: function (state){
            /**
             * 1st parent is sw-card-view
             * 2nd parent is sw-order-detail-base
             */
            this.$parent.$parent.onQuickOrderStatusChange(state);
        }
    }
});
