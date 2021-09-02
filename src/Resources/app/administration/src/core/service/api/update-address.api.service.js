const ApiService = Shopware.Classes.ApiService;

/**
 * Gateway for the API end point "update-address"
 * @class
 * @extends ApiService
 */
class NRLEJPostDirektUpdateAddressService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'postdirekt/addressfactory') {
        super(httpClient, loginService, apiEndpoint);
    }

    /**
     * Update delivery address of the given order with the result from the ADDRESSFACTORY response
     *
     * @param {string} orderId
     * @return {Promise<T>}
     */
    updateAddress(orderId) {
        const headers = this.getBasicHeaders();

        return this.httpClient.post(
            'postdirekt/addressfactory/update-address',
            {order_id: orderId},
            {headers},
        ).then(ApiService.handleResponse);
    }
}

export default NRLEJPostDirektUpdateAddressService;
