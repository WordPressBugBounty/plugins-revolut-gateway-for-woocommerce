import { registerCheckoutBlock } from '@woocommerce/blocks-checkout'
import {
  registerExpressPaymentMethod,
  registerPaymentMethod,
} from '@woocommerce/blocks-registry'
import {
  RevolutGatewayBlock,
  RevolutPayBlock,
  RevolutPayByBankBlock,
  RevolutPayExpressCheckoutBlock,
  RevolutPaymentRequestBlock,
  RevolutPaymentRequestExpressCheckoutBlock,
  RevPointsBannerBlock,
} from './blocks'

registerPaymentMethod(RevolutGatewayBlock)
registerPaymentMethod(RevolutPayByBankBlock)

registerExpressPaymentMethod(RevolutPayExpressCheckoutBlock)
registerPaymentMethod(RevolutPayBlock)
registerCheckoutBlock(RevPointsBannerBlock)

registerExpressPaymentMethod(RevolutPaymentRequestExpressCheckoutBlock)
registerPaymentMethod(RevolutPaymentRequestBlock)
