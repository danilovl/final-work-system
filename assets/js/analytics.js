const codeElement = document.querySelector('meta[name="google-analytics-code"]');

if (codeElement !== null) {
    window.dataLayer = window.dataLayer || [];

    window.gtag = function () {
        window.dataLayer.push(arguments);
    };

    window.gtag('js', new Date());
    window.gtag('config', codeElement.getAttribute('content'));
}
