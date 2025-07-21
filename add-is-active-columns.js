const { Sequelize } = require('sequelize');
require('dotenv').config();

// Create Sequelize instance
const sequelize = new Sequelize(
  process.env.DB_DATABASE,
  process.env.DB_USERNAME,
  process.env.DB_PASSWORD,
  {
    host: process.env.DB_HOST,
    port: parseInt(process.env.DB_PORT),
    dialect: 'mysql',
    logging: false
  }
);

async function addIsActiveColumns() {
  try {
    console.log('Adding is_active columns to blog tables...');

    // Add is_active column to blog_categories table
    await sequelize.query(`
      ALTER TABLE blog_categories 
      ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 
      AFTER description
    `);
    console.log('✅ Added is_active column to blog_categories table');

    // Change is_published to TINYINT
    await sequelize.query(`
      ALTER TABLE blogs 
      MODIFY COLUMN is_published TINYINT(1) NOT NULL DEFAULT 0
    `);
    console.log('✅ Changed is_published to TINYINT');

    // Add is_active column to blogs table
    await sequelize.query(`
      ALTER TABLE blogs 
      ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 
      AFTER is_published
    `);
    console.log('✅ Added is_active column to blogs table');

    // Update existing records to have is_active = 1
    await sequelize.query(`
      UPDATE blog_categories 
      SET is_active = 1 
      WHERE is_active IS NULL
    `);
    console.log('✅ Updated existing blog_categories records');

    await sequelize.query(`
      UPDATE blogs 
      SET is_active = 1 
      WHERE is_active IS NULL
    `);
    console.log('✅ Updated existing blogs records');

    console.log('🎉 Successfully added is_active columns to both tables!');
    
  } catch (error) {
    console.error('❌ Error adding is_active columns:', error);
    throw error;
  } finally {
    await sequelize.close();
  }
}

// Run the migration
addIsActiveColumns()
  .then(() => {
    console.log('Migration completed successfully!');
    process.exit(0);
  })
  .catch((error) => {
    console.error('Migration failed:', error);
    process.exit(1);
  }); 