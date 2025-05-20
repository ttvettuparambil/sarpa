class SightingFormPage {
  // Selectors
  elements = {
    speciesSelect: () => cy.get('select[name="species"]'),
    locationInput: () => cy.get('input[name="location"]'),
    descriptionTextarea: () => cy.get('textarea[name="description"]'),
    fileUploadInput: () => cy.get('input[type="file"]'),
    submitButton: () => cy.get('button[type="submit"]'),
    errorMessage: () => cy.get('.error-message'),
    successMessage: () => cy.get('.success-message')
  };

  // Actions
  visit() {
    cy.visit('/snake-sighting-form.php');
    return this;
  }

  selectSpecies(species) {
    this.elements.speciesSelect().select(species);
    return this;
  }

  typeLocation(location) {
    this.elements.locationInput().clear().type(location);
    return this;
  }

  typeDescription(description) {
    this.elements.descriptionTextarea().clear().type(description);
    return this;
  }

  uploadFile(fileContent, fileName, mimeType) {
    this.elements.fileUploadInput().attachFile({
      fileContent,
      fileName,
      mimeType
    });
    return this;
  }

  clickSubmit() {
    this.elements.submitButton().click();
    return this;
  }

  // Combined actions
  submitSighting(species, location, description, fileContent = null, fileName = null, mimeType = null) {
    this.selectSpecies(species);
    this.typeLocation(location);
    this.typeDescription(description);
    
    if (fileContent && fileName && mimeType) {
      this.uploadFile(fileContent, fileName, mimeType);
    }
    
    this.clickSubmit();
    return this;
  }

  // Assertions
  assertFormVisible() {
    this.elements.speciesSelect().should('exist');
    this.elements.locationInput().should('exist');
    this.elements.descriptionTextarea().should('exist');
    this.elements.fileUploadInput().should('exist');
    this.elements.submitButton().should('exist');
    return this;
  }

  assertErrorMessage(message) {
    if (message) {
      this.elements.errorMessage().should('be.visible').and('contain', message);
    } else {
      this.elements.errorMessage().should('be.visible');
    }
    return this;
  }

  assertSuccessMessage() {
    this.elements.successMessage().should('be.visible');
    return this;
  }
}

export default new SightingFormPage();
