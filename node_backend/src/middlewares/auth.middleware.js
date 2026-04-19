import { extractToken, isTokenRevoked, verifyJWT } from '../utils/helpers.js';

export const authenticate = async (req, res, next) => {
    try {
        const token = extractToken(req.headers.authorization);
        if (!token) return res.api.error('No token provided', 401);

        if (await isTokenRevoked(token)) {
            return res.api.error('Token revoked. Please login again', 401);
        }

        const decoded = verifyJWT(token);
        if (!decoded) return res.api.error('Invalid or expired token', 401);
        req.user = decoded;
        next();
    } catch (err) {
        next(err);
    }
};

export const authorize = (...roles) => {
    return (req, res, next) => {
        if (!roles.includes(req.user.role)) {
            return res.api.error(
                `Role ${req.user.role} is not authorized to access this route`,
                403
            );
        }
        next();
    };
};