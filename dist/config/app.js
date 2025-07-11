"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.config = void 0;
exports.config = {
    port: process.env['PORT'] || 3000,
    nodeEnv: process.env['NODE_ENV'] || 'development',
    jwtSecret: process.env['JWT_SECRET'] || 'fallback-secret-key',
    jwtExpiresIn: process.env['JWT_EXPIRES_IN'] || '7d',
    maxFileSize: parseInt(process.env['MAX_FILE_SIZE'] || '10485760'),
    uploadPath: process.env['UPLOAD_PATH'] || './uploads',
    logLevel: process.env['LOG_LEVEL'] || 'info',
    rateLimitWindowMs: 15 * 60 * 1000,
    rateLimitMax: 100,
};
//# sourceMappingURL=app.js.map