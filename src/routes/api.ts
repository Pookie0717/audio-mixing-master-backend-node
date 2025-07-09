import { Router, Request, Response } from 'express';
import multer from 'multer';
import { AuthController } from '../controllers/AuthController';
import { ServiceController } from '../controllers/ServiceController';
import { CategoryController } from '../controllers/CategoryController';
import { OrderController } from '../controllers/OrderController';
import { CartController } from '../controllers/CartController';
import { PaymentController } from '../controllers/PaymentController';
import { auth } from '../middleware/auth';
import { SampleAudioController } from '../controllers/SampleAudioController';
import { GalleryController } from '../controllers/GalleryController';
import { FaqController } from '../controllers/FaqController';
import { TestimonialController } from '../controllers/TestimonialController';
import { UploadLeadController } from '../controllers/UploadLeadController';
import { ContactLeadController } from '../controllers/ContactLeadController';
// Import controllers (to be implemented)
// import AuthController from '../controllers/AuthController';
// ... (other controllers)

const router = Router();

// AUTH ROUTES
router.post('/auth/register', AuthController.register);
router.post('/auth/login', AuthController.login);
router.get('/auth/verify-email/:id/:hash', (_req: Request, _res: Response) => {/* AuthController.emailVerify */});
router.post('/auth/forgot-password', AuthController.forgotPassword);
router.post('/auth/reset-password', AuthController.resetPassword);
router.get('/auth/me', auth, AuthController.getCurrentUser);

// Favourite routes
router.get('/my-favourites', auth, AuthController.getFavourites);
router.post('/my-favourites', auth, AuthController.addFavourite);
router.delete('/my-favourites/:service_id', auth, AuthController.removeFavourite);
router.get('/my-favourites/:service_id/check', auth, AuthController.checkFavourite);
router.get('/my-favourites/count', auth, AuthController.getFavouriteCount);

// PUBLIC ROUTES
router.get('/sample-audios', SampleAudioController.index);
router.get('/sample-audios/:id', (_req: Request, _res: Response) => {/* SampleAudioController.show */});
router.get('/gallary', GalleryController.index);
router.get('/gallary/:id', GalleryController.show);
router.get('/categories', CategoryController.index);
// router.get('/categories/:id', CategoryController.show);
router.get('/categories/with-count', CategoryController.getWithServices);
router.get('/tags', (_req: Request, _res: Response) => {/* ServiceTagController.index */});
router.get('/services', ServiceController.index);
router.get('/services/:tag', ServiceController.show);
router.get('/services/search', ServiceController.search);
router.get('/services/category/:categoryId', ServiceController.getByCategory);
router.get('/service-details/:id', ServiceController.getServiceDetails);
router.get('/services-list', (_req: Request, _res: Response) => {/* AdminServiceController.serviceList */});
router.get('/gifts', (_req: Request, _res: Response) => {/* GiftController.index */});
router.get('/gifts/:id', (_req: Request, _res: Response) => {/* GiftController.show */});
router.get('/lead/generation', (_req: Request, _res: Response) => {/* leadGenerationController.index */});
router.get('/lead/generation/:id', (_req: Request, _res: Response) => {/* leadGenerationController.show */});
router.post('/lead/generation', (_req: Request, _res: Response) => {/* leadGenerationController.store */});
router.delete('/lead/generation/:id', (_req: Request, _res: Response) => {/* leadGenerationController.destroy */});
router.get('/upload/lead/gen', UploadLeadController.index);
router.get('/upload/lead/gen/:id', UploadLeadController.show);

const upload = multer();

router.post('/upload/lead/gen', upload.none(), UploadLeadController.store);
router.delete('/upload/lead/gen/:id', UploadLeadController.destroy);
router.get('/download/zip/lead/:id', UploadLeadController.downloadZip);
router.post('/download-audio/:id', UploadLeadController.downloadAudio);
router.get('/export/lead', (_req: Request, _res: Response) => {/* leadGenerationController.exportLead */});
router.get('/contact/lead/generation', ContactLeadController.index);
router.get('/contact/lead/generation/:id', ContactLeadController.show);
router.post('/contact/lead/generation', ContactLeadController.store);
router.delete('/contact/lead/generation/:id', ContactLeadController.destroy);
router.get('/promo-codes', (_req: Request, _res: Response) => {/* ServicesPromoCodeController.index */});
router.get('/promo-codes/:id', (_req: Request, _res: Response) => {/* ServicesPromoCodeController.show */});
router.put('/promo-codes/:id', (_req: Request, _res: Response) => {/* ServicesPromoCodeController.update */});
router.delete('/promo-codes/:id', (_req: Request, _res: Response) => {/* ServicesPromoCodeController.destroy */});
router.post('/insert-service-promo-codes', (_req: Request, _res: Response) => {/* ServicesPromoCodeController.insertServicePromoCodes */});
router.get('/my-promo-codes/verify/:code', (_req: Request, _res: Response) => {/* ServicesPromoCodeController.verifyPromoCodes */});
router.get('/faq-list', FaqController.FaqList);
router.get('/testimonial-list', TestimonialController.TestimonialList);
router.post('/buy-revision', (_req: Request, _res: Response) => {/* PayPalController.revisionSuccess */});
router.post('/order/update-status/:id', (_req: Request, _res: Response) => {/* OrderController.orderUpdateStatus */});
router.get('/generate-pdf', (_req: Request, _res: Response) => {/* ExcelController.exportOrders */});

// AUTHENTICATED ROUTES (to be wrapped with auth middleware)
router.get('/orders', auth, OrderController.index);
router.get('/orders/:id', auth, OrderController.show);
router.post('/orders', auth, OrderController.create);
router.put('/orders/:id/status', auth, OrderController.updateStatus);

// Cart routes (protected)
router.get('/cart', auth, CartController.index);
router.post('/cart', auth, CartController.add);
router.put('/cart/:serviceId', auth, CartController.update);
router.delete('/cart/:serviceId', auth, CartController.remove);

// Payment routes (protected)
router.post('/payments/process', auth, PaymentController.processPayment);
router.post('/payments/:paymentId/refund', auth, PaymentController.refundPayment);
router.get('/payments/history', auth, PaymentController.getPaymentHistory);
router.get('/payments/:id', auth, PaymentController.getPayment);

// ADMIN ROUTES (to be wrapped with admin middleware)
// router.use('/admin', adminMiddleware, adminRouter);
// ... (add admin routes here)

export default router; 