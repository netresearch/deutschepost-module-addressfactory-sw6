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
    methods: {
        getSystemConfig() {
            let p = this.$parent;
            while (p) {
                if (p.$options?.name === 'sw-system-config') {
                    return p;
                }
                p = p.$parent;
            }
            return null;
        },
        getConfigValue(configData, key) {
            return (configData[null] && configData[null][key]) || configData[key];
        },
        onButtonClick() {
            const sysConfig = this.getSystemConfig();
            if (!sysConfig) {
                console.error('Failed retrieving config field values. Make sure the button\'s nested inside a sw-system-config component.');
                return;
            }

            this.isLoading = true;

            const username = this.getConfigValue(sysConfig.actualConfigData, 'NRLEJPostDirektAddressfactory.config.apiUser');
            const password = this.getConfigValue(sysConfig.actualConfigData, 'NRLEJPostDirektAddressfactory.config.apiPassword');
            const configurationName = this.getConfigValue(sysConfig.actualConfigData, 'NRLEJPostDirektAddressfactory.config.configurationName');
            const clientId = this.getConfigValue(sysConfig.actualConfigData, 'NRLEJPostDirektAddressfactory.config.clientId');

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
