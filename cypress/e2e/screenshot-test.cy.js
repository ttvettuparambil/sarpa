describe('Screenshot Test', () => {
  it('should take a screenshot when failing', () => {
    // Visit the base URL
    cy.visit('/');
    
    // Take a screenshot manually (this should work regardless of test result)
    // cy.screenshot('manual-screenshot');
    
    // This assertion will fail, triggering an automatic screenshot
    cy.get('element-that-does-not-exist').should('be.visible');
  });
});
