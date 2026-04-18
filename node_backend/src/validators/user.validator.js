import { body } from 'express-validator';

const VALID_ROLES = ['Admin', 'Biller', 'Payment Poster'];

export const createUserValidator = [
    body('first_name')
        .notEmpty().withMessage('First name is required')
        .isString().withMessage('First name must be a string'),

    body('last_name')
        .notEmpty().withMessage('Last name is required')
        .isString().withMessage('Last name must be a string'),

    body('email')
        .notEmpty().withMessage('Email is required')
        .isEmail().withMessage('Invalid email format'),

    body('password')
        .notEmpty().withMessage('Password is required')
        .isLength({ min: 8 }).withMessage('Password must be at least 8 characters'),

    body('role')
        .notEmpty().withMessage('Role is required')
        .isIn(VALID_ROLES).withMessage(`Role must be one of: ${VALID_ROLES.join(', ')}`),
];

export const updateUserValidator = [
    body('first_name')
        .optional()
        .isString().withMessage('First name must be a string'),

    body('last_name')
        .optional()
        .isString().withMessage('Last name must be a string'),

    body('email')
        .optional()
        .isEmail().withMessage('Invalid email format'),

    body('password')
        .optional()
        .isLength({ min: 8 }).withMessage('Password must be at least 8 characters'),

    body('role')
        .optional()
        .isIn(VALID_ROLES).withMessage(`Role must be one of: ${VALID_ROLES.join(', ')}`),
];