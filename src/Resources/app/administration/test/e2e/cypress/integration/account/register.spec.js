/// <reference types="Cypress" />
describe('PostdirektAddressfactoryCypressTest: Test registration address', () => {
    afterEach(function () {
        if (this.currentTest.state === 'failed') {
            cy.screenshot(this.currentTest.fullTitle(), {
                capture: 'fullPage'
            });
        }
    });
    it('configure addressfactory plugin', () => {
        cy.login('admin')
            .then(() => {
                cy.setLocaleToEnGb();
            });
        cy.visit('/admin#/sw/plugin/settings/NRLEJPostDirektAddressfactory');
        cy.server();
        cy.route({
            url: '/api/v1/_action/system-config/batch',
            method: 'post'
        }).as('saveData');
        cy.get('.sw-card.sw-system-config__card--0').should('be.visible');
        cy.get('.sw-card.sw-system-config__card--0 .sw-card__title').contains('Deutsche Post Direkt');
        cy.get('.sw-card.sw-system-config__card--1').should('be.visible');
        cy.get('.sw-card.sw-system-config__card--1 .sw-card__title').contains('Addressfactory Settings');
        cy.get('input[name="NRLEJPostDirektAddressfactory.config.active"]').should('be.visible').check();
        cy.get('input[name="NRLEJPostDirektAddressfactory.config.logging"]').should('be.visible');
        cy.get('input[name="NRLEJPostDirektAddressfactory.config.apiUser"]').should('be.visible')
            .clear()
            .type(Cypress.env('ADDRESSFACTORY_USER'));
        cy.get('input[name="NRLEJPostDirektAddressfactory.config.apiPassword"]').should('be.visible')
            .clear()
            .type(Cypress.env('ADDRESSFACTORY_PASSWORD'));
        cy.get('.sw-plugin-config__save-action').click();
        cy.wait('@saveData').then(() => {
            cy.get('.sw-notifications__notification--0 .sw-alert__message').should('be.visible')
                .contains('Configuration has been saved.');
        });
        cy.openUserActionMenu();
        cy.get('.sw-admin-menu__logout-action').click();
    });
});
