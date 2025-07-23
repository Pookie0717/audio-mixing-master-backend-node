import nodemailer from 'nodemailer';

let transporter: nodemailer.Transporter | null = null;
let emailServiceAvailable = false;

// Helper function to strip HTML tags
const stripHtmlTags = (html: string): string => {
  return html.replace(/<[^>]*>/g, '');
};

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


    if (!smtpHost || !smtpUser || !smtpPass) {
      console.log('⚠️  SMTP configuration not provided, email service will be disabled');
      emailServiceAvailable = false;
      return;
    }

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
      from: process.env['SMTP_USER'],
      to: options.to,
      subject: options.subject,
      html: options.html,
      text: options.text || stripHtmlTags(options.html),
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
  const resetUrl = `${process.env['FRONTEND_URL']}/reset-password/${user.email}/${resetToken}`;
  
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

// New email templates
export const sendSubscriptionDiscountEmail = async (data: {
  name: string;
  email: string;
  couponCode: string;
}) => {
  const html = `
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
      <h1 style="color: #333;">🎁 Your 10% OFF Coupon Code!</h1>
      
      <h2 style="color: #333;">Your 10% Off Coupon Code!</h2>
      <p>Thank you for subscribing!</p>
      
      <p>Here's your exclusive coupon code: <strong style="color: #e74c3c; font-size: 18px;">${data.couponCode}</strong></p>
      
      <p>To redeem, simply copy the code and paste it at checkout to receive your discount. Enjoy 10% off on any of our services and include as many songs as you'd like for this first order.</p>
      
      <p><strong>Note:</strong> This code is valid for a single use only. Don't miss the chance to maximize your savings on your initial purchase.</p>
      
      <div style="text-align: center; margin: 30px 0;">
        <a href="https://audiomixingmastering.com/services" style="background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Use Coupon Now</a>
      </div>
      
      <p style="margin-top: 30px; color: #666;">Audio Mixing Mastering</p>
    </div>
  `;

  return sendEmail({
    to: data.email,
    subject: '🎁 Your 10% OFF Coupon Code!',
    html,
  });
};

export const sendOrderStatusUpdateEmail = async (data: {
  name: string;
  email: string;
  order_id: number;
  status: string;
  url: string;
}) => {
  const html = `
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
      <h1 style="color: #333;">Update on Your Order Status!</h1>
      
      <p>Your project is now <strong style="color: #e74c3c;">${data.status}</strong>! You can view the latest changes or additions in your panel.</p>
      
      <p><strong>Order ID:</strong> ${data.order_id}</p>
      
      <div style="text-align: center; margin: 30px 0;">
        <a href="${data.url}" style="background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">View Order Details</a>
      </div>
      
      <p>If you have any questions or need further clarification, feel free to reach out.</p>
      
      <p>Thank you for your continued trust - We're making great progress!</p>
      
      <p style="margin-top: 30px; color: #666;">Audio Mixing Mastering</p>
    </div>
  `;

  return sendEmail({
    to: data.email,
    subject: 'Update on Your Order Status!',
    html,
  });
};

export const sendOrderDeliveredEmail = async (data: {
  name: string;
  email: string;
  order_id: number;
  url: string;
}) => {
  const html = `
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
      <h1 style="color: #333;">Hooray! Your Order Has Been Successfully Delivered!</h1>
      
      <p>Hi ${data.name},</p>
      
      <p>We are pleased to inform you that your order has been delivered successfully! You can view the full details of your order by clicking the button below:</p>
      
      <p><strong>Order ID:</strong> ${data.order_id}</p>
      
      <div style="text-align: center; margin: 30px 0;">
        <a href="${data.url}" style="background-color: #27ae60; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">View Order Details</a>
      </div>
      
      <p>If you have any questions or need assistance, feel free to reach out to us. We appreciate your trust in us and hope you enjoy your new radio ready banger! ;)</p>
      
      <p style="margin-top: 30px; color: #666;">Audio Mixing Mastering</p>
    </div>
  `;

  return sendEmail({
    to: data.email,
    subject: 'Hooray! Your Order Has Been Successfully Delivered!',
    html,
  });
};

export const sendRevisionProgressEmail = async (data: {
  name: string;
  email: string;
  order_id: number;
}) => {
  const html = `
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
      <h1 style="color: #333;">Your Revision is in Progress</h1>
      
      <p>Hi ${data.name},</p>
      
      <p>Your revision request has been successfully received, and our engineering team is actively working on it.</p>
      
      <p><strong>Order ID:</strong> ${data.order_id}</p>
      
      <p>We'll keep you updated on the progress and notify you promptly once the revision is completed.</p>
      
      <p>Thank you for your patience and trust in our team!</p>
      
      <p style="margin-top: 30px; color: #666;">Audio Mixing Mastering</p>
    </div>
  `;

  return sendEmail({
    to: data.email,
    subject: 'Your Revision is in Progress',
    html,
  });
};

export const sendPasswordResetEmailUpdated = async (data: {
  name: string;
  email: string;
  resetUrl: string;
}) => {
  const html = `
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
      <h1 style="color: #333;">Password Reset Request</h1>
      
      <p>Hi ${data.name},</p>
      
      <p>It looks like you've requested a password reset. If you did not make this request, please disregard this email.</p>
      
      <p>To reset your password, simply click the link below:</p>
      
      <div style="text-align: center; margin: 30px 0;">
        <a href="${data.resetUrl}" style="background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a>
      </div>
      
      <p>Thank you,<br>Audio Mixing Mastering Team</p>
    </div>
  `;

  return sendEmail({
    to: data.email,
    subject: 'Password Reset Request',
    html,
  });
};

export const sendEmailVerificationRequest = async (data: {
  name: string;
  email: string;
  verificationUrl: string;
}) => {
    const html = `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1 style="color: #333;">Action Required: Verify Your Email Address</h1>
        <p>Dear ${data.name},</p>
        <p>To complete your registration, please click the button below to verify your email address:</p>
        <div style="text-align: center; margin: 30px 0;">
          <a href="${data.verificationUrl}" style="background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Verify My Email</a>
        </div>
        <p>If you did not initiate this request, please disregard this email.</p>
        <p>Thank you for joining us at AudioMixingMastering.com!</p>
        <p>Best regards,<br>Audio Mixing Mastering</p>
      </div>
    `;

  return sendEmail({
    to: data.email,
    subject: 'Action Required: Verify Your Email Address',
    html,
  });
};

export const sendEmailVerificationSuccess = async (data: {
  name: string;
  email: string;
}) => {
  const html = `
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
      <h1 style="color: #333;">Email Verification Successful</h1>
      
      <p>Dear ${data.name},</p>
      
      <p>We are pleased to inform you that your email address has been successfully verified. You can now enjoy full access to all features of our services.</p>
      
      <p>If you have any questions or need further assistance, please don't hesitate to reach out.</p>
      
      <p>Thank you for being a valued member of our community!</p>
      
      <p>Best regards,<br>Audio Mixing Mastering</p>
    </div>
  `;

  return sendEmail({
    to: data.email,
    subject: 'Email Verification Successful',
    html,
  });
};

export const sendCartReminderEmail = async (data: {
  name: string;
  email: string;
}) => {
  const html = `
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
      <h1 style="color: #333;">Don't Forget! Your Services Are Waiting in Your Cart!</h1>
      
      <p>Hi ${data.name},</p>
      
      <p>It looks like you've left some services in your shopping cart. Don't miss out!</p>
      
      <div style="text-align: center; margin: 30px 0;">
        <a href="https://audiomixingmastering.com/services" style="background-color: #e74c3c; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Complete Your Order Now</a>
      </div>
      
      <p>Complete your order now at audiomixingmastering.com/services before it's gone.</p>
      
      <p>Thank you,<br>Audio Mixing Mastering</p>
    </div>
  `;

  return sendEmail({
    to: data.email,
    subject: "Don't Forget! Your Services Are Waiting in Your Cart!",
    html,
  });
}; 