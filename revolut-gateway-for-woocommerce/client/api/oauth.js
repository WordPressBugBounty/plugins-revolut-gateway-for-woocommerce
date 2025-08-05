import { AJAX_ACTIONS_OAUTH } from '../constants'

export const saveToken = ({ code, verifier, mode }) =>
  new Promise((resolve, reject) =>
    jQuery.ajax({
      type: 'POST',
      url: ConnectVars.ajax_url,
      data: {
        action: AJAX_ACTIONS_OAUTH.ACTION_SAVE_TOKEN,
        code,
        verifier,
        mode,
        _wpnonce: ConnectVars.nonce,
      },
      success: function (response) {
        if(response.success) {
          return resolve(response)
        }
        reject((response.data || 'Could not complete connection.'))
      },
      error: function (xhr, status, error) {
        reject(error || 'Unexpected AJAX error')
      },
    }),
  )

export const removeToken = ({ mode }) =>
  new Promise((resolve, reject) =>
    jQuery.ajax({
      type: 'POST',
      url: ConnectVars.ajax_url,
      data: {
        action: AJAX_ACTIONS_OAUTH.ACTION_REMOVE_TOKEN,
        mode: mode,
        _wpnonce: ConnectVars.disconnect_nonce,
      },
      success: function (response) {
        if (response.success) {
          return resolve(response)
        }
        reject('Something went wrong while trying to delete oauth token')
      },
      error: function (xhr, status, error) {
        reject(error || 'Unexpected AJAX failure.')
      },
    }),
  )
