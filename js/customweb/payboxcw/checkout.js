if (typeof Customweb == 'undefined') {
	var Customweb = {};
}

Customweb.PayboxCw = Class.create({
	initialize : function(hiddenFieldsUrl, visibleFieldsUrl, serverUrl, javascriptUrl, saveShippingUrl, methodCode)
	{
		this.hiddenFieldsUrl = hiddenFieldsUrl;
		this.visibleFieldsUrl = visibleFieldsUrl;
		this.serverUrl = serverUrl;
		this.javascriptUrl = javascriptUrl;
		this.saveShippingUrl = saveShippingUrl;
		this.methodCode = methodCode;
		
		this.defaultFormValidation = function(){
			eval('var result = ' + this.methodCode + 'validatePaymentFormElements();');
			return (result != false);
		};
		this.formValidation = this.defaultFormValidation;

		this.onOrderCreated = this.onOrderCreated.bindAsEventListener(this);
		this.onReceivedHiddenFields = this.gatherHiddenFields.bindAsEventListener(this);
		this.onReceivedVisibleFields = this.displayVisibleFields.bindAsEventListener(this);
		this.onReceiveJavascript = this.runAjaxAuthorization.bindAsEventListener(this);

		if (typeof checkout != 'undefined' && typeof Review != 'undefined' && typeof FireCheckout == 'undefined' && typeof IWD == 'undefined') {
			checkout.accordion.openSection = checkout.accordion.openSection.wrap(this.opcGotoSection.bind(this));
			Review.prototype.save = Review.prototype.save.wrap(this.beforePlaceOrder.bind(this));
			Payment.prototype.save = Payment.prototype.save.wrap(this.beforePaymentSave.bind(this));
			if (typeof shippingMethod != 'undefined') {
				shippingMethod.onSave = this.loadPaymentForm.bindAsEventListener(this);
				shippingMethod.saveUrl = this.saveShippingUrl;
			}
		} else if (typeof AWOnestepcheckoutForm != 'undefined') {
			awOSCForm.placeOrderButton.stopObserving('click');
			AWOnestepcheckoutPayment.prototype.savePayment = AWOnestepcheckoutPayment.prototype.savePayment.wrap(this.awcheckoutPaymentSave.bind(this));
			AWOnestepcheckoutForm.prototype.placeOrder = AWOnestepcheckoutForm.prototype.placeOrder.wrap(this.awcheckoutPlaceOrder.bind(this));
			awOSCForm.placeOrderButton.observe('click', awOSCForm.placeOrder.bind(awOSCForm));
			this.formValidation = function(){
				return this.defaultFormValidation() && awOSCForm.validate();
			};
		}
		// For GoMage LightCheckout
		else if (typeof checkout != 'undefined' && typeof checkout.LightcheckoutSubmit != 'undefined') {
			Lightcheckout.prototype.LightcheckoutSubmit = Lightcheckout.prototype.LightcheckoutSubmit.wrap(this.beforePaymentSave.bind(this));
			Lightcheckout.prototype.saveorder = Lightcheckout.prototype.saveorder.wrap(this.lightcheckoutSaveOrder.bind(this));
			this.formValidation = function(){
				return this.defaultFormValidation() && checkoutForm.validator.validate();
			};
		}
		// For FireCheckout
		else if (typeof FireCheckout != 'undefined') {
			FireCheckout.prototype.save = FireCheckout.prototype.save.wrap(this.firecheckoutSave.bind(this));
			FireCheckout.prototype.update = FireCheckout.prototype.update.wrap(this.firecheckoutUpdate.bind(this));
			FireCheckout.prototype.setResponse = FireCheckout.prototype.setResponse.wrap(this.firecheckoutSetResponse.bind(this));
			this.formValidation = function(){
				return this.defaultFormValidation() && checkout.validate();
			};
		} else if (typeof IWD != 'undefined') {
			IWD.OPC.savePayment = IWD.OPC.savePayment.wrap(this.iwdSavePayment.bind(this));
			IWD.OPC.saveOrder = IWD.OPC.saveOrder.wrap(this.iwdSaveOrder.bind(this));
			IWD.OPC.prepareOrderResponse = IWD.OPC.prepareOrderResponse.wrap(this.iwdPrepareOrderResponse.bind(this));
			this.formValidation = function(){
				return true;
			};
		} else {
			$('onestepcheckout-place-order').observe('click', this.createOrder.bind(this));
			var form = new VarienForm('onestepcheckout-form');
			this.originalFunction = Validation.prototype.validate.bind(form.validator);
			Validation.prototype.validate = Validation.prototype.validate.wrap(this.onestepValidate.bind(this));
			this.formValidation = function(){
				return this.defaultFormValidation() && this.originalFunction();
			};
		}
	},
	
	opcGotoSection: function(callOriginal, section)
	{
		if (typeof section != "string") {
			section = section.id;
		}
		
		if (section == 'opc-payment') {
			this.refillPaymentForm(this.formFields);
		}
		
		callOriginal(section);
	},
	
	loadPaymentForm: function(transport)
	{
		if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
		
		shippingMethod.nextStep(transport);
		
		if (!response.error && response.update_section.js) {
			eval.call(window, response.update_section.js);
		}
	},

	loadAliasData : function(element)
	{
		var sel = element;
		var value = sel.options[sel.selectedIndex].value;
		new Ajax.Request(this.visibleFieldsUrl, {
			method : 'get',
			parameters : 'alias_id=' + value + '&payment_method=' + this.methodCode,
			onSuccess : this.onReceivedVisibleFields
		});
	},

	displayVisibleFields : function(transport)
	{
		if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
		
		if (response.error) {
            alert(response.message);
            return false;
        }
		
		var container = $('payment_form_fields_' + this.methodCode);
		container.update(response.html);
		
		eval.call(window, response.js);
	},

	isAuthorization : function(method)
	{
		var result = false;
		var currentMethod = payment.currentMethod;
		if (typeof awOSCPayment != 'undefined' && typeof awOSCPayment.currentMethod != 'undefined') {
			currentMethod = awOSCPayment.currentMethod;
		}
		if (currentMethod && currentMethod == this.methodCode) {
			if ($(currentMethod + '_authorization_method').value == method) {
				result = true;
			}
		}
		return result;
	},

	onOrderCreated: function(transport)
	{
		return this.requestHiddenFields(transport, review.onComplete);
	},

	requestHiddenFields : function(transport, onComplete)
	{
		var response = false;
		if (transport && transport.responseText) {
			try {
				response = eval('(' + transport.responseText + ')');
			} catch (e) {
				response = {};
			}
		} else if (typeof transport == 'object') {
			response = transport;
		}
		
		if (response && typeof response == 'object') {
			var transactionId;
			if (response.transaction_id) {
				transactionId = response.transaction_id;
			} else if (response.redirect) {
				transactionId = /transaction_id\/(\d+)/i.exec(response.redirect)[1];
			} else if (response.messages && response.messages.redirect) {
				transactionId = /transaction_id\/(\d+)/i.exec(response.messages.redirect)[1];
			}
			
			if (!response.success) {
				var msg = response.error_messages;
				if (typeof (msg) == 'object') {
					msg = msg.join("\n");
				}
				onComplete();
				if (msg) {
					alert(msg);
				}
			} else if (this.isAuthorization('hidden')) {
				new Ajax.Request(this.hiddenFieldsUrl, {
					parameters : 'transaction_id=' + transactionId,
					onSuccess : this.onReceivedHiddenFields,
					onFailure: onComplete
				});
			} else if (this.isAuthorization('ajax')) {
				new Ajax.Request(this.javascriptUrl, {
					parameters : 'transaction_id=' + transactionId,
					onSuccess : this.onReceiveJavascript,
					onFailure: onComplete
				});
			} else if (this.isAuthorization('server')) {
				this.sendFieldsToUrl(this.serverUrl, {transaction_id: transactionId});
			} else {
				this.sendFieldsToUrl(response.redirect, []);
			}
		}
	},

	runAjaxAuthorization : function(transport)
	{
		var data = eval('(' + transport.responseText + ')');

		if (data.error == 'no') {
			var javascriptUrl = data.javascriptUrl;
			var callbackFunction = data.callbackFunction;

			this.loadJavascript(javascriptUrl, (function()
			{
				callbackFunction(this.formFields);
			}).bind(this));
		} else {
			alert(data.message);
		}
	},

	gatherHiddenFields : function(transport)
	{
		var formInfo = eval('(' + transport.responseText + ')');

		this.extendMaps(this.formFields, formInfo.fields);
		this.sendFieldsToUrl(formInfo.actionUrl);
	},

	sendFieldsToUrl : function(url, params)
	{
		var me = this,
			tmpForm = new Element('form', {
			'action' : url,
			'method' : 'post',
			'id' : 'customweb_payboxcw_form'
		});
		$$('body')[0].insert(tmpForm);
		var fields = $H(this.formFields);
		fields.each(function(pair)
		{
			me.insertHiddenField(tmpForm, pair.key, pair.value);
		}, this);
		if (params) {
			params = $H(params);
			params.each(function(pair)
			{
				me.insertHiddenField(tmpForm, pair.key, pair.value);
			}, this);
		}
		tmpForm.submit();
	},
	
	insertHiddenField: function(form, key, value)
	{
		if (typeof value == 'object') {
			for (var i = 0; i < value.length; i++) {
				form.insert(new Element('input', {
					'type' : 'hidden',
					'name' : key + '[]',
					'value' : value[i]
				}));
			}
		} else {
			form.insert(new Element('input', {
				'type' : 'hidden',
				'name' : key,
				'value' : value
			}));
		}
		
	},
	
	// This function is used for onestepcheckout only
	onestepValidate: function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			return false;
		}
		return callOriginal();
	},

	// This function is used for onestepcheckout only
	createOrder : function(event)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			try { this.savePaymentInfoInBrowser(); } catch (e) { return false; }
			
			var submitelement = $('onestepcheckout-place-order');
            var loaderelement = new Element('div').
                addClassName('onestepcheckout-place-order-loading').
                update('Please wait, processing your order...');
            submitelement.parentNode.appendChild(loaderelement);
            submitelement.removeClassName('orange').addClassName('grey');
            submitelement.disabled = true;
			
			var form = $('onestepcheckout-form');
			var formUrl = form.readAttribute('action');
			formUrl = formUrl.slice(0, -1) + '/';
			form.writeAttribute('action', 'javascript:void(0);');
			var params = Form.serialize(form);
			this.refillPaymentForm(this.formFields);
			var request = new Ajax.Request(formUrl, {
				method : 'post',
				parameters : params,
				onSuccess : this.checkOrderStatus.bindAsEventListener(this),
				onFailure : checkout.ajaxFailure.bind(checkout)
			});
		}
	},

	// This function is used for onestepcheckout only
	checkOrderStatus : function(transport)
	{
		var html = transport.responseText;
		try {
			response = eval('(' + html + ')');
		} catch (e) {
			response = {};
		}
		if (response.success) {
			this.requestHiddenFields(transport);
		} else {
			// Show the error messages by rendering the returned form
			// content
			var formStartTag = '<form id="onestepcheckout-form"';
			var formEndTag = '</form>';
			var start = html.indexOf(formStartTag);
			var stop = html.indexOf(formEndTag, start) + formEndTag.length;
			var formData = html.substr(start, stop - start);
			$('onestepcheckout-form').replace(formData);
			$('onestepcheckout-place-order').observe('click', this.createOrder.bind(this));
		}
	},

	beforePlaceOrder : function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			if (checkout.loadWaiting != false)
				return;
			checkout.setLoadWaiting('review');
			var params = Form.serialize(payment.form);
			if (review.agreementsForm) {
				params += '&' + Form.serialize(review.agreementsForm);
			}
			params.save = true;
			var request = new Ajax.Request(review.saveUrl, {
				method : 'post',
				parameters : params,
				onSuccess : this.onOrderCreated,
				onFailure : function(){
					review.onComplete();
					checkout.ajaxFailure();
				}
			});
		} else {
			callOriginal();
		}
	},

	extendMaps : function(destination, source)
	{
		for ( var property in source) {
			if (source.hasOwnProperty(property)) {
				destination[property] = source[property];
			}
		}
		return destination;
	},

	removeErrorMsg : function()
	{
		var messageContainer = $$('.messages');
		messageContainer.each(function(item)
		{
			item.update("");
		});
	},

	savePaymentInfoInBrowser : function()
	{
		// Validate forms
//		eval('var result = ' + this.methodCode + 'validatePaymentFormElements();');
//		if (result == false) {
//			throw 'invalid input';
//		}
		
		if(!this.formValidation())  {
			throw 'invalid input';
        }

		// Get all form elements
		var fields = {};
		var tmp = '#payment_form_' + this.methodCode;
		var remove = this.methodCode + '[';

		var inputs = $$(tmp + ' input');
		inputs.each(function(i)
		{
			if (i.readAttribute('data-cloned-element-id')) {
				i.value = '';
				i.removeClassName('required-entry');
			} else if (i.type != 'hidden' || i.readAttribute('originalElement')) {
				var name = i.name.replace(remove, "");
				name = name.replace("]", "");
				fields[name] = i.value;
				i.value = '';
				i.removeClassName('required-entry');
			}
		});

		var selects = $$(tmp + ' select');
		selects.each(function(s)
		{
			var name = s.name.replace(remove, "");
			name = name.replace("]", "");
			fields[name] = s.options[s.selectedIndex].value;
			s.selectedIndex = 0;
			s.removeClassName('required-entry');
			s.removeClassName('validate-select');
		});

		// Remove possible error messages that could confuse the
		// customer.
		this.removeErrorMsg();

		this.formFields = fields;
	},
	
	refillPaymentForm: function(fields) {
		if (fields) {
			var tmp = '#payment_form_' + this.methodCode;
			var remove = this.methodCode + '[';
			
			$$(tmp + ' input').each(function(i){
				if (i.type != 'hidden' || i.readAttribute('originalElement')) {
					var name = i.name.replace(remove, "");
					name = name.replace("]", "");
					i.value = fields[name];
					if (i.readAttribute('originalElement')) {
						$(i.readAttribute('originalElement')).value = i.value;
					}
				}
			});
	
			$$(tmp + ' select').each(function(s){
				var name = s.name.replace(remove, "");
				name = name.replace("]", "");
				s.value = fields[name];
			});
		}
	},

	beforePaymentSave : function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			try { this.savePaymentInfoInBrowser(); } catch (e) { return false; }
		}

		callOriginal();
	},

	// Aheadworks One Step Checkout
	awcheckoutPaymentSave : function(callOriginal)
	{
		// Get all form elements
		var fields = {};
		var tmp = '#payment_form_' + this.methodCode;
		var remove = this.methodCode + '[';

		var inputs = $$(tmp + ' input');
		var selects = $$(tmp + ' select');

		inputs.each(function(i)
		{
			if (i.readAttribute('data-cloned-element-id')) {
				i.value = '';
			} else if (i.type != 'hidden' || i.readAttribute('originalElement')) {
				var name = i.name.replace(remove, "");
				name = name.replace("]", "");
				fields[name] = i.value;
				i.value = '';
			}
		});

		selects.each(function(s)
		{
			var name = s.name.replace(remove, "");
			name = name.replace("]", "");
			fields[name] = s.options[s.selectedIndex].value;
			s.selectedIndex = 0;
		});

		// Remove possible error messages that could confuse the
		// customer.
		this.removeErrorMsg();

		callOriginal();

		this.refillPaymentForm(fields);
	},

	awcheckoutPlaceOrder : function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			if (this.formValidation()) {
				awOSCForm.showOverlay();
				awOSCForm.showPleaseWaitNotice();
				awOSCForm.disablePlaceOrderButton();
				try { this.savePaymentInfoInBrowser(); } catch (e) { return false; }
				var parameters = Form.serialize(awOSCForm.form.form, true);
				this.refillPaymentForm(this.formFields);
				new Ajax.Request(awOSCForm.placeOrderUrl, {
					method : 'post',
					parameters : parameters,
					onComplete : function(transport)
					{
						if (transport && transport.responseText) {
							try {
								response = eval('(' + transport.responseText + ')');
							} catch (e) {
								response = {};
							}
							if (response.redirect) {
								this.requestHiddenFields(transport);
								return;
							}

							var msg = response.messages;
							if (typeof (msg) == 'object') {
								msg = msg.join("\n");
							}
							if (msg) {
								alert(msg);
							}
							awOSCForm.enablePlaceOrderButton();
							awOSCForm.hidePleaseWaitNotice();
							awOSCForm.hideOverlay();
						}
					}.bind(this)
				})
			}
		} else {
			callOriginal();
		}
	},

	// GoMage Lightcheckout only
	lightcheckoutSaveOrder : function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			checkout.showLoadinfo();

			var params = checkout.getFormData();

			var request = new Ajax.Request(checkout.save_order_url, {
				method : 'post',
				parameters : params,
				onSuccess : function(transport)
				{
					eval('var response = ' + transport.responseText);

					if (response.redirect) {
						this.requestHiddenFields(transport, checkout.hideLoadinfo.bind(checkout));
						return;
					} else if (response.error) {
						if (response.message) {
							alert(response.message);
						}
					} else if (response.update_section) {
						this.accordion.currentSection = 'opc-review';
						this.innerHTMLwithScripts($('checkout-update-section'), response.update_section.html);

					}
					checkout.hideLoadinfo();

				}.bind(this),
				onFailure : function()
				{

				}
			});
		} else {
			callOriginal();
		}
	},
	
	firecheckoutSave: function(callOriginal, urlSuffix, forceSave)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			try { this.savePaymentInfoInBrowser(); } catch (e) { return false; }
		}

		callOriginal(urlSuffix, forceSave);

		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.refillPaymentForm(this.formFields);
		}
	},

	firecheckoutUpdate: function(callOriginal, url, params)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			try { this.savePaymentInfoInBrowser(); } catch (e) { return false; }
		}

		callOriginal(url, params);

		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.refillPaymentForm(this.formFields);
		}
	},

	firecheckoutSetResponse: function(callOriginal, transport)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			try {
	            response = transport.responseText.evalJSON();
	        } catch (err) {
	            alert('An error has occured during request processing. Try again please');
	            checkout.setLoadWaiting(false);
	            $('review-please-wait').hide();
	            return false;
	        }

	        if (response.redirect || response.order_created) {
	        	this.requestHiddenFields(transport);
	        } else {
	        	callOriginal(transport);
	        }
	    } else {
	    	callOriginal(transport);
	    }
	},
	
	iwdSavePayment: function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			if(IWD.OPC.saveOrderStatus && !this.defaultFormValidation())  {
				IWD.OPC.Checkout.hideLoader();
				IWD.OPC.Checkout.unlockPlaceOrder();
				IWD.OPC.saveOrderStatus = false;
				return;
        	}
			this.savePaymentInfoInBrowser();
		}

		callOriginal();

		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.refillPaymentForm(this.formFields);
		}
	},
	
	iwdSaveOrder: function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.savePaymentInfoInBrowser();
		}

		callOriginal();

		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.refillPaymentForm(this.formFields);
		}
	},
	
	iwdPrepareOrderResponse: function(callOriginal, response)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
	        if (response.redirect) {
	        	response.success = true;
	        	this.requestHiddenFields(response);
	        } else {
	        	callOriginal(response);
	        }
	    } else {
	    	callOriginal(response);
	    }
	},
	
	loadJavascript : function(url, callback)
	{
		var head = document.getElementsByTagName("head")[0] || document.documentElement;
		var script = document.createElement("script");
		script.src = url;

		// Handle Script loading
		var done = false;

		// Attach handlers for all browsers
		script.onload = script.onreadystatechange = function()
		{
			if (!done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
				done = true;
				callback();

				// Handle memory leak in IE
				script.onload = script.onreadystatechange = null;
				if (head && script.parentNode) {
					head.removeChild(script);
				}
			}
		};

		// Use insertBefore instead of appendChild to circumvent an IE6
		// bug.
		// This arises when a base node is used (#2709 and #4378).
		head.insertBefore(script, head.firstChild);

	}
});

if (!Customweb.CheckoutPreload) {
	Customweb.CheckoutPreloadFlag = false;
	Customweb.CheckoutPreload = Class.create({
		initialize : function(onepagePreloadUrl) {
			this.onepagePreloadUrl = onepagePreloadUrl;
	
			if (!Customweb.CheckoutPreloadFlag) {
				this.preloadCheckout();
				Customweb.CheckoutPreloadFlag = true;
			}
		},
	
		hasLoadFailed : function()
		{
			if (typeof customweb_on_load_called == 'undefined') {
				var params = document.URL.toQueryParams();
				if (params.hasOwnProperty('loadFailed')) {
					var loadFailed = params['loadFailed'];
					if (loadFailed != 'undefined' && loadFailed == 'true') {
						return true;
					}
				}
			}
			return false;
		},
	
		preloadCheckout : function()
		{
			var me = this;
			if (this.hasLoadFailed()) {
				if (checkout && checkout.gotoSection) {
					checkout.gotoSection('payment');
	
					if (this.onepagePreloadUrl) {
						checkout.setLoadWaiting('payment');
						new Ajax.Request(this.onepagePreloadUrl, {
							onSuccess : function(transport)
							{
								if (transport && transport.responseText) {
									try {
										response = eval('(' + transport.responseText + ')');
									} catch (e) {
										response = {};
									}
								}
								if (response.update_section) {
									for ( var i = 0; i < response.update_section.length; i++) {
										if ($('checkout-' + response.update_section[i].name + '-load')) {
											$('checkout-' + response.update_section[i].name + '-load').update(response.update_section[i].html);
										}
									}
								}
								me.allowCheckoutSteps('payment');
								checkout.setLoadWaiting(false);
							}
						});
					} else {
						me.allowCheckoutSteps('payment');
					}
				}
			}
		},
	
		allowCheckoutSteps : function(gotoSection)
		{
			for ( var s = 0; s < checkout.steps.length; s++) {
				if (checkout.steps[s] == gotoSection) {
					break;
				}
				if (document.getElementById('opc-' + checkout.steps[s])) {
					document.getElementById('opc-' + checkout.steps[s]).addClassName('allow');
				}
			}
		}
	});
}
