import express from 'express';
import { authorize, authenticate } from '../middlewares/auth.middleware.js';

const router = express.Router();

router.use(authenticate);
router.use(authorize('Admin'));

// user management system by admin
router.get('/', (req, res) => {
    // list all users
});
router.post('/', (req, res) => {
    // add new user Biller or Poster
});
router.put('/', (req, res) => {
    // edit user profile roles
});
router.delete('/', (req, res) => {
    // soft deletes user
});
export default router;
