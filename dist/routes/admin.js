"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = require("express");
const auth_1 = require("../middleware/auth");
const router = (0, express_1.Router)();
router.use(auth_1.adminAuth);
router.get('/users', async (_req, res) => {
    try {
        const users = [
            {
                id: '1',
                name: 'Test User 1',
                email: 'user1@example.com',
                phone: '+1234567890',
                role: 'USER',
                status: 'ACTIVE',
                createdAt: new Date(),
                updatedAt: new Date(),
            },
            {
                id: '2',
                name: 'Test User 2',
                email: 'user2@example.com',
                phone: '+0987654321',
                role: 'USER',
                status: 'ACTIVE',
                createdAt: new Date(),
                updatedAt: new Date(),
            },
        ];
        res.json({
            success: true,
            data: { users },
        });
    }
    catch (error) {
        console.error('Get users error:', error);
        res.status(500).json({ message: 'Server error' });
    }
});
router.get('/users/:id', async (req, res) => {
    try {
        const { id } = req.params;
        if (!id) {
            return res.status(400).json({ message: 'User ID is required' });
        }
        const user = {
            id,
            name: 'Test User',
            email: 'test@example.com',
            phone: '+1234567890',
            address: '123 Test St',
            city: 'Test City',
            state: 'Test State',
            country: 'Test Country',
            zipCode: '12345',
            profileImage: null,
            role: 'USER',
            status: 'ACTIVE',
            createdAt: new Date(),
            updatedAt: new Date(),
        };
        return res.json({
            success: true,
            data: { user },
        });
    }
    catch (error) {
        console.error('Get user error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.put('/users/:id/status', async (req, res) => {
    try {
        const { id } = req.params;
        const { status } = req.body;
        if (!id) {
            return res.status(400).json({ message: 'User ID is required' });
        }
        const user = {
            id,
            name: 'Test User',
            email: 'test@example.com',
            status,
        };
        return res.json({
            success: true,
            message: 'User status updated successfully',
            data: { user },
        });
    }
    catch (error) {
        console.error('Update user status error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.get('/categories', async (_req, res) => {
    try {
        const categories = [
            {
                id: '1',
                name: 'Mixing',
                description: 'Audio mixing services',
                image: null,
                status: 'ACTIVE',
                createdAt: new Date(),
                updatedAt: new Date(),
                _count: { services: 5 },
            },
            {
                id: '2',
                name: 'Mastering',
                description: 'Audio mastering services',
                image: null,
                status: 'ACTIVE',
                createdAt: new Date(),
                updatedAt: new Date(),
                _count: { services: 3 },
            },
        ];
        return res.json({
            success: true,
            data: { categories },
        });
    }
    catch (error) {
        console.error('Get categories error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.post('/categories', async (req, res) => {
    try {
        const { name, description, image } = req.body;
        const category = {
            id: 'new-id',
            name,
            description,
            image,
            status: 'ACTIVE',
            createdAt: new Date(),
            updatedAt: new Date(),
        };
        return res.status(201).json({
            success: true,
            message: 'Category created successfully',
            data: { category },
        });
    }
    catch (error) {
        console.error('Create category error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.put('/categories/:id', async (req, res) => {
    try {
        const { id } = req.params;
        const { name, description, image } = req.body;
        if (!id) {
            return res.status(400).json({ message: 'Category ID is required' });
        }
        const category = {
            id,
            name,
            description,
            image,
            status: 'ACTIVE',
            createdAt: new Date(),
            updatedAt: new Date(),
        };
        return res.json({
            success: true,
            message: 'Category updated successfully',
            data: { category },
        });
    }
    catch (error) {
        console.error('Update category error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.put('/categories/:id/status', async (req, res) => {
    try {
        const { id } = req.params;
        const { status } = req.body;
        if (!id) {
            return res.status(400).json({ message: 'Category ID is required' });
        }
        const category = {
            id,
            name: 'Test Category',
            description: 'Test description',
            image: null,
            status,
            createdAt: new Date(),
            updatedAt: new Date(),
        };
        return res.json({
            success: true,
            message: 'Category status updated successfully',
            data: { category },
        });
    }
    catch (error) {
        console.error('Update category status error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.get('/services', async (_req, res) => {
    try {
        const services = [
            {
                id: '1',
                name: 'Basic Mixing',
                description: 'Basic audio mixing service',
                price: 100,
                duration: '2-3 days',
                categoryId: '1',
                status: 'ACTIVE',
                createdAt: new Date(),
                updatedAt: new Date(),
            },
            {
                id: '2',
                name: 'Premium Mastering',
                description: 'Premium audio mastering service',
                price: 200,
                duration: '3-5 days',
                categoryId: '2',
                status: 'ACTIVE',
                createdAt: new Date(),
                updatedAt: new Date(),
            },
        ];
        return res.json({
            success: true,
            data: { services },
        });
    }
    catch (error) {
        console.error('Get services error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.post('/services', async (req, res) => {
    try {
        const { name, description, price, duration, categoryId } = req.body;
        const service = {
            id: 'new-service-id',
            name,
            description,
            price,
            duration,
            categoryId,
            status: 'ACTIVE',
            createdAt: new Date(),
            updatedAt: new Date(),
        };
        return res.status(201).json({
            success: true,
            message: 'Service created successfully',
            data: { service },
        });
    }
    catch (error) {
        console.error('Create service error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.put('/services/:id', async (req, res) => {
    try {
        const { id } = req.params;
        const { name, description, price, duration, categoryId } = req.body;
        if (!id) {
            return res.status(400).json({ message: 'Service ID is required' });
        }
        const service = {
            id,
            name,
            description,
            price,
            duration,
            categoryId,
            status: 'ACTIVE',
            createdAt: new Date(),
            updatedAt: new Date(),
        };
        return res.json({
            success: true,
            message: 'Service updated successfully',
            data: { service },
        });
    }
    catch (error) {
        console.error('Update service error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.put('/services/:id/status', async (req, res) => {
    try {
        const { id } = req.params;
        const { status } = req.body;
        if (!id) {
            return res.status(400).json({ message: 'Service ID is required' });
        }
        const service = {
            id,
            name: 'Test Service',
            description: 'Test service description',
            price: 100,
            duration: '2-3 days',
            categoryId: '1',
            status,
            createdAt: new Date(),
            updatedAt: new Date(),
        };
        return res.json({
            success: true,
            message: 'Service status updated successfully',
            data: { service },
        });
    }
    catch (error) {
        console.error('Update service status error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.get('/orders', async (_req, res) => {
    try {
        const orders = [
            {
                id: '1',
                userId: '1',
                totalAmount: 100,
                status: 'PENDING',
                createdAt: new Date(),
                updatedAt: new Date(),
            },
            {
                id: '2',
                userId: '2',
                totalAmount: 200,
                status: 'COMPLETED',
                createdAt: new Date(),
                updatedAt: new Date(),
            },
        ];
        return res.json({
            success: true,
            data: { orders },
        });
    }
    catch (error) {
        console.error('Get orders error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.get('/orders/:id', async (req, res) => {
    try {
        const { id } = req.params;
        if (!id) {
            return res.status(400).json({ message: 'Order ID is required' });
        }
        const order = {
            id,
            userId: '1',
            totalAmount: 100,
            status: 'PENDING',
            createdAt: new Date(),
            updatedAt: new Date(),
        };
        return res.json({
            success: true,
            data: { order },
        });
    }
    catch (error) {
        console.error('Get order error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.put('/orders/:id/status', async (req, res) => {
    try {
        const { id } = req.params;
        const { status } = req.body;
        if (!id) {
            return res.status(400).json({ message: 'Order ID is required' });
        }
        const order = {
            id,
            userId: '1',
            totalAmount: 100,
            status,
            createdAt: new Date(),
            updatedAt: new Date(),
        };
        return res.json({
            success: true,
            message: 'Order status updated successfully',
            data: { order },
        });
    }
    catch (error) {
        console.error('Update order status error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
router.get('/dashboard', async (_req, res) => {
    try {
        const stats = {
            totalUsers: 100,
            totalOrders: 50,
            totalRevenue: 5000,
            pendingOrders: 10,
            completedOrders: 40,
        };
        return res.json({
            success: true,
            data: { stats },
        });
    }
    catch (error) {
        console.error('Get dashboard stats error:', error);
        return res.status(500).json({ message: 'Server error' });
    }
});
exports.default = router;
//# sourceMappingURL=admin.js.map