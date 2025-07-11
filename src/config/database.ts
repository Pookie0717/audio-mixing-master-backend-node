import { Sequelize } from 'sequelize';
import dotenv from 'dotenv';

dotenv.config();

const sequelize = new Sequelize(
  process.env['DATABASE_NAME'] || 'audio_mixing',
  process.env['DATABASE_USER'] || 'adminuser',
  process.env['DATABASE_PASSWORD'] || 'AndyKaPass_123',
  {
    host: process.env['DATABASE_HOST'] || '69.55.54.209',
    port: parseInt(process.env['DATABASE_PORT'] || '3306'),
    dialect: 'mysql',
    logging: process.env['NODE_ENV'] === 'development' ? console.log : false,
    pool: {
      max: 5,
      min: 0,
      acquire: 30000,
      idle: 10000,
    },
    define: {
      timestamps: true,
      underscored: true,
    },
  }
);

export const initializeDatabase = async () => {
  try {
    await sequelize.authenticate();
    console.log('✅ Database connection established successfully');
    
    // Sync all models (in development)
    if (process.env['NODE_ENV'] === 'development') {
      await sequelize.sync({ alter: true });
      console.log('✅ Database models synchronized');
    }
  } catch (error) {
    console.error('❌ Database connection failed:', error);
    throw error;
  }
};

export default sequelize; 