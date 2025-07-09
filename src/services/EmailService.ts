import nodemailer from 'nodemailer';

let transporter: nodemailer.Transporter | null = null;
let emailServiceAvailable = false;

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
    });

    // Verify connection configuration
    await transporter.verify();
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
      from: process.env['MAIL_FROM'],
      to: options.to,
      subject: options.subject,
      html: options.html,
      text: options.text,
    };

    const info = await transporter.sendMail(mailOptions);
    console.log('✅ Email sent successfully:', info.messageId);
    return info;
  } catch (error) {
    console.error('❌ Email sending failed:', error);
    // Don't throw error, just log it
    return { error: 'Email sending failed' };
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