/**
 * AJAX Nette Framwork plugin for jQuery
 *
 * @copyright   Copyright (c) 2009 Jan Marek, 2011 Matus Matula
 * @license     MIT
 * @link        http://nettephp.com/cs/extras/jquery-ajax
 * @version     0.2
 */
jQuery.extend({
	Nette: {
		AJAX_PROCESSING_TEXT: 'Spracúvam..',
//		AJAX_PROCESSING_TEXT: 'Processing..',

		AJAX_SUCCESS_EVENT: 'onSuccess',
		AJAX_ERROR_EVENT: 'onError',

		// array of html IDs which shouldn't be animated while updating content
		dummyUpdateSnippets: [],

		// array of callbacks that are called after event fired
		callbacks: [],


		// constructor
		init: function()
		{
			this.callbacks[this.AJAX_SUCCESS_EVENT] = [];
			this.callbacks[this.AJAX_ERROR_EVENT] = [];
		},

		// adds callback fn to stack that are called when event fired
		addCallback: function(fn, event)
		{
			if (!$.isFunction(fn)) {
				alert('argument "' + fn + '" is NOT a function');
				return;
			}

			if (!event) {
				event = this.AJAX_SUCCESS_EVENT;
			}

			if ($.inArray(fn, this.callbacks[event]) == -1) {
				this.callbacks[event].push(fn);
			}
		},

		// removes callback fn from stack for required event if present
		removeCallback: function(fn, event)
		{
			if (!$.isFunction(fn)) {
				alert('argument "' + fn + '" is NOT a function');
				return;
			}

			if (!event) {
				event = this.AJAX_SUCCESS_EVENT;
			}

			var pos = $.inArray(fn, this.callbacks[event]);
			if (pos != -1) {
				this.callbacks[event].splice(pos, 1);
			}
		},


		// @param string jQuerySelector
		addDummyUpdateSnippet: function(snippetName)
		{
			this.dummyUpdateSnippets.push(snippetName);
		},


		confirm: function()
		{
			var $this = $(this);
			var confirmMsg = $this.attr('data-nette-confirm');
			if (isset(confirmMsg)) {
				confirmMsg = confirmMsg.replace('%delete%', "Really delete?");

				if (!confirm(confirmMsg)) {
					return false;
				}

				// some need double confirmation
				var confirm2Msg = $this.attr('data-nette-confirm2');
				if (isset(confirm2Msg)) {
					if (!confirm(confirm2Msg)) {
						return false;
					}
				}

			}

			return true;
		},

		/********************/
		/* DOM Manipulation */
		/********************/

			updateSnippet: function (id, html)
			{
				// for flash messages we APPEND instead of REPLACE html
				if (id === 'snippet--flashes') {
					$("#" + id).append(html);
				} else {
					this.update("#" + id, html);
				}
			},

			replaceSnippet: function (id, html)
			{
				this.replace("#" + id, html);
			},

			insertSnippet: function (id, html)
			{
				this.insert("#" + id, html);
			},

			removeSnippet: function (id)
			{
				this.remove("#" + id);
			},

			update: function (selector, html)
			{
				if ($.inArray(selector, this.dummyUpdateSnippets) !== -1) {
					$(selector).html(html);
				} else {
					$(selector).fadeTo("fast", 0.3, function () {
						$(this).html(html).fadeTo("fast", 1);
					});
				}

//				$(selector).html(html)
//					.effect("highlight", {}, 1500);

			},

			replace: function (selector, html)
			{
				$(selector).fadeTo("fast", 0.3, function () {
					$(this).replaceWith(html).fadeTo("fast", 1);
				});
			},

			insert: function (selector, html)
			{
				$(html).hide().prependTo(selector).slideDown('slow');
			},

			remove: function (selector)
			{
				$(selector).fadeTo("fast", 0, function () {
					$(this).remove();
				});
			},



		/************************/
		/* DOM Manipulation END */
		/************************/


		/*******************************/
		/* zobrazenie hlasok && jGrowl */
		/*******************************/

			// pretazenie fcie, aby prijimala aj pole hodnot a kazdu samostatne vykreslila
			jGrowl: function (msg, options)
			{
				options = options || {};
				if (typeof msg === 'object' ) {
			 		$.each(
						msg, function(k, v) {
						    $.jGrowl(v, options);
						});
			 	} else {
					$.jGrowl(msg, options);
			 	}
			},


			showError: function (msg)
			{
				this.jGrowl(msg, {
					theme: 'error'
				});
			},

			showWarning: function (msg)
			{
				this.jGrowl(msg, {
					theme: 'warning'
				});
			},

			showInfo: function (msg)
			{
				this.jGrowl(msg, {
					theme: 'info'
				});
			},

			showSuccess: function (msg)
			{
				this.jGrowl(msg, {
					theme: 'success'
				});
			},

		/***********************************/
		/* zobrazenie hlasok && jGrowl END */
		/***********************************/

		success: function (payload, textStatus, XMLHttpRequest)
		{
			var i; // iterator for 'for in' loops

			// to avoid errors when loading js scripts via ajax
			if (payload === null || payload === undefined) {
				return false;
			}

			// redirect
			if (payload.redirect) {
				window.location.href = payload.redirect;
				return;
			}

			// state
			if (payload.state) {
				$.Nette.state = payload.state;
			}

			// eval
			if (payload.eval) {
				eval(payload.eval);
			}

			// snippets
			if (payload.snippets) {
				for (i in payload.snippets) {
					$.Nette.updateSnippet(i, payload.snippets[i]);
				}
			}

			// remove snippets
			if (payload.removed) {
				for (i in payload.removed) {
					$.Nette.removeSnippet(payload.removed[i]);
				}
			}

			// errors
			if (payload.error) {
                $.Nette.showError(payload.error);
			}

			// custom callbacks
			var cbs = $.Nette.callbacks[$.Nette.AJAX_SUCCESS_EVENT];
			$.Nette.fireAjaxCb(cbs, payload, textStatus, XMLHttpRequest);

			// custom actions - SHOULD NOT BE USED SINCE CALLBACKS IMPLEMENTED
			if (payload.actions) {
				for (i in payload.actions) {
					switch (i)
					{
						case 'info':
							$.Nette.showInfo(payload.actions[i]);
							break;

						//	vypisujeme chybu
						case 'error':
							var errorMsg = payload.actions[i];
							/*
							switch (payload.actions[i])
							{
								case 'delete_joke':
									errorMsg = 'Vtip sa nepodarilo vymazat';
									break;

								case 'delete_bookmark':
									errorMsg = 'bookmark sa nepodarilo vymazat';
									break;

								case 'add_bookmark':
									errorMsg = 'bookmark sa nepodarilo pridat';
									break;

								default:
//									errorMsg = 'nenastavena chyba! msg => ' + payload.actions[i];
									errorMsg = payload.actions[i];
									break;
							}
							*/
							$.Nette.showError(errorMsg);
							break;

						default:
//							$.Nette.showWarning('no action defined!');
							break;

					}
				}
			}
		},

		// fire callback after ajax request event raised [success, error]
		fireAjaxCb: function(cbs, payload, textStatus, XMLHttpRequest)
		{
			for (var i=0; i<cbs.length; i++) {
				// if a callback returns true, following callbacks wont be fired
				if (cbs[i](payload, textStatus, XMLHttpRequest) === true) {
					break;
				}
			}
		},

		error: function(jqXHR, textStatus, errorThrown)
		{
			// custom callbacks
			var cbs = $.Nette.callbacks[$.Nette.AJAX_ERROR_EVENT];
			$.Nette.fireAjaxCb(cbs, jqXHR, textStatus, errorThrown);

            // errors
			if (jqXHR.responseText) {
                var response = JSON.parse(jqXHR.responseText).error;
                $.Nette.showError(response);
			}

//			console.log(jqXHR);
//			console.log(textStatus);
//			console.log(errorThrown);
//			document.location.href = url;
//			return false;
		},

		// create animated spinner
		createSpinner: function(id, useAjaxStart)
		{
			this.spinner = $('<div></div>').attr('id', id ? id : 'ajax-spinner')
				.ajaxStop(this.hideSpinner)
				.appendTo('body').hide();

			if (useAjaxStart) {
				this.spinner.ajaxStart(this.showSpinner);
			}

//			return this.spinner;
		},

		/**
		 * zobrazi spinner
		 * @param Event [optional] - if set, spinner is shown nearby
		 */
		showSpinner: function(event)
		{
			var pos;
			if (event && event.pageX) {
				pos = {
					position: 'absolute',
					left: event.pageX,
					top: event.pageY
				};
			} else {
				pos = {
					position: 'fixed',
					left: '45%',
					top: '45%'
				};
			}

			$.Nette.spinner.css(pos)
							.show();
		},

		hideSpinner: function()
		{
			$.Nette.spinner.hide().css({
				position: 'fixed',
				left: '45%',
				top: '45%'
			});
		},

		// current page state
		state: null,

		// spinner element
		spinner: null
	}
});


$(function () {

	$.Nette.init();

	$.ajaxSetup({
		success: $.Nette.success,
		error: $.Nette.error,
		dataType: 'json'
	});

	$.Nette.createSpinner();

	/* History.js INIT */
	    var History = window.History; // Note: We are using a capital H instead of a lower h
	    if ( !History.enabled ) {
	         // History.js is disabled for this browser.
	         // This is because we can optionally choose to support HTML4 browsers or not.
	        return false;
	    }

	    // Bind to StateChange Event
	    History.Adapter.bind(window, 'statechange', function() { // Note: We are using statechange instead of popstate

	        // Prepare Variables
			var
				State = History.getState(),
				url = State.url,
				$body = $(document.body),
				rootUrl = History.getRootUrl(),
				relativeUrl = url.replace(rootUrl, ''),
				scrollOptions = {
					duration: 800,
					easing:'swing'
				};

			$.Nette.spinner.css({
				position: 'fixed',
		//			position: 'absolute',
				left: '45%',
				top: 300
			}).show();


			$.post(url, function(data, textStatus, jqXHR){
				// Complete the change
				if ( $body.ScrollTo || false ) {
					$body.ScrollTo(scrollOptions); /* http://balupton.com/projects/jquery-scrollto */
				}

				// Inform Google Analytics of the change
				if ( typeof window._gaq !== 'undefined' ) {
					window._gaq.push(['_trackPageview', relativeUrl]);
				}

				$.Nette.spinner.hide();

				// process retrieved data
				$.Nette.success(data, textStatus, jqXHR);
			});

	    });
	/* History.js INIT END */


	// apply AJAX unobtrusive way
	$('a.ajax').live('click', function(event) {

		// Prepare
		var
			$this = $(this),
			url = $this.attr('href'),
			title = $this.attr('data-history-title') || $this.attr('title') || document.title,
			customSpinner = $this.attr('data-nette-spinner');

		// Continue as normal for cmd clicks etc
		if ( event.which == 2 || event.metaKey ) { return true; }

		event.preventDefault();
//		if ($.active) return false;

		if (!$.Nette.confirm.call($this)) {
			return false;
		}


		var relativeUrl = '/' + History.getState().url.replace(History.getRootUrl(), '');
		// if not explicitly skipping history NOR link to signal (except paging) NOR same url
//		if ($this.attr('rel') !== 'nohistory' && !$this.attr('href').match(/[&?]do=/i)
		if (
			$this.attr('rel') !== 'nohistory'
			&& (!$this.attr('href').match(/[&?]do=(?!(.*)itemPaginator-goto)/i) || $this.attr('rel') && $this.attr('rel').match(/forceHistory/i)) // no signals by default, exception can be made by rel="forceHistory"
			&& relativeUrl !== $this.attr('href') // if on the same url 'statechange' would not fire -> we want to be able ajaxify these links too -> via standard ajax post
		) {
			// change state and request for new content
			History.pushState(null, title, url);
		} else {
			if (customSpinner) {
				customSpinner = $(customSpinner).show();
			} else {
				$.Nette.showSpinner(event);
			}
			var jqxhr = $.post(this.href, $.Nette.success);

			if (customSpinner) {
				jqxhr.complete(function() {
					customSpinner.hide();
				});
			}
		}

	});


	// odeslání na formulářích
    $("form.ajax").livequery(function(){
		$(this).enableAjaxSubmit();
	});

});


/**
 * AJAX form plugin for jQuery
 *
 * @copyright  Copyright (c) 2009 Jan Marek
 * @license    MIT
 * @link       http://nettephp.com/cs/extras/ajax-form
 * @version    0.1
 */

jQuery.fn.extend({
    ajaxSubmit: function (e,callback) {
        var form;
        var sendValues = {};

        // submit button
        if (this.is(":submit")) {
            form = this.parents("form");
            sendValues[this.attr("name")] = this.val() || "";

        // form
        } else if (this.is("form")) {
            form = this;

        // invalid element, do nothing
        } else {
            return null;
        }

        // Vynecháme výchozí akci prohlížeče
        e.preventDefault();
        
        // validation
        if (form.get(0).onsubmit /*&& isset(form.get(0).onsubmit())*/ && !form.get(0).onsubmit()) {
            // Zastavíme vykonávání jakýchkoli dalších eventů
            e.stopImmediatePropagation();
            return null;
        }

        // Abychom formulář neodeslali zbytečně vícekrát
//        if (form.data("ajaxSubmitCalled") === true) {
//        	return null;
//        }
//
//        form.data("ajaxSubmitCalled", true);

        // get values
        var values = form.serializeArray();

        for (var i = 0; i < values.length; i++) {
            var name = values[i].name;

            // multi
            if (name in sendValues) {
                var val = sendValues[name];

                if (!(val instanceof Array)) {
                    val = [val];
                }

                val.push(values[i].value);
                sendValues[name] = val;
            } else {
                sendValues[name] = values[i].value;
            }
        }

        // send ajax request
        var ajaxOptions = {
            url: form.attr("action"),
            data: sendValues,
            type: form.attr("method") || "get"
        };

        // submit button - to show processing to user
        var submitBtn = $(":submit", form).eq(0);
        var submitBtnOrigText = submitBtn.val();

        var customSpinner = $(form.attr('data-nette-spinner'));

        ajaxOptions.complete = function(){
            form.data("ajaxSubmitCalled",false);

			if (!empty(customSpinner)) {
				customSpinner.hide();
			} else {
                $.Nette.spinner.hide();
			}

            // obnovime btn text
        	submitBtn.val(submitBtnOrigText).attr("disabled", false);
        	$.Nette.spinner.removeClass('spinnerText').text('');
        };

        ajaxOptions.beforeSend = function() {
        	if (form.hasClass('spinnerText')) {
            	$.Nette.spinner.addClass('spinnerText').text($.Nette.AJAX_PROCESSING_TEXT);
        	}

			if (!empty(customSpinner)) {
				customSpinner.show();
			} else {
            	$.Nette.showSpinner(e);
			}

        	// ukazeme, ze sa nieco deje
        	submitBtn.val($.Nette.AJAX_PROCESSING_TEXT).attr("disabled", "disabled");
        };

        if (callback) {
            ajaxOptions.success = callback;
        }

        return jQuery.ajax(ajaxOptions);
    },
    
     __submit: function(e) {
            $(this).ajaxSubmit(e, null);
    },

    enableAjaxSubmit: function() {
        this.bind('submit', this.__submit);
        $(':submit', this).bind('click', this.__submit);
    },
    disableAjaxSubmit: function() {
        this.unbind('submit', this.__submit);
        $(':submit', this).unbind('click', this.__submit);
    }
});
