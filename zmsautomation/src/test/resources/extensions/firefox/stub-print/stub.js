(function () {
  var stub =
    "window.print=function(){};" +
    "(function(orig){window.setTimeout=function(fn,delay){" +
    "if(delay===1500){return 0;}" +
    "return orig.apply(this,arguments);};})(window.setTimeout);";
  try {
    if (window.wrappedJSObject && typeof exportFunction === "function") {
      window.wrappedJSObject.print = exportFunction(function () {}, window);
      var orig = window.wrappedJSObject.setTimeout.bind(window.wrappedJSObject);
      window.wrappedJSObject.setTimeout = exportFunction(function (fn, delay) {
        if (delay === 1500) {
          return 0;
        }
        return orig(fn, delay);
      }, window);
      return;
    }
  } catch (e) {}
  var script = document.createElement("script");
  script.textContent = stub;
  (document.documentElement || document).appendChild(script);
  if (script.parentNode) {
    script.parentNode.removeChild(script);
  }
})();
