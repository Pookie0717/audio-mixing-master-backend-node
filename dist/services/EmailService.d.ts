export declare const initializeEmailService: () => Promise<void>;
export declare const sendEmail: (options: {
    to: string;
    subject: string;
    html: string;
    text?: string;
}) => Promise<any>;
export declare const sendWelcomeEmail: (user: {
    name: string;
    email: string;
}) => Promise<any>;
export declare const sendPasswordResetEmail: (user: {
    name: string;
    email: string;
}, resetToken: string) => Promise<any>;
export declare const sendOrderConfirmationEmail: (user: {
    name: string;
    email: string;
}, order: any) => Promise<any>;
export declare const sendOrderCompletedEmail: (user: {
    name: string;
    email: string;
}, order: any) => Promise<any>;
export declare const sendOrderSuccessEmail: (data: {
    name: string;
    order_id: number;
    message: string;
    items: any[];
    url: string;
    email?: string;
}) => Promise<any>;
export declare const sendOrderStatusEmail: (data: {
    name: string;
    order_id: number;
    status: string;
    message: string;
    url: string;
    email?: string;
}) => Promise<any>;
export declare const sendGiftCardEmail: (data: {
    name: string;
    message: string;
    code: string;
    email?: string;
}) => Promise<any>;
export declare const sendRevisionSuccessEmail: (data: {
    name: string;
    order_id: number;
    service_id: number;
    amount: number;
    message: string;
    url: string;
    email?: string;
}) => Promise<any>;
//# sourceMappingURL=EmailService.d.ts.map