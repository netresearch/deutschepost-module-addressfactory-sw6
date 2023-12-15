import template from './api-test-button.html.twig';

const {Mixin} = Shopware;

Shopware.Component.register('postdirekt-addressfactory-api-test-button', {
    template: template,
    inject: [
        'postdirektTestCredentialService'
    ],
    mixins: [
        Mixin.getByName('notification'),
    ],
    data() {
        return {
            isLoading: false,
        };
    },
    created() {
        console.log('postdirekt-addressfactory-api-test-button')
    },
    methods: {
        onButtonClick() {
            this.isLoading = true;
            const username = document.querySelector("[id='NRLEJPostDirektAddressfactory.config.apiUser']").value;
            const password = document.querySelector("[id='NRLEJPostDirektAddressfactory.config.apiPassword']").value;
            const configurationName = document.querySelector("[id='NRLEJPostDirektAddressfactory.config.configurationName']").value;
            const clientId = document.querySelector("[id='NRLEJPostDirektAddressfactory.config.clientId']").value;

            return this.postdirektTestCredentialService.testCredentials(username, password, configurationName, clientId)
                .then((response) => {
                    if (response.status === 200) {
                        this.createNotificationSuccess({
                            title: this.$t('postdirekt-addressfactory.apiTest.successTitle'),
                            message: this.$t('postdirekt-addressfactory.apiTest.successMessage'),
                        });
                    } else {
                        throw new Error();
                    }
                })
                .catch(() => {
                    this.createNotificationError({
                        title: this.$t('postdirekt-addressfactory.apiTest.errorTitle'),
                        message: this.$t("postdirekt-addressfactory.apiTest.errorMessage"),
                    });
                })
                .finally(() => {
                    this.isLoading = false;
                });
        }
    }
});
