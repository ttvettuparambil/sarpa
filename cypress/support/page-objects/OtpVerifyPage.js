class OtpVerifyPage {
  // Selectors
  elements = {
    title: () => cy.get('h2').contains('OTP Verification'),
    otpInput: () => cy.get('input[name="otp"]'),
    verifyButton: () => cy.get('button[type="submit"]').contains('Verify'),
    resendLink: () => cy.get('a').contains('Resend OTP'),
    errorAlert: () => cy.get('.alert-error')
  };

  // Actions
  visit() {
    cy.visit('/otp-verify.php');
    return this;
  }

  typeOtp(otp) {
    this.elements.otpInput().clear().type(otp);
    return this;
  }

  clickVerify() {
    this.elements.verifyButton().click();
    return this;
  }

  clickResendOtp() {
    this.elements.resendLink().click();
    return this;
  }

  // Combined actions
  verifyOtp(otp) {
    this.typeOtp(otp);
    this.clickVerify();
    return this;
  }

  // Assertions
  assertPageVisible() {
    this.elements.title().should('be.visible');
    this.elements.otpInput().should('exist');
    this.elements.verifyButton().should('be.visible');
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
}

export default new OtpVerifyPage();
