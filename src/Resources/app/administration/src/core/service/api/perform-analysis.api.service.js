const ApiService = Shopware.Classes.ApiService;

/**
 * Gateway for the API end point "perform-analysis"
 * @class
 * @extends ApiService
 */
class NRLEJPostDirektPerformAnalysisService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'postdirekt/addressfactory') {
        super(httpClient, loginService, apiEndpoint);
    }

    /**
     * Perform analysis for given order and give back update results
     *
     * @param {string} orderId
     * @return {Promise<T>}
     */
    performAnalysis(orderId) {
        const headers = this.getBasicHeaders();

        return this.httpClient.post(
            'postdirekt/addressfactory/perform-analysis',
            {order_id: orderId},
            {headers},
        ).then(ApiService.handleResponse);
    }
}

export default NRLEJPostDirektPerformAnalysisService;
