(function() {
	jQuery("#"+this.activeUI.id).parents("form").disableAjaxSubmit(); // @see nette-ajax-form.js
	return true;
}).call(this);