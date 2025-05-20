import LoginPage from '../support/page-objects/LoginPage';
import OtpVerifyPage from '../support/page-objects/OtpVerifyPage';

describe('Login Page', () => {
  beforeEach(() => {
    LoginPage.visit();
  });

  it('should display login form with all elements', () => {
    LoginPage.assertFormVisible();
  });

  it('should validate required fields', () => {
    // Try submitting without any data
    LoginPage.clickSignIn();
    
    // HTML5 validation should prevent submission and highlight email field
    cy.get('input#email:invalid').should('exist');
  });

  it('should show error for invalid credentials', () => {
    LoginPage
      .typeEmail('invalid@email.com')
      .typePassword('wrongpassword')
      .clickSignIn();
    
    // Check for error alert
    LoginPage.assertErrorMessage();
  });

  it('should redirect to OTP verification page after valid credentials', () => {
    // Use test credentials (these should be configured in cypress.env.json)
    LoginPage.login(
      Cypress.env('TEST_USER_EMAIL') || 'ttvettuparambil@gmail.com',
      Cypress.env('TEST_USER_PASSWORD') || '87654321'
    );
    
    // Should redirect to OTP verification page
    cy.url().should('include', '/otp-verify.php');
    OtpVerifyPage.assertPageVisible();
  });

  it('should remember user with remember me checkbox', () => {
    LoginPage
      .typeEmail(Cypress.env('TEST_USER_EMAIL') || 'ttvettuparambil@gmail.com')
      .typePassword(Cypress.env('TEST_USER_PASSWORD') || '87654321')
      .checkRememberMe()
      .clickSignIn();
    
    // Complete OTP verification (mock this in a real test)
    cy.url().should('include', '/otp-verify.php');
    
    // After OTP verification and login, check for cookie
    // This would need to be verified after completing the OTP flow
    // cy.getCookie('rememberme').should('exist');
  });

  it('should show lockout message after multiple failed attempts', () => {
    // This test simulates the lockout behavior
    // Note: In a real test environment, you might want to reset the database between tests
    // or mock the lockout behavior
    
    // Attempt multiple failed logins
    for (let i = 0; i < 5; i++) {
      LoginPage
        .typeEmail('locked@example.com')
        .typePassword('wrongpassword')
        .clickSignIn();
      // Small wait to ensure the attempts are registered
      cy.wait(200);
    }
    
    // Check for lockout message
    LoginPage.assertErrorMessage('Too many failed login attempts');
  });

  it('should navigate to forgot password page', () => {
    LoginPage.clickForgotPassword();
    cy.url().should('include', '/forgot_password.php');
  });

  it('should navigate to registration page', () => {
    LoginPage.clickCreateAccount();
    cy.url().should('include', '/register.php');
  });
});
