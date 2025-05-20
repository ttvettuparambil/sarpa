const { defineConfig } = require('cypress')
const path = require('path')

module.exports = defineConfig({
  e2e: {
    baseUrl: 'http://sarpa.test', //
    //  Update this to your local development URL
    video: true,
    watchForFileChanges: true,
    setupNodeEvents(on, config) {
      // Configure screenshot behavior in node events
      on('after:screenshot', (details) => {
        console.log('Screenshot captured:', details.path)
        return details
      })
      
      on('task', {
        log(message) {
          console.log(message)
          return null
        }
      })
      
      // Force screenshots to be enabled
      config.screenshotOnRunFailure = true
      return config
    },
    viewportWidth: 1280,
    viewportHeight: 720,
    defaultCommandTimeout: 5000,
    pageLoadTimeout: 10000,
    requestTimeout: 10000,
    responseTimeout: 10000,
    retries: {
      runMode: 2,
      openMode: 0
    },
    // Screenshot settings
    screenshotOnRunFailure: true,
    screenshotsFolder: 'cypress/screenshots',
    trashAssetsBeforeRuns: false,
    // Video settings
    video: true,
    videosFolder: 'cypress/videos',
    videoCompression: 32
  }
})
