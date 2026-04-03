import express from 'express';
import { authenticate } from '../middlewares/auth.middleware.js';
import { getMe, login, logout } from '../controllers/auth.controller.js';

const router = express.Router();

router.post('/login', login);

router.post('/logout', authenticate ,logout);
router.get('/me', authenticate, getMe);

export default router;