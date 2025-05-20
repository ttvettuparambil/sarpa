class DashboardPage {
  // Selectors
  elements = {
    userProfile: () => cy.get('.user-profile'),
    userName: () => cy.get('.user-name'),
    userEmail: () => cy.get('.user-email'),
    sightingsList: () => cy.get('.sightings-list'),
    sightingItems: () => cy.get('.sighting-item'),
    filterForm: () => cy.get('.filter-form'),
    speciesSelect: () => cy.get('select[name="species"]'),
    locationSelect: () => cy.get('select[name="location"]'),
    applyFiltersButton: () => cy.get('.apply-filters'),
    notificationsButton: () => cy.get('.notifications-btn'),
    notificationsList: () => cy.get('.notifications-list'),
    notificationItems: () => cy.get('.notification-item'),
    logoutButton: () => cy.get('.logout-btn')
  };

  // Actions
  visit() {
    cy.visit('/user-dashboard.php');
    return this;
  }

  clickNotificationsButton() {
    this.elements.notificationsButton().click();
    return this;
  }

  selectSpecies(species) {
    this.elements.speciesSelect().select(species);
    return this;
  }

  selectLocation(location) {
    this.elements.locationSelect().select(location);
    return this;
  }

  applyFilters() {
    this.elements.applyFiltersButton().click();
    return this;
  }

  clickLogout() {
    this.elements.logoutButton().click();
    return this;
  }

  // Combined actions
  filterSightings(species, location) {
    this.selectSpecies(species);
    this.selectLocation(location);
    this.applyFilters();
    return this;
  }

  // Assertions
  assertProfileVisible() {
    this.elements.userProfile().should('be.visible');
    this.elements.userName().should('exist');
    this.elements.userEmail().should('exist');
    return this;
  }

  assertSightingsVisible() {
    this.elements.sightingsList().should('be.visible');
    this.elements.sightingItems().should('have.length.at.least', 1);
    return this;
  }

  assertFilterFormVisible() {
    this.elements.filterForm().should('exist');
    this.elements.speciesSelect().should('exist');
    this.elements.locationSelect().should('exist');
    this.elements.applyFiltersButton().should('exist');
    return this;
  }

  assertNotificationsVisible() {
    this.elements.notificationsList().should('be.visible');
    this.elements.notificationItems().should('have.length.at.least', 1);
    return this;
  }
}

export default new DashboardPage();
