import { AppError } from '../errors/errors.js';

export const notFound = (req, res) => {
    return res.api.error('Route not found', 404);
};

export const errorHandler = (err, req, res, next) => {
    if (err instanceof AppError) {
        return res.api.error(err.message, err.statusCode);
    }

    if (err.name === 'JsonWebTokenError') {
        return res.api.error('Invalid token', 401);
    }

    if (err.name === 'TokenExpiredError') {
        return res.api.error('Token has expired', 401);
    }

    if (err.name === 'SequelizeValidationError') {
        return res.api.error(err.errors.map(e => e.message).join(', '), 422);
    }

    if (err.name === 'SequelizeUniqueConstraintError') {
        return res.api.error('A record with this value already exists', 409);
    }

    console.error('Unhandled error:', err);
    return res.api.error('Internal server error', 500);
};