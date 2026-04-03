import jwt from 'jsonwebtoken';
import bcrypt from 'bcryptjs';
import redisClient from '../configs/redis.client.js';
import User from '../models/User.model.js';

export const login = async (req, res, next) => {
    try {
        const { email, password } = req.body;

        const user = await User.findOne({ where: { email, deleted_at: null } });

        const passwordMatched = user ? await bcrypt.compare(password, user.password) : false;

        if (!user || !passwordMatched) {
            return res.api.unauthorized("Invalid email or password");
        }

        const token = jwt.sign(
            { id: user.id, name: `${user.first_name} ${user.last_name}`, email: user.email, role: user.role, },
            process.env.JWT_SECRET,
            { expiresIn: "1d" });

        return res.api.success({
            token, user: {
                id: user.id,
                name: `${user.first_name} ${user.last_name}`,
                role: user.role,
                email: user.email
            }
        }, "Login successful");
    } catch (err) {
        next(err);
    }
}

export const logout = async (req, res, next) => {
    try {
        const authHeader = req.headers.authorization;

        if (!authHeader || !authHeader.startsWith("Bearer ")) {
            return res.api.error("No token provided", 400);
        }

        const token = authHeader.split(" ")[1];

        const decoded = jwt.decode(token);

        const expiry = decoded.exp - Math.floor(Date.now() / 1000);

        if (expiry > 0) {
            await redisClient.setEx(`blacklist:${token}`, expiry, 'true');
        }

        return res.api.success(null, "Logged out successfully");
    } catch (err) {
        next(err);
    }
}

export const getMe = async (req, res, next) => {
    try {
        const userId = req.user.id;

        const user = await User.findByPk(userId, {
            attributes: { exclude: ['password'] }
        });

        if (!user) {
            return res.api.notFound("User session is no longer valid");
        }

        return res.api.success({
            user: {
                id: user.id,
                role: user.role,
                first_name: user.first_name,
                last_name: user.last_name,
                name: `${user.first_name} ${user.last_name}`
            }
        }, "Current user profile retrieved")
    } catch (err) {
        next(err);
    }
}