(function(funcName, baseObj) {
    funcName = funcName || "docReady";
    baseObj = baseObj || window;
    var readyList = [];
    var readyFired = false;
    var readyEventHandlersInstalled = false;

    function ready() {
        if (!readyFired) {
            readyFired = true;
            for (var i = 0; i < readyList.length; i++) {
                readyList[i].fn.call(window, readyList[i].ctx);
            }
            readyList = [];
        }
    }

    function readyStateChange() {
        if ( document.readyState === "complete" ) {
            ready();
        }
    }

    baseObj[funcName] = function(callback, context) {
        if (typeof callback !== "function") {
            throw new TypeError("callback for docReady(fn) must be a function");
        }
        if (readyFired) {
            setTimeout(function() {callback(context);}, 1);
            return;
        } else {
            readyList.push({fn: callback, ctx: context});
        }
        if (document.readyState === "complete") {
            setTimeout(ready, 1);
        } else if (!readyEventHandlersInstalled) {
            if (document.addEventListener) {
                document.addEventListener("DOMContentLoaded", ready, false);
                window.addEventListener("load", ready, false);
            } else {
                document.attachEvent("onreadystatechange", readyStateChange);
                window.attachEvent("onload", ready);
            }
            readyEventHandlersInstalled = true;
        }
    }
})("docReady", window);

docReady(function() {
    document.body.appendChild('<div class=\"pswp\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\"><div class=\"pswp__bg\"><\/div><div class=\"pswp__scroll-wrap\"><div class=\"pswp__container\"><div class=\"pswp__item\"><\/div><div class=\"pswp__item\"><\/div><div class=\"pswp__item\"><\/div><\/div><div class=\"pswp__ui pswp__ui--hidden\"><div class=\"pswp__top-bar\"><div class=\"pswp__counter\"><\/div><button class=\"pswp__button pswp__button--close\" title=\"Close (Esc)\"><\/button><button class=\"pswp__button pswp__button--share\" title=\"Share\"><\/button><button class=\"pswp__button pswp__button--fs\" title=\"Toggle fullscreen\"><\/button><button class=\"pswp__button pswp__button--zoom\" title=\"Zoom in\/out\"><\/button><div class=\"pswp__preloader\"><div class=\"pswp__preloader__icn\"><div class=\"pswp__preloader__cut\"><div class=\"pswp__preloader__donut\"><\/div><\/div><\/div><\/div><\/div><div class=\"pswp__share-modal pswp__share-modal--hidden pswp__single-tap\"><div class=\"pswp__share-tooltip\"><\/div><\/div><button class=\"pswp__button pswp__button--arrow--left\" title=\"Previous (arrow left)\"> <\/button><button class=\"pswp__button pswp__button--arrow--right\" title=\"Next (arrow right)\"> <\/button><div class=\"pswp__caption\"><div class=\"pswp__caption__center\"><\/div><\/div><\/div><\/div><\/div>');
});
