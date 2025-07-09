export const config = {
  // Server Configuration
  port: process.env['PORT'] || 3000,
  nodeEnv: process.env['NODE_ENV'] || 'development',
  
  // JWT Configuration
  jwtSecret: process.env['JWT_SECRET'] || 'fallback-secret-key',
  jwtExpiresIn: process.env['JWT_EXPIRES_IN'] || '7d',
  
  // File Upload Configuration
  maxFileSize: parseInt(process.env['MAX_FILE_SIZE'] || '10485760'), // 10MB
  uploadPath: process.env['UPLOAD_PATH'] || './uploads',
  
  // Logging
  logLevel: process.env['LOG_LEVEL'] || 'info',
  
  // Rate Limiting
  rateLimitWindowMs: 15 * 60 * 1000, // 15 minutes
  rateLimitMax: 100, // limit each IP to 100 requests per windowMs
} as const; 