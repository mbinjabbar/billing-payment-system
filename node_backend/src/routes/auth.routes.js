import express from 'express';
import { authenticate } from '../middlewares/auth.middleware.js';
import { login, logout, register } from '../controllers/auth.controller.js';

const router = express.Router();

router.get('/me', authenticate, (req, res) => {
    // for current user profile
});
router.post('/login', login);
router.post('/register', register);
router.post('/logout', logout);

export default router;