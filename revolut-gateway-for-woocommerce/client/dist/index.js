(()=>{"use strict";const e=window.React,t=window.wp.element,r=["error.3ds-failed","error.email-is-not-specified","error.invalid-postcode","error.invalid-email","error.incorrect-cvv-code","error.expired-card","error.do-not-honour","error.insufficient-funds"],n=window.wc.wcSettings,s=window.wp.i18n,o=window.wp.data,a=e=>(0,n.getSetting)(`${e}_data`),c=e=>(0,s.__)(e,"revolut-gateway-for-woocommerce"),u=async e=>{try{const t=await i({data:{security:e.create_revolut_order_nonce},endpoint:e.create_revolut_order_endpoint});if(t?.success)return t;throw new Error("Something went wrong while creating the payment.")}catch(e){throw new Error(e.message||"An unexpected error occurred.")}},l=async({response:e,paymentMethod:t,shouldSavePayment:r})=>{try{const{processingResponse:n}=e,{wc_order_id:s,revolut_public_id:o,process_payment_result:c}=n.paymentDetails,u=await(async({process_payment_result:e,revolut_public_id:t,shouldSavePayment:r,wc_order_id:n,paymentMethod:s})=>{try{const o=a(s),c={revolut_gateway:s,security:e,revolut_public_id:t,revolut_payment_error:"",wc_order_id:n,reload_checkout:0,revolut_save_payment_method:Number(r)||Number(o.is_save_payment_method_mandatory)},u=await i({data:c,endpoint:o.process_order_endpoint});if("fail"===u?.result)throw new Error(u?.messages||"Something went wrong while trying to charge your card, please try again");if("success"===u?.result)return u;throw new Error("Failed to process your order due to server issue")}catch(e){throw new Error(e.message||"An unexpected error occurred")}})({wc_order_id:s,revolut_public_id:o,process_payment_result:c,shouldSavePayment:r,paymentMethod:t});if(u.redirect)return window.location.href=decodeURI(u.redirect),{type:"success"};throw new Error("Could not redirect you to the confirmation page due to an unexpected error. Please contact the merchant")}catch(e){return{type:"error",message:c(e?.message),retry:!0,messageContext:"wc/checkout/payments"}}},i=async({data:e,endpoint:t})=>{const r=await fetch(t,{headers:{"Content-type":"application/x-www-form-urlencoded; charset=UTF-8"},method:"POST",body:Object.keys(e).map((t=>encodeURIComponent(t)+"="+encodeURIComponent(e[t]))).join("&")});if(!r.ok)throw new Error("Failed to process your request due to network issue");return await r.json()},d=e=>({countryCode:e.country,region:e.state,city:e.city,streetLine1:e.address_1,streetLine2:e.address_2,postcode:e.postcode}),m="revolut_cc",p="revolut_pay",y="revolut_payment_request",_=()=>{(0,t.useEffect)((()=>{const e=document.querySelector(".wp-element-button.wc-block-components-checkout-place-order-button");return e&&(e.disabled=!0,e.style.display="none"),()=>{e&&(e.disabled=!1,e.style.display="block")}}),[])},h=({settings:t})=>{const{available_card_brands:r,wc_revolut_plugin_url:n,title:s}=t;return(0,e.createElement)("div",{className:"revolut-card-label-container"},(0,e.createElement)("strong",null,s),(0,e.createElement)("div",{className:"revolut-card-label-brands"},r&&r.filter((e=>"maestro"!==e)).map((t=>(0,e.createElement)("img",{key:t,src:`${n}/assets/images/${t}.svg`,style:{marginLeft:2},alt:t})))))},v=a(m),w={name:m,label:(0,e.createElement)(h,{settings:v}),content:(0,e.createElement)((({eventRegistration:n,billing:s,shippingData:i,shouldSavePayment:p,emitResponse:y,components:_,checkoutStatus:h})=>{const v=m,{onPaymentSetup:w,onCheckoutSuccess:g}=n,f=a(v),E=(0,t.useRef)(),[b,k]=(0,t.useState)(!1),[S,R]=(0,t.useState)([]),[P,C]=(0,t.useState)(!1),[T,A]=(0,t.useState)(""),[M,L]=(0,t.useState)(0),{createErrorNotice:x,removeAllNotices:N}=(0,o.dispatch)("core/notices");(0,t.useEffect)((()=>{const e=w(D),t=g(I);return()=>{e(),t()}}),[g,w,S,b,p,s.billingAddress,i.shippingAddress]),(0,t.useEffect)((()=>{(async()=>{s?.cartTotal?.value&&(C(!0),u(f).then((e=>{A(e.revolut_order_public_id),L(e.revolut_order_amount),C(!1)})).catch((e=>x(c(e.message||"An unexpected error occurred"),{id:"create_order_failed",context:y.noticeContexts.PAYMENTS}))))})()}),[s.cartTotal.value]);const O=(({onMsg:e,publicId:n,locale:s},o)=>{const a=(0,t.useRef)(e),c=(0,t.useRef)(null),u=(0,t.useRef)(null);return(0,t.useEffect)((()=>{let e=!1;return u.current&&(u.current.destroy(),a.current({type:"instance_destroyed"})),RevolutCheckout(n).then((t=>{!e&&c.current&&(u.current=t.createCardField({locale:s,target:c.current,onSuccess(){a.current({type:"payment_successful"})},onError(e){r.includes(e.type)?a.current({type:"fields_errors_changed",errors:[e]}):a.current({type:"payment_failed",error:e})},onValidation:e=>a.current({type:"fields_errors_changed",errors:e}),onStatusChange:e=>{a.current({type:"fields_status_changed",status:e})},onCancel(){a.current({type:"payment_cancelled"})}}),a.current({type:"instance_mounted",instance:u.current}))})),()=>{e=!0,u.current&&(u.current.destroy(),u.current=null,a.current({type:"instance_destroyed"}))}}),[n,a,s,...o]),c})({publicId:T,locale:f.locale,onMsg:e=>{switch(e.type){case"payment_successful":document.dispatchEvent(new Event("payment_successful"));break;case"payment_failed":document.dispatchEvent(new CustomEvent("payment_failed",{detail:e.error.toString()}));break;case"instance_destroyed":E.current=null;break;case"instance_mounted":E.current=e.instance;break;case"fields_errors_changed":R(e.errors);break;case"fields_status_changed":k(e.status)}}},[M]),D=async()=>{N();let e=null;return s?.billingAddress||(e="Please check your billing address, and retry again."),(!b.completed||S.length>0)&&(e="The payment form is not ready for submission.",E.current&&(E.current.validate(),e="The payment form is not ready for submission, please fix the errors below.")),e?{type:y.responseTypes.ERROR,message:c(e),retry:!0,messageContext:y.noticeContexts.PAYMENTS}:{type:y.responseTypes.SUCCESS}},I=async e=>{C(!0);const{billingAddress:t}=s,{shippingAddress:r}=i,n={name:`${t.first_name} ${t.last_name}`,email:t.email,phone:t.phone,savePaymentMethodFor:p||f.is_save_payment_method_mandatory?"merchant":""};void 0!==t.country&&void 0!==t.postcode&&(n.billingAddress=d(t),n.shippingAddress=d(t)),r&&void 0!==r.country&&void 0!==r.postcode&&(n.shippingAddress=d(r));const o=await F({paymentData:n});if(!o.success)return o.error?(C(!1),{type:y.responseTypes.ERROR,message:c(o.error||"Unexpected error occurred, please try again later"),retry:!0,messageContext:y.noticeContexts.PAYMENTS}):void C(!1);l({response:e,paymentMethod:m,shouldSavePayment:p})},F=async({paymentData:e})=>(E.current.submit(e),new Promise(((e,t)=>{document.addEventListener("payment_successful",(()=>{e({success:!0})})),document.addEventListener("payment_failed",(t=>{e({success:!1,error:t.detail})}))})));return(0,e.createElement)(e.Fragment,null,(0,e.createElement)(_.LoadingMask,{showSpinner:!0,isLoading:h.isProcessing||h.isComplete||P},(0,e.createElement)(e.Fragment,null,T&&(0,e.createElement)("div",{id:"woocommerce-revolut-card-element",ref:O}),(0,e.createElement)("div",null,S.length>0&&S.map(((t,r)=>(0,e.createElement)("li",{className:"card-field-error",key:r},t.message)))))))}),null),edit:(0,e.createElement)("p",null,c("Revolut Gateway is not available in editor mode")),ariaLabel:c("Revolut Card`s Gateway"),canMakePayment:()=>v.can_make_payment,supports:{features:["products"],showSavedCards:!0,showSaveOption:!v.is_save_payment_method_mandatory}},{PAYMENT_STORE_KEY:g}=window.wc.wcBlocksData,f=a(p),E=new URLSearchParams(window.location.search),b=E.get("_rp_fr"),k=E.get("_rp_oid"),S=E.get("_rp_s");(b||!S&&k)&&((0,o.dispatch)("core/notices").createErrorNotice(c(b||"Payment Rejected"),{id:"rp-fr",context:"wc/checkout/payments"}),(0,o.dispatch)(g).__internalSetActivePaymentMethod(p));const R={name:"revolut_pay",label:(0,e.createElement)(h,{settings:f}),content:(0,e.createElement)((({billing:r,components:n,eventRegistration:s,onSubmit:c,emitResponse:i})=>{const{onCheckoutSuccess:d,onCheckoutFail:m}=s,{VALIDATION_STORE_KEY:y}=window.wc.wcBlocksData,h=a(p),[v,w]=(0,t.useState)(!1),[g,f]=(0,t.useState)(!1),E=(0,t.useRef)(null),b=(0,t.useRef)(null),k=(0,t.useRef)(null),S=(0,t.useRef)(null),R=(0,t.useRef)(),P=e=>({type:i.responseTypes.ERROR,message:e,retry:!0,messageContext:i.noticeContexts.PAYMENTS});return(0,t.useEffect)((()=>((async()=>{const{revolutPay:e,destroy:t}=await RevolutCheckout.payments({publicToken:h.merchant_public_key,locale:h.locale});R.current=t,S.current&&e.mount(S.current,{currency:h.order_currency,totalAmount:r.cartTotal.value,mobileRedirectUrls:{success:h.mobile_redirect_url,failure:h.mobile_redirect_url,cancel:h.mobile_redirect_url},validate:()=>(f(!1),new Promise((e=>{w(!0),(0,o.select)(y).hasValidationErrors()&&(w(!1),e(!1)),u(h).then((t=>{E.current=t.revolut_order_public_id,c(),document.addEventListener("checkout_success",(()=>{e(!0)})),document.addEventListener("checkout_fail",(()=>{w(!1),e(!1)}))}))}))),createOrder:()=>({publicId:E.current})}),e.on("payment",(e=>{switch(e.type){case"cancel":f(!0),k.current&&k.current.resolve(P("Payment cancelled!"));break;case"success":w(!0),l({response:b.current,paymentMethod:p,shouldSavePayment:0});break;case"error":w(!1),k.current&&k.current.resolve(P(e.error.message))}}))})(),()=>{R.current()})),[r.cartTotal.value]),(0,t.useEffect)((()=>{const e=d((async e=>new Promise((t=>{if(g)return w(!1),t(P("Payment cancelled!"));w(!1),b.current=e,document.dispatchEvent(new CustomEvent("checkout_success")),k.current={resolve:t}})))),t=m((e=>{e&&e.paymentDetails&&e.paymentDetails.wc_order_id||document.dispatchEvent(new CustomEvent("checkout_fail"))}));return()=>{e(),t()}}),[d,m,g]),_(),(0,e.createElement)(e.Fragment,null," ",(0,e.createElement)(n.LoadingMask,{showSpinner:!0,isLoading:v},(0,e.createElement)("div",{ref:S})))}),null),edit:(0,e.createElement)("p",null,c("Revolut Pay is not available in editor mode")),ariaLabel:"Revolut Pay",canMakePayment:()=>f.can_make_payment,paymentMethodId:"revolut_pay",supports:{features:["products"]}},P=R,C=a(y),T={name:"revolut_payment_request",label:(0,e.createElement)(h,{settings:C}),content:(0,e.createElement)((({billing:r,components:n,checkoutStatus:s,eventRegistration:c,emitResponse:i,onSubmit:d})=>{const{onCheckoutSuccess:m,onCheckoutFail:p}=c,{VALIDATION_STORE_KEY:h}=window.wc.wcBlocksData,[v,w]=(0,t.useState)(!1),g=a(y),f=(0,t.useRef)(null),E=(0,t.useRef)(null),b=(0,t.useRef)(null),k=(({paymentOptions:e,onSuccess:r,onError:n},s)=>{const o=(0,t.useRef)(null),c=(0,t.useRef)(),u=a(y);return(0,t.useEffect)((()=>((async()=>{const{paymentRequest:t,destroy:s}=await RevolutCheckout.payments({publicToken:u.merchant_public_key,locale:u.locale});if(c.current=s,o.current){const s=t.mount(o.current,{...e,onSuccess(){r()},onError(e){n(e.message)},onCancel(){n("Payment rejected!")}});s.canMakePayment().then((e=>{e?s.render():s.destroy()}))}})(),()=>c.current())),s),o})({paymentOptions:{currency:g.order_currency,amount:r.cartTotal.value,validate:()=>new Promise(((e,t)=>{w(!0),(0,o.select)(h).hasValidationErrors()&&(w(!1),t("Checkout form is incomplete")),u(g).then((r=>{f.current=r.revolut_order_public_id,d(),document.addEventListener("checkout_success",(()=>{e(!0)})),document.addEventListener("checkout_fail",(()=>{w(!1),t("Something went wrong")}))}))})),createOrder:()=>({publicId:f.current})},onSuccess:()=>{w(!0),l({response:E.current,paymentMethod:y,shouldSavePayment:0})},onError:e=>{var t;w(!1),b.current&&b.current.resolve((t=e,{type:i.responseTypes.ERROR,message:t,retry:!0,messageContext:i.noticeContexts.PAYMENTS}))}},[r.cartTotal.value]);return(0,t.useEffect)((()=>{const e=m((async e=>new Promise((t=>{w(!1),E.current=e,document.dispatchEvent(new CustomEvent("checkout_success")),b.current={resolve:t}})))),t=p((e=>{e&&e.paymentDetails&&e.paymentDetails.wc_order_id||document.dispatchEvent(new CustomEvent("checkout_fail"))}));return()=>{e(),t()}}),[m,p]),_(),(0,e.createElement)(e.Fragment,null," ",(0,e.createElement)(n.LoadingMask,{showSpinner:!0,isLoading:v||s.isProcessing||s.isComplete},(0,e.createElement)("div",{ref:k})))}),null),edit:(0,e.createElement)("p",null,c("Google/Apple Pay block is not available in editor mode")),ariaLabel:"Google Pay/Apple Pay",canMakePayment:()=>C.can_make_payment,paymentMethodId:"revolut_payment_request",supports:{features:["products"]}},A=T,{registerPaymentMethod:M}=window.wc.wcBlocksRegistry;M(w),M(P),M(A)})();