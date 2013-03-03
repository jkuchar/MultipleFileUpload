
var MFUFallbackController;
(function(){
	MFUFallbackController = function(rootEl, fallbacks) {

		this.fallbackLinkText = "Nejdou vám soubory nahrát? Klikněte zde."; 

		this.fallbacks = fallbacks;
		this.rootEl = rootEl;

		this.each = function(fn, scope) {
			for(var i=0, l=this.fallbacks.length; i<l; i++) {
				var fallback = this.fallbacks[i];
				fn.call(scope||this,fallback, this.fallbacks);
			}
		}

		this.getLastAlternativeUI = function() {
			return this.fallbacks[this.fallbacks.length-1];
		}

		this.hideUI = function(ui) {
			document.getElementById(ui.id).style.display = "none";
		}

		this.showUI = function(ui) {
			document.getElementById(ui.id).style.display = "block";
		}
		
		this.swithUI = function(ui) {
			var currentUI = this.activeUI||this.fallbacks[0];
			if(eval(currentUI.destruct,this) == true &&eval(ui.init,this)==true) {
				this.hideUI(currentUI);
				this.activeUI = ui;
				this.showUI(ui);

				this.onUISwitch(currentUI,ui);
				
			}
		}

		this.isFallbackAvailable = function() {
			var index = this.fallbacks.indexOf(this.activeUI);
			if(index<0) return false;
			var newUI = this.fallbacks[index+1];
			if(typeof newUI == "undefined") return false;
			return newUI;
		}

		this.fallback = function() {
			var ui = this.isFallbackAvailable();
			if(ui===false) return;
			this.swithUI(ui);
		}

		// Fallback link
		var fallbackLink = document.createElement('a');
		fallbackLink.href = "#";
		fallbackLink.onclick = function() {
			rootEl.fallbackCntrl.fallback();
			return false;
		};
		fallbackLink.innerHTML = this.fallbackLinkText;
		this.rootEl.appendChild(fallbackLink);
		this.fallbackLink = fallbackLink;

		this.onUISwitch = function(from,to) {
			if(!this.isFallbackAvailable()) {
				fallbackLink.style.display = "none";
			}
		}


		// Init
		this.activeUI = this.getLastAlternativeUI();
		this.swithUI(this.fallbacks[0]);
		this.rootEl.fallbackCntrl = this;
	}
})();



// Array.indexOf
if (!Array.prototype.indexOf)
{
  Array.prototype.indexOf = function(elt /*, from*/)
  {
    var len = this.length;

    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++)
    {
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}
