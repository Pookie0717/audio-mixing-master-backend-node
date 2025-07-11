import 'express-async-errors';
import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import compression from 'compression';
// import rateLimit from 'express-rate-limit';
import dotenv from 'dotenv';

// Import routes
import apiRoutes from './routes/api';
import authRoutes from './routes/auth';
import adminRoutes from './routes/admin';

// Import middleware
import { errorHandler } from './middleware/errorHandler';
import { notFound } from './middleware/notFound';

// Import services
import { initializeDatabase } from './config/database';
import { initializeEmailService } from './services/EmailService';
import { initializePaymentServices } from './services/PaymentService';
import './models'; // Import models to register associations

// Load environment variables
dotenv.config();

const app = express();
const PORT = process.env['PORT'] || 3000;



// Rate limiting
// const limiter = rateLimit({
//   windowMs: parseInt(process.env['RATE_LIMIT_WINDOW_MS'] || '900000'), // 15 minutes
//   max: parseInt(process.env['RATE_LIMIT_MAX_REQUESTS'] || '100'), // limit each IP to 100 requests per windowMs
//   message: 'Too many requests from this IP, please try again later.',
//   standardHeaders: true,
//   legacyHeaders: false,
// });

// Middleware
app.use(helmet());
app.use(cors({
  origin: process.env['ALLOWED_ORIGINS']?.split(',') || [
    'http://localhost:3000',
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'https://audio-mixing-master-test.vercel.app/'
  ],
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With'],
}));
app.use(compression());
app.use(morgan('combined'));
// app.use(limiter);
app.use(express.json({ limit: '100mb' }));
app.use(express.urlencoded({ extended: true, limit: '100mb' }));

// Static files
app.use('/uploads', express.static('uploads'));
app.use('/public', express.static('public'));

// Root Route
app.get("/", (_req, res) => {
  res.send("🎉 Backend is running!");
});

// Health check
app.get('/health', (_req, res) => {
  res.status(200).json({
    status: 'OK',
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
    environment: process.env['NODE_ENV'],
  });
});

// API Routes
app.use('/api', apiRoutes);
app.use('/api/auth', authRoutes);
app.use('/api/admin', adminRoutes);

// Error handling middleware
app.use(notFound);
app.use(errorHandler);

// Graceful shutdown
process.on('SIGTERM', async () => {
  console.log('SIGTERM received, shutting down gracefully');
  process.exit(0);
});

process.on('SIGINT', async () => {
  console.log('SIGINT received, shutting down gracefully');
  process.exit(0);
});

// Initialize services and start server
async function startServer() {
  try {
    // Initialize database
    await initializeDatabase();
    
    // Initialize email service (optional - won't crash if it fails)
    try {
      await initializeEmailService();
    } catch (error) {
      console.log('⚠️  Email service initialization failed, continuing without email support');
    }
    
    // Initialize payment services
    await initializePaymentServices();
    
    // Start server
    app.listen(PORT, () => {
      console.log(`🚀 Server running on port ${PORT}`);
      console.log(`📊 Environment: ${process.env['NODE_ENV']}`);
      console.log(`🔗 API URL: http://localhost:${PORT}/api`);
    });
  } catch (error) {
    console.error('Failed to start server:', error);
    process.exit(1);
  }
}

startServer();

export default app; 