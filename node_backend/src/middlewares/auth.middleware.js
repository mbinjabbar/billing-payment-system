import jwt from 'jsonwebtoken';
import redisClient from '../configs/redis.client.js';

export const authenticate = async (req, res, next) => {
    try {
        const authHeader = req.headers.authorization;
        if (!authHeader || !authHeader.startsWith("Bearer ")) {
            return res.status(401).json({ success: false, message: "No token, unauthorized" });
        }
        const token = authHeader.split(" ")[1];
        const isBlacklisted = await redisClient.get(`blaclist:${token}`);
        if(isBlacklisted) {
            return res.status(401).json({success: false, message: "Token revoked. Please login again"})
        }
        const decoded = jwt.verify(token, process.env.JWT_SECRET);
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