/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./client/blocks/card-field/index.js":
/*!*******************************************!*\
  !*** ./client/blocks/card-field/index.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   RevolutGatewayBlock: () => (/* binding */ RevolutGatewayBlock)
/* harmony export */ });
/* harmony import */ var _revolut_card_field_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./revolut-card-field.js */ "./client/blocks/card-field/revolut-card-field.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var _components_index_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components/index.js */ "./client/blocks/components/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");




const RevolutGatewayBlock = {
  name: _utils__WEBPACK_IMPORTED_MODULE_1__.PAYMENT_METHODS.REVOLUT_CARD,
  label: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_components_index_js__WEBPACK_IMPORTED_MODULE_2__.BlockLabel, {
    settings: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutGatewaySettings
  }),
  content: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_revolut_card_field_js__WEBPACK_IMPORTED_MODULE_0__["default"], {
    settings: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutGatewaySettings
  }),
  edit: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("p", {
    children: (0,_utils__WEBPACK_IMPORTED_MODULE_1__.i18n)('Revolut Gateway is not available in editor mode')
  }),
  ariaLabel: (0,_utils__WEBPACK_IMPORTED_MODULE_1__.i18n)('Revolut Card`s Gateway'),
  canMakePayment: () => _utils__WEBPACK_IMPORTED_MODULE_1__.revolutGatewaySettings.can_make_payment,
  supports: {
    features: ['products', 'subscriptions'],
    showSavedCards: true,
    showSaveOption: !_utils__WEBPACK_IMPORTED_MODULE_1__.revolutGatewaySettings.is_save_payment_method_mandatory
  }
};

/***/ }),

/***/ "./client/blocks/card-field/revolut-card-field.js":
/*!********************************************************!*\
  !*** ./client/blocks/card-field/revolut-card-field.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _hooks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../hooks */ "./client/hooks/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var _components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../components */ "./client/blocks/components/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");





const RevolutCardField = ({
  eventRegistration,
  billing,
  shippingData,
  shouldSavePayment,
  emitResponse,
  components,
  checkoutStatus,
  settings
}) => {
  const {
    onPaymentSetup,
    onCheckoutSuccess
  } = eventRegistration;
  const rcRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)();
  const cardHolderFieldRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)();
  const [cardStatus, setCardStatus] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [cardErrors, setCardErrors] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  const [isLoading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [revolutPublicId, setRevolutPublicId] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const [orderAmount, setOrderAmount] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(0);
  const {
    createErrorNotice,
    removeAllNotices
  } = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.dispatch)('core/notices');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const unsubscribeOnPaymentSetup = onPaymentSetup(handleOnPaymentSetup);
    const unsubscribeOnCheckoutSuccess = onCheckoutSuccess(handleOnPaymentSuccess);
    return () => {
      unsubscribeOnPaymentSetup();
      unsubscribeOnCheckoutSuccess();
    };
  }, [onCheckoutSuccess, onPaymentSetup, cardErrors, cardStatus, shouldSavePayment, billing.billingAddress, shippingData.shippingAddress]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const updateOrder = async () => {
      if (billing?.cartTotal?.value) {
        setLoading(true);
        (0,_utils__WEBPACK_IMPORTED_MODULE_2__.createRevolutOrder)().then(json => {
          setRevolutPublicId(json.revolut_order_public_id);
          setOrderAmount(json.revolut_order_amount);
          setLoading(false);
        }).catch(err => createErrorNotice((0,_utils__WEBPACK_IMPORTED_MODULE_2__.i18n)(err.message || 'An unexpected error occurred'), {
          id: 'create_order_failed',
          context: emitResponse.noticeContexts.PAYMENTS
        }));
      }
    };
    updateOrder();
  }, [billing.cartTotal.value]);
  const cardInputRef = (0,_hooks__WEBPACK_IMPORTED_MODULE_1__.useCardField)({
    publicId: revolutPublicId,
    onMsg: msg => {
      switch (msg.type) {
        case 'payment_successful':
          {
            document.dispatchEvent(new Event('payment_successful'));
            break;
          }
        case 'payment_failed':
          {
            document.dispatchEvent(new CustomEvent('payment_failed', {
              detail: msg.error.toString()
            }));
            break;
          }
        case 'instance_destroyed':
          {
            rcRef.current = null;
            break;
          }
        case 'instance_mounted':
          {
            rcRef.current = msg.instance;
            break;
          }
        case 'fields_errors_changed':
          {
            setCardErrors(msg.errors);
            break;
          }
        case 'fields_status_changed':
          {
            setCardStatus(msg.status);
            break;
          }
        default:
          break;
      }
    }
  }, [orderAmount]);
  const handleOnPaymentSetup = async () => {
    removeAllNotices();
    let errorMessage = null;
    if (!billing?.billingAddress) {
      errorMessage = 'Please check your billing address, and retry again.';
    }
    if (!cardStatus.completed || cardErrors.length > 0) {
      errorMessage = 'The payment form is not ready for submission.';
      if (rcRef.current) {
        rcRef.current.validate();
        errorMessage = 'The payment form is not ready for submission. please fix the errors below and retry again.';
      }
    }
    if (settings.card_holder_name_field_enabled) {
      if (!cardHolderFieldRef.current.isComplete()) {
        errorMessage = 'The payment form is not ready for submission. please fix the errors below and retry again.';
      }
    }
    return errorMessage ? {
      type: emitResponse.responseTypes.ERROR,
      message: (0,_utils__WEBPACK_IMPORTED_MODULE_2__.i18n)(errorMessage),
      retry: true,
      messageContext: emitResponse.noticeContexts.PAYMENTS
    } : {
      type: emitResponse.responseTypes.SUCCESS
    };
  };
  const handleOnPaymentSuccess = async response => {
    setLoading(true);
    const {
      billingAddress
    } = billing;
    const {
      shippingAddress
    } = shippingData;
    const fullName = settings.card_holder_name_field_enabled && cardHolderFieldRef.current.value.length > 0 ? cardHolderFieldRef.current.value : `${billingAddress.first_name} ${billingAddress.last_name}`;
    const paymentData = {
      name: fullName,
      email: billingAddress.email,
      phone: billingAddress.phone,
      savePaymentMethodFor: shouldSavePayment || settings.is_save_payment_method_mandatory ? 'merchant' : ''
    };
    if (billingAddress.country !== undefined && billingAddress.postcode !== undefined) {
      paymentData.billingAddress = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.createAddress)(billingAddress);
      paymentData.shippingAddress = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.createAddress)(billingAddress);
    }
    if (shippingAddress && shippingAddress.country !== undefined && shippingAddress.postcode !== undefined) {
      paymentData.shippingAddress = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.createAddress)(shippingAddress);
    }
    const paymentResult = await submitCard({
      paymentData
    });
    if (paymentResult.success) {
      (0,_utils__WEBPACK_IMPORTED_MODULE_2__.onPaymentSuccessHandler)({
        response,
        paymentMethod: _utils__WEBPACK_IMPORTED_MODULE_2__.PAYMENT_METHODS.REVOLUT_CARD,
        shouldSavePayment: shouldSavePayment
      });
      return;
    }
    if (paymentResult.error) {
      setLoading(false);
      return {
        type: emitResponse.responseTypes.ERROR,
        message: (0,_utils__WEBPACK_IMPORTED_MODULE_2__.i18n)(paymentResult.error || 'Unexpected error occurred, please try again later'),
        retry: true,
        messageContext: emitResponse.noticeContexts.PAYMENTS
      };
    }
    setLoading(false);
  };
  const submitCard = async ({
    paymentData
  }) => {
    rcRef.current.submit(paymentData);
    return new Promise((resolve, reject) => {
      document.addEventListener('payment_successful', () => {
        resolve({
          success: true
        });
      });
      document.addEventListener('payment_failed', event => {
        resolve({
          success: false,
          error: event.detail
        });
      });
    });
  };
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!cardInputRef.current) return;
    if (cardErrors.length > 0) {
      cardInputRef.current.classList.add('woocommerce-revolut-card-element-error');
      return;
    }
    cardInputRef.current.classList.remove('woocommerce-revolut-card-element-error');
  }, [cardErrors]);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.Fragment, {
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(components.LoadingMask, {
      showSpinner: true,
      isLoading: checkoutStatus.isProcessing || checkoutStatus.isComplete || isLoading,
      children: revolutPublicId && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.Fragment, {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("div", {
            id: "woocommerce-revolut-card-element",
            ref: cardInputRef
          }), cardErrors.length > 0 && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_components__WEBPACK_IMPORTED_MODULE_3__.ErrorsBlock, {
            errorList: cardErrors
          })]
        }), settings.card_holder_name_field_enabled && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("div", {
          style: {
            marginTop: 10
          },
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_components__WEBPACK_IMPORTED_MODULE_3__.CardHolderNameField, {
            inputRef: cardHolderFieldRef
          })
        }), settings.banner.upsell_banner_enabled && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("div", {
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_components__WEBPACK_IMPORTED_MODULE_3__.GatewayUpsellBanner, {
            orderToken: revolutPublicId
          })
        })]
      })
    })
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (RevolutCardField);

/***/ }),

/***/ "./client/blocks/components/index.js":
/*!*******************************************!*\
  !*** ./client/blocks/components/index.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   BlockLabel: () => (/* binding */ BlockLabel),
/* harmony export */   CardHolderNameField: () => (/* binding */ CardHolderNameField),
/* harmony export */   ErrorsBlock: () => (/* binding */ ErrorsBlock),
/* harmony export */   GatewayUpsellBanner: () => (/* binding */ GatewayUpsellBanner),
/* harmony export */   PayByBankBlockLabel: () => (/* binding */ PayByBankBlockLabel),
/* harmony export */   RevolutPayLabel: () => (/* binding */ RevolutPayLabel),
/* harmony export */   SchemeIcons: () => (/* binding */ SchemeIcons)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");



const BlockLabel = ({
  settings
}) => {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: "revolut-payment-method-label-container",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("strong", {
      children: settings.title
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(SchemeIcons, {})]
  });
};
const PayByBankBlockLabel = ({
  settings
}) => {
  const {
    institutions,
    popular_institution_ids
  } = settings.pay_by_bank_brands;
  const popular = popular_institution_ids.map(id => institutions.find(bank => Object.values(bank.details)[0].institution_id === id)).filter(Boolean);
  const nonPopular = institutions.filter(bank => !popular_institution_ids.includes(Object.values(bank.details)[0].institution_id));
  const bankList = [...popular, ...nonPopular];
  const banks_info = {
    firstFive: bankList.slice(0, 5),
    remainingCount: Math.max(0, bankList.length - 5)
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: "revolut-payment-method-label-container",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("strong", {
      children: settings.title
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "revolut-payment-method-label-scheme-icons",
      children: [banks_info.firstFive && banks_info.firstFive.map(brand => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("img", {
        src: brand.logo.value,
        alt: brand.name
      }, brand.name)), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("strong", {
        children: ["+", banks_info.remainingCount]
      })]
    })]
  });
};
const RevolutPayLabel = () => {
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    (0,_utils__WEBPACK_IMPORTED_MODULE_1__.mountRevolutPayIcon)();
  }, []);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: "revolut-payment-method-label-container",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "revolut-pay-label-title-wrapper",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("strong", {
        style: {
          whiteSpace: 'nowrap'
        },
        children: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPaySettings.title
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
        style: {
          marginLeft: '5px',
          display: 'flex'
        },
        id: "revolut-pay-label-informational-icon"
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(SchemeIcons, {})]
  });
};
const SchemeIcons = () => {
  const {
    available_card_brands,
    wc_plugin_url
  } = _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
    className: "revolut-payment-method-label-scheme-icons",
    children: available_card_brands && available_card_brands.filter(brand => brand !== 'maestro').map(brand => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("img", {
      src: `${wc_plugin_url}/assets/images/${brand}.svg`,
      style: {
        marginLeft: 2
      },
      alt: brand
    }, brand))
  });
};
const CardHolderNameField = ({
  inputRef
}) => {
  const [errors, setErrors] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  const shouldShowError = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(false);
  const validateFullName = () => {
    const fullName = inputRef.current.value.trim().split(/\s+/);
    const isValid = fullName.length > 1;
    if (!isValid && shouldShowError.current) {
      inputRef.current.classList.add('wc-revolut-cardholder-name-error');
      setErrors([{
        message: 'Please provide your full name'
      }]);
    } else {
      inputRef.current.classList.remove('wc-revolut-cardholder-name-error');
      setErrors([]);
    }
    return isValid;
  };
  if (inputRef.current) {
    inputRef.current.isComplete = () => {
      shouldShowError.current = true;
      return validateFullName();
    };
  }
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: "form-row validate-required",
    id: "cardholder-name",
    "data-priority": "10",
    style: {
      display: 'block',
      width: '100%',
      marginBottom: 15
    },
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
      ref: inputRef,
      type: "text",
      onChange: validateFullName,
      className: "input-text",
      name: "wc-revolut-cardholder-name",
      id: "wc-revolut-cardholder-name",
      placeholder: "Cardholder name",
      autoComplete: "cardholder",
      required: true
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      style: {
        marginBottom: 10,
        marginTop: 10
      },
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(ErrorsBlock, {
        errorList: errors
      })
    })]
  });
};
const ErrorsBlock = ({
  errorList
}) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
  children: errorList.map((error, key) => {
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("li", {
      className: "card-field-error",
      children: error.message
    }, key);
  })
});
const GatewayUpsellBanner = ({
  orderToken
}) => {
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    (0,_utils__WEBPACK_IMPORTED_MODULE_1__.mountCardGatewayBanner)(orderToken);
  }, [orderToken]);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
    id: "revolut-upsell-banner"
  });
};

/***/ }),

/***/ "./client/blocks/index.js":
/*!********************************!*\
  !*** ./client/blocks/index.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   RevPointsBannerBlock: () => (/* reexport safe */ _revolut_banners__WEBPACK_IMPORTED_MODULE_1__.RevPointsBannerBlock),
/* harmony export */   RevolutGatewayBlock: () => (/* reexport safe */ _card_field__WEBPACK_IMPORTED_MODULE_0__.RevolutGatewayBlock),
/* harmony export */   RevolutPayBlock: () => (/* reexport safe */ _revolut_pay__WEBPACK_IMPORTED_MODULE_3__.RevolutPayBlock),
/* harmony export */   RevolutPayByBankBlock: () => (/* reexport safe */ _pay_by_bank__WEBPACK_IMPORTED_MODULE_4__.RevolutPayByBankBlock),
/* harmony export */   RevolutPayExpressCheckoutBlock: () => (/* reexport safe */ _revolut_pay__WEBPACK_IMPORTED_MODULE_3__.RevolutPayExpressCheckoutBlock),
/* harmony export */   RevolutPaymentRequestBlock: () => (/* reexport safe */ _payment_request__WEBPACK_IMPORTED_MODULE_2__.RevolutPaymentRequestBlock),
/* harmony export */   RevolutPaymentRequestExpressCheckoutBlock: () => (/* reexport safe */ _payment_request__WEBPACK_IMPORTED_MODULE_2__.RevolutPaymentRequestExpressCheckoutBlock)
/* harmony export */ });
/* harmony import */ var _card_field__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./card-field */ "./client/blocks/card-field/index.js");
/* harmony import */ var _revolut_banners__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./revolut-banners */ "./client/blocks/revolut-banners/index.js");
/* harmony import */ var _payment_request__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./payment-request */ "./client/blocks/payment-request/index.js");
/* harmony import */ var _revolut_pay__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./revolut-pay */ "./client/blocks/revolut-pay/index.js");
/* harmony import */ var _pay_by_bank__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./pay-by-bank */ "./client/blocks/pay-by-bank/index.js");






/***/ }),

/***/ "./client/blocks/pay-by-bank/index.js":
/*!********************************************!*\
  !*** ./client/blocks/pay-by-bank/index.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   RevolutPayByBankBlock: () => (/* binding */ RevolutPayByBankBlock)
/* harmony export */ });
/* harmony import */ var _pay_by_bank_field_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./pay-by-bank-field.js */ "./client/blocks/pay-by-bank/pay-by-bank-field.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var _components_index_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components/index.js */ "./client/blocks/components/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");




const RevolutPayByBankBlock = {
  name: _utils__WEBPACK_IMPORTED_MODULE_1__.PAYMENT_METHODS.REVOLUT_PAY_BY_BANK,
  label: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_components_index_js__WEBPACK_IMPORTED_MODULE_2__.PayByBankBlockLabel, {
    settings: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPayByBankSettings
  }),
  content: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_pay_by_bank_field_js__WEBPACK_IMPORTED_MODULE_0__["default"], {
    settings: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPayByBankSettings
  }),
  edit: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("p", {
    children: (0,_utils__WEBPACK_IMPORTED_MODULE_1__.i18n)('Revolut Pay By Bank Gateway is not available in editor mode')
  }),
  ariaLabel: (0,_utils__WEBPACK_IMPORTED_MODULE_1__.i18n)('Revolut Pay By Bank Gateway'),
  canMakePayment: () => _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPayByBankSettings.can_make_payment
};

/***/ }),

/***/ "./client/blocks/pay-by-bank/pay-by-bank-field.js":
/*!********************************************************!*\
  !*** ./client/blocks/pay-by-bank/pay-by-bank-field.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");




const RevolutPayByBankField = ({
  eventRegistration,
  emitResponse
}) => {
  const {
    onCheckoutSuccess
  } = eventRegistration;
  const payByBankInstanceRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const unsubscribeOnCheckoutSuccess = onCheckoutSuccess(response => {
      return new Promise(async (resolve, reject) => {
        const {
          payByBank
        } = await RevolutCheckout.payments({
          publicToken: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings.merchant_public_key,
          locale: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings.locale
        });
        payByBankInstanceRef.current = payByBank({
          createOrder: () => (0,_utils__WEBPACK_IMPORTED_MODULE_1__.createRevolutOrder)().then(json => {
            return {
              publicId: json.revolut_order_public_id
            };
          }),
          onError: errorMsg => {
            resolve({
              type: emitResponse.responseTypes.ERROR,
              message: (0,_utils__WEBPACK_IMPORTED_MODULE_1__.i18n)(errorMsg || 'Unexpected error occurred, please try again later'),
              retry: true,
              messageContext: emitResponse.noticeContexts.PAYMENTS
            });
          },
          onSuccess: () => {
            (0,_utils__WEBPACK_IMPORTED_MODULE_1__.onPaymentSuccessHandler)({
              response,
              paymentMethod: _utils__WEBPACK_IMPORTED_MODULE_1__.PAYMENT_METHODS.REVOLUT_PAY_BY_BANK,
              shouldSavePayment: 0
            });
          },
          onCancel: () => {
            resolve({
              type: emitResponse.responseTypes.ERROR,
              message: (0,_utils__WEBPACK_IMPORTED_MODULE_1__.i18n)('Payment cancelled!'),
              retry: true,
              messageContext: emitResponse.noticeContexts.PAYMENTS
            });
          }
        });
        payByBankInstanceRef.current.show();
      });
    });
    return () => {
      if (payByBankInstanceRef.current) {
        payByBankInstanceRef.current.destroy();
      }
      unsubscribeOnCheckoutSuccess();
    };
  }, [onCheckoutSuccess]);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.Fragment, {});
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (RevolutPayByBankField);

/***/ }),

/***/ "./client/blocks/payment-request/index.js":
/*!************************************************!*\
  !*** ./client/blocks/payment-request/index.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   RevolutPaymentRequestBlock: () => (/* binding */ RevolutPaymentRequestBlock),
/* harmony export */   RevolutPaymentRequestExpressCheckoutBlock: () => (/* binding */ RevolutPaymentRequestExpressCheckoutBlock)
/* harmony export */ });
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var _payment_request__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./payment-request */ "./client/blocks/payment-request/payment-request.js");
/* harmony import */ var _components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components */ "./client/blocks/components/index.js");
/* harmony import */ var _payment_request_express__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./payment-request-express */ "./client/blocks/payment-request/payment-request-express.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");





const RevolutPaymentRequestBlock = {
  name: 'revolut_payment_request',
  label: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_components__WEBPACK_IMPORTED_MODULE_2__.BlockLabel, {
    settings: _utils__WEBPACK_IMPORTED_MODULE_0__.revolutPrbSettings
  }),
  content: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_payment_request__WEBPACK_IMPORTED_MODULE_1__.PaymentRequest, {}),
  edit: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("p", {
    children: (0,_utils__WEBPACK_IMPORTED_MODULE_0__.i18n)('Google/Apple Pay block is not available in editor mode')
  }),
  ariaLabel: 'Google Pay/Apple Pay',
  canMakePayment: () => _utils__WEBPACK_IMPORTED_MODULE_0__.revolutPrbSettings.can_make_payment,
  paymentMethodId: 'revolut_payment_request',
  supports: {
    features: ['products']
  }
};
const RevolutPaymentRequestExpressCheckoutBlock = {
  ...RevolutPaymentRequestBlock,
  name: 'revolut_payment_request_express_checkout',
  paymentMethodId: 'revolut_payment_request',
  content: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_payment_request_express__WEBPACK_IMPORTED_MODULE_3__.PaymentRequestExpress, {}),
  canMakePayment: () => _utils__WEBPACK_IMPORTED_MODULE_0__.revolutPrbSettings.can_make_payment && _utils__WEBPACK_IMPORTED_MODULE_0__.revolutPrbSettings.is_cart
};

/***/ }),

/***/ "./client/blocks/payment-request/payment-request-express.js":
/*!******************************************************************!*\
  !*** ./client/blocks/payment-request/payment-request-express.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PaymentRequestExpress: () => (/* binding */ PaymentRequestExpress)
/* harmony export */ });
/* harmony import */ var _hooks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../hooks */ "./client/hooks/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");




const PaymentRequestExpress = ({
  billing,
  setExpressPaymentError,
  eventRegistration,
  onSubmit,
  onClick,
  onClose,
  emitResponse
}) => {
  const {
    onPaymentSetup,
    onCheckoutFail
  } = eventRegistration;
  const addressRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useRef)();
  const publicIdRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useRef)();
  const {
    revolutPrbRef,
    destroyRef
  } = (0,_hooks__WEBPACK_IMPORTED_MODULE_0__.usePaymentRequest)({
    paymentOptions: {
      amount: 0,
      requestShipping: true,
      validate: () => {
        onClick();
        return true;
      },
      onShippingOptionChange: selectedShippingOption => (0,_utils__WEBPACK_IMPORTED_MODULE_1__.updateShippingOptions)(selectedShippingOption),
      onShippingAddressChange: selectedShippingAddress => (0,_utils__WEBPACK_IMPORTED_MODULE_1__.getShippingOptions)(selectedShippingAddress),
      createOrder: () => {
        return new Promise((resolve, reject) => {
          (0,_utils__WEBPACK_IMPORTED_MODULE_1__.createRevolutExpressOrder)().then(publicId => {
            publicIdRef.current = publicId;
            resolve({
              publicId
            });
          }).catch(err => {
            reject(err);
          });
        });
      },
      validate(address) {
        addressRef.current = address;
      }
    },
    onSuccess: () => (0,_utils__WEBPACK_IMPORTED_MODULE_1__.submitWoocommerceOrder)({
      onSubmit,
      address: addressRef.current
    }).catch(err => {
      destroyRef.current();
      (0,_utils__WEBPACK_IMPORTED_MODULE_1__.cancelOrder)(publicIdRef.current);
      setExpressPaymentError(err);
    }),
    onError: errorMsg => {
      setExpressPaymentError(errorMsg || 'Something went wrong while completing your payment');
      onClose();
    },
    onCancel: errorMsg => {
      setExpressPaymentError(errorMsg);
      onClose();
    }
  }, [billing.cartTotal.value]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    const unsubscribeOnPaymentSetup = onPaymentSetup(() => {
      return {
        type: emitResponse.responseTypes.SUCCESS,
        meta: {
          paymentMethodData: {
            is_express_checkout: 1
          }
        }
      };
    });
    const unsubscribeOnCheckoutFail = onCheckoutFail(() => {
      (0,_utils__WEBPACK_IMPORTED_MODULE_1__.handleFailExpressCheckout)(publicIdRef.current).then(errorMsg => {
        setExpressPaymentError(errorMsg.message);
        if (errorMsg.type === 'failure') {
          destroyRef.current();
        }
      });
    });
    return () => {
      unsubscribeOnPaymentSetup();
      unsubscribeOnCheckoutFail();
    };
  }, [onPaymentSetup, onCheckoutFail]);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.Fragment, {
    children: [' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
      ref: revolutPrbRef
    })]
  });
};

/***/ }),

/***/ "./client/blocks/payment-request/payment-request.js":
/*!**********************************************************!*\
  !*** ./client/blocks/payment-request/payment-request.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PaymentRequest: () => (/* binding */ PaymentRequest)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var _hooks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../hooks */ "./client/hooks/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");




const PaymentRequest = ({
  billing,
  components,
  checkoutStatus,
  eventRegistration,
  emitResponse,
  onSubmit
}) => {
  const {
    onCheckoutSuccess,
    onCheckoutFail
  } = eventRegistration;
  const {
    VALIDATION_STORE_KEY
  } = window.wc.wcBlocksData;
  const [isLoading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const revolutOrderPublicToken = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const woocommerceCheckoutSubmissionResult = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const setPaymentResult = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const paymentError = errorMsg => ({
    type: emitResponse.responseTypes.ERROR,
    message: errorMsg,
    retry: true,
    messageContext: emitResponse.noticeContexts.PAYMENTS
  });
  const {
    revolutPrbRef
  } = (0,_hooks__WEBPACK_IMPORTED_MODULE_2__.usePaymentRequest)({
    paymentOptions: {
      amount: billing.cartTotal.value,
      requestPayerInfo: {
        billingAddress: false
      },
      validate: () => {
        return new Promise((resolve, reject) => {
          setLoading(true);
          if ((0,_utils__WEBPACK_IMPORTED_MODULE_1__.select)(VALIDATION_STORE_KEY).hasValidationErrors()) {
            setLoading(false);
            reject('Checkout form is incomplete');
          }
          (0,_utils__WEBPACK_IMPORTED_MODULE_1__.createRevolutOrder)().then(json => {
            revolutOrderPublicToken.current = json.revolut_order_public_id;
            onSubmit();
            document.addEventListener('checkout_success', () => {
              resolve(true);
            });
            document.addEventListener('checkout_fail', () => {
              setLoading(false);
              reject('Something went wrong');
            });
          });
        });
      },
      createOrder: () => {
        return {
          publicId: revolutOrderPublicToken.current
        };
      }
    },
    onSuccess: () => {
      setLoading(true);
      (0,_utils__WEBPACK_IMPORTED_MODULE_1__.onPaymentSuccessHandler)({
        response: woocommerceCheckoutSubmissionResult.current,
        paymentMethod: _utils__WEBPACK_IMPORTED_MODULE_1__.PAYMENT_METHODS.REVOLUT_PRB,
        shouldSavePayment: 0
      });
    },
    onError: errorMsg => {
      setLoading(false);
      if (setPaymentResult.current) {
        setPaymentResult.current.resolve(paymentError(errorMsg));
      }
    }
  }, [billing.cartTotal.value]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const unsubscribeOnCheckoutSuccess = onCheckoutSuccess(async response => {
      return new Promise(resolve => {
        setLoading(false);
        woocommerceCheckoutSubmissionResult.current = response;
        document.dispatchEvent(new CustomEvent('checkout_success'));
        setPaymentResult.current = {
          resolve
        };
      });
    });
    const unsubscribeOnCheckoutFail = onCheckoutFail(response => {
      if (!response || !response.paymentDetails || !response.paymentDetails.wc_order_id) {
        document.dispatchEvent(new CustomEvent('checkout_fail'));
      }
    });
    return () => {
      unsubscribeOnCheckoutSuccess();
      unsubscribeOnCheckoutFail();
    };
  }, [onCheckoutSuccess, onCheckoutFail]);
  (0,_hooks__WEBPACK_IMPORTED_MODULE_2__.useHidePlacerOrderButton)();
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.Fragment, {
    children: [' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(components.LoadingMask, {
      showSpinner: true,
      isLoading: isLoading || checkoutStatus.isProcessing || checkoutStatus.isComplete,
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
        ref: revolutPrbRef
      })
    })]
  });
};

/***/ }),

/***/ "./client/blocks/revolut-banners/index.js":
/*!************************************************!*\
  !*** ./client/blocks/revolut-banners/index.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   RevPointsBannerBlock: () => (/* binding */ RevPointsBannerBlock)
/* harmony export */ });
/* harmony import */ var _revolut_banner__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./revolut-banner */ "./client/blocks/revolut-banners/revolut-banner.js");
/* harmony import */ var _woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @woocommerce/blocks-checkout */ "@woocommerce/blocks-checkout");
/* harmony import */ var _woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");




const isActive = _utils__WEBPACK_IMPORTED_MODULE_2__.revolutPaySettings.can_make_payment && _utils__WEBPACK_IMPORTED_MODULE_2__.revolutPaySettings.banner.points_banner_enabled;
const RevPointsBannerBlock = {
  metadata: {
    name: _utils__WEBPACK_IMPORTED_MODULE_2__.REVOLUT_POINTS_BLOCK_NAME,
    category: 'woocommerce',
    parent: [_woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_1__.innerBlockAreas.CHECKOUT_ORDER_SUMMARY],
    attributes: {
      lock: {
        type: 'object',
        default: {
          remove: true,
          move: true
        }
      }
    }
  },
  force: true,
  component: () => isActive ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_revolut_banner__WEBPACK_IMPORTED_MODULE_0__["default"], {}) : ''
};

/***/ }),

/***/ "./client/blocks/revolut-banners/revolut-banner.js":
/*!*********************************************************!*\
  !*** ./client/blocks/revolut-banners/revolut-banner.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");



const RevPointsBanner = () => {
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    (0,_utils__WEBPACK_IMPORTED_MODULE_1__.mountRevPointsBanner)();
  }, []);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
    id: _utils__WEBPACK_IMPORTED_MODULE_1__.REVOLUT_PAY_INFORMATIONAL_BANNER_ID
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (RevPointsBanner);

/***/ }),

/***/ "./client/blocks/revolut-pay/index.js":
/*!********************************************!*\
  !*** ./client/blocks/revolut-pay/index.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   RevolutPayBlock: () => (/* binding */ RevolutPayBlock),
/* harmony export */   RevolutPayExpressCheckoutBlock: () => (/* binding */ RevolutPayExpressCheckoutBlock)
/* harmony export */ });
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var _revolut_pay__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./revolut-pay */ "./client/blocks/revolut-pay/revolut-pay.js");
/* harmony import */ var _components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components */ "./client/blocks/components/index.js");
/* harmony import */ var _revolut_pay_express__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./revolut-pay-express */ "./client/blocks/revolut-pay/revolut-pay-express.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");





const urlParams = new URLSearchParams(window.location.search);
const declineReason = urlParams.get('_rp_fr');
const revolutOrderPublicToken = urlParams.get('_rp_oid');
const paymentSuccess = urlParams.get('_rp_s');
if (declineReason || !paymentSuccess && revolutOrderPublicToken) {
  (0,_utils__WEBPACK_IMPORTED_MODULE_0__.dispatch)('core/notices').createErrorNotice((0,_utils__WEBPACK_IMPORTED_MODULE_0__.i18n)(declineReason || 'Payment Rejected'), {
    id: 'rp-fr',
    context: _utils__WEBPACK_IMPORTED_MODULE_0__.CHECKOUT_PAYMENT_CONTEXT
  });
  if (!_utils__WEBPACK_IMPORTED_MODULE_0__.revolutPaySettings.is_cart) {
    (0,_utils__WEBPACK_IMPORTED_MODULE_0__.dispatch)(_utils__WEBPACK_IMPORTED_MODULE_0__.PAYMENT_STORE_KEY).__internalSetActivePaymentMethod(_utils__WEBPACK_IMPORTED_MODULE_0__.PAYMENT_METHODS.REVOLUT_PAY);
  }
}
const RevolutPayBlock = {
  name: 'revolut_pay',
  label: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_components__WEBPACK_IMPORTED_MODULE_2__.RevolutPayLabel, {}),
  content: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_revolut_pay__WEBPACK_IMPORTED_MODULE_1__.RevolutPay, {}),
  edit: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("p", {
    children: (0,_utils__WEBPACK_IMPORTED_MODULE_0__.i18n)('Revolut Pay is not available in editor mode')
  }),
  ariaLabel: 'Revolut Pay',
  canMakePayment: () => _utils__WEBPACK_IMPORTED_MODULE_0__.revolutPaySettings.can_make_payment,
  paymentMethodId: 'revolut_pay',
  supports: {
    features: ['products']
  }
};
const RevolutPayExpressCheckoutBlock = {
  ...RevolutPayBlock,
  name: 'revolut_pay_express_checkout',
  paymentMethodId: 'revolut_pay',
  content: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_revolut_pay_express__WEBPACK_IMPORTED_MODULE_3__.RevolutPayExpress, {
    settings: _utils__WEBPACK_IMPORTED_MODULE_0__.revolutPaySettings
  }),
  canMakePayment: () => _utils__WEBPACK_IMPORTED_MODULE_0__.revolutPaySettings.can_make_payment && _utils__WEBPACK_IMPORTED_MODULE_0__.revolutPaySettings.is_cart
};

/***/ }),

/***/ "./client/blocks/revolut-pay/revolut-pay-express.js":
/*!**********************************************************!*\
  !*** ./client/blocks/revolut-pay/revolut-pay-express.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   RevolutPayExpress: () => (/* binding */ RevolutPayExpress)
/* harmony export */ });
/* harmony import */ var _hooks_use_revolut_pay__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../hooks/use-revolut-pay */ "./client/hooks/use-revolut-pay.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");




const RevolutPayExpress = ({
  billing,
  setExpressPaymentError,
  eventRegistration,
  onSubmit,
  onClick,
  onClose,
  emitResponse
}) => {
  const {
    onPaymentSetup,
    onCheckoutFail
  } = eventRegistration;
  const publicIdRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useRef)();
  const paymentOptions = {
    requestShipping: true,
    validate: () => {
      onClick();
      return true;
    },
    createOrder: () => (0,_utils__WEBPACK_IMPORTED_MODULE_1__.createRevolutExpressOrder)().then(publicId => {
      publicIdRef.current = publicId;
      return (0,_utils__WEBPACK_IMPORTED_MODULE_1__.updatePaymentTotal)(publicId).then(() => ({
        publicId
      }));
    })
  };
  const {
    revolutPayRef,
    destroyRef
  } = (0,_hooks_use_revolut_pay__WEBPACK_IMPORTED_MODULE_0__.useRevolutPay)({
    paymentOptions,
    onSuccess: publicId => (0,_utils__WEBPACK_IMPORTED_MODULE_1__.loadOrderData)(publicId).then(orderData => {
      (0,_utils__WEBPACK_IMPORTED_MODULE_1__.submitWoocommerceOrder)({
        onSubmit,
        address: orderData.address_info
      }).catch(err => {
        destroyRef.current();
        (0,_utils__WEBPACK_IMPORTED_MODULE_1__.cancelOrder)(publicId);
        setExpressPaymentError(err);
      });
    }).catch(err => {
      (0,_utils__WEBPACK_IMPORTED_MODULE_1__.cancelOrder)(publicId);
      setExpressPaymentError(err);
    }),
    onError: errorMsg => {
      setExpressPaymentError(errorMsg || 'Something went wrong while completing your payment');
      onClose();
    },
    onCancel: errorMsg => {
      setExpressPaymentError(errorMsg || 'Payment cancelled!');
      onClose();
    }
  }, [billing.cartTotal.value]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    const unsubscribeOnPaymentSetup = onPaymentSetup(() => {
      return {
        type: emitResponse.responseTypes.SUCCESS,
        meta: {
          paymentMethodData: {
            is_express_checkout: 1
          }
        }
      };
    });
    const unsubscribeOnCheckoutFail = onCheckoutFail(() => {
      (0,_utils__WEBPACK_IMPORTED_MODULE_1__.handleFailExpressCheckout)(publicIdRef.current).then(errorMsg => {
        setExpressPaymentError(errorMsg.message);
        if (errorMsg.type === 'failure') {
          destroyRef.current();
        }
      });
    });
    return () => {
      unsubscribeOnPaymentSetup();
      unsubscribeOnCheckoutFail();
    };
  }, [onPaymentSetup, onCheckoutFail]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    (0,_utils__WEBPACK_IMPORTED_MODULE_1__.mountRevPointsBanner)(_utils__WEBPACK_IMPORTED_MODULE_1__.REVOLUT_PAY_INFORMATIONAL_BANNER_ID);
  }, []);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.Fragment, {
    children: [' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
      id: _utils__WEBPACK_IMPORTED_MODULE_1__.REVOLUT_PAY_INFORMATIONAL_BANNER_ID
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
      ref: revolutPayRef
    })]
  });
};

/***/ }),

/***/ "./client/blocks/revolut-pay/revolut-pay.js":
/*!**************************************************!*\
  !*** ./client/blocks/revolut-pay/revolut-pay.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   RevolutPay: () => (/* binding */ RevolutPay)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../utils */ "./client/utils/index.js");
/* harmony import */ var _hooks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../hooks */ "./client/hooks/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");




const RevolutPay = ({
  billing,
  components,
  eventRegistration,
  onSubmit,
  emitResponse
}) => {
  const {
    onCheckoutSuccess,
    onCheckoutFail
  } = eventRegistration;
  const {
    VALIDATION_STORE_KEY
  } = window.wc.wcBlocksData;
  const [isLoading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [isPaymentCanceled, setPaymentCanceled] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const revolutOrderPublicToken = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const woocommerceCheckoutSubmissionResult = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const setPaymentResult = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const paymentError = errorMsg => ({
    type: emitResponse.responseTypes.ERROR,
    message: errorMsg,
    retry: true,
    messageContext: emitResponse.noticeContexts.PAYMENTS
  });
  const paymentOptions = {
    totalAmount: billing.cartTotal.value,
    validate: () => {
      setPaymentCanceled(false);
      return new Promise(resolve => {
        setLoading(true);
        if ((0,_utils__WEBPACK_IMPORTED_MODULE_1__.select)(VALIDATION_STORE_KEY).hasValidationErrors()) {
          setLoading(false);
          resolve(false);
        }
        (0,_utils__WEBPACK_IMPORTED_MODULE_1__.createRevolutOrder)().then(json => {
          revolutOrderPublicToken.current = json.revolut_order_public_id;
          onSubmit();
          document.addEventListener('checkout_success', () => {
            resolve(true);
          });
          document.addEventListener('checkout_fail', () => {
            setLoading(false);
            resolve(false);
          });
        });
      });
    },
    createOrder: () => {
      return {
        publicId: revolutOrderPublicToken.current
      };
    }
  };
  const {
    revolutPayRef
  } = (0,_hooks__WEBPACK_IMPORTED_MODULE_2__.useRevolutPay)({
    paymentOptions,
    onSuccess: () => {
      setLoading(true);
      (0,_utils__WEBPACK_IMPORTED_MODULE_1__.onPaymentSuccessHandler)({
        response: woocommerceCheckoutSubmissionResult.current,
        paymentMethod: _utils__WEBPACK_IMPORTED_MODULE_1__.PAYMENT_METHODS.REVOLUT_PAY,
        shouldSavePayment: 0
      });
    },
    onError: error => {
      setLoading(false);
      if (setPaymentResult.current) {
        setPaymentResult.current.resolve(paymentError(error));
      }
    },
    onCancel: () => {
      setPaymentCanceled(true);
      if (setPaymentResult.current) {
        setPaymentResult.current.resolve(paymentError('Payment cancelled!'));
      }
    }
  }, [billing.cartTotal.value]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const unsubscribeOnCheckoutSuccess = onCheckoutSuccess(async response => {
      return new Promise(resolve => {
        if (isPaymentCanceled) {
          setLoading(false);
          return resolve(paymentError('Payment cancelled!'));
        }
        setLoading(false);
        woocommerceCheckoutSubmissionResult.current = response;
        document.dispatchEvent(new CustomEvent('checkout_success'));
        setPaymentResult.current = {
          resolve
        };
      });
    });
    const unsubscribeOnCheckoutFail = onCheckoutFail(response => {
      if (!response || !response.paymentDetails || !response.paymentDetails.wc_order_id) {
        document.dispatchEvent(new CustomEvent('checkout_fail'));
      }
    });
    return () => {
      unsubscribeOnCheckoutSuccess();
      unsubscribeOnCheckoutFail();
    };
  }, [onCheckoutSuccess, onCheckoutFail, isPaymentCanceled]);
  (0,_hooks__WEBPACK_IMPORTED_MODULE_2__.useHidePlacerOrderButton)();
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.Fragment, {
    children: [' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(components.LoadingMask, {
      showSpinner: true,
      isLoading: isLoading,
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
        ref: revolutPayRef
      })
    })]
  });
};

/***/ }),

/***/ "./client/hooks/index.js":
/*!*******************************!*\
  !*** ./client/hooks/index.js ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useCardField: () => (/* reexport safe */ _use_card_field__WEBPACK_IMPORTED_MODULE_0__.useCardField),
/* harmony export */   useHidePlacerOrderButton: () => (/* reexport safe */ _use_hide_place_order_button__WEBPACK_IMPORTED_MODULE_2__.useHidePlacerOrderButton),
/* harmony export */   usePaymentRequest: () => (/* reexport safe */ _use_payment_request__WEBPACK_IMPORTED_MODULE_1__.usePaymentRequest),
/* harmony export */   useRevolutPay: () => (/* reexport safe */ _use_revolut_pay__WEBPACK_IMPORTED_MODULE_3__.useRevolutPay)
/* harmony export */ });
/* harmony import */ var _use_card_field__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./use-card-field */ "./client/hooks/use-card-field.js");
/* harmony import */ var _use_payment_request__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./use-payment-request */ "./client/hooks/use-payment-request.js");
/* harmony import */ var _use_hide_place_order_button__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./use-hide-place-order-button */ "./client/hooks/use-hide-place-order-button.js");
/* harmony import */ var _use_revolut_pay__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./use-revolut-pay */ "./client/hooks/use-revolut-pay.js");





/***/ }),

/***/ "./client/hooks/use-card-field.js":
/*!****************************************!*\
  !*** ./client/hooks/use-card-field.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useCardField: () => (/* binding */ useCardField)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../utils */ "./client/utils/index.js");


const REVOLUT_CHECKOUT_RETRY_ERROR_TYPES = ['error.3ds-failed', 'error.email-is-not-specified', 'error.invalid-postcode', 'error.invalid-email', 'error.incorrect-cvv-code', 'error.expired-card', 'error.do-not-honour', 'error.insufficient-funds'];
const useCardField = ({
  onMsg,
  publicId
}, deps) => {
  const onMsgRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(onMsg);
  const cardInputRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const rcRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    let isCancelled = false;
    if (rcRef.current) {
      rcRef.current.destroy();
      onMsgRef.current({
        type: 'instance_destroyed'
      });
    }
    RevolutCheckout(publicId).then(RC => {
      if (isCancelled || !cardInputRef.current) {
        return;
      }
      rcRef.current = RC.createCardField({
        locale: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings.locale,
        target: cardInputRef.current,
        hidePostcodeField: true,
        onSuccess() {
          onMsgRef.current({
            type: 'payment_successful'
          });
        },
        onError(error) {
          if (REVOLUT_CHECKOUT_RETRY_ERROR_TYPES.includes(error.type)) {
            onMsgRef.current({
              type: 'fields_errors_changed',
              errors: [error]
            });
          } else {
            onMsgRef.current({
              type: 'payment_failed',
              error
            });
          }
        },
        onValidation: errors => onMsgRef.current({
          type: 'fields_errors_changed',
          errors
        }),
        onStatusChange: status => {
          onMsgRef.current({
            type: 'fields_status_changed',
            status
          });
        },
        onCancel() {
          onMsgRef.current({
            type: 'payment_cancelled'
          });
        }
      });
      onMsgRef.current({
        type: 'instance_mounted',
        instance: rcRef.current
      });
    });
    const cleanup = () => {
      isCancelled = true;
      if (rcRef.current) {
        rcRef.current.destroy();
        rcRef.current = null;
        onMsgRef.current({
          type: 'instance_destroyed'
        });
      }
    };
    return cleanup;
  }, [publicId, onMsgRef, ...deps]);
  return cardInputRef;
};

/***/ }),

/***/ "./client/hooks/use-hide-place-order-button.js":
/*!*****************************************************!*\
  !*** ./client/hooks/use-hide-place-order-button.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useHidePlacerOrderButton: () => (/* binding */ useHidePlacerOrderButton)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

const useHidePlacerOrderButton = () => {
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const placeOrderButton = document.querySelector('.wp-element-button.wc-block-components-checkout-place-order-button');
    if (placeOrderButton) {
      placeOrderButton.disabled = true;
      placeOrderButton.style.display = 'none';
    }
    return () => {
      if (placeOrderButton) {
        placeOrderButton.disabled = false;
        placeOrderButton.style.display = 'block';
      }
    };
  }, []);
};

/***/ }),

/***/ "./client/hooks/use-payment-request.js":
/*!*********************************************!*\
  !*** ./client/hooks/use-payment-request.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   usePaymentRequest: () => (/* binding */ usePaymentRequest)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../utils */ "./client/utils/index.js");


const usePaymentRequest = ({
  paymentOptions,
  onSuccess,
  onError
}, deps) => {
  const revolutPrbRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const destroyRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)();
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const initPaymentRequestButton = async () => {
      const {
        paymentRequest,
        destroy
      } = await RevolutCheckout.payments({
        publicToken: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings.merchant_public_key,
        locale: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings.locale
      });
      destroyRef.current = destroy;
      if (revolutPrbRef.current) {
        const paymentRequestButton = paymentRequest.mount(revolutPrbRef.current, {
          ...paymentOptions,
          buttonStyle: {
            action: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPrbSettings.payment_request_button_type,
            size: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPrbSettings.payment_request_button_size,
            variant: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPrbSettings.payment_request_button_theme,
            radius: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPrbSettings.payment_request_button_radius
          },
          currency: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings.order.currency,
          shippingOptions: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings.fast_checkout_params.free_shipping_option,
          onSuccess() {
            onSuccess();
          },
          onError(error) {
            onError(error.message);
          },
          onCancel() {
            onError('Payment cancelled!');
          }
        });
        paymentRequestButton.canMakePayment().then(method => {
          if (method) {
            paymentRequestButton.render();
          } else {
            paymentRequestButton.destroy();
          }
        });
      }
    };
    initPaymentRequestButton();
    return () => destroyRef.current();
  }, deps);
  return {
    revolutPrbRef,
    destroyRef
  };
};

/***/ }),

/***/ "./client/hooks/use-revolut-pay.js":
/*!*****************************************!*\
  !*** ./client/hooks/use-revolut-pay.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useRevolutPay: () => (/* binding */ useRevolutPay)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../utils */ "./client/utils/index.js");


const useRevolutPay = ({
  paymentOptions,
  onSuccess,
  onError,
  onCancel
}, deps) => {
  const revolutPayRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const destroyRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)();
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const initRevolutPayWidget = async () => {
      const {
        revolutPay,
        destroy
      } = await RevolutCheckout.payments({
        publicToken: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings.merchant_public_key,
        locale: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings.locale
      });
      destroyRef.current = destroy;
      if (revolutPayRef.current) {
        revolutPay.mount(revolutPayRef.current, {
          currency: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings.order.currency,
          totalAmount: 0,
          mobileRedirectUrls: {
            success: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPaySettings.mobile_redirect_url,
            failure: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPaySettings.mobile_redirect_url,
            cancel: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPaySettings.mobile_redirect_url
          },
          buttonStyle: {
            cashbackCurrency: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutSettings.order.currency,
            variant: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPaySettings.revolut_pay_button_theme,
            size: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPaySettings.revolut_pay_button_size,
            radius: _utils__WEBPACK_IMPORTED_MODULE_1__.revolutPaySettings.revolut_pay_button_radius
          },
          ...paymentOptions
        });
      }
      revolutPay.on('payment', event => {
        switch (event.type) {
          case 'cancel':
            {
              onCancel();
              break;
            }
          case 'success':
            {
              onSuccess(event.orderId);
              break;
            }
          case 'error':
            {
              onError(event.error.message);
              break;
            }
          default:
            break;
        }
      });
    };
    initRevolutPayWidget();
    return () => {
      destroyRef.current();
    };
  }, deps);
  return {
    revolutPayRef,
    destroyRef
  };
};

/***/ }),

/***/ "./client/utils/checkout.js":
/*!**********************************!*\
  !*** ./client/utils/checkout.js ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   cancelOrder: () => (/* binding */ cancelOrder),
/* harmony export */   createRevolutExpressOrder: () => (/* binding */ createRevolutExpressOrder),
/* harmony export */   createRevolutOrder: () => (/* binding */ createRevolutOrder),
/* harmony export */   getShippingOptions: () => (/* binding */ getShippingOptions),
/* harmony export */   handleFailExpressCheckout: () => (/* binding */ handleFailExpressCheckout),
/* harmony export */   loadOrderData: () => (/* binding */ loadOrderData),
/* harmony export */   onPaymentSuccessHandler: () => (/* binding */ onPaymentSuccessHandler),
/* harmony export */   processPayment: () => (/* binding */ processPayment),
/* harmony export */   submitWoocommerceOrder: () => (/* binding */ submitWoocommerceOrder),
/* harmony export */   updatePaymentTotal: () => (/* binding */ updatePaymentTotal),
/* harmony export */   updateShippingOptions: () => (/* binding */ updateShippingOptions)
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./client/utils/index.js");

const createRevolutExpressOrder = async () => {
  const json = await (0,___WEBPACK_IMPORTED_MODULE_0__.sendAjax)({
    endpoint: (0,___WEBPACK_IMPORTED_MODULE_0__.getAjaxURL)({
      endpoint: ___WEBPACK_IMPORTED_MODULE_0__.FAST_CHECKOUT_ROUTES.GET_EXPRESS_CHECKOUT_PARAMS
    }),
    data: {
      security: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.fast_checkout_params.nonce.get_express_checkout_params
    }
  });
  if (json?.success) {
    return json.revolut_public_id;
  }
  return Promise.reject(new Error('Something went wrong while creating the payment.'));
};
const createRevolutOrder = async () => {
  const json = await (0,___WEBPACK_IMPORTED_MODULE_0__.sendAjax)({
    endpoint: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.route.create_revolut_order,
    data: {
      security: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.nonce.create_revolut_order
    }
  });
  if (json?.success) {
    return json;
  }
  throw new Error('An unexpected error occurred');
};
function updateShippingOptions(shippingOption) {
  let shipping_option_data = {
    security: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.fast_checkout_params.nonce.update_shipping,
    shipping_method: [shippingOption.id],
    is_product_page: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.fast_checkout_params.is_product_page
  };
  return new Promise((resolve, reject) => {
    (0,___WEBPACK_IMPORTED_MODULE_0__.sendAjax)({
      data: shipping_option_data,
      endpoint: (0,___WEBPACK_IMPORTED_MODULE_0__.getAjaxURL)({
        endpoint: ___WEBPACK_IMPORTED_MODULE_0__.FAST_CHECKOUT_ROUTES.UPDATE_SHIPPING_METHOD
      })
    }).then(response => {
      resolve(response);
    }).catch(error => {
      reject(error);
    });
  });
}
function getShippingOptions(address) {
  let address_data = {
    security: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.fast_checkout_params.nonce.shipping,
    country: address.country,
    state: address.region,
    postcode: address.postalCode,
    city: address.city,
    address: '',
    address_2: '',
    is_product_page: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.fast_checkout_params.is_product_page,
    require_shipping: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.fast_checkout_params.shipping_required
  };
  return new Promise((resolve, reject) => {
    (0,___WEBPACK_IMPORTED_MODULE_0__.sendAjax)({
      data: address_data,
      endpoint: (0,___WEBPACK_IMPORTED_MODULE_0__.getAjaxURL)({
        endpoint: ___WEBPACK_IMPORTED_MODULE_0__.FAST_CHECKOUT_ROUTES.GET_SHIPPING_OPTIONS
      })
    }).then(response => {
      resolve(response);
    }).catch(error => {
      reject(error);
    });
  });
}
const loadOrderData = async publicId => {
  try {
    const json = await (0,___WEBPACK_IMPORTED_MODULE_0__.sendAjax)({
      data: {
        security: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.fast_checkout_params.nonce.load_order_data,
        revolut_public_id: publicId
      },
      endpoint: (0,___WEBPACK_IMPORTED_MODULE_0__.getAjaxURL)({
        endpoint: ___WEBPACK_IMPORTED_MODULE_0__.FAST_CHECKOUT_ROUTES.LOAD_ORDER_DATA
      })
    });
    if (json) {
      return json;
    }
    throw new Error('Something went wrong while retrieving the billing address. your payment will be cancelled');
  } catch (err) {
    throw new Error(err.message || 'An unexpected error occurred.');
  }
};
const cancelOrder = async publicId => {
  const json = await (0,___WEBPACK_IMPORTED_MODULE_0__.sendAjax)({
    data: {
      revolut_public_id: publicId,
      security: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.fast_checkout_params.nonce.cancel_order
    },
    endpoint: (0,___WEBPACK_IMPORTED_MODULE_0__.getAjaxURL)({
      endpoint: ___WEBPACK_IMPORTED_MODULE_0__.FAST_CHECKOUT_ROUTES.CANCEL_ORDER
    })
  });
  return json.success;
};
const handleFailExpressCheckout = async publicId => {
  try {
    const orderCancelled = await cancelOrder(publicId);
    if (orderCancelled) {
      return {
        type: 'error',
        message: (0,___WEBPACK_IMPORTED_MODULE_0__.i18n)('Something went wrong, your order has been cancelled.')
      };
    }
    throw new Error('Couldn`t cancel the order');
  } catch (err) {
    return {
      type: 'failure',
      message: (0,___WEBPACK_IMPORTED_MODULE_0__.i18n)("Your order has been completed, but we couldn't redirect you to the confirmation page. Please contact us for assistance.")
    };
  }
};
const processPayment = async ({
  revolut_public_id,
  shouldSavePayment,
  wc_order_id,
  paymentMethod,
  process_payment_result
}) => {
  try {
    const data = {
      revolut_gateway: paymentMethod,
      security: process_payment_result,
      revolut_public_id: revolut_public_id,
      revolut_payment_error: '',
      wc_order_id: wc_order_id,
      reload_checkout: 0,
      revolut_save_payment_method: Number(paymentMethod === ___WEBPACK_IMPORTED_MODULE_0__.PAYMENT_METHODS.REVOLUT_CARD) && (Number(shouldSavePayment) || Number(___WEBPACK_IMPORTED_MODULE_0__.revolutGatewaySettings.is_save_payment_method_mandatory))
    };
    const response = await (0,___WEBPACK_IMPORTED_MODULE_0__.sendAjax)({
      data,
      endpoint: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.route.process_order
    });
    if (response?.result === 'fail') {
      throw new Error(response?.messages || 'Something went wrong while trying to charge your card, please try again');
    }
    if (response?.result === 'success') {
      return response;
    }
    throw new Error('Failed to process your order due to server issue');
  } catch (err) {
    throw new Error(err.message || 'An unexpected error occurred');
  }
};
const onPaymentSuccessHandler = async ({
  response,
  paymentMethod,
  shouldSavePayment
}) => {
  try {
    const {
      processingResponse
    } = response;
    const {
      wc_order_id,
      revolut_public_id,
      process_payment_result
    } = processingResponse.paymentDetails;
    const processResult = await processPayment({
      wc_order_id,
      revolut_public_id,
      shouldSavePayment,
      paymentMethod,
      process_payment_result
    });
    if (processResult.redirect) {
      window.location.href = decodeURI(processResult.redirect);
      return {
        type: 'success'
      };
    }
    throw new Error('Could not redirect you to the confirmation page due to an unexpected error. Please contact the merchant');
  } catch (e) {
    return {
      type: 'error',
      message: (0,___WEBPACK_IMPORTED_MODULE_0__.i18n)(e?.message),
      retry: true,
      messageContext: 'wc/checkout/payments'
    };
  }
};
const submitWoocommerceOrder = async ({
  onSubmit,
  address
}) => {
  const {
    billingAddress,
    shippingAddress
  } = address;
  let firstSpaceIndex = billingAddress.recipient.indexOf(' ');
  let firstName = billingAddress.recipient.substring(0, firstSpaceIndex);
  let lastName = billingAddress.recipient.substring(firstSpaceIndex + 1);
  (0,___WEBPACK_IMPORTED_MODULE_0__.dispatch)(___WEBPACK_IMPORTED_MODULE_0__.CART_STORE_KEY).setBillingAddress({
    first_name: firstName,
    last_name: lastName,
    email: address.email,
    ...(0,___WEBPACK_IMPORTED_MODULE_0__.normalizeFcAddress)(billingAddress)
  });
  (0,___WEBPACK_IMPORTED_MODULE_0__.dispatch)(___WEBPACK_IMPORTED_MODULE_0__.CART_STORE_KEY).setShippingAddress({
    first_name: firstName,
    last_name: lastName,
    ...(0,___WEBPACK_IMPORTED_MODULE_0__.normalizeFcAddress)(shippingAddress)
  });
  onSubmit();
};
const updatePaymentTotal = publicId => (0,___WEBPACK_IMPORTED_MODULE_0__.sendAjax)({
  data: {
    revolut_public_id: publicId,
    security: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.fast_checkout_params.nonce.update_order_total
  },
  endpoint: (0,___WEBPACK_IMPORTED_MODULE_0__.getAjaxURL)({
    endpoint: ___WEBPACK_IMPORTED_MODULE_0__.FAST_CHECKOUT_ROUTES.UPDATE_PAYMENT_TOTAL
  })
});

/***/ }),

/***/ "./client/utils/common.js":
/*!********************************!*\
  !*** ./client/utils/common.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createAddress: () => (/* binding */ createAddress),
/* harmony export */   dispatch: () => (/* reexport safe */ _wordpress_data__WEBPACK_IMPORTED_MODULE_2__.dispatch),
/* harmony export */   getAjaxURL: () => (/* binding */ getAjaxURL),
/* harmony export */   i18n: () => (/* binding */ i18n),
/* harmony export */   normalizeFcAddress: () => (/* binding */ normalizeFcAddress),
/* harmony export */   revolutGatewaySettings: () => (/* binding */ revolutGatewaySettings),
/* harmony export */   revolutPayByBankSettings: () => (/* binding */ revolutPayByBankSettings),
/* harmony export */   revolutPaySettings: () => (/* binding */ revolutPaySettings),
/* harmony export */   revolutPrbSettings: () => (/* binding */ revolutPrbSettings),
/* harmony export */   revolutSettings: () => (/* binding */ revolutSettings),
/* harmony export */   select: () => (/* reexport safe */ _wordpress_data__WEBPACK_IMPORTED_MODULE_2__.select),
/* harmony export */   sendAjax: () => (/* binding */ sendAjax)
/* harmony export */ });
/* harmony import */ var _woocommerce_settings__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @woocommerce/settings */ "@woocommerce/settings");
/* harmony import */ var _woocommerce_settings__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_settings__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__);



const revolutSettings = (0,_woocommerce_settings__WEBPACK_IMPORTED_MODULE_0__.getSetting)('revolut_data');
const revolutPaySettings = (0,_woocommerce_settings__WEBPACK_IMPORTED_MODULE_0__.getSetting)('revolut_data').revolut_pay_data;
const revolutPayByBankSettings = (0,_woocommerce_settings__WEBPACK_IMPORTED_MODULE_0__.getSetting)('revolut_data').revolut_pay_by_bank_data;
const revolutGatewaySettings = (0,_woocommerce_settings__WEBPACK_IMPORTED_MODULE_0__.getSetting)('revolut_data').revolut_cc_data;
const revolutPrbSettings = (0,_woocommerce_settings__WEBPACK_IMPORTED_MODULE_0__.getSetting)('revolut_data').revolut_payment_request_data;
const i18n = msg => (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)(msg, 'revolut-gateway-for-woocommerce');
const getAjaxURL = ({
  endpoint,
  controller = 'revolut_payment_request_'
}) => {
  return revolutSettings.fast_checkout_params.ajax_url.toString().replace('%%wc_revolut_gateway_ajax_endpoint%%', `${controller}${endpoint}`);
};
function buildFormData(formData, data, parentKey) {
  const newFormData = formData;
  if (data && typeof data === 'object') {
    Object.keys(data).forEach(key => {
      buildFormData(newFormData, data[key], parentKey ? `${parentKey}[${key}]` : key);
    });
  } else {
    const value = data == null ? '' : data;
    newFormData.append(parentKey, value);
  }
  return newFormData;
}
const sendAjax = async ({
  data,
  endpoint
}) => {
  const requestData = buildFormData(new FormData(), data);
  const response = await fetch(endpoint, {
    method: 'POST',
    body: requestData
  });
  if (!response.ok) {
    throw new Error('Failed to process your request due to network issue');
  }
  const json = await response.json();
  return json;
};
const createAddress = address => {
  return {
    countryCode: address.country,
    region: address.state,
    city: address.city,
    streetLine1: address.address_1,
    streetLine2: address.address_2,
    postcode: address.postcode
  };
};
const normalizeFcAddress = address => {
  return {
    address_1: address.address || address.addressLine.at(0),
    address_2: address.address_2 || address.addressLine.at(1) || '',
    city: address.city,
    state: address.state || address.region,
    postcode: address.postcode || address.postalCode,
    country: address.country,
    phone: address.phone
  };
};

/***/ }),

/***/ "./client/utils/constants.js":
/*!***********************************!*\
  !*** ./client/utils/constants.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CART_STORE_KEY: () => (/* binding */ CART_STORE_KEY),
/* harmony export */   CHECKOUT_PAYMENT_CONTEXT: () => (/* binding */ CHECKOUT_PAYMENT_CONTEXT),
/* harmony export */   FAST_CHECKOUT_ROUTES: () => (/* binding */ FAST_CHECKOUT_ROUTES),
/* harmony export */   PAYMENT_METHODS: () => (/* binding */ PAYMENT_METHODS),
/* harmony export */   PAYMENT_STORE_KEY: () => (/* binding */ PAYMENT_STORE_KEY),
/* harmony export */   REVOLUT_GATEWAY_UPSELL_BANNER_ID: () => (/* binding */ REVOLUT_GATEWAY_UPSELL_BANNER_ID),
/* harmony export */   REVOLUT_PAY_INFORMATIONAL_BANNER_ID: () => (/* binding */ REVOLUT_PAY_INFORMATIONAL_BANNER_ID),
/* harmony export */   REVOLUT_PAY_INFORMATIONAL_ICON_ID: () => (/* binding */ REVOLUT_PAY_INFORMATIONAL_ICON_ID),
/* harmony export */   REVOLUT_POINTS_BLOCK_NAME: () => (/* binding */ REVOLUT_POINTS_BLOCK_NAME)
/* harmony export */ });
const {
  CART_STORE_KEY,
  PAYMENT_STORE_KEY
} = window.wc.wcBlocksData;
const PAYMENT_METHODS = {
  REVOLUT_CARD: 'revolut_cc',
  REVOLUT_PAY: 'revolut_pay',
  REVOLUT_PAY_BY_BANK: 'revolut_pay_by_bank',
  REVOLUT_PRB: 'revolut_payment_request'
};
const CHECKOUT_PAYMENT_CONTEXT = 'wc/checkout/payments';
const REVOLUT_PAY_INFORMATIONAL_BANNER_ID = 'revolut-pay-informational-banner';
const REVOLUT_PAY_INFORMATIONAL_ICON_ID = 'revolut-pay-label-informational-icon';
const REVOLUT_GATEWAY_UPSELL_BANNER_ID = 'revolut-upsell-banner';
const REVOLUT_POINTS_BLOCK_NAME = 'revolut-gateway-for-woocommerce/revolut-banner';
const FAST_CHECKOUT_ROUTES = {
  GET_EXPRESS_CHECKOUT_PARAMS: 'get_express_checkout_params',
  UPDATE_SHIPPING_METHOD: 'update_shipping_method',
  GET_SHIPPING_OPTIONS: 'get_shipping_options',
  LOAD_ORDER_DATA: 'load_order_data',
  CANCEL_ORDER: 'cancel_order',
  PROCESS_ORDER: 'process_payment_result',
  UPDATE_PAYMENT_TOTAL: 'update_payment_total'
};

/***/ }),

/***/ "./client/utils/index.js":
/*!*******************************!*\
  !*** ./client/utils/index.js ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CART_STORE_KEY: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_3__.CART_STORE_KEY),
/* harmony export */   CHECKOUT_PAYMENT_CONTEXT: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_3__.CHECKOUT_PAYMENT_CONTEXT),
/* harmony export */   FAST_CHECKOUT_ROUTES: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_3__.FAST_CHECKOUT_ROUTES),
/* harmony export */   PAYMENT_METHODS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_3__.PAYMENT_METHODS),
/* harmony export */   PAYMENT_STORE_KEY: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_3__.PAYMENT_STORE_KEY),
/* harmony export */   REVOLUT_GATEWAY_UPSELL_BANNER_ID: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_3__.REVOLUT_GATEWAY_UPSELL_BANNER_ID),
/* harmony export */   REVOLUT_PAY_INFORMATIONAL_BANNER_ID: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_3__.REVOLUT_PAY_INFORMATIONAL_BANNER_ID),
/* harmony export */   REVOLUT_PAY_INFORMATIONAL_ICON_ID: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_3__.REVOLUT_PAY_INFORMATIONAL_ICON_ID),
/* harmony export */   REVOLUT_POINTS_BLOCK_NAME: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_3__.REVOLUT_POINTS_BLOCK_NAME),
/* harmony export */   cancelOrder: () => (/* reexport safe */ _checkout__WEBPACK_IMPORTED_MODULE_1__.cancelOrder),
/* harmony export */   createAddress: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.createAddress),
/* harmony export */   createRevolutExpressOrder: () => (/* reexport safe */ _checkout__WEBPACK_IMPORTED_MODULE_1__.createRevolutExpressOrder),
/* harmony export */   createRevolutOrder: () => (/* reexport safe */ _checkout__WEBPACK_IMPORTED_MODULE_1__.createRevolutOrder),
/* harmony export */   dispatch: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.dispatch),
/* harmony export */   getAjaxURL: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.getAjaxURL),
/* harmony export */   getShippingOptions: () => (/* reexport safe */ _checkout__WEBPACK_IMPORTED_MODULE_1__.getShippingOptions),
/* harmony export */   handleFailExpressCheckout: () => (/* reexport safe */ _checkout__WEBPACK_IMPORTED_MODULE_1__.handleFailExpressCheckout),
/* harmony export */   i18n: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.i18n),
/* harmony export */   loadOrderData: () => (/* reexport safe */ _checkout__WEBPACK_IMPORTED_MODULE_1__.loadOrderData),
/* harmony export */   mountCardGatewayBanner: () => (/* reexport safe */ _upsell__WEBPACK_IMPORTED_MODULE_2__.mountCardGatewayBanner),
/* harmony export */   mountRevPointsBanner: () => (/* reexport safe */ _upsell__WEBPACK_IMPORTED_MODULE_2__.mountRevPointsBanner),
/* harmony export */   mountRevolutPayIcon: () => (/* reexport safe */ _upsell__WEBPACK_IMPORTED_MODULE_2__.mountRevolutPayIcon),
/* harmony export */   normalizeFcAddress: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.normalizeFcAddress),
/* harmony export */   onPaymentSuccessHandler: () => (/* reexport safe */ _checkout__WEBPACK_IMPORTED_MODULE_1__.onPaymentSuccessHandler),
/* harmony export */   processPayment: () => (/* reexport safe */ _checkout__WEBPACK_IMPORTED_MODULE_1__.processPayment),
/* harmony export */   revolutGatewaySettings: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.revolutGatewaySettings),
/* harmony export */   revolutPayByBankSettings: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.revolutPayByBankSettings),
/* harmony export */   revolutPaySettings: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.revolutPaySettings),
/* harmony export */   revolutPrbSettings: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.revolutPrbSettings),
/* harmony export */   revolutSettings: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.revolutSettings),
/* harmony export */   select: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.select),
/* harmony export */   sendAjax: () => (/* reexport safe */ _common__WEBPACK_IMPORTED_MODULE_0__.sendAjax),
/* harmony export */   submitWoocommerceOrder: () => (/* reexport safe */ _checkout__WEBPACK_IMPORTED_MODULE_1__.submitWoocommerceOrder),
/* harmony export */   updatePaymentTotal: () => (/* reexport safe */ _checkout__WEBPACK_IMPORTED_MODULE_1__.updatePaymentTotal),
/* harmony export */   updateShippingOptions: () => (/* reexport safe */ _checkout__WEBPACK_IMPORTED_MODULE_1__.updateShippingOptions)
/* harmony export */ });
/* harmony import */ var _common__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./common */ "./client/utils/common.js");
/* harmony import */ var _checkout__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./checkout */ "./client/utils/checkout.js");
/* harmony import */ var _upsell__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./upsell */ "./client/utils/upsell.js");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./constants */ "./client/utils/constants.js");





/***/ }),

/***/ "./client/utils/upsell.js":
/*!********************************!*\
  !*** ./client/utils/upsell.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   mountCardGatewayBanner: () => (/* binding */ mountCardGatewayBanner),
/* harmony export */   mountRevPointsBanner: () => (/* binding */ mountRevPointsBanner),
/* harmony export */   mountRevolutPayIcon: () => (/* binding */ mountRevolutPayIcon)
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./client/utils/index.js");


const __metadata = {
  channel: 'woocommerce-blocks'
};
let RevolutUpsellInstance = null;
const getRevolutUpsellInstance = () => {
  if (RevolutUpsellInstance) return RevolutUpsellInstance;
  if (typeof RevolutUpsell !== 'undefined') {
    RevolutUpsellInstance = RevolutUpsell({
      locale: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.locale,
      publicToken: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.merchant_public_key
    });
  }
  return RevolutUpsellInstance;
};
const mountCardGatewayBanner = orderToken => {
  const instance = getRevolutUpsellInstance();
  if (!instance) return;
  const target = document.getElementById(___WEBPACK_IMPORTED_MODULE_0__.REVOLUT_GATEWAY_UPSELL_BANNER_ID);
  if (!target || !___WEBPACK_IMPORTED_MODULE_0__.revolutGatewaySettings.banner.upsell_banner_enabled) return;
  instance.cardGatewayBanner.mount(target, {
    orderToken
  });
};
const mountRevolutPayIcon = () => {
  const instance = getRevolutUpsellInstance();
  if (!instance) return;
  const target = document.getElementById(___WEBPACK_IMPORTED_MODULE_0__.REVOLUT_PAY_INFORMATIONAL_ICON_ID);
  if (!target || !___WEBPACK_IMPORTED_MODULE_0__.revolutPaySettings.banner.label_icon_variant) return;
  instance.promotionalBanner.mount(target, {
    amount: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.order.amount,
    variant: ___WEBPACK_IMPORTED_MODULE_0__.revolutPaySettings.banner.label_icon_variant === 'cashback' ? 'link' : ___WEBPACK_IMPORTED_MODULE_0__.revolutPaySettings.banner.label_icon_variant,
    currency: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.order.currency,
    style: {
      text: ___WEBPACK_IMPORTED_MODULE_0__.revolutPaySettings.banner.label_icon_variant === 'cashback' ? 'cashback' : null,
      color: 'blue'
    },
    __metadata
  });
};
const mountRevPointsBanner = () => {
  const instance = getRevolutUpsellInstance();
  if (!instance) return;
  const target = document.getElementById(___WEBPACK_IMPORTED_MODULE_0__.REVOLUT_PAY_INFORMATIONAL_BANNER_ID);
  if (!target || !___WEBPACK_IMPORTED_MODULE_0__.revolutPaySettings.banner.points_banner_enabled) return;
  instance.promotionalBanner.mount(target, {
    amount: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.order.amount,
    variant: 'banner',
    currency: ___WEBPACK_IMPORTED_MODULE_0__.revolutSettings.order.currency,
    __metadata
  });
};

/***/ }),

/***/ "./node_modules/react/cjs/react-jsx-runtime.development.js":
/*!*****************************************************************!*\
  !*** ./node_modules/react/cjs/react-jsx-runtime.development.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {

/**
 * @license React
 * react-jsx-runtime.development.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



if (true) {
  (function() {
'use strict';

var React = __webpack_require__(/*! react */ "react");

// ATTENTION
// When adding new symbols to this file,
// Please consider also adding to 'react-devtools-shared/src/backend/ReactSymbols'
// The Symbol used to tag the ReactElement-like types.
var REACT_ELEMENT_TYPE = Symbol.for('react.element');
var REACT_PORTAL_TYPE = Symbol.for('react.portal');
var REACT_FRAGMENT_TYPE = Symbol.for('react.fragment');
var REACT_STRICT_MODE_TYPE = Symbol.for('react.strict_mode');
var REACT_PROFILER_TYPE = Symbol.for('react.profiler');
var REACT_PROVIDER_TYPE = Symbol.for('react.provider');
var REACT_CONTEXT_TYPE = Symbol.for('react.context');
var REACT_FORWARD_REF_TYPE = Symbol.for('react.forward_ref');
var REACT_SUSPENSE_TYPE = Symbol.for('react.suspense');
var REACT_SUSPENSE_LIST_TYPE = Symbol.for('react.suspense_list');
var REACT_MEMO_TYPE = Symbol.for('react.memo');
var REACT_LAZY_TYPE = Symbol.for('react.lazy');
var REACT_OFFSCREEN_TYPE = Symbol.for('react.offscreen');
var MAYBE_ITERATOR_SYMBOL = Symbol.iterator;
var FAUX_ITERATOR_SYMBOL = '@@iterator';
function getIteratorFn(maybeIterable) {
  if (maybeIterable === null || typeof maybeIterable !== 'object') {
    return null;
  }

  var maybeIterator = MAYBE_ITERATOR_SYMBOL && maybeIterable[MAYBE_ITERATOR_SYMBOL] || maybeIterable[FAUX_ITERATOR_SYMBOL];

  if (typeof maybeIterator === 'function') {
    return maybeIterator;
  }

  return null;
}

var ReactSharedInternals = React.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED;

function error(format) {
  {
    {
      for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
        args[_key2 - 1] = arguments[_key2];
      }

      printWarning('error', format, args);
    }
  }
}

function printWarning(level, format, args) {
  // When changing this logic, you might want to also
  // update consoleWithStackDev.www.js as well.
  {
    var ReactDebugCurrentFrame = ReactSharedInternals.ReactDebugCurrentFrame;
    var stack = ReactDebugCurrentFrame.getStackAddendum();

    if (stack !== '') {
      format += '%s';
      args = args.concat([stack]);
    } // eslint-disable-next-line react-internal/safe-string-coercion


    var argsWithFormat = args.map(function (item) {
      return String(item);
    }); // Careful: RN currently depends on this prefix

    argsWithFormat.unshift('Warning: ' + format); // We intentionally don't use spread (or .apply) directly because it
    // breaks IE9: https://github.com/facebook/react/issues/13610
    // eslint-disable-next-line react-internal/no-production-logging

    Function.prototype.apply.call(console[level], console, argsWithFormat);
  }
}

// -----------------------------------------------------------------------------

var enableScopeAPI = false; // Experimental Create Event Handle API.
var enableCacheElement = false;
var enableTransitionTracing = false; // No known bugs, but needs performance testing

var enableLegacyHidden = false; // Enables unstable_avoidThisFallback feature in Fiber
// stuff. Intended to enable React core members to more easily debug scheduling
// issues in DEV builds.

var enableDebugTracing = false; // Track which Fiber(s) schedule render work.

var REACT_MODULE_REFERENCE;

{
  REACT_MODULE_REFERENCE = Symbol.for('react.module.reference');
}

function isValidElementType(type) {
  if (typeof type === 'string' || typeof type === 'function') {
    return true;
  } // Note: typeof might be other than 'symbol' or 'number' (e.g. if it's a polyfill).


  if (type === REACT_FRAGMENT_TYPE || type === REACT_PROFILER_TYPE || enableDebugTracing  || type === REACT_STRICT_MODE_TYPE || type === REACT_SUSPENSE_TYPE || type === REACT_SUSPENSE_LIST_TYPE || enableLegacyHidden  || type === REACT_OFFSCREEN_TYPE || enableScopeAPI  || enableCacheElement  || enableTransitionTracing ) {
    return true;
  }

  if (typeof type === 'object' && type !== null) {
    if (type.$$typeof === REACT_LAZY_TYPE || type.$$typeof === REACT_MEMO_TYPE || type.$$typeof === REACT_PROVIDER_TYPE || type.$$typeof === REACT_CONTEXT_TYPE || type.$$typeof === REACT_FORWARD_REF_TYPE || // This needs to include all possible module reference object
    // types supported by any Flight configuration anywhere since
    // we don't know which Flight build this will end up being used
    // with.
    type.$$typeof === REACT_MODULE_REFERENCE || type.getModuleId !== undefined) {
      return true;
    }
  }

  return false;
}

function getWrappedName(outerType, innerType, wrapperName) {
  var displayName = outerType.displayName;

  if (displayName) {
    return displayName;
  }

  var functionName = innerType.displayName || innerType.name || '';
  return functionName !== '' ? wrapperName + "(" + functionName + ")" : wrapperName;
} // Keep in sync with react-reconciler/getComponentNameFromFiber


function getContextName(type) {
  return type.displayName || 'Context';
} // Note that the reconciler package should generally prefer to use getComponentNameFromFiber() instead.


function getComponentNameFromType(type) {
  if (type == null) {
    // Host root, text node or just invalid type.
    return null;
  }

  {
    if (typeof type.tag === 'number') {
      error('Received an unexpected object in getComponentNameFromType(). ' + 'This is likely a bug in React. Please file an issue.');
    }
  }

  if (typeof type === 'function') {
    return type.displayName || type.name || null;
  }

  if (typeof type === 'string') {
    return type;
  }

  switch (type) {
    case REACT_FRAGMENT_TYPE:
      return 'Fragment';

    case REACT_PORTAL_TYPE:
      return 'Portal';

    case REACT_PROFILER_TYPE:
      return 'Profiler';

    case REACT_STRICT_MODE_TYPE:
      return 'StrictMode';

    case REACT_SUSPENSE_TYPE:
      return 'Suspense';

    case REACT_SUSPENSE_LIST_TYPE:
      return 'SuspenseList';

  }

  if (typeof type === 'object') {
    switch (type.$$typeof) {
      case REACT_CONTEXT_TYPE:
        var context = type;
        return getContextName(context) + '.Consumer';

      case REACT_PROVIDER_TYPE:
        var provider = type;
        return getContextName(provider._context) + '.Provider';

      case REACT_FORWARD_REF_TYPE:
        return getWrappedName(type, type.render, 'ForwardRef');

      case REACT_MEMO_TYPE:
        var outerName = type.displayName || null;

        if (outerName !== null) {
          return outerName;
        }

        return getComponentNameFromType(type.type) || 'Memo';

      case REACT_LAZY_TYPE:
        {
          var lazyComponent = type;
          var payload = lazyComponent._payload;
          var init = lazyComponent._init;

          try {
            return getComponentNameFromType(init(payload));
          } catch (x) {
            return null;
          }
        }

      // eslint-disable-next-line no-fallthrough
    }
  }

  return null;
}

var assign = Object.assign;

// Helpers to patch console.logs to avoid logging during side-effect free
// replaying on render function. This currently only patches the object
// lazily which won't cover if the log function was extracted eagerly.
// We could also eagerly patch the method.
var disabledDepth = 0;
var prevLog;
var prevInfo;
var prevWarn;
var prevError;
var prevGroup;
var prevGroupCollapsed;
var prevGroupEnd;

function disabledLog() {}

disabledLog.__reactDisabledLog = true;
function disableLogs() {
  {
    if (disabledDepth === 0) {
      /* eslint-disable react-internal/no-production-logging */
      prevLog = console.log;
      prevInfo = console.info;
      prevWarn = console.warn;
      prevError = console.error;
      prevGroup = console.group;
      prevGroupCollapsed = console.groupCollapsed;
      prevGroupEnd = console.groupEnd; // https://github.com/facebook/react/issues/19099

      var props = {
        configurable: true,
        enumerable: true,
        value: disabledLog,
        writable: true
      }; // $FlowFixMe Flow thinks console is immutable.

      Object.defineProperties(console, {
        info: props,
        log: props,
        warn: props,
        error: props,
        group: props,
        groupCollapsed: props,
        groupEnd: props
      });
      /* eslint-enable react-internal/no-production-logging */
    }

    disabledDepth++;
  }
}
function reenableLogs() {
  {
    disabledDepth--;

    if (disabledDepth === 0) {
      /* eslint-disable react-internal/no-production-logging */
      var props = {
        configurable: true,
        enumerable: true,
        writable: true
      }; // $FlowFixMe Flow thinks console is immutable.

      Object.defineProperties(console, {
        log: assign({}, props, {
          value: prevLog
        }),
        info: assign({}, props, {
          value: prevInfo
        }),
        warn: assign({}, props, {
          value: prevWarn
        }),
        error: assign({}, props, {
          value: prevError
        }),
        group: assign({}, props, {
          value: prevGroup
        }),
        groupCollapsed: assign({}, props, {
          value: prevGroupCollapsed
        }),
        groupEnd: assign({}, props, {
          value: prevGroupEnd
        })
      });
      /* eslint-enable react-internal/no-production-logging */
    }

    if (disabledDepth < 0) {
      error('disabledDepth fell below zero. ' + 'This is a bug in React. Please file an issue.');
    }
  }
}

var ReactCurrentDispatcher = ReactSharedInternals.ReactCurrentDispatcher;
var prefix;
function describeBuiltInComponentFrame(name, source, ownerFn) {
  {
    if (prefix === undefined) {
      // Extract the VM specific prefix used by each line.
      try {
        throw Error();
      } catch (x) {
        var match = x.stack.trim().match(/\n( *(at )?)/);
        prefix = match && match[1] || '';
      }
    } // We use the prefix to ensure our stacks line up with native stack frames.


    return '\n' + prefix + name;
  }
}
var reentry = false;
var componentFrameCache;

{
  var PossiblyWeakMap = typeof WeakMap === 'function' ? WeakMap : Map;
  componentFrameCache = new PossiblyWeakMap();
}

function describeNativeComponentFrame(fn, construct) {
  // If something asked for a stack inside a fake render, it should get ignored.
  if ( !fn || reentry) {
    return '';
  }

  {
    var frame = componentFrameCache.get(fn);

    if (frame !== undefined) {
      return frame;
    }
  }

  var control;
  reentry = true;
  var previousPrepareStackTrace = Error.prepareStackTrace; // $FlowFixMe It does accept undefined.

  Error.prepareStackTrace = undefined;
  var previousDispatcher;

  {
    previousDispatcher = ReactCurrentDispatcher.current; // Set the dispatcher in DEV because this might be call in the render function
    // for warnings.

    ReactCurrentDispatcher.current = null;
    disableLogs();
  }

  try {
    // This should throw.
    if (construct) {
      // Something should be setting the props in the constructor.
      var Fake = function () {
        throw Error();
      }; // $FlowFixMe


      Object.defineProperty(Fake.prototype, 'props', {
        set: function () {
          // We use a throwing setter instead of frozen or non-writable props
          // because that won't throw in a non-strict mode function.
          throw Error();
        }
      });

      if (typeof Reflect === 'object' && Reflect.construct) {
        // We construct a different control for this case to include any extra
        // frames added by the construct call.
        try {
          Reflect.construct(Fake, []);
        } catch (x) {
          control = x;
        }

        Reflect.construct(fn, [], Fake);
      } else {
        try {
          Fake.call();
        } catch (x) {
          control = x;
        }

        fn.call(Fake.prototype);
      }
    } else {
      try {
        throw Error();
      } catch (x) {
        control = x;
      }

      fn();
    }
  } catch (sample) {
    // This is inlined manually because closure doesn't do it for us.
    if (sample && control && typeof sample.stack === 'string') {
      // This extracts the first frame from the sample that isn't also in the control.
      // Skipping one frame that we assume is the frame that calls the two.
      var sampleLines = sample.stack.split('\n');
      var controlLines = control.stack.split('\n');
      var s = sampleLines.length - 1;
      var c = controlLines.length - 1;

      while (s >= 1 && c >= 0 && sampleLines[s] !== controlLines[c]) {
        // We expect at least one stack frame to be shared.
        // Typically this will be the root most one. However, stack frames may be
        // cut off due to maximum stack limits. In this case, one maybe cut off
        // earlier than the other. We assume that the sample is longer or the same
        // and there for cut off earlier. So we should find the root most frame in
        // the sample somewhere in the control.
        c--;
      }

      for (; s >= 1 && c >= 0; s--, c--) {
        // Next we find the first one that isn't the same which should be the
        // frame that called our sample function and the control.
        if (sampleLines[s] !== controlLines[c]) {
          // In V8, the first line is describing the message but other VMs don't.
          // If we're about to return the first line, and the control is also on the same
          // line, that's a pretty good indicator that our sample threw at same line as
          // the control. I.e. before we entered the sample frame. So we ignore this result.
          // This can happen if you passed a class to function component, or non-function.
          if (s !== 1 || c !== 1) {
            do {
              s--;
              c--; // We may still have similar intermediate frames from the construct call.
              // The next one that isn't the same should be our match though.

              if (c < 0 || sampleLines[s] !== controlLines[c]) {
                // V8 adds a "new" prefix for native classes. Let's remove it to make it prettier.
                var _frame = '\n' + sampleLines[s].replace(' at new ', ' at '); // If our component frame is labeled "<anonymous>"
                // but we have a user-provided "displayName"
                // splice it in to make the stack more readable.


                if (fn.displayName && _frame.includes('<anonymous>')) {
                  _frame = _frame.replace('<anonymous>', fn.displayName);
                }

                {
                  if (typeof fn === 'function') {
                    componentFrameCache.set(fn, _frame);
                  }
                } // Return the line we found.


                return _frame;
              }
            } while (s >= 1 && c >= 0);
          }

          break;
        }
      }
    }
  } finally {
    reentry = false;

    {
      ReactCurrentDispatcher.current = previousDispatcher;
      reenableLogs();
    }

    Error.prepareStackTrace = previousPrepareStackTrace;
  } // Fallback to just using the name if we couldn't make it throw.


  var name = fn ? fn.displayName || fn.name : '';
  var syntheticFrame = name ? describeBuiltInComponentFrame(name) : '';

  {
    if (typeof fn === 'function') {
      componentFrameCache.set(fn, syntheticFrame);
    }
  }

  return syntheticFrame;
}
function describeFunctionComponentFrame(fn, source, ownerFn) {
  {
    return describeNativeComponentFrame(fn, false);
  }
}

function shouldConstruct(Component) {
  var prototype = Component.prototype;
  return !!(prototype && prototype.isReactComponent);
}

function describeUnknownElementTypeFrameInDEV(type, source, ownerFn) {

  if (type == null) {
    return '';
  }

  if (typeof type === 'function') {
    {
      return describeNativeComponentFrame(type, shouldConstruct(type));
    }
  }

  if (typeof type === 'string') {
    return describeBuiltInComponentFrame(type);
  }

  switch (type) {
    case REACT_SUSPENSE_TYPE:
      return describeBuiltInComponentFrame('Suspense');

    case REACT_SUSPENSE_LIST_TYPE:
      return describeBuiltInComponentFrame('SuspenseList');
  }

  if (typeof type === 'object') {
    switch (type.$$typeof) {
      case REACT_FORWARD_REF_TYPE:
        return describeFunctionComponentFrame(type.render);

      case REACT_MEMO_TYPE:
        // Memo may contain any component type so we recursively resolve it.
        return describeUnknownElementTypeFrameInDEV(type.type, source, ownerFn);

      case REACT_LAZY_TYPE:
        {
          var lazyComponent = type;
          var payload = lazyComponent._payload;
          var init = lazyComponent._init;

          try {
            // Lazy may contain any component type so we recursively resolve it.
            return describeUnknownElementTypeFrameInDEV(init(payload), source, ownerFn);
          } catch (x) {}
        }
    }
  }

  return '';
}

var hasOwnProperty = Object.prototype.hasOwnProperty;

var loggedTypeFailures = {};
var ReactDebugCurrentFrame = ReactSharedInternals.ReactDebugCurrentFrame;

function setCurrentlyValidatingElement(element) {
  {
    if (element) {
      var owner = element._owner;
      var stack = describeUnknownElementTypeFrameInDEV(element.type, element._source, owner ? owner.type : null);
      ReactDebugCurrentFrame.setExtraStackFrame(stack);
    } else {
      ReactDebugCurrentFrame.setExtraStackFrame(null);
    }
  }
}

function checkPropTypes(typeSpecs, values, location, componentName, element) {
  {
    // $FlowFixMe This is okay but Flow doesn't know it.
    var has = Function.call.bind(hasOwnProperty);

    for (var typeSpecName in typeSpecs) {
      if (has(typeSpecs, typeSpecName)) {
        var error$1 = void 0; // Prop type validation may throw. In case they do, we don't want to
        // fail the render phase where it didn't fail before. So we log it.
        // After these have been cleaned up, we'll let them throw.

        try {
          // This is intentionally an invariant that gets caught. It's the same
          // behavior as without this statement except with a better message.
          if (typeof typeSpecs[typeSpecName] !== 'function') {
            // eslint-disable-next-line react-internal/prod-error-codes
            var err = Error((componentName || 'React class') + ': ' + location + ' type `' + typeSpecName + '` is invalid; ' + 'it must be a function, usually from the `prop-types` package, but received `' + typeof typeSpecs[typeSpecName] + '`.' + 'This often happens because of typos such as `PropTypes.function` instead of `PropTypes.func`.');
            err.name = 'Invariant Violation';
            throw err;
          }

          error$1 = typeSpecs[typeSpecName](values, typeSpecName, componentName, location, null, 'SECRET_DO_NOT_PASS_THIS_OR_YOU_WILL_BE_FIRED');
        } catch (ex) {
          error$1 = ex;
        }

        if (error$1 && !(error$1 instanceof Error)) {
          setCurrentlyValidatingElement(element);

          error('%s: type specification of %s' + ' `%s` is invalid; the type checker ' + 'function must return `null` or an `Error` but returned a %s. ' + 'You may have forgotten to pass an argument to the type checker ' + 'creator (arrayOf, instanceOf, objectOf, oneOf, oneOfType, and ' + 'shape all require an argument).', componentName || 'React class', location, typeSpecName, typeof error$1);

          setCurrentlyValidatingElement(null);
        }

        if (error$1 instanceof Error && !(error$1.message in loggedTypeFailures)) {
          // Only monitor this failure once because there tends to be a lot of the
          // same error.
          loggedTypeFailures[error$1.message] = true;
          setCurrentlyValidatingElement(element);

          error('Failed %s type: %s', location, error$1.message);

          setCurrentlyValidatingElement(null);
        }
      }
    }
  }
}

var isArrayImpl = Array.isArray; // eslint-disable-next-line no-redeclare

function isArray(a) {
  return isArrayImpl(a);
}

/*
 * The `'' + value` pattern (used in in perf-sensitive code) throws for Symbol
 * and Temporal.* types. See https://github.com/facebook/react/pull/22064.
 *
 * The functions in this module will throw an easier-to-understand,
 * easier-to-debug exception with a clear errors message message explaining the
 * problem. (Instead of a confusing exception thrown inside the implementation
 * of the `value` object).
 */
// $FlowFixMe only called in DEV, so void return is not possible.
function typeName(value) {
  {
    // toStringTag is needed for namespaced types like Temporal.Instant
    var hasToStringTag = typeof Symbol === 'function' && Symbol.toStringTag;
    var type = hasToStringTag && value[Symbol.toStringTag] || value.constructor.name || 'Object';
    return type;
  }
} // $FlowFixMe only called in DEV, so void return is not possible.


function willCoercionThrow(value) {
  {
    try {
      testStringCoercion(value);
      return false;
    } catch (e) {
      return true;
    }
  }
}

function testStringCoercion(value) {
  // If you ended up here by following an exception call stack, here's what's
  // happened: you supplied an object or symbol value to React (as a prop, key,
  // DOM attribute, CSS property, string ref, etc.) and when React tried to
  // coerce it to a string using `'' + value`, an exception was thrown.
  //
  // The most common types that will cause this exception are `Symbol` instances
  // and Temporal objects like `Temporal.Instant`. But any object that has a
  // `valueOf` or `[Symbol.toPrimitive]` method that throws will also cause this
  // exception. (Library authors do this to prevent users from using built-in
  // numeric operators like `+` or comparison operators like `>=` because custom
  // methods are needed to perform accurate arithmetic or comparison.)
  //
  // To fix the problem, coerce this object or symbol value to a string before
  // passing it to React. The most reliable way is usually `String(value)`.
  //
  // To find which value is throwing, check the browser or debugger console.
  // Before this exception was thrown, there should be `console.error` output
  // that shows the type (Symbol, Temporal.PlainDate, etc.) that caused the
  // problem and how that type was used: key, atrribute, input value prop, etc.
  // In most cases, this console output also shows the component and its
  // ancestor components where the exception happened.
  //
  // eslint-disable-next-line react-internal/safe-string-coercion
  return '' + value;
}
function checkKeyStringCoercion(value) {
  {
    if (willCoercionThrow(value)) {
      error('The provided key is an unsupported type %s.' + ' This value must be coerced to a string before before using it here.', typeName(value));

      return testStringCoercion(value); // throw (to help callers find troubleshooting comments)
    }
  }
}

var ReactCurrentOwner = ReactSharedInternals.ReactCurrentOwner;
var RESERVED_PROPS = {
  key: true,
  ref: true,
  __self: true,
  __source: true
};
var specialPropKeyWarningShown;
var specialPropRefWarningShown;
var didWarnAboutStringRefs;

{
  didWarnAboutStringRefs = {};
}

function hasValidRef(config) {
  {
    if (hasOwnProperty.call(config, 'ref')) {
      var getter = Object.getOwnPropertyDescriptor(config, 'ref').get;

      if (getter && getter.isReactWarning) {
        return false;
      }
    }
  }

  return config.ref !== undefined;
}

function hasValidKey(config) {
  {
    if (hasOwnProperty.call(config, 'key')) {
      var getter = Object.getOwnPropertyDescriptor(config, 'key').get;

      if (getter && getter.isReactWarning) {
        return false;
      }
    }
  }

  return config.key !== undefined;
}

function warnIfStringRefCannotBeAutoConverted(config, self) {
  {
    if (typeof config.ref === 'string' && ReactCurrentOwner.current && self && ReactCurrentOwner.current.stateNode !== self) {
      var componentName = getComponentNameFromType(ReactCurrentOwner.current.type);

      if (!didWarnAboutStringRefs[componentName]) {
        error('Component "%s" contains the string ref "%s". ' + 'Support for string refs will be removed in a future major release. ' + 'This case cannot be automatically converted to an arrow function. ' + 'We ask you to manually fix this case by using useRef() or createRef() instead. ' + 'Learn more about using refs safely here: ' + 'https://reactjs.org/link/strict-mode-string-ref', getComponentNameFromType(ReactCurrentOwner.current.type), config.ref);

        didWarnAboutStringRefs[componentName] = true;
      }
    }
  }
}

function defineKeyPropWarningGetter(props, displayName) {
  {
    var warnAboutAccessingKey = function () {
      if (!specialPropKeyWarningShown) {
        specialPropKeyWarningShown = true;

        error('%s: `key` is not a prop. Trying to access it will result ' + 'in `undefined` being returned. If you need to access the same ' + 'value within the child component, you should pass it as a different ' + 'prop. (https://reactjs.org/link/special-props)', displayName);
      }
    };

    warnAboutAccessingKey.isReactWarning = true;
    Object.defineProperty(props, 'key', {
      get: warnAboutAccessingKey,
      configurable: true
    });
  }
}

function defineRefPropWarningGetter(props, displayName) {
  {
    var warnAboutAccessingRef = function () {
      if (!specialPropRefWarningShown) {
        specialPropRefWarningShown = true;

        error('%s: `ref` is not a prop. Trying to access it will result ' + 'in `undefined` being returned. If you need to access the same ' + 'value within the child component, you should pass it as a different ' + 'prop. (https://reactjs.org/link/special-props)', displayName);
      }
    };

    warnAboutAccessingRef.isReactWarning = true;
    Object.defineProperty(props, 'ref', {
      get: warnAboutAccessingRef,
      configurable: true
    });
  }
}
/**
 * Factory method to create a new React element. This no longer adheres to
 * the class pattern, so do not use new to call it. Also, instanceof check
 * will not work. Instead test $$typeof field against Symbol.for('react.element') to check
 * if something is a React Element.
 *
 * @param {*} type
 * @param {*} props
 * @param {*} key
 * @param {string|object} ref
 * @param {*} owner
 * @param {*} self A *temporary* helper to detect places where `this` is
 * different from the `owner` when React.createElement is called, so that we
 * can warn. We want to get rid of owner and replace string `ref`s with arrow
 * functions, and as long as `this` and owner are the same, there will be no
 * change in behavior.
 * @param {*} source An annotation object (added by a transpiler or otherwise)
 * indicating filename, line number, and/or other information.
 * @internal
 */


var ReactElement = function (type, key, ref, self, source, owner, props) {
  var element = {
    // This tag allows us to uniquely identify this as a React Element
    $$typeof: REACT_ELEMENT_TYPE,
    // Built-in properties that belong on the element
    type: type,
    key: key,
    ref: ref,
    props: props,
    // Record the component responsible for creating this element.
    _owner: owner
  };

  {
    // The validation flag is currently mutative. We put it on
    // an external backing store so that we can freeze the whole object.
    // This can be replaced with a WeakMap once they are implemented in
    // commonly used development environments.
    element._store = {}; // To make comparing ReactElements easier for testing purposes, we make
    // the validation flag non-enumerable (where possible, which should
    // include every environment we run tests in), so the test framework
    // ignores it.

    Object.defineProperty(element._store, 'validated', {
      configurable: false,
      enumerable: false,
      writable: true,
      value: false
    }); // self and source are DEV only properties.

    Object.defineProperty(element, '_self', {
      configurable: false,
      enumerable: false,
      writable: false,
      value: self
    }); // Two elements created in two different places should be considered
    // equal for testing purposes and therefore we hide it from enumeration.

    Object.defineProperty(element, '_source', {
      configurable: false,
      enumerable: false,
      writable: false,
      value: source
    });

    if (Object.freeze) {
      Object.freeze(element.props);
      Object.freeze(element);
    }
  }

  return element;
};
/**
 * https://github.com/reactjs/rfcs/pull/107
 * @param {*} type
 * @param {object} props
 * @param {string} key
 */

function jsxDEV(type, config, maybeKey, source, self) {
  {
    var propName; // Reserved names are extracted

    var props = {};
    var key = null;
    var ref = null; // Currently, key can be spread in as a prop. This causes a potential
    // issue if key is also explicitly declared (ie. <div {...props} key="Hi" />
    // or <div key="Hi" {...props} /> ). We want to deprecate key spread,
    // but as an intermediary step, we will use jsxDEV for everything except
    // <div {...props} key="Hi" />, because we aren't currently able to tell if
    // key is explicitly declared to be undefined or not.

    if (maybeKey !== undefined) {
      {
        checkKeyStringCoercion(maybeKey);
      }

      key = '' + maybeKey;
    }

    if (hasValidKey(config)) {
      {
        checkKeyStringCoercion(config.key);
      }

      key = '' + config.key;
    }

    if (hasValidRef(config)) {
      ref = config.ref;
      warnIfStringRefCannotBeAutoConverted(config, self);
    } // Remaining properties are added to a new props object


    for (propName in config) {
      if (hasOwnProperty.call(config, propName) && !RESERVED_PROPS.hasOwnProperty(propName)) {
        props[propName] = config[propName];
      }
    } // Resolve default props


    if (type && type.defaultProps) {
      var defaultProps = type.defaultProps;

      for (propName in defaultProps) {
        if (props[propName] === undefined) {
          props[propName] = defaultProps[propName];
        }
      }
    }

    if (key || ref) {
      var displayName = typeof type === 'function' ? type.displayName || type.name || 'Unknown' : type;

      if (key) {
        defineKeyPropWarningGetter(props, displayName);
      }

      if (ref) {
        defineRefPropWarningGetter(props, displayName);
      }
    }

    return ReactElement(type, key, ref, self, source, ReactCurrentOwner.current, props);
  }
}

var ReactCurrentOwner$1 = ReactSharedInternals.ReactCurrentOwner;
var ReactDebugCurrentFrame$1 = ReactSharedInternals.ReactDebugCurrentFrame;

function setCurrentlyValidatingElement$1(element) {
  {
    if (element) {
      var owner = element._owner;
      var stack = describeUnknownElementTypeFrameInDEV(element.type, element._source, owner ? owner.type : null);
      ReactDebugCurrentFrame$1.setExtraStackFrame(stack);
    } else {
      ReactDebugCurrentFrame$1.setExtraStackFrame(null);
    }
  }
}

var propTypesMisspellWarningShown;

{
  propTypesMisspellWarningShown = false;
}
/**
 * Verifies the object is a ReactElement.
 * See https://reactjs.org/docs/react-api.html#isvalidelement
 * @param {?object} object
 * @return {boolean} True if `object` is a ReactElement.
 * @final
 */


function isValidElement(object) {
  {
    return typeof object === 'object' && object !== null && object.$$typeof === REACT_ELEMENT_TYPE;
  }
}

function getDeclarationErrorAddendum() {
  {
    if (ReactCurrentOwner$1.current) {
      var name = getComponentNameFromType(ReactCurrentOwner$1.current.type);

      if (name) {
        return '\n\nCheck the render method of `' + name + '`.';
      }
    }

    return '';
  }
}

function getSourceInfoErrorAddendum(source) {
  {
    if (source !== undefined) {
      var fileName = source.fileName.replace(/^.*[\\\/]/, '');
      var lineNumber = source.lineNumber;
      return '\n\nCheck your code at ' + fileName + ':' + lineNumber + '.';
    }

    return '';
  }
}
/**
 * Warn if there's no key explicitly set on dynamic arrays of children or
 * object keys are not valid. This allows us to keep track of children between
 * updates.
 */


var ownerHasKeyUseWarning = {};

function getCurrentComponentErrorInfo(parentType) {
  {
    var info = getDeclarationErrorAddendum();

    if (!info) {
      var parentName = typeof parentType === 'string' ? parentType : parentType.displayName || parentType.name;

      if (parentName) {
        info = "\n\nCheck the top-level render call using <" + parentName + ">.";
      }
    }

    return info;
  }
}
/**
 * Warn if the element doesn't have an explicit key assigned to it.
 * This element is in an array. The array could grow and shrink or be
 * reordered. All children that haven't already been validated are required to
 * have a "key" property assigned to it. Error statuses are cached so a warning
 * will only be shown once.
 *
 * @internal
 * @param {ReactElement} element Element that requires a key.
 * @param {*} parentType element's parent's type.
 */


function validateExplicitKey(element, parentType) {
  {
    if (!element._store || element._store.validated || element.key != null) {
      return;
    }

    element._store.validated = true;
    var currentComponentErrorInfo = getCurrentComponentErrorInfo(parentType);

    if (ownerHasKeyUseWarning[currentComponentErrorInfo]) {
      return;
    }

    ownerHasKeyUseWarning[currentComponentErrorInfo] = true; // Usually the current owner is the offender, but if it accepts children as a
    // property, it may be the creator of the child that's responsible for
    // assigning it a key.

    var childOwner = '';

    if (element && element._owner && element._owner !== ReactCurrentOwner$1.current) {
      // Give the component that originally created this child.
      childOwner = " It was passed a child from " + getComponentNameFromType(element._owner.type) + ".";
    }

    setCurrentlyValidatingElement$1(element);

    error('Each child in a list should have a unique "key" prop.' + '%s%s See https://reactjs.org/link/warning-keys for more information.', currentComponentErrorInfo, childOwner);

    setCurrentlyValidatingElement$1(null);
  }
}
/**
 * Ensure that every element either is passed in a static location, in an
 * array with an explicit keys property defined, or in an object literal
 * with valid key property.
 *
 * @internal
 * @param {ReactNode} node Statically passed child of any type.
 * @param {*} parentType node's parent's type.
 */


function validateChildKeys(node, parentType) {
  {
    if (typeof node !== 'object') {
      return;
    }

    if (isArray(node)) {
      for (var i = 0; i < node.length; i++) {
        var child = node[i];

        if (isValidElement(child)) {
          validateExplicitKey(child, parentType);
        }
      }
    } else if (isValidElement(node)) {
      // This element was passed in a valid location.
      if (node._store) {
        node._store.validated = true;
      }
    } else if (node) {
      var iteratorFn = getIteratorFn(node);

      if (typeof iteratorFn === 'function') {
        // Entry iterators used to provide implicit keys,
        // but now we print a separate warning for them later.
        if (iteratorFn !== node.entries) {
          var iterator = iteratorFn.call(node);
          var step;

          while (!(step = iterator.next()).done) {
            if (isValidElement(step.value)) {
              validateExplicitKey(step.value, parentType);
            }
          }
        }
      }
    }
  }
}
/**
 * Given an element, validate that its props follow the propTypes definition,
 * provided by the type.
 *
 * @param {ReactElement} element
 */


function validatePropTypes(element) {
  {
    var type = element.type;

    if (type === null || type === undefined || typeof type === 'string') {
      return;
    }

    var propTypes;

    if (typeof type === 'function') {
      propTypes = type.propTypes;
    } else if (typeof type === 'object' && (type.$$typeof === REACT_FORWARD_REF_TYPE || // Note: Memo only checks outer props here.
    // Inner props are checked in the reconciler.
    type.$$typeof === REACT_MEMO_TYPE)) {
      propTypes = type.propTypes;
    } else {
      return;
    }

    if (propTypes) {
      // Intentionally inside to avoid triggering lazy initializers:
      var name = getComponentNameFromType(type);
      checkPropTypes(propTypes, element.props, 'prop', name, element);
    } else if (type.PropTypes !== undefined && !propTypesMisspellWarningShown) {
      propTypesMisspellWarningShown = true; // Intentionally inside to avoid triggering lazy initializers:

      var _name = getComponentNameFromType(type);

      error('Component %s declared `PropTypes` instead of `propTypes`. Did you misspell the property assignment?', _name || 'Unknown');
    }

    if (typeof type.getDefaultProps === 'function' && !type.getDefaultProps.isReactClassApproved) {
      error('getDefaultProps is only used on classic React.createClass ' + 'definitions. Use a static property named `defaultProps` instead.');
    }
  }
}
/**
 * Given a fragment, validate that it can only be provided with fragment props
 * @param {ReactElement} fragment
 */


function validateFragmentProps(fragment) {
  {
    var keys = Object.keys(fragment.props);

    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];

      if (key !== 'children' && key !== 'key') {
        setCurrentlyValidatingElement$1(fragment);

        error('Invalid prop `%s` supplied to `React.Fragment`. ' + 'React.Fragment can only have `key` and `children` props.', key);

        setCurrentlyValidatingElement$1(null);
        break;
      }
    }

    if (fragment.ref !== null) {
      setCurrentlyValidatingElement$1(fragment);

      error('Invalid attribute `ref` supplied to `React.Fragment`.');

      setCurrentlyValidatingElement$1(null);
    }
  }
}

var didWarnAboutKeySpread = {};
function jsxWithValidation(type, props, key, isStaticChildren, source, self) {
  {
    var validType = isValidElementType(type); // We warn in this case but don't throw. We expect the element creation to
    // succeed and there will likely be errors in render.

    if (!validType) {
      var info = '';

      if (type === undefined || typeof type === 'object' && type !== null && Object.keys(type).length === 0) {
        info += ' You likely forgot to export your component from the file ' + "it's defined in, or you might have mixed up default and named imports.";
      }

      var sourceInfo = getSourceInfoErrorAddendum(source);

      if (sourceInfo) {
        info += sourceInfo;
      } else {
        info += getDeclarationErrorAddendum();
      }

      var typeString;

      if (type === null) {
        typeString = 'null';
      } else if (isArray(type)) {
        typeString = 'array';
      } else if (type !== undefined && type.$$typeof === REACT_ELEMENT_TYPE) {
        typeString = "<" + (getComponentNameFromType(type.type) || 'Unknown') + " />";
        info = ' Did you accidentally export a JSX literal instead of a component?';
      } else {
        typeString = typeof type;
      }

      error('React.jsx: type is invalid -- expected a string (for ' + 'built-in components) or a class/function (for composite ' + 'components) but got: %s.%s', typeString, info);
    }

    var element = jsxDEV(type, props, key, source, self); // The result can be nullish if a mock or a custom function is used.
    // TODO: Drop this when these are no longer allowed as the type argument.

    if (element == null) {
      return element;
    } // Skip key warning if the type isn't valid since our key validation logic
    // doesn't expect a non-string/function type and can throw confusing errors.
    // We don't want exception behavior to differ between dev and prod.
    // (Rendering will throw with a helpful message and as soon as the type is
    // fixed, the key warnings will appear.)


    if (validType) {
      var children = props.children;

      if (children !== undefined) {
        if (isStaticChildren) {
          if (isArray(children)) {
            for (var i = 0; i < children.length; i++) {
              validateChildKeys(children[i], type);
            }

            if (Object.freeze) {
              Object.freeze(children);
            }
          } else {
            error('React.jsx: Static children should always be an array. ' + 'You are likely explicitly calling React.jsxs or React.jsxDEV. ' + 'Use the Babel transform instead.');
          }
        } else {
          validateChildKeys(children, type);
        }
      }
    }

    {
      if (hasOwnProperty.call(props, 'key')) {
        var componentName = getComponentNameFromType(type);
        var keys = Object.keys(props).filter(function (k) {
          return k !== 'key';
        });
        var beforeExample = keys.length > 0 ? '{key: someKey, ' + keys.join(': ..., ') + ': ...}' : '{key: someKey}';

        if (!didWarnAboutKeySpread[componentName + beforeExample]) {
          var afterExample = keys.length > 0 ? '{' + keys.join(': ..., ') + ': ...}' : '{}';

          error('A props object containing a "key" prop is being spread into JSX:\n' + '  let props = %s;\n' + '  <%s {...props} />\n' + 'React keys must be passed directly to JSX without using spread:\n' + '  let props = %s;\n' + '  <%s key={someKey} {...props} />', beforeExample, componentName, afterExample, componentName);

          didWarnAboutKeySpread[componentName + beforeExample] = true;
        }
      }
    }

    if (type === REACT_FRAGMENT_TYPE) {
      validateFragmentProps(element);
    } else {
      validatePropTypes(element);
    }

    return element;
  }
} // These two functions exist to still get child warnings in dev
// even with the prod transform. This means that jsxDEV is purely
// opt-in behavior for better messages but that we won't stop
// giving you warnings if you use production apis.

function jsxWithValidationStatic(type, props, key) {
  {
    return jsxWithValidation(type, props, key, true);
  }
}
function jsxWithValidationDynamic(type, props, key) {
  {
    return jsxWithValidation(type, props, key, false);
  }
}

var jsx =  jsxWithValidationDynamic ; // we may want to special case jsxs internally to take advantage of static children.
// for now we can ship identical prod functions

var jsxs =  jsxWithValidationStatic ;

exports.Fragment = REACT_FRAGMENT_TYPE;
exports.jsx = jsx;
exports.jsxs = jsxs;
  })();
}


/***/ }),

/***/ "./node_modules/react/jsx-runtime.js":
/*!*******************************************!*\
  !*** ./node_modules/react/jsx-runtime.js ***!
  \*******************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



if (false) {} else {
  module.exports = __webpack_require__(/*! ./cjs/react-jsx-runtime.development.js */ "./node_modules/react/cjs/react-jsx-runtime.development.js");
}


/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ }),

/***/ "@woocommerce/blocks-checkout":
/*!****************************************!*\
  !*** external ["wc","blocksCheckout"] ***!
  \****************************************/
/***/ ((module) => {

module.exports = window["wc"]["blocksCheckout"];

/***/ }),

/***/ "@woocommerce/blocks-registry":
/*!******************************************!*\
  !*** external ["wc","wcBlocksRegistry"] ***!
  \******************************************/
/***/ ((module) => {

module.exports = window["wc"]["wcBlocksRegistry"];

/***/ }),

/***/ "@woocommerce/settings":
/*!************************************!*\
  !*** external ["wc","wcSettings"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wc"]["wcSettings"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*************************!*\
  !*** ./client/index.js ***!
  \*************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @woocommerce/blocks-checkout */ "@woocommerce/blocks-checkout");
/* harmony import */ var _woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @woocommerce/blocks-registry */ "@woocommerce/blocks-registry");
/* harmony import */ var _woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _blocks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./blocks */ "./client/blocks/index.js");



(0,_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_1__.registerPaymentMethod)(_blocks__WEBPACK_IMPORTED_MODULE_2__.RevolutGatewayBlock);
(0,_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_1__.registerPaymentMethod)(_blocks__WEBPACK_IMPORTED_MODULE_2__.RevolutPayByBankBlock);
(0,_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_1__.registerExpressPaymentMethod)(_blocks__WEBPACK_IMPORTED_MODULE_2__.RevolutPayExpressCheckoutBlock);
(0,_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_1__.registerPaymentMethod)(_blocks__WEBPACK_IMPORTED_MODULE_2__.RevolutPayBlock);
(0,_woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_0__.registerCheckoutBlock)(_blocks__WEBPACK_IMPORTED_MODULE_2__.RevPointsBannerBlock);
(0,_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_1__.registerExpressPaymentMethod)(_blocks__WEBPACK_IMPORTED_MODULE_2__.RevolutPaymentRequestExpressCheckoutBlock);
(0,_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_1__.registerPaymentMethod)(_blocks__WEBPACK_IMPORTED_MODULE_2__.RevolutPaymentRequestBlock);
})();

/******/ })()
;
//# sourceMappingURL=index.js.map
