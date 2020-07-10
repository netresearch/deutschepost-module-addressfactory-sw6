import NRLEJPostDirektPerformAnalysisService from '../core/service/api/perform-analysis.api.service'
import NRLEJPostDirektUpdateAddressService from "../core/service/api/update-address.api.service";
import NRLEJPostDirektTestCredentialsService from "../core/service/api/test-api-access.api.service";

const {Application} = Shopware;

Application.addServiceProvider('postdirektPerformAnalysisService', (container) => {
    const initContainer = Application.getContainer('init');

    return new NRLEJPostDirektPerformAnalysisService(initContainer.httpClient, container.loginService);
});

Application.addServiceProvider('postdirektUpdateAddressService', (container) => {
    const initContainer = Application.getContainer('init');

    return new NRLEJPostDirektUpdateAddressService(initContainer.httpClient, container.loginService);
});

Application.addServiceProvider('postdirektTestCredentialService', (container) => {
    const initContainer = Application.getContainer('init');

    return new NRLEJPostDirektTestCredentialsService(initContainer.httpClient, container.loginService);
});
