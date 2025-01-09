(()=>{"use strict";var e={20:(e,t,r)=>{var n=r(609),o=Symbol.for("react.element"),a=Symbol.for("react.fragment"),s=Object.prototype.hasOwnProperty,c=n.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner,i={key:!0,ref:!0,__self:!0,__source:!0};function u(e,t,r){var n,a={},u=null,l=null;for(n in void 0!==r&&(u=""+r),void 0!==t.key&&(u=""+t.key),void 0!==t.ref&&(l=t.ref),t)s.call(t,n)&&!i.hasOwnProperty(n)&&(a[n]=t[n]);if(e&&e.defaultProps)for(n in t=e.defaultProps)void 0===a[n]&&(a[n]=t[n]);return{$$typeof:o,type:e,key:u,ref:l,props:a,_owner:c.current}}t.Fragment=a,t.jsx=u,t.jsxs=u},848:(e,t,r)=>{e.exports=r(20)},609:e=>{e.exports=window.React}},t={};function r(n){var o=t[n];if(void 0!==o)return o.exports;var a=t[n]={exports:{}};return e[n](a,a.exports,r),a.exports}(()=>{const e=window.wc.blocksCheckout,t=window.wc.wcSettings,n=window.wp.i18n,o=window.wp.data,a=e=>(0,t.getSetting)(`${e}_data`),s=e=>(0,n.__)(e,"revolut-gateway-for-woocommerce"),c=(e,t="revolut_payment_request_")=>wc_revolut_payment_request_params.ajax_url.toString().replace("%%wc_revolut_gateway_ajax_endpoint%%",`${t}${e}`);function i(e,t,r){const n=e;if(t&&"object"==typeof t)Object.keys(t).forEach((e=>{i(n,t[e],r?`${r}[${e}]`:e)}));else{const e=null==t?"":t;n.append(r,e)}return n}const u=async({data:e,endpoint:t})=>{const r=i(new FormData,e),n=await fetch(t,{method:"POST",body:r});if(!n.ok)throw new Error("Failed to process your request due to network issue");return await n.json()},l=e=>({countryCode:e.country,region:e.state,city:e.city,streetLine1:e.address_1,streetLine2:e.address_2,postcode:e.postcode}),d=async()=>{if("undefined"==typeof wc_revolut_payment_request_params)return Promise.reject(new Error("Unexpected error occurred"));const e=await u({endpoint:c("get_express_checkout_params"),data:{security:wc_revolut_payment_request_params.nonce.get_express_checkout_params}});return e?.success?(wc_revolut_payment_request_params.revolut_public_id=e.revolut_public_id,e.revolut_public_id):Promise.reject(new Error("Something went wrong while creating the payment."))},p=async e=>{const t=await u({endpoint:e.create_revolut_order_endpoint,data:{security:e.create_revolut_order_nonce}});if(t?.success)return t;throw new Error("An unexpected error occurred")},_=async()=>(await u({data:{revolut_public_id:wc_revolut_payment_request_params.revolut_public_id,security:wc_revolut_payment_request_params.nonce.cancel_order},endpoint:c("cancel_order")})).success,m=async()=>{try{if(await _())return{type:"error",message:s("Something went wrong, your order has been cancelled.")};throw new Error("Couldn`t cancel the order")}catch(e){return{type:"failure",message:s("Your order has been completed, but we couldn't redirect you to the confirmation page. Please contact us for assistance.")}}},y=async({response:e,paymentMethod:t,shouldSavePayment:r})=>{try{const{processingResponse:n}=e,{wc_order_id:o,revolut_public_id:s,process_payment_result:c}=n.paymentDetails,i=await(async({process_payment_result:e,revolut_public_id:t,shouldSavePayment:r,wc_order_id:n,paymentMethod:o})=>{try{const s=a(o),c={revolut_gateway:o,security:e,revolut_public_id:t,revolut_payment_error:"",wc_order_id:n,reload_checkout:0,revolut_save_payment_method:Number(r)||Number(s.is_save_payment_method_mandatory)},i=await u({data:c,endpoint:s.process_order_endpoint});if("fail"===i?.result)throw new Error(i?.messages||"Something went wrong while trying to charge your card, please try again");if("success"===i?.result)return i;throw new Error("Failed to process your order due to server issue")}catch(e){throw new Error(e.message||"An unexpected error occurred")}})({wc_order_id:o,revolut_public_id:s,process_payment_result:c,shouldSavePayment:r,paymentMethod:t});if(i.redirect)return window.location.href=decodeURI(i.redirect),{type:"success"};throw new Error("Could not redirect you to the confirmation page due to an unexpected error. Please contact the merchant")}catch(e){return{type:"error",message:s(e?.message),retry:!0,messageContext:"wc/checkout/payments"}}},h=async({onSubmit:e})=>new Promise(((t,r)=>(async()=>{try{const e=await u({data:{security:wc_revolut_payment_request_params.nonce.load_order_data,revolut_public_id:wc_revolut_payment_request_params.revolut_public_id},endpoint:c("load_order_data")});if(e)return e;throw new Error("Something went wrong while retrieving the billing address. your payment will be cancelled")}catch(e){throw new Error(e.message||"An unexpected error occurred.")}})().then((r=>{const{billingAddress:n,shippingAddress:a}=r.address_info;let s=n.recipient.indexOf(" "),c=n.recipient.substring(0,s),i=n.recipient.substring(s+1);(0,o.dispatch)(k).setBillingAddress({first_name:c,last_name:i,address_1:n.address,address_2:n.address_2,city:n.city,state:n.state,postcode:n.postcode,country:n.country,email:r.address_info.email,phone:n.phone}),(0,o.dispatch)(k).setShippingAddress({first_name:c,last_name:i,address_1:a.address,address_2:a.address_2,city:a.city,state:a.state,postcode:a.postcode,country:a.country,phone:a.phone}),e(),t(!0)})).catch((e=>r(e))))),{gatewayUpsellBannerEnabled:v,amount:w,locale:f,publicToken:g,currency:b}="undefined"!=typeof wc_revolut?wc_revolut.informational_banner_data:{},x="undefined"!=typeof RevolutUpsell?RevolutUpsell({locale:f,publicToken:g}):null,E={channel:"woocommerce-blocks"},S=()=>{if("undefined"==typeof wc_revolut_pay_banner_data||!x)return;const{revPointsBannerEnabled:e}=wc_revolut_pay_banner_data,t=document.getElementById(q);t&&e&&x.promotionalBanner.mount(t,{amount:w,variant:"banner",currency:b,__metadata:E})},{CART_STORE_KEY:k,PAYMENT_STORE_KEY:R}=window.wc.wcBlocksData,P="revolut_cc",C="revolut_pay",j="revolut_payment_request",T="undefined"!=typeof wc_revolut_payment_request_params&&wc_revolut_payment_request_params.is_cart_page,q="revolut-pay-informational-banner",A=window.wp.element,O=["error.3ds-failed","error.email-is-not-specified","error.invalid-postcode","error.invalid-email","error.incorrect-cvv-code","error.expired-card","error.do-not-honour","error.insufficient-funds"],L=({paymentOptions:e,onSuccess:t,onError:r},n)=>{const o=(0,A.useRef)(null),s=(0,A.useRef)(),c=a(j);return(0,A.useEffect)((()=>((async()=>{const{paymentRequest:n,destroy:a}=await RevolutCheckout.payments({publicToken:c.merchant_public_key,locale:c.locale});if(s.current=a,o.current){const a=n.mount(o.current,{...e,onSuccess(){t()},onError(e){r(e.message)},onCancel(){r("Payment cancelled!")}});a.canMakePayment().then((e=>{e?a.render():a.destroy()}))}})(),()=>s.current())),n),{revolutPrbRef:o,destroyRef:s}},M=()=>{(0,A.useEffect)((()=>{const e=document.querySelector(".wp-element-button.wc-block-components-checkout-place-order-button");return e&&(e.disabled=!0,e.style.display="none"),()=>{e&&(e.disabled=!1,e.style.display="block")}}),[])},N=({paymentOptions:e,onSuccess:t,onError:r,onCancel:n},o)=>{const s=(0,A.useRef)(null),c=(0,A.useRef)(),i=a(C);return(0,A.useEffect)((()=>((async()=>{const{revolutPay:o,destroy:a}=await RevolutCheckout.payments({publicToken:i.merchant_public_key,locale:i.locale});c.current=a,s.current&&o.mount(s.current,e),o.on("payment",(e=>{switch(e.type){case"cancel":n();break;case"success":t();break;case"error":r(e.error.message)}}))})(),()=>{c.current()})),o),{revolutPayRef:s,destroyRef:c}};var I=r(848);const D=({settings:e})=>(0,I.jsxs)("div",{className:"revolut-payment-method-label-container",children:[(0,I.jsx)("strong",{children:e.title}),(0,I.jsx)(F,{settings:e})]}),B=({settings:e})=>{const{title:t}=e;return(0,A.useEffect)((()=>{e.payment_method_name===C&&(()=>{if("undefined"==typeof wc_revolut_pay_banner_data||!x)return;const{revolutPayIconVariant:e}=wc_revolut_pay_banner_data,t=document.getElementById("revolut-pay-label-informational-icon");t&&e&&x.promotionalBanner.mount(t,{amount:w,variant:"cashback"===e?"link":e,currency:b,style:{text:"cashback"===e?"cashback":null,color:"blue"},__metadata:E})})()}),[]),(0,I.jsxs)("div",{className:"revolut-payment-method-label-container",children:[(0,I.jsxs)("div",{className:"revolut-pay-label-title-wrapper",children:[(0,I.jsx)("strong",{style:{whiteSpace:"nowrap"},children:t}),(0,I.jsx)("div",{style:{marginLeft:"5px",display:"flex"},id:"revolut-pay-label-informational-icon"})]}),(0,I.jsx)(F,{settings:e})]})},F=({settings:e})=>{const{available_card_brands:t,wc_revolut_plugin_url:r}=e;return(0,I.jsx)("div",{className:"revolut-payment-method-label-scheme-icons",children:t&&t.filter((e=>"maestro"!==e)).map((e=>(0,I.jsx)("img",{src:`${r}/assets/images/${e}.svg`,style:{marginLeft:2},alt:e},e)))})},U=({inputRef:e})=>{const[t,r]=(0,A.useState)([]),n=(0,A.useRef)(!1),o=()=>{const t=e.current.value.trim().split(/\s+/).length>1;return!t&&n.current?(e.current.classList.add("wc-revolut-cardholder-name-error"),r([{message:"Please provide your full name"}])):(e.current.classList.remove("wc-revolut-cardholder-name-error"),r([])),t};return e.current&&(e.current.isComplete=()=>(n.current=!0,o())),(0,I.jsxs)("div",{className:"form-row form-row-first validate-required",id:"cardholder-name","data-priority":"10",style:{display:"block",width:"100%",marginBottom:15},children:[(0,I.jsx)("input",{ref:e,type:"text",onChange:o,className:"input-text",name:"wc-revolut-cardholder-name",id:"wc-revolut-cardholder-name",placeholder:"Cardholder name",autoComplete:"cardholder",required:!0}),(0,I.jsx)("div",{style:{marginBottom:10,marginTop:10},children:(0,I.jsx)(Y,{errorList:t})})]})},Y=({errorList:e})=>(0,I.jsx)("div",{children:e.map(((e,t)=>(0,I.jsx)("li",{className:"card-field-error",children:e.message},t)))}),$=({orderToken:e})=>((0,A.useEffect)((()=>{(e=>{if(!x)return;const t=document.getElementById("revolut-upsell-banner");t&&v&&x.cardGatewayBanner.mount(t,{orderToken:e})})(e)}),[e]),(0,I.jsx)("div",{id:"revolut-upsell-banner"})),V=({eventRegistration:e,billing:t,shippingData:r,shouldSavePayment:n,emitResponse:c,components:i,checkoutStatus:u})=>{const d=P,{onPaymentSetup:_,onCheckoutSuccess:m}=e,h=a(d),v=(0,A.useRef)(),w=(0,A.useRef)(),[f,g]=(0,A.useState)(!1),[b,x]=(0,A.useState)([]),[E,S]=(0,A.useState)(!1),[k,R]=(0,A.useState)(""),[C,j]=(0,A.useState)(0),{createErrorNotice:T,removeAllNotices:q}=(0,o.dispatch)("core/notices");(0,A.useEffect)((()=>{const e=_(M),t=m(N);return()=>{e(),t()}}),[m,_,b,f,n,t.billingAddress,r.shippingAddress]),(0,A.useEffect)((()=>{(async()=>{t?.cartTotal?.value&&(S(!0),p(h).then((e=>{R(e.revolut_order_public_id),j(e.revolut_order_amount),S(!1)})).catch((e=>T(s(e.message||"An unexpected error occurred"),{id:"create_order_failed",context:c.noticeContexts.PAYMENTS}))))})()}),[t.cartTotal.value]);const L=(({onMsg:e,publicId:t,locale:r},n)=>{const o=(0,A.useRef)(e),a=(0,A.useRef)(null),s=(0,A.useRef)(null);return(0,A.useEffect)((()=>{let e=!1;return s.current&&(s.current.destroy(),o.current({type:"instance_destroyed"})),RevolutCheckout(t).then((t=>{!e&&a.current&&(s.current=t.createCardField({locale:r,target:a.current,onSuccess(){o.current({type:"payment_successful"})},onError(e){O.includes(e.type)?o.current({type:"fields_errors_changed",errors:[e]}):o.current({type:"payment_failed",error:e})},onValidation:e=>o.current({type:"fields_errors_changed",errors:e}),onStatusChange:e=>{o.current({type:"fields_status_changed",status:e})},onCancel(){o.current({type:"payment_cancelled"})}}),o.current({type:"instance_mounted",instance:s.current}))})),()=>{e=!0,s.current&&(s.current.destroy(),s.current=null,o.current({type:"instance_destroyed"}))}}),[t,o,r,...n]),a})({publicId:k,locale:h.locale,onMsg:e=>{switch(e.type){case"payment_successful":document.dispatchEvent(new Event("payment_successful"));break;case"payment_failed":document.dispatchEvent(new CustomEvent("payment_failed",{detail:e.error.toString()}));break;case"instance_destroyed":v.current=null;break;case"instance_mounted":v.current=e.instance;break;case"fields_errors_changed":x(e.errors);break;case"fields_status_changed":g(e.status)}}},[C]),M=async()=>{q();let e=null;return t?.billingAddress||(e="Please check your billing address, and retry again."),(!f.completed||b.length>0)&&(e="The payment form is not ready for submission.",v.current&&(v.current.validate(),e="The payment form is not ready for submission. please fix the errors below and retry again.")),h.card_holder_name_field_enabled&&(w.current.isComplete()||(e="The payment form is not ready for submission. please fix the errors below and retry again.")),e?{type:c.responseTypes.ERROR,message:s(e),retry:!0,messageContext:c.noticeContexts.PAYMENTS}:{type:c.responseTypes.SUCCESS}},N=async e=>{S(!0);const{billingAddress:o}=t,{shippingAddress:a}=r,i={name:h.card_holder_name_field_enabled&&w.current.value.length>0?w.current.value:`${o.first_name} ${o.last_name}`,email:o.email,phone:o.phone,savePaymentMethodFor:n||h.is_save_payment_method_mandatory?"merchant":""};void 0!==o.country&&void 0!==o.postcode&&(i.billingAddress=l(o),i.shippingAddress=l(o)),a&&void 0!==a.country&&void 0!==a.postcode&&(i.shippingAddress=l(a));const u=await D({paymentData:i});if(!u.success)return u.error?(S(!1),{type:c.responseTypes.ERROR,message:s(u.error||"Unexpected error occurred, please try again later"),retry:!0,messageContext:c.noticeContexts.PAYMENTS}):void S(!1);y({response:e,paymentMethod:P,shouldSavePayment:n})},D=async({paymentData:e})=>(v.current.submit(e),new Promise(((e,t)=>{document.addEventListener("payment_successful",(()=>{e({success:!0})})),document.addEventListener("payment_failed",(t=>{e({success:!1,error:t.detail})}))})));return(0,A.useEffect)((()=>{L.current&&(b.length>0?L.current.classList.add("woocommerce-revolut-card-element-error"):L.current.classList.remove("woocommerce-revolut-card-element-error"))}),[b]),(0,I.jsx)(I.Fragment,{children:(0,I.jsx)(i.LoadingMask,{showSpinner:!0,isLoading:u.isProcessing||u.isComplete||E,children:k&&(0,I.jsxs)(I.Fragment,{children:[(0,I.jsxs)("div",{children:[(0,I.jsx)("div",{id:"woocommerce-revolut-card-element",ref:L}),b.length>0&&(0,I.jsx)(Y,{errorList:b})]}),h.card_holder_name_field_enabled&&(0,I.jsx)("div",{style:{marginTop:10},children:(0,I.jsx)(U,{inputRef:w})}),h.promotional_banner_enabled&&(0,I.jsx)("div",{children:(0,I.jsx)($,{orderToken:k})})]})})})},G=a(P),K={name:P,label:(0,I.jsx)(D,{settings:G}),content:(0,I.jsx)(V,{}),edit:(0,I.jsx)("p",{children:s("Revolut Gateway is not available in editor mode")}),ariaLabel:s("Revolut Card`s Gateway"),canMakePayment:()=>G.can_make_payment,supports:{features:["products","subscriptions"],showSavedCards:!0,showSaveOption:!G.is_save_payment_method_mandatory}},z=()=>((0,A.useEffect)((()=>{S()}),[]),(0,I.jsx)("div",{id:q})),H={metadata:{name:"revolut-gateway-for-woocommerce/revolut-banner",category:"woocommerce",parent:[e.innerBlockAreas.CHECKOUT_ORDER_SUMMARY],attributes:{lock:{type:"object",default:{remove:!0,move:!0}}}},force:!0,component:()=>(0,I.jsx)(z,{})},W=({billing:e,components:t,checkoutStatus:r,eventRegistration:n,emitResponse:s,onSubmit:c})=>{const{onCheckoutSuccess:i,onCheckoutFail:u}=n,{VALIDATION_STORE_KEY:l}=window.wc.wcBlocksData,[d,_]=(0,A.useState)(!1),m=a(j),h=(0,A.useRef)(null),v=(0,A.useRef)(null),w=(0,A.useRef)(null),{revolutPrbRef:f}=L({paymentOptions:{currency:m.order_currency,amount:e.cartTotal.value,validate:()=>new Promise(((e,t)=>{_(!0),(0,o.select)(l).hasValidationErrors()&&(_(!1),t("Checkout form is incomplete")),p(m).then((r=>{h.current=r.revolut_order_public_id,c(),document.addEventListener("checkout_success",(()=>{e(!0)})),document.addEventListener("checkout_fail",(()=>{_(!1),t("Something went wrong")}))}))})),createOrder:()=>({publicId:h.current})},onSuccess:()=>{_(!0),y({response:v.current,paymentMethod:j,shouldSavePayment:0})},onError:e=>{_(!1),w.current&&w.current.resolve((e=>({type:s.responseTypes.ERROR,message:e,retry:!0,messageContext:s.noticeContexts.PAYMENTS}))(e))}},[e.cartTotal.value]);return(0,A.useEffect)((()=>{const e=i((async e=>new Promise((t=>{_(!1),v.current=e,document.dispatchEvent(new CustomEvent("checkout_success")),w.current={resolve:t}})))),t=u((e=>{e&&e.paymentDetails&&e.paymentDetails.wc_order_id||document.dispatchEvent(new CustomEvent("checkout_fail"))}));return()=>{e(),t()}}),[i,u]),M(),(0,I.jsxs)(I.Fragment,{children:[" ",(0,I.jsx)(t.LoadingMask,{showSpinner:!0,isLoading:d||r.isProcessing||r.isComplete,children:(0,I.jsx)("div",{ref:f})})]})},J=({billing:e,setExpressPaymentError:t,eventRegistration:r,onSubmit:n,onClick:o,onClose:s,emitResponse:i})=>{const{onPaymentSetup:l,onCheckoutFail:p}=r,y=a(j),{revolutPrbRef:v,destroyRef:w}=L({paymentOptions:{currency:y.order_currency,amount:0,requestShipping:!0,shippingOptions:wc_revolut_payment_request_params.free_shipping_option,validate:()=>(o(),!0),onShippingOptionChange:e=>function(e){let t={security:wc_revolut_payment_request_params.nonce.update_shipping,shipping_method:[e.id],is_product_page:wc_revolut_payment_request_params.is_product_page};return new Promise(((e,r)=>{u({data:t,endpoint:c("update_shipping_method")}).then((t=>{e(t)})).catch((e=>{r(e)}))}))}(e),onShippingAddressChange:e=>function(e){let t={security:wc_revolut_payment_request_params.nonce.shipping,country:e.country,state:e.region,postcode:e.postalCode,city:e.city,address:"",address_2:"",is_product_page:wc_revolut_payment_request_params.is_product_page,require_shipping:wc_revolut_payment_request_params.shipping_required};return new Promise(((e,r)=>{u({data:t,endpoint:c("get_shipping_options")}).then((t=>{e(t)})).catch((e=>{r(e)}))}))}(e),createOrder:()=>new Promise(((e,t)=>{d().then((t=>{e({publicId:t})})).catch((e=>{t(e)}))})),buttonStyle:{action:wc_revolut_payment_request_params.payment_request_button_type,size:wc_revolut_payment_request_params.payment_request_button_size,variant:wc_revolut_payment_request_params.payment_request_button_theme,radius:wc_revolut_payment_request_params.payment_request_button_radius}},onSuccess:()=>h({onSubmit:n}).catch((e=>{w.current(),_(),t(e)})),onError:e=>{t(e||"Something went wrong while completing your payment"),s()},onCancel:e=>{t(e),s()}},[e.cartTotal.value]);return(0,A.useEffect)((()=>{const e=l((()=>({type:i.responseTypes.SUCCESS,meta:{paymentMethodData:{is_express_checkout:1}}}))),r=p((()=>{m().then((e=>{t(e.message),"failure"===e.type&&w.current()}))}));return()=>{e(),r()}}),[l,p]),(0,I.jsxs)(I.Fragment,{children:[" ",(0,I.jsx)("div",{ref:v})]})},Q=a(j),X={name:"revolut_payment_request",label:(0,I.jsx)(D,{settings:Q}),content:T?(0,I.jsx)(J,{}):(0,I.jsx)(W,{}),edit:(0,I.jsx)("p",{children:s("Google/Apple Pay block is not available in editor mode")}),ariaLabel:"Google Pay/Apple Pay",canMakePayment:()=>Q.can_make_payment,paymentMethodId:"revolut_payment_request",supports:{features:["products"]}},Z=({billing:e,components:t,eventRegistration:r,onSubmit:n,emitResponse:s})=>{const{onCheckoutSuccess:c,onCheckoutFail:i}=r,{VALIDATION_STORE_KEY:u}=window.wc.wcBlocksData,l=a(C),[d,_]=(0,A.useState)(!1),[m,h]=(0,A.useState)(!1),v=(0,A.useRef)(null),w=(0,A.useRef)(null),f=(0,A.useRef)(null),g=e=>({type:s.responseTypes.ERROR,message:e,retry:!0,messageContext:s.noticeContexts.PAYMENTS}),b={currency:l.order_currency,totalAmount:e.cartTotal.value,mobileRedirectUrls:{success:l.mobile_redirect_url,failure:l.mobile_redirect_url,cancel:l.mobile_redirect_url},validate:()=>(h(!1),new Promise((e=>{_(!0),(0,o.select)(u).hasValidationErrors()&&(_(!1),e(!1)),p(l).then((t=>{v.current=t.revolut_order_public_id,n(),document.addEventListener("checkout_success",(()=>{e(!0)})),document.addEventListener("checkout_fail",(()=>{_(!1),e(!1)}))}))}))),createOrder:()=>({publicId:v.current})},{revolutPayRef:x}=N({paymentOptions:b,onSuccess:()=>{_(!0),y({response:w.current,paymentMethod:C,shouldSavePayment:0})},onError:e=>{_(!1),f.current&&f.current.resolve(g(e))},onCancel:()=>{h(!0),f.current&&f.current.resolve(g("Payment cancelled!"))}},[e.cartTotal.value]);return(0,A.useEffect)((()=>{const e=c((async e=>new Promise((t=>{if(m)return _(!1),t(g("Payment cancelled!"));_(!1),w.current=e,document.dispatchEvent(new CustomEvent("checkout_success")),f.current={resolve:t}})))),t=i((e=>{e&&e.paymentDetails&&e.paymentDetails.wc_order_id||document.dispatchEvent(new CustomEvent("checkout_fail"))}));return()=>{e(),t()}}),[c,i,m]),M(),(0,I.jsxs)(I.Fragment,{children:[" ",(0,I.jsx)(t.LoadingMask,{showSpinner:!0,isLoading:d,children:(0,I.jsx)("div",{ref:x})})]})},ee=({billing:e,setExpressPaymentError:t,eventRegistration:r,onSubmit:n,onClick:o,onClose:s,emitResponse:i})=>{const{onPaymentSetup:l,onCheckoutFail:p}=r,y=a(C),v={currency:y.order_currency,totalAmount:0,mobileRedirectUrls:{success:y.mobile_redirect_url,failure:y.mobile_redirect_url,cancel:y.mobile_redirect_url},buttonStyle:{cashbackCurrency:wc_revolut_payment_request_params.currency,variant:wc_revolut_payment_request_params.revolut_pay_button_theme,size:wc_revolut_payment_request_params.revolut_pay_button_size,radius:wc_revolut_payment_request_params.revolut_pay_button_radius},requestShipping:!0,validate:()=>(o(),!0),createOrder:()=>d().then((e=>(e=>u({data:{revolut_public_id:e,security:wc_revolut_payment_request_params.nonce.update_order_total},endpoint:c("update_payment_total")}))(e).then((()=>({publicId:e})))))},{revolutPayRef:w,destroyRef:f}=N({paymentOptions:v,onSuccess:()=>h({onSubmit:n}).catch((e=>{f.current(),_(),t(e)})),onError:e=>{t(e||"Something went wrong while completing your payment"),s()},onCancel:e=>{t(e||"Payment cancelled!"),s()}},[e.cartTotal.value]);return(0,A.useEffect)((()=>{const e=l((()=>({type:i.responseTypes.SUCCESS,meta:{paymentMethodData:{is_express_checkout:1}}}))),r=p((()=>{m().then((e=>{t(e.message),"failure"===e.type&&f.current()}))}));return()=>{e(),r()}}),[l,p]),(0,A.useEffect)((()=>{S()}),[]),(0,I.jsxs)(I.Fragment,{children:[" ",(0,I.jsx)("div",{id:q}),(0,I.jsx)("div",{ref:w})]})},te=a(C),re=new URLSearchParams(window.location.search),ne=re.get("_rp_fr"),oe=re.get("_rp_oid"),ae=re.get("_rp_s");(ne||!ae&&oe)&&((0,o.dispatch)("core/notices").createErrorNotice(s(ne||"Payment Rejected"),{id:"rp-fr",context:"wc/checkout/payments"}),T||(0,o.dispatch)(R).__internalSetActivePaymentMethod(C));const se={name:"revolut_pay",label:(0,I.jsx)(B,{settings:te}),content:T?(0,I.jsx)(ee,{}):(0,I.jsx)(Z,{}),edit:(0,I.jsx)("p",{children:s("Revolut Pay is not available in editor mode")}),ariaLabel:"Revolut Pay",canMakePayment:()=>te.can_make_payment,paymentMethodId:"revolut_pay",supports:{features:["products"]}},{registerPaymentMethod:ce,registerExpressPaymentMethod:ie}=window.wc.wcBlocksRegistry;T?(ie(se),ie(X)):(ce(K),ce(se),ce(X)),(0,e.registerCheckoutBlock)(H)})()})();