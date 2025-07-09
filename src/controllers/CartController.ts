import { Response } from 'express';
import { Cart, Service } from '../models';
import { AuthRequest } from '../middleware/auth';

export class CartController {
  // Get all cart items for a user
  static async index(req: AuthRequest, res: Response) {
    try {
      const userId = req.user?.id;
      const cartItems = await Cart.findAll({
        where: { user_id: userId },
        include: [{ model: Service, as: 'service' }],
        order: [['createdAt', 'DESC']],
      });
      return res.json({ success: true, data: { cartItems } });
    } catch (error) {
      console.error('Cart index error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Add item to cart
  static async add(req: AuthRequest, res: Response) {
    try {
      const userId = req.user?.id;
      const { serviceId, qty, price, total_price } = req.body;
      let cartItem = await Cart.findOne({ where: { user_id: userId, service_id: serviceId } });
      if (cartItem) {
        cartItem.qty = (parseInt(cartItem.qty) + parseInt(qty)).toString();
        cartItem.total_price = (parseFloat(cartItem.total_price) + parseFloat(total_price)).toString();
        await cartItem.save();
      } else {
        cartItem = await Cart.create({ user_id: userId, service_id: serviceId, qty, price, total_price });
      }
      return res.status(201).json({ success: true, data: { cartItem } });
    } catch (error) {
      console.error('Cart add error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Update cart item quantity
  static async update(req: AuthRequest, res: Response) {
    try {
      const userId = req.user?.id;
      const { serviceId, qty } = req.body;
      const cartItem = await Cart.findOne({ where: { user_id: userId, service_id: serviceId } });
      if (!cartItem) {
        return res.status(404).json({ message: 'Cart item not found' });
      }
      cartItem.qty = qty;
      await cartItem.save();
      return res.json({ success: true, data: { cartItem } });
    } catch (error) {
      console.error('Cart update error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Remove item from cart
  static async remove(req: AuthRequest, res: Response) {
    try {
      const userId = req.user?.id;
      const { serviceId } = req.body;
      const cartItem = await Cart.findOne({ where: { user_id: userId, service_id: serviceId } });
      if (!cartItem) {
        return res.status(404).json({ message: 'Cart item not found' });
      }
      await cartItem.destroy();
      return res.json({ success: true, message: 'Cart item removed' });
    } catch (error) {
      console.error('Cart remove error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }
} 