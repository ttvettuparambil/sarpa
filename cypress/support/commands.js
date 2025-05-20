/// <reference types="cypress" />

Cypress.Commands.add('login', (email, password) => {
  cy.visit('/login.php')
  cy.get('input[name="email"]').type(email)
  cy.get('input[name="password"]').type(password)
  cy.get('button[type="submit"]').click()
})

Cypress.Commands.add('logout', () => {
  cy.visit('/logout.php')
})

// Custom command to verify user is logged in
Cypress.Commands.add('verifyLoggedIn', () => {
  cy.get('.user-profile').should('be.visible')
})

// Custom command to verify user is logged out
Cypress.Commands.add('verifyLoggedOut', () => {
  cy.get('.login-btn').should('be.visible')
})
