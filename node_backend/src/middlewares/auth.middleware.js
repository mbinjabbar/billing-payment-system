import { extractToken, isTokenRevoked, verifyJWT } from '../utils/helpers.js';

export const authenticate = async (req, res, next) => {
    try {
        const token = extractToken(req.headers.authorization);
        if (!token) return res.api.unauthorized("No token provided");

        if (await isTokenRevoked(token)) {
            return res.api.unauthorized("Token revoked. Please login again");
        }

        const decoded = verifyJWT(token);
        req.user = decoded;
        next();
    } catch (err) {
        return res.status(401).json({ success: false, message: "Invalid or expired token" })
    }
}

export const authorize = (...roles) => {
    return (req, res, next) => {
        if (!roles.includes(req.user.role)) {
            return res.status(403).json({
                success: false,
                message: `Role ${req.user.role} is not authorized to access this route`
            });
        }
        next();
    };
};