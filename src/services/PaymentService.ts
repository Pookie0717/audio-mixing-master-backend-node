import Stripe from 'stripe';
import paypal from '@paypal/checkout-server-sdk';

let stripe: Stripe;
let paypalClient: paypal.core.PayPalHttpClient;

export const initializePaymentServices = async () => {
  try {
    // Initialize Stripe
    stripe = new Stripe(process.env['STRIPE_SECRET_KEY'] || '', {
      apiVersion: '2023-10-16',
    });

    // Initialize PayPal
    const environment = process.env['PAYPAL_MODE'] === 'live' 
      ? new paypal.core.LiveEnvironment(process.env['PAYPAL_CLIENT_ID'] || '', process.env['PAYPAL_CLIENT_SECRET'] || '')
      : new paypal.core.SandboxEnvironment(process.env['PAYPAL_CLIENT_ID'] || '', process.env['PAYPAL_CLIENT_SECRET'] || '');
    
    paypalClient = new paypal.core.PayPalHttpClient(environment);

    console.log('✅ Payment services initialized successfully');
  } catch (error) {
    console.error('❌ Payment services initialization failed:', error);
    throw error;
  }
};

// Stripe Payment Methods
export const createStripePaymentIntent = async (amount: number, currency: string = 'usd') => {
  try {
    const paymentIntent = await stripe.paymentIntents.create({
      amount: Math.round(amount * 100), // Convert to cents
      currency,
      automatic_payment_methods: {
        enabled: true,
      },
    });

    return paymentIntent;
  } catch (error) {
    console.error('Stripe payment intent creation failed:', error);
    throw error;
  }
};

export const createStripeSubscription = async (customerId: string, priceId: string) => {
  try {
    const subscription = await stripe.subscriptions.create({
      customer: customerId,
      items: [{ price: priceId }],
      payment_behavior: 'default_incomplete',
      payment_settings: { save_default_payment_method: 'on_subscription' },
      expand: ['latest_invoice.payment_intent'],
    });

    return subscription;
  } catch (error) {
    console.error('Stripe subscription creation failed:', error);
    throw error;
  }
};

// PayPal Payment Methods
export const createPayPalOrder = async (amount: number, currency: string = 'USD') => {
  try {
    const request = new paypal.orders.OrdersCreateRequest();
    request.prefer("return=representation");
    request.requestBody({
      intent: 'CAPTURE',
      purchase_units: [{
        amount: {
          currency_code: currency,
          value: amount.toString(),
        },
      }],
    });

    const order = await paypalClient.execute(request);
    return order.result;
  } catch (error) {
    console.error('PayPal order creation failed:', error);
    throw error;
  }
};

export const capturePayPalOrder = async (orderId: string) => {
  try {
    const request = new paypal.orders.OrdersCaptureRequest(orderId);

    const capture = await paypalClient.execute(request);
    return capture.result;
  } catch (error) {
    console.error('PayPal order capture failed:', error);
    throw error;
  }
};

// Utility functions
export const formatAmountForStripe = (amount: number): number => {
  return Math.round(amount * 100);
};

export const formatAmountFromStripe = (amount: number): number => {
  return amount / 100;
};

export const validatePaymentAmount = (amount: number): boolean => {
  return amount > 0 && amount <= 999999.99; // Reasonable limit
}; 