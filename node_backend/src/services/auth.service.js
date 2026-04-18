import redisClient from '../configs/redis.client.js';
import userRepository from '../repositories/user.repository.js';
import { generateToken, getTokenRemainingTime, comparePassword } from '../utils/helpers.js';
import { AuthError, NotFoundError } from '../errors/errors.js';

class AuthService {
    async loginUser(email, password) {
        const user = await userRepository.findByEmail(email);

        if (!user || !(await comparePassword(password, user.password))) {
            throw new AuthError('Invalid email or password');
        }

        const token = generateToken(user);
        return { token, user };
    }

    async logoutUser(token) {
        const expiry = getTokenRemainingTime(token);
        if (expiry > 0) {
            await redisClient.setEx(`blacklist:${token}`, expiry, 'true');
        }
        return true;
    }

    async getCurrentProfile(userId) {
        const user = await userRepository.findById(userId);
        if (!user) throw new NotFoundError('User not found');
        return user;
    }
}

export default new AuthService();