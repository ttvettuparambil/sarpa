import LoginPage from '../support/page-objects/LoginPage';
import OtpVerifyPage from '../support/page-objects/OtpVerifyPage';
import DashboardPage from '../support/page-objects/DashboardPage';

describe('Dashboard Page', () => {
  beforeEach(() => {
    // Setup: Login and navigate to dashboard
    // In a real test, you would handle the OTP verification
    // This is a simplified version that assumes direct access to dashboard
    cy.visit('/user-dashboard.php');
    
    // Alternatively, you can use a custom command to bypass login
    // cy.loginByApi('ttvettuparambil@gmail.com', '87654321');
  });

  it('should display user profile information', () => {
    DashboardPage.assertProfileVisible();
  });

  it('should display recent sightings', () => {
    DashboardPage.assertSightingsVisible();
    
    // Check first sighting item details
    DashboardPage.elements.sightingItems().first().within(() => {
      cy.get('.sighting-date').should('exist');
      cy.get('.sighting-location').should('exist');
    });
  });

  it('should allow user to filter sightings', () => {
    DashboardPage.assertFilterFormVisible();
    DashboardPage.filterSightings('cobra', 'kerala');
    DashboardPage.assertSightingsVisible();
  });
  
  it('should display notifications', () => {
    DashboardPage.clickNotificationsButton();
    DashboardPage.assertNotificationsVisible();
  });
  
  it('should allow user to logout', () => {
    DashboardPage.clickLogout();
    cy.url().should('include', '/login.php');
  });
});
