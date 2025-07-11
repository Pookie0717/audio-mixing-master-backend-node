"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.ContactLeadGeneration = exports.UploadLeadGeneration = exports.FAQ = exports.Gallery = exports.Sample = exports.Label = exports.Favourite = exports.Testimonial = exports.Payment = exports.Cart = exports.OrderItem = exports.Order = exports.Service = exports.Category = exports.User = void 0;
const User_1 = __importDefault(require("./User"));
exports.User = User_1.default;
const Category_1 = __importDefault(require("./Category"));
exports.Category = Category_1.default;
const Service_1 = __importDefault(require("./Service"));
exports.Service = Service_1.default;
const Order_1 = require("./Order");
Object.defineProperty(exports, "Order", { enumerable: true, get: function () { return Order_1.Order; } });
Object.defineProperty(exports, "OrderItem", { enumerable: true, get: function () { return Order_1.OrderItem; } });
const Cart_1 = __importDefault(require("./Cart"));
exports.Cart = Cart_1.default;
const Payment_1 = __importDefault(require("./Payment"));
exports.Payment = Payment_1.default;
const Testimonial_1 = __importDefault(require("./Testimonial"));
exports.Testimonial = Testimonial_1.default;
const Favourite_1 = __importDefault(require("./Favourite"));
exports.Favourite = Favourite_1.default;
const Label_1 = __importDefault(require("./Label"));
exports.Label = Label_1.default;
const Sample_1 = __importDefault(require("./Sample"));
exports.Sample = Sample_1.default;
const Gallery_1 = __importDefault(require("./Gallery"));
exports.Gallery = Gallery_1.default;
const FAQ_1 = __importDefault(require("./FAQ"));
exports.FAQ = FAQ_1.default;
const UploadLeadGeneration_1 = __importDefault(require("./UploadLeadGeneration"));
exports.UploadLeadGeneration = UploadLeadGeneration_1.default;
const ContactLeadGeneration_1 = __importDefault(require("./ContactLeadGeneration"));
exports.ContactLeadGeneration = ContactLeadGeneration_1.default;
exports.default = {
    User: User_1.default,
    Category: Category_1.default,
    Service: Service_1.default,
    Order: Order_1.Order,
    OrderItem: Order_1.OrderItem,
    Cart: Cart_1.default,
    Payment: Payment_1.default,
    Testimonial: Testimonial_1.default,
    Favourite: Favourite_1.default,
    Label: Label_1.default,
};
//# sourceMappingURL=index.js.map