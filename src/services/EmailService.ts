import nodemailer from 'nodemailer';

let transporter: nodemailer.Transporter | null = null;
let emailServiceAvailable = false;

export const getEmailServiceStatus = () => {
  return {
    available: emailServiceAvailable,
    configured: !!(process.env['SMTP_HOST'] && process.env['SMTP_USER'] && process.env['SMTP_PASS']),
    smtpHost: process.env['SMTP_HOST'] || 'Not configured',
    smtpUser: process.env['SMTP_USER'] || 'Not configured',
    smtpPort: process.env['SMTP_PORT'] || '587',
  };
};

export const initializeEmailService = async () => {
  try {
    // Check if SMTP configuration is provided
    const smtpHost = process.env['SMTP_HOST'];
    const smtpUser = process.env['SMTP_USER'];
    const smtpPass = process.env['SMTP_PASS'];

    console.log('🔧 Initializing email service...');
    console.log('📧 SMTP Configuration:', {
      host: smtpHost || 'Not set',
      user: smtpUser || 'Not set',
      port: process.env['SMTP_PORT'] || '587',
      configured: !!(smtpHost && smtpUser && smtpPass)
    });

    if (!smtpHost || !smtpUser || !smtpPass) {
      console.log('⚠️  SMTP configuration not provided, email service will be disabled');
      emailServiceAvailable = false;
      return;
    }

    console.log('🔄 Creating SMTP transporter...');
    transporter = nodemailer.createTransport({
      host: smtpHost,
      port: parseInt(process.env['SMTP_PORT'] || '587'),
      secure: false, // true for 465, false for other ports
      auth: {
        user: smtpUser,
        pass: smtpPass,
      },
      // Add connection timeout settings
      connectionTimeout: 10000, // 10 seconds
      greetingTimeout: 10000, // 10 seconds
      socketTimeout: 10000, // 10 seconds
    });

    console.log('🔍 Verifying SMTP connection...');
    // Verify connection configuration with timeout
    const verifyPromise = transporter.verify();
    const timeoutPromise = new Promise((_, reject) => {
      setTimeout(() => reject(new Error('Connection timeout')), 15000); // 15 second timeout
    });

    await Promise.race([verifyPromise, timeoutPromise]);
    emailServiceAvailable = true;
    console.log('✅ Email service initialized successfully');
  } catch (error) {
    console.error('❌ Email service initialization failed:', error);
    console.log('⚠️  Email service will be disabled, emails will be logged to console');
    emailServiceAvailable = false;
    transporter = null;
  }
};

export const sendEmail = async (options: {
  to: string;
  subject: string;
  html: string;
  text?: string;
}) => {
  try {
    if (!emailServiceAvailable || !transporter) {
      // Log email to console when email service is not available
      console.log('📧 Email would be sent (email service disabled):', {
        to: options.to,
        subject: options.subject,
        html: options.html,
        text: options.text,
      });
      return { messageId: 'console-log' };
    }

    const mailOptions = {
      from: process.env['MAIL_FROM'] || process.env['SMTP_USER'],
      to: options.to,
      subject: options.subject,
      html: options.html,
      text: options.text,
    };

    // Add timeout to email sending
    const sendPromise = transporter.sendMail(mailOptions);
    const timeoutPromise = new Promise((_, reject) => {
      setTimeout(() => reject(new Error('Email sending timeout')), 30000); // 30 second timeout
    });

    const info = await Promise.race([sendPromise, timeoutPromise]);
    console.log('✅ Email sent successfully:', info.messageId);
    return info;
  } catch (error) {
    console.error('❌ Email sending failed:', error);
    
    // Log the email content to console as fallback
    console.log('📧 Email content (sending failed):', {
      to: options.to,
      subject: options.subject,
      html: options.html,
      text: options.text,
    });
    
    // Don't throw error, just log it and return a fallback response
    return { error: 'Email sending failed', messageId: 'failed' };
  }
};

export const sendWelcomeEmail = async (user: { name: string; email: string }) => {
  const html = `
    <h1>Welcome to Audio Mixing Services!</h1>
    <p>Hi ${user.name},</p>
    <p>Thank you for registering with us. We're excited to have you on board!</p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;

  return sendEmail({
    to: user.email,
    subject: 'Welcome to Audio Mixing Services',
    html,
  });
};

export const sendPasswordResetEmail = async (user: { name: string; email: string }, resetToken: string) => {
  const resetUrl = `${process.env['APP_URL']}/reset-password/${user.email}/${resetToken}`;
  
  const html = `
    <h1>Password Reset Request</h1>
    <p>Hi ${user.name},</p>
    <p>You requested a password reset. Click the link below to reset your password:</p>
    <a href="${resetUrl}">Reset Password</a>
    <p>If you didn't request this, please ignore this email.</p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;

  return sendEmail({
    to: user.email,
    subject: 'Password Reset Request',
    html,
  });
};

export const sendOrderConfirmationEmail = async (user: { name: string; email: string }, order: any) => {
  const html = `
    <h1>Order Confirmation</h1>
    <p>Hi ${user.name},</p>
    <p>Your order #${order.orderNumber} has been confirmed.</p>
    <p>Total Amount: $${order.totalAmount}</p>
    <p>We'll start working on your audio files soon!</p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;

  return sendEmail({
    to: user.email,
    subject: `Order Confirmation - #${order.orderNumber}`,
    html,
  });
};

export const sendOrderCompletedEmail = async (user: { name: string; email: string }, order: any) => {
  const html = `
    <h1>Order Completed</h1>
    <p>Hi ${user.name},</p>
    <p>Your order #${order.orderNumber} has been completed!</p>
    <p>You can download your processed audio files from your dashboard.</p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;

  return sendEmail({
    to: user.email,
    subject: `Order Completed - #${order.orderNumber}`,
    html,
  });
};

export const sendOrderSuccessEmail = async (data: {
  name: string;
  order_id: number;
  message: string;
  items: any[];
  url: string;
  email?: string;
}) => {
  const itemsHtml = data.items.map(item => `
    <tr>
      <td>${item.name}</td>
      <td>$${item.price}</td>
      <td>${item.quantity}</td>
      <td>$${item.total_price}</td>
    </tr>
  `).join('');

  const html = `
    <h1>Order Success</h1>
    <p>Hi ${data.name},</p>
    <p>${data.message}</p>
    <h2>Order Details:</h2>
    <table border="1" style="border-collapse: collapse; width: 100%;">
      <thead>
        <tr>
          <th>Service</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        ${itemsHtml}
      </tbody>
    </table>
    <p><a href="${data.url}">View Order Details</a></p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;

  return sendEmail({
    to: data.email || 'user@example.com',
    subject: `Order Success - #${data.order_id}`,
    html,
  });
};

export const sendOrderStatusEmail = async (data: {
  name: string;
  order_id: number;
  status: string;
  message: string;
  url: string;
  email?: string;
}) => {
  const html = `
    <h1>Order Status Update</h1>
    <p>Hi ${data.name},</p>
    <p>Your order #${data.order_id} status has been updated to: <strong>${data.status}</strong></p>
    <p>${data.message}</p>
    <p><a href="${data.url}">View Order Details</a></p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;

  return sendEmail({
    to: data.email || 'user@example.com',
    subject: `Order Status Update - #${data.order_id}`,
    html,
  });
};

export const sendGiftCardEmail = async (data: {
  name: string;
  message: string;
  code: string;
  email?: string;
}) => {
  const html = `
    <h1>Gift Card Purchase</h1>
    <p>Hi ${data.name},</p>
    <p>${data.message}</p>
    <p><strong>Your Gift Card Code: ${data.code}</strong></p>
    <p>You can use this code for future purchases.</p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;

  return sendEmail({
    to: data.email || 'user@example.com',
    subject: 'Gift Card Purchase Confirmation',
    html,
  });
};

export const sendRevisionSuccessEmail = async (data: {
  name: string;
  order_id: number;
  service_id: number;
  amount: number;
  message: string;
  url: string;
  email?: string;
}) => {
  const html = `
    <h1>Revision Purchase Success</h1>
    <p>Hi ${data.name},</p>
    <p>${data.message}</p>
    <p><strong>Order ID:</strong> ${data.order_id}</p>
    <p><strong>Service ID:</strong> ${data.service_id}</p>
    <p><strong>Amount:</strong> $${data.amount}</p>
    <p><a href="${data.url}">View Order Details</a></p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;

  return sendEmail({
    to: data.email || 'user@example.com',
    subject: `Revision Purchase - Order #${data.order_id}`,
    html,
  });
}; 