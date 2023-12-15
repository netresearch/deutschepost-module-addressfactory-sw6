const ApiService = Shopware.Classes.ApiService;

/**
 * Gateway for the API end point "test-api-access"
 * @class
 * @extends ApiService
 */
class NRLEJPostDirektTestCredentialsService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'postdirekt/addressfactory') {
        super(httpClient, loginService, apiEndpoint);
    }

    /**
     * Check if authorization against the ADDRESSFACTORY API is working with given credentials
     *
     * @param {string} username
     * @param {string} password
     * @param {string} configurationName
     * @param {string} clientId
     * @return {Promise<T>}
     */
    testCredentials(username, password, configurationName, clientId) {
        const headers = this.getBasicHeaders();

        return this.httpClient.post(
            `_action/${this.getApiBasePath()}/test-api-access`,
            {username, password, configurationName, clientId},
            {headers},
        );
    }
}

export default NRLEJPostDirektTestCredentialsService;
