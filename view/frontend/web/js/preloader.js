(function () {
    var SCRIPTS_TO_PUSH = `[{"tag":"script","type":"module","src":"https://fastsimon-grid.akamaized.net/preload.js"}]`;

    const run = () => {

        function preloadItem(item) {
            const element = document.createElement('script');
            element.src = item.src;
            element.async = true;
            element.className = "fast-simon-link-preload";
            document.head.prepend(element);
        }
        JSON.parse(SCRIPTS_TO_PUSH).forEach(item => preloadItem(item));
    }

    run();
})()