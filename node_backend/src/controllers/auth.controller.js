import authService from '../services/auth.service.js';
import { sanitizeUser } from '../utils/helpers.js';

export const login = async (req, res, next) => {
    try {
        const { email, password } = req.body;
        const { token, user } = await authService.loginUser(email, password);
        return res.api.success({ token, user: sanitizeUser(user) }, "Login successful");
    } catch (err) {
        next(err);
    }
}

export const logout = async (req, res, next) => {
    try {
        const token = req.headers.authorization?.split(" ")[1];
        if (!token) return res.api.error("No token provided", 400);

        await authService.logoutUser(token);
        return res.api.success(null, "Logged out successfully");
    } catch (err) {
        next(err);
    }
}

export const getMe = async (req, res, next) => {
    try {
        const user = await authService.getCurrentProfile(req.user.id);
        return res.api.success({ user }, "Current user profile retrieved")
    } catch (err) {
        next(err);
    }
}