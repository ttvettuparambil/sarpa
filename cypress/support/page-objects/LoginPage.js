class LoginPage {
  // Selectors
  elements = {
    title: () => cy.get('h2').contains('Sign in to your account'),
    emailLabel: () => cy.get('label').contains('Email address'),
    emailInput: () => cy.get('input#email[name="email"][type="email"]'),
    passwordLabel: () => cy.get('label').contains('Password'),
    passwordInput: () => cy.get('input#password[name="password"][type="password"]'),
    rememberMeCheckbox: () => cy.get('input#remember[name="remember"][type="checkbox"]'),
    rememberMeLabel: () => cy.get('label').contains('Remember me'),
    forgotPasswordLink: () => cy.get('a').contains('Forgot your password?'),
    signInButton: () => cy.get('button[type="submit"]').contains('Sign in'),
    createAccountLink: () => cy.get('a[href="register.php"]').contains('create a new account'),
    errorAlert: () => cy.get('.alert-error')
  };

  // Actions
  visit() {
    cy.visit('/login.php');
    return this;
  }

  typeEmail(email) {
    this.elements.emailInput().clear().type(email);
    return this;
  }

  typePassword(password) {
    this.elements.passwordInput().clear().type(password);
    return this;
  }

  checkRememberMe() {
    this.elements.rememberMeCheckbox().check();
    return this;
  }

  uncheckRememberMe() {
    this.elements.rememberMeCheckbox().uncheck();
    return this;
  }

  clickSignIn() {
    this.elements.signInButton().click();
    return this;
  }

  clickForgotPassword() {
    this.elements.forgotPasswordLink().click();
  }

  clickCreateAccount() {
    this.elements.createAccountLink().click();
  }

  // Combined actions
  login(email, password, rememberMe = false) {
    this.typeEmail(email);
    this.typePassword(password);
    
    if (rememberMe) {
      this.checkRememberMe();
    }
    
    this.clickSignIn();
    return this;
  }

  // Assertions
  assertFormVisible() {
    this.elements.title().should('be.visible');
    this.elements.emailLabel().should('be.visible');
    this.elements.emailInput().should('exist');
    this.elements.passwordLabel().should('be.visible');
    this.elements.passwordInput().should('exist');
    this.elements.rememberMeCheckbox().should('exist');
    this.elements.rememberMeLabel().should('be.visible');
    this.elements.forgotPasswordLink().should('be.visible');
    this.elements.signInButton().should('be.visible');
    this.elements.createAccountLink().should('be.visible');
    return this;
  }

  assertErrorMessage(message) {
    if (message) {
      this.elements.errorAlert().should('be.visible').and('contain', message);
    } else {
      this.elements.errorAlert().should('be.visible');
    }
    return this;
  }

  assertInvalidField() {
    this.elements.emailInput().should('have.attr', 'aria-invalid', 'true');
    return this;
  }
}

export default new LoginPage();
