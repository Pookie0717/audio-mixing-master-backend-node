import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import User from '../models/User';

export interface AuthRequest extends Request {
  user?: any;
}

export const auth = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    const token = req.header('Authorization')?.replace('Bearer ', '');

    if (!token) {
      return res.status(401).json({ message: 'No token, authorization denied' });
    }

    const decoded = jwt.verify(token, process.env['JWT_SECRET'] || 'fallback-secret') as any;
    
    const user = await User.findByPk(decoded.id, {
      attributes: ['id', 'first_name', 'last_name', 'email', 'role', 'is_active'],
    });

    if (!user) {
      return res.status(401).json({ message: 'Token is not valid' });
    }

    if (user.is_active !== 1) {
      return res.status(401).json({ message: 'Account is not active' });
    }

    req.user = user;
    return next();
  } catch (error) {
    return res.status(401).json({ message: 'Token is not valid' });
  }
};

// Optional auth middleware - allows both authenticated and guest users
export const optionalAuth = async (req: AuthRequest, _res: Response, next: NextFunction) => {
  try {
    const token = req.header('Authorization')?.replace('Bearer ', '');

    if (!token) {
      // No token provided, continue as guest user
      req.user = null;
      return next();
    }

    const decoded = jwt.verify(token, process.env['JWT_SECRET'] || 'fallback-secret') as any;
    
    const user = await User.findByPk(decoded.id, {
      attributes: ['id', 'first_name', 'last_name', 'email', 'role', 'is_active'],
    });

    if (!user) {
      // Invalid token, continue as guest user
      req.user = null;
      return next();
    }

    if (user.is_active !== 1) {
      // Inactive account, continue as guest user
      req.user = null;
      return next();
    }

    req.user = user;
    return next();
  } catch (error) {
    // Token error, continue as guest user
    req.user = null;
    return next();
  }
};

export const adminAuth = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    await new Promise<void>((resolve, reject) => {
      auth(req, res, (err) => {
        if (err) reject(err);
        else resolve();
      });
    });
    
    if (req.user?.role !== 'ADMIN') {
      return res.status(403).json({ message: 'Access denied. Admin only.' });
    }
    
    return next();
  } catch (error) {
    return res.status(401).json({ message: 'Authentication failed' });
  }
};

export const engineerAuth = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    await new Promise<void>((resolve, reject) => {
      auth(req, res, (err) => {
        if (err) reject(err);
        else resolve();
      });
    });
    
    if (!['ADMIN', 'ENGINEER'].includes(req.user?.role)) {
      return res.status(403).json({ message: 'Access denied. Engineer or Admin only.' });
    }
    
    return next();
  } catch (error) {
    return res.status(401).json({ message: 'Authentication failed' });
  }
}; 