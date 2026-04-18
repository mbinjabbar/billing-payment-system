import express from 'express';
import { authorize, authenticate } from '../middlewares/auth.middleware.js';
import { getAllUsers, createUser, getUserById, updateUser, deleteUser } from '../controllers/user.controller.js';
import { createUserValidator, updateUserValidator } from '../validators/user.validator.js';
import { handleValidationErrors } from '../middlewares/validate.middlware.js';

const router = express.Router();

router.use(authenticate);
router.use(authorize('Admin'));

router.get('/', getAllUsers);
router.post('/', createUserValidator, handleValidationErrors, createUser);
router.get('/:id', getUserById);
router.patch('/:id', updateUserValidator, handleValidationErrors, updateUser);
router.delete('/:id', deleteUser);

export default router;