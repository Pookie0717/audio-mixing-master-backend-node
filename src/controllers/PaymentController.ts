import { Response } from 'express';
import { Payment, Order, User } from '../models';
import { AuthRequest } from '../middleware/auth';
import { 
  createStripePaymentIntent, 
  createPayPalOrder
} from '../services/PaymentService';

export class PaymentController {
  // Process payment
  static async processPayment(req: AuthRequest, res: Response) {
    try {
      const { orderId, paymentMethod, paymentData } = req.body;

      if (!orderId || !paymentMethod) {
        return res.status(400).json({ message: 'Order ID and payment method are required' });
      }

      const order = await Order.findOne({
        where: { id: orderId },
        include: [{ model: User, as: 'user' }],
      });

      if (!order) {
        return res.status(404).json({ message: 'Order not found' });
      }

      if (order.user_id !== req.user?.id) {
        return res.status(403).json({ message: 'Access denied' });
      }

      let paymentResult;

      switch (paymentMethod) {
        case 'stripe':
          paymentResult = await createStripePaymentIntent(paymentData.amount, paymentData.currency);
          break;
        case 'paypal':
          paymentResult = await createPayPalOrder(paymentData.amount, paymentData.currency);
          break;
        default:
          return res.status(400).json({ message: 'Invalid payment method' });
      }

      if (paymentResult.success) {
        // Update order status
        order.Order_status = 1; // PAID
        await order.save();

        // Create payment record
        await Payment.create({
          payment_id: paymentResult.transactionId,
          product_name: `Order ${orderId}`,
          quantity: '1',
          amount: order.amount.toString(),
          currency: order.currency,
          payer_name: req.user?.first_name + ' ' + req.user?.last_name,
          payer_email: req.user?.email,
          payment_status: 'COMPLETED',
          payment_method: paymentMethod.toUpperCase(),
        });
      }

      return res.json({
        success: true,
        message: 'Payment processed successfully',
        data: paymentResult,
      });
    } catch (error) {
      console.error('Payment process error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Refund payment
  static async refundPayment(req: AuthRequest, res: Response) {
    try {
      const { paymentId } = req.params;
      const { reason: _reason } = req.body;

      const payment = await Payment.findOne({
        where: { id: paymentId },
      });

      if (!payment) {
        return res.status(404).json({ message: 'Payment not found' });
      }

      // TODO: Implement refund logic with payment gateway
      const refundResult = { success: true, message: 'Refund processed' };

      if (refundResult.success) {
        // Update payment status
        payment.payment_status = 'REFUNDED';
        await payment.save();
      }

      return res.json({
        success: true,
        message: 'Payment refunded successfully',
        data: refundResult,
      });
    } catch (error) {
      console.error('Payment refund error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Get payment history
  static async getPaymentHistory(req: AuthRequest, res: Response) {
    try {
      const { page = 1, limit = 10 } = req.query;
      const offset = (parseInt(page as string) - 1) * parseInt(limit as string);

      const payments = await Payment.findAndCountAll({
        include: [
          {
            model: Order,
            as: 'order',
            where: { userId: req.user?.id },
          },
        ],
        offset,
        limit: parseInt(limit as string),
        order: [['createdAt', 'DESC']],
      });

      return res.json({
        success: true,
        data: {
          payments: payments.rows,
          pagination: {
            page: parseInt(page as string),
            limit: parseInt(limit as string),
            total: payments.count,
            pages: Math.ceil(payments.count / parseInt(limit as string)),
          },
        },
      });
    } catch (error) {
      console.error('Payment history error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Get payment by ID
  static async getPayment(req: AuthRequest, res: Response) {
    try {
      const { id } = req.params;

      const payment = await Payment.findOne({
        where: { id },
      });

      if (!payment) {
        return res.status(404).json({ message: 'Payment not found' });
      }

      return res.json({
        success: true,
        data: { payment },
      });
    } catch (error) {
      console.error('Payment get error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }
} 