import express from 'express';
import { authenticate } from '../middlewares/auth.middleware.js';

const router = express.Router();

router.get('/me', authenticate, (req, res) => {
    // for current user profile
});
router.post('/login', (req, res) => {
    // role based login
});
router.post('/register', (req, res) => {
    // biller and poster registeration and confirmation by admin
});
router.post('/logout', authenticate, (req, res) => {
    // remove access token
});

export default router;