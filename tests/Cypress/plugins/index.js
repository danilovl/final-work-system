/**
 * @type {Cypress.PluginConfig}
 */
module.exports = (on, config) => {
    on("before:browser:launch", (browser = {}, launchOptions) => {
        if (browser.name === "chrome") {
            launchOptions.args.push("--disable-dev-shm-usage");
        }

        return launchOptions;
    })
}
