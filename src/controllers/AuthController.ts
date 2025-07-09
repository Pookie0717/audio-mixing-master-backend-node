import { Request, Response } from 'express';
import jwt from 'jsonwebtoken';
import User from '../models/User';
import { Favourite, Service, Category } from '../models';
import { sendWelcomeEmail, sendPasswordResetEmail } from '../services/EmailService';

export class AuthController {
  // Register user
  static async register(req: Request, res: Response) {
    try {
      const { first_name, last_name, email, phone_number, password } = req.body;
      
      // Validate required fields
      if (!first_name || !last_name || !email || !password) {
        return res.status(400).json({ message: 'First name, last name, email, and password are required' });
      }

      // Check if user already exists
      const existingUser = await User.findOne({ where: { email } });
      if (existingUser) {
        return res.status(400).json({ message: 'User already exists' });
      }

              // Create user (password will be hashed automatically by model hooks)
        const user = await User.create({
          first_name,
          last_name,
          email,
          password,
          phone_number,
          role: 'user',
          is_active: 0,
        });

      // Generate JWT token
      const secret = process.env['JWT_SECRET'] || 'fallback-secret';
      const token = jwt.sign(
        { id: user.id },
        secret,
        { expiresIn: '7d' }
      );

      // Send welcome email
      try {
        await sendWelcomeEmail({ name: user.first_name + ' ' + user.last_name, email: user.email });
      } catch (emailError) {
        console.error('Failed to send welcome email:', emailError);
      }

      return res.status(200).json({
        success: true,
        message: 'User registered successfully',
        data: {
          user,
          token,
        },
      });
    } catch (error) {
      console.error('Registration error:', error);
      if (error instanceof Error) {
        console.error('Error details:', {
          name: error.name,
          message: error.message,
          stack: error.stack
        });
      }
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Login user
  static async login(req: Request, res: Response) {
    try {
      const { email, password } = req.body;

      // Validate required fields
      if (!email || !password) {
        return res.status(400).json({ message: 'Email and password are required' });
      }

      // Check if user exists
      const user = await User.findOne({ where: { email } });
      if (!user) {
        return res.status(400).json({ message: 'Invalid credentials' });
      }

      // Check if user is active
      if (user.is_active !== 1) {
        return res.status(400).json({ message: 'Account is not active' });
      }

      // Check password
      const isPasswordValid = await user.comparePassword(password);
      if (!isPasswordValid) {
        return res.status(400).json({ message: 'Invalid credentials' });
      }

      // Generate JWT token
      const secret = process.env['JWT_SECRET'] || 'fallback-secret';
      const token = jwt.sign(
        { id: user.id },
        secret,
        { expiresIn: '7d' }
      );

      return res.json({
        success: true,
        message: 'Login successful',
        data: {
                      user: {
              id: user.id,
              name: user.first_name + ' ' + user.last_name,
              email: user.email,
              role: user.role,
              is_active: user.is_active,
            },
          token,
        },
      });
    } catch (error) {
      console.error('Login error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Forgot password
  static async forgotPassword(req: Request, res: Response) {
    try {
      const { email } = req.body;

      if (!email) {
        return res.status(400).json({ message: 'Email is required' });
      }

      const user = await User.findOne({ where: { email } });
      if (!user) {
        return res.status(404).json({ message: 'User not found' });
      }

      // Generate reset token
      const resetToken = jwt.sign(
        { id: user.id },
        process.env['JWT_SECRET'] || 'fallback-secret',
        { expiresIn: '1h' }
      );

      // Note: Password reset token functionality would need a separate model
      // For now, we'll just send the email with the token
      // In a real implementation, you'd create a PasswordResetToken model

      // Send reset email
      try {
        await sendPasswordResetEmail({ name: user.first_name + ' ' + user.last_name, email: user.email }, resetToken);
      } catch (emailError) {
        console.error('Failed to send reset email:', emailError);
        return res.status(500).json({ message: 'Failed to send reset email' });
      }

      return res.json({
        success: true,
        message: 'Password reset email sent',
      });
    } catch (error) {
      console.error('Forgot password error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Reset password
  static async resetPassword(req: Request, res: Response) {
    try {
      const { email, token } = req.params;
      const { password } = req.body;

      if (!email || !token || !password) {
        return res.status(400).json({ message: 'Email, token, and password are required' });
      }

      // Note: Token verification would need a PasswordResetToken model
      // For now, we'll just update the user password directly
      // In a real implementation, you'd verify the token first
      
      const user = await User.findOne({ where: { email } });
      if (!user) {
        return res.status(404).json({ message: 'User not found' });
      }

      // Update user password
      user.password = password;
      await user.save();

      return res.json({
        success: true,
        message: 'Password reset successful',
      });
    } catch (error) {
      console.error('Reset password error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Get current user
  static async getCurrentUser(req: any, res: Response) {
    try {
      const user = await User.findByPk(req.user.id, {
        attributes: { exclude: ['password'] },
      });

      if (!user) {
        return res.status(404).json({ message: 'User not found' });
      }

      return res.json({
        success: true,
        data: { user },
      });
    } catch (error) {
      console.error('Get current user error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Get user favourites
  static async getFavourites(req: any, res: Response) {
    try {
      const userId = req.user.id;
      const page = parseInt(req.query.page as string) || 1;
      const perPage = parseInt(req.query.per_page as string) || 10;
      const offset = (page - 1) * perPage;

      const { count, rows: favourites } = await Favourite.findAndCountAll({
        where: { user_id: userId },
        include: [
          {
            model: Service,
            as: 'service',
            include: [
              {
                model: Category,
                as: 'category',
              },
            ],
          },
        ],
        order: [['createdAt', 'DESC']],
        limit: perPage,
        offset: offset,
      });

      const totalPages = Math.ceil(count / perPage);

      return res.json({
        success: true,
        data: {
          favourites,
          pagination: {
            current_page: page,
            per_page: perPage,
            total: count,
            total_pages: totalPages,
            has_next_page: page < totalPages,
            has_prev_page: page > 1,
          },
        },
      });
    } catch (error) {
      console.error('Get favourites error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Add service to favourites
  static async addFavourite(req: any, res: Response) {
    try {
      const userId = req.user.id;
      const { service_id } = req.body;

      if (!service_id) {
        return res.status(400).json({ message: 'Service ID is required' });
      }

      // Check if service exists
      const service = await Service.findByPk(service_id);
      if (!service) {
        return res.status(404).json({ message: 'Service not found' });
      }

      // Check if already favourited
      const existingFavourite = await Favourite.findOne({
        where: { user_id: userId, service_id },
      });

      if (existingFavourite) {
        return res.status(400).json({ message: 'Service already in favourites' });
      }

      const favourite = await Favourite.create({
        user_id: userId,
        service_id,
      });

      return res.status(201).json({ success: true, data: { favourite } });
    } catch (error) {
      console.error('Add favourite error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Remove service from favourites
  static async removeFavourite(req: any, res: Response) {
    try {
      const userId = req.user.id;
      const { service_id } = req.params;

      const favourite = await Favourite.findOne({
        where: { user_id: userId, service_id },
      });

      if (!favourite) {
        return res.status(404).json({ message: 'Favourite not found' });
      }

      await favourite.destroy();
      return res.json({ success: true, message: 'Service removed from favourites' });
    } catch (error) {
      console.error('Remove favourite error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Check if service is favourited
  static async checkFavourite(req: any, res: Response) {
    try {
      const userId = req.user.id;
      const { service_id } = req.params;

      const favourite = await Favourite.findOne({
        where: { user_id: userId, service_id },
      });

      return res.json({
        success: true,
        data: { isFavourited: !!favourite },
      });
    } catch (error) {
      console.error('Check favourite error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }

  // Get user's favourite count
  static async getFavouriteCount(req: any, res: Response) {
    try {
      const userId = req.user.id;

      const count = await Favourite.count({
        where: { user_id: userId },
      });

      return res.json({
        success: true,
        data: { count },
      });
    } catch (error) {
      console.error('Get favourite count error:', error);
      return res.status(500).json({ message: 'Server error' });
    }
  }
} 