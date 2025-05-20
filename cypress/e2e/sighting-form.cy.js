import LoginPage from '../support/page-objects/LoginPage';
import SightingFormPage from '../support/page-objects/SightingFormPage';

describe('Snake Sighting Form', () => {
  beforeEach(() => {
    // In a real test, you would handle the login and OTP verification
    // This is a simplified version that assumes direct access to the form
    cy.visit('/snake-sighting-form.php');
    
    // Alternatively, you can use a custom command to bypass login
    // cy.loginByApi('ttvettuparambil@gmail.com', '87654321');
  });

  it('should display all required form fields', () => {
    SightingFormPage.assertFormVisible();
  });

  it('should validate form inputs', () => {
    SightingFormPage.clickSubmit();
    SightingFormPage.assertErrorMessage('Please select a species');
    SightingFormPage.assertErrorMessage('Location is required');
  });

  it('should submit a valid sighting', () => {
    // Using the combined action method
    SightingFormPage
      .selectSpecies('cobra')
      .typeLocation('Kochi')
      .typeDescription('Found a snake in the garden');
    
    // Mock file upload
    cy.fixture('test-image.jpg').then(fileContent => {
      SightingFormPage.uploadFile(
        fileContent,
        'test-image.jpg',
        'image/jpeg'
      );
    });

    SightingFormPage.clickSubmit();
    cy.url().should('include', '/sighting-summary.php');
    SightingFormPage.assertSuccessMessage();
  });
  
  it('should validate image upload', () => {
    SightingFormPage.uploadFile(
      'invalid-content',
      'invalid.txt',
      'text/plain'
    );
    SightingFormPage.assertErrorMessage('Please upload a valid image');
  });
});
