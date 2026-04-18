import { validationResult } from 'express-validator';

export const handleValidationErrors = (req, res, next) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        const message = errors.array().map(e => e.msg).join(', ');
        return res.api.error(message, 422);
    }
    next();
};