"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.sendRevisionSuccessEmail = exports.sendGiftCardEmail = exports.sendOrderStatusEmail = exports.sendOrderSuccessEmail = exports.sendOrderCompletedEmail = exports.sendOrderConfirmationEmail = exports.sendPasswordResetEmail = exports.sendWelcomeEmail = exports.sendEmail = exports.initializeEmailService = void 0;
const nodemailer_1 = __importDefault(require("nodemailer"));
let transporter = null;
let emailServiceAvailable = false;
const initializeEmailService = async () => {
    try {
        const smtpHost = process.env['SMTP_HOST'];
        const smtpUser = process.env['SMTP_USER'];
        const smtpPass = process.env['SMTP_PASS'];
        if (!smtpHost || !smtpUser || !smtpPass) {
            console.log('⚠️  SMTP configuration not provided, email service will be disabled');
            emailServiceAvailable = false;
            return;
        }
        transporter = nodemailer_1.default.createTransport({
            host: smtpHost,
            port: parseInt(process.env['SMTP_PORT'] || '587'),
            secure: false,
            auth: {
                user: smtpUser,
                pass: smtpPass,
            },
        });
        await transporter.verify();
        emailServiceAvailable = true;
        console.log('✅ Email service initialized successfully');
    }
    catch (error) {
        console.error('❌ Email service initialization failed:', error);
        console.log('⚠️  Email service will be disabled, emails will be logged to console');
        emailServiceAvailable = false;
        transporter = null;
    }
};
exports.initializeEmailService = initializeEmailService;
const sendEmail = async (options) => {
    try {
        if (!emailServiceAvailable || !transporter) {
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
    }
    catch (error) {
        console.error('❌ Email sending failed:', error);
        return { error: 'Email sending failed' };
    }
};
exports.sendEmail = sendEmail;
const sendWelcomeEmail = async (user) => {
    const html = `
    <h1>Welcome to Audio Mixing Services!</h1>
    <p>Hi ${user.name},</p>
    <p>Thank you for registering with us. We're excited to have you on board!</p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;
    return (0, exports.sendEmail)({
        to: user.email,
        subject: 'Welcome to Audio Mixing Services',
        html,
    });
};
exports.sendWelcomeEmail = sendWelcomeEmail;
const sendPasswordResetEmail = async (user, resetToken) => {
    const resetUrl = `${process.env['APP_URL']}/reset-password/${user.email}/${resetToken}`;
    const html = `
    <h1>Password Reset Request</h1>
    <p>Hi ${user.name},</p>
    <p>You requested a password reset. Click the link below to reset your password:</p>
    <a href="${resetUrl}">Reset Password</a>
    <p>If you didn't request this, please ignore this email.</p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;
    return (0, exports.sendEmail)({
        to: user.email,
        subject: 'Password Reset Request',
        html,
    });
};
exports.sendPasswordResetEmail = sendPasswordResetEmail;
const sendOrderConfirmationEmail = async (user, order) => {
    const html = `
    <h1>Order Confirmation</h1>
    <p>Hi ${user.name},</p>
    <p>Your order #${order.orderNumber} has been confirmed.</p>
    <p>Total Amount: $${order.totalAmount}</p>
    <p>We'll start working on your audio files soon!</p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;
    return (0, exports.sendEmail)({
        to: user.email,
        subject: `Order Confirmation - #${order.orderNumber}`,
        html,
    });
};
exports.sendOrderConfirmationEmail = sendOrderConfirmationEmail;
const sendOrderCompletedEmail = async (user, order) => {
    const html = `
    <h1>Order Completed</h1>
    <p>Hi ${user.name},</p>
    <p>Your order #${order.orderNumber} has been completed!</p>
    <p>You can download your processed audio files from your dashboard.</p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;
    return (0, exports.sendEmail)({
        to: user.email,
        subject: `Order Completed - #${order.orderNumber}`,
        html,
    });
};
exports.sendOrderCompletedEmail = sendOrderCompletedEmail;
const sendOrderSuccessEmail = async (data) => {
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
    return (0, exports.sendEmail)({
        to: data.email || 'user@example.com',
        subject: `Order Success - #${data.order_id}`,
        html,
    });
};
exports.sendOrderSuccessEmail = sendOrderSuccessEmail;
const sendOrderStatusEmail = async (data) => {
    const html = `
    <h1>Order Status Update</h1>
    <p>Hi ${data.name},</p>
    <p>Your order #${data.order_id} status has been updated to: <strong>${data.status}</strong></p>
    <p>${data.message}</p>
    <p><a href="${data.url}">View Order Details</a></p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;
    return (0, exports.sendEmail)({
        to: data.email || 'user@example.com',
        subject: `Order Status Update - #${data.order_id}`,
        html,
    });
};
exports.sendOrderStatusEmail = sendOrderStatusEmail;
const sendGiftCardEmail = async (data) => {
    const html = `
    <h1>Gift Card Purchase</h1>
    <p>Hi ${data.name},</p>
    <p>${data.message}</p>
    <p><strong>Your Gift Card Code: ${data.code}</strong></p>
    <p>You can use this code for future purchases.</p>
    <p>Best regards,<br>The Audio Mixing Team</p>
  `;
    return (0, exports.sendEmail)({
        to: data.email || 'user@example.com',
        subject: 'Gift Card Purchase Confirmation',
        html,
    });
};
exports.sendGiftCardEmail = sendGiftCardEmail;
const sendRevisionSuccessEmail = async (data) => {
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
    return (0, exports.sendEmail)({
        to: data.email || 'user@example.com',
        subject: `Revision Purchase - Order #${data.order_id}`,
        html,
    });
};
exports.sendRevisionSuccessEmail = sendRevisionSuccessEmail;
//# sourceMappingURL=EmailService.js.map