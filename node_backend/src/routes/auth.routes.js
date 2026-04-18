import express from 'express';
import { authenticate } from '../middlewares/auth.middleware.js';
import { getMe, login, logout } from '../controllers/auth.controller.js';
import { loginValidator } from '../validators/auth.validator.js';
import { handleValidationErrors } from '../middlewares/validate.middlware.js';

const router = express.Router();

router.post('/login', loginValidator, handleValidationErrors, login);

router.post('/logout', authenticate, logout);
router.get('/me', authenticate, getMe);

export default router;