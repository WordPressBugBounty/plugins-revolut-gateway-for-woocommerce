import { getSetting } from '@woocommerce/settings'

import { __ } from '@wordpress/i18n'

export { select, dispatch } from '@wordpress/data'

export const revolutSettings = paymentMethod => getSetting(`${paymentMethod}_data`)

export const i18n = msg => __(msg, 'revolut-gateway-for-woocommerce')

export const createRevolutOrder = async settings => {
  try {
    const json = await sendAjax({
      data: {
        security: settings.create_revolut_order_nonce,
      },
      endpoint: settings.create_revolut_order_endpoint,
    })
    if (json?.success) {
      return json
    }
    throw new Error('Something went wrong while creating the payment.')
  } catch (err) {
    throw new Error(err.message || 'An unexpected error occurred.')
  }
}

export const processPayment = async ({
  process_payment_result,
  revolut_public_id,
  shouldSavePayment,
  wc_order_id,
  paymentMethod,
}) => {
  try {
    const settings = revolutSettings(paymentMethod)
    const data = {
      revolut_gateway: paymentMethod,
      security: process_payment_result,
      revolut_public_id: revolut_public_id,
      revolut_payment_error: '',
      wc_order_id: wc_order_id,
      reload_checkout: 0,
      revolut_save_payment_method: Number(shouldSavePayment) || Number(settings.is_save_payment_method_mandatory),
    }

    const response = await sendAjax({ data, endpoint: settings.process_order_endpoint })
    if (response?.result === 'fail') {
      throw new Error(
        response?.messages ||
          'Something went wrong while trying to charge your card, please try again',
      )
    }
    if (response?.result === 'success') {
      return response
    }
    throw new Error('Failed to process your order due to server issue')
  } catch (err) {
    throw new Error(err.message || 'An unexpected error occurred')
  }
}

export const onPaymentSuccessHandler = async ({ response, paymentMethod, shouldSavePayment }) => {
  try {
    const { processingResponse } = response
    const { wc_order_id, revolut_public_id, process_payment_result } =
      processingResponse.paymentDetails

    const processResult = await processPayment({
      wc_order_id,
      revolut_public_id,
      process_payment_result,
      shouldSavePayment,
      paymentMethod,
    })

    if (processResult.redirect) {
      window.location.href = decodeURI(processResult.redirect)
      return {
        type: 'success',
      }
    }

    throw new Error(
      'Could not redirect you to the confirmation page due to an unexpected error. Please contact the merchant',
    )
  } catch (e) {
    return {
      type: 'error',
      message: i18n(e?.message),
      retry: true,
      messageContext: 'wc/checkout/payments',
    }
  }
}

export const sendAjax = async ({ data, endpoint }) => {
  const response = await fetch(endpoint, {
    headers: { 'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    method: 'POST',
    body: Object.keys(data)
      .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(data[key]))
      .join('&'),
  })
  if (!response.ok) {
    throw new Error('Failed to process your request due to network issue')
  }
  const json = await response.json()
  return json
}

export const createAddress = address => {
  return {
    countryCode: address.country,
    region: address.state,
    city: address.city,
    streetLine1: address.address_1,
    streetLine2: address.address_2,
    postcode: address.postcode,
  }
}

export const PAYMENT_METHODS = {
  REVOLUT_CARD: 'revolut_cc',
  REVOLUT_PAY: 'revolut_pay',
  REVOLUT_PRB: 'revolut_payment_request',
}

export const CHECKOUT_PAYMENT_CONTEXT = 'wc/checkout/payments'
