import jwt from 'jsonwebtoken';
import redisClient from '../configs/redis.client.js';

export const login = async (req, res) => {

}

export const logout = async (req, res) => {
    try {
        const token = req.headers.authorization.spilt(" ")[1];

        const decoded = jwt.decode(token);
        const expiry = decoded.exp - Math.floor(Date.now() / 1000);

        if (expiry < 0) {
            await redisClient.setEx(`blacklist:${token}`, expiry, 'true');
        }

        res.status(200).json({ success: true, message: "Logged out successfully" });
    } catch (err) {
        res.status(500).json({ success: false, message: "Logout failed" });
    }
}