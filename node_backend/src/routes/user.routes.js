import express from 'express';
import { authorize, authenticate } from '../middlewares/auth.middleware.js';
import { getAllUsers, createUser,getUserById, updateUser, deleteUser } from '../controllers/userController.js';

const router = express.Router();

router.use(authenticate);
router.use(authorize('Admin'));


router.get('/', getAllUsers);
router.post('/', createUser);
router.get('/:id', getUserById);
router.put('/:id', updateUser);
router.delete('/:id', deleteUser);
export default router;
