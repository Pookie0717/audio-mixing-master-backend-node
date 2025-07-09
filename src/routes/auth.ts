import { Router } from 'express';
import { AuthController } from '../controllers/AuthController';
import { auth } from '../middleware/auth';

const router = Router();

// Register user
router.post('/register', AuthController.register);

// Login user
router.post('/login', AuthController.login);

// Forgot password
router.post('/forgot-password', AuthController.forgotPassword);

// Reset password
router.post('/reset-password/:email/:token', AuthController.resetPassword);

// Get current user
router.get('/me', auth, AuthController.getCurrentUser);

export default router; 