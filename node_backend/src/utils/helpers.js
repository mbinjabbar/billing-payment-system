import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import redisClient from '../configs/redis.client.js';

export const getTokenRemainingTime = (token) => {
    try {
        const decoded = jwt.decode(token);
        if (!decoded || !decoded.exp) return 0;
        const now = Math.floor(Date.now() / 1000);
        return decoded.exp - now;
    } catch (err) {
        return 0;
    }
}

export const hashPassword = async (password) => {
    const salt = await bcrypt.genSalt(10);
    return await bcrypt.hash(password, salt)
}

export const comparePassword = async (plain, hashed) => {
    return await bcrypt.compare(plain, hashed);
};

export const formatFullName = (user) => {
    if (!user) return 'N/A';
    return [user.first_name, user.last_name]
        .filter(name => name && name.trim().length > 0)
        .join(' ');
}

export const sanitizeUser = (user) => {
    return {
        id: user.id,
        email: user.email,
        role: user.role,
        full_name: formatFullName(user),
        created_at: user.created_at
    };
}

export const generateToken = (user) => {
    return jwt.sign(
        {
            id: user.id,
            name: formatFullName(user),
            email: user.email,
            role: user.role
        },
        process.env.JWT_SECRET,
        {
            expiresIn: process.env.JWT_EXPIRES_IN || '8h'
        }
    );
};

export const verifyJWT = (token) => {
    try {
        return jwt.verify(token, process.env.JWT_SECRET);
    } catch (err) {
        return null;
    }
};

export const extractToken = (authHeader) => {
    if (!authHeader || !authHeader.startsWith("Bearer ")) return null;
    return authHeader.split(" ")[1];
};

export const isTokenRevoked = async (token) => {
    const blacklisted = await redisClient.get(`blacklist:${token}`);
    return !!blacklisted;
};

export const paginate = async (model, { page = 1, limit = 10, where = {}, attributes, order = [['created_at', 'DESC']] }) => {
    const offset = (page - 1) * limit;

    const { count, rows } = await model.findAndCountAll({
        where,
        attributes,
        limit,
        offset,
        order
    });

    return {
        data: rows,
        meta: {
            total: count,
            per_page: limit,
            current_page: page,
            last_page: Math.ceil(count / limit),
            from: count === 0 ? 0 : offset + 1,
            to: Math.min(offset + limit, count),
        }
    };
}