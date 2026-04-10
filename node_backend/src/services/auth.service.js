import jwt from 'jsonwebtoken';
import bcrypt from 'bcryptjs';
import redisClient from '../configs/redis.client.js';
import userRepository from '../repositories/user.repository.js';

class AuthService {
    async loginUser(email, password) {
        const user = await userRepository.findByEmail(email);
        if (!user) throw new Error("Invalid email or password");

        const isMatch = await bcrypt.compare(password, user.password);
        if (!isMatch) throw new Error("Invalid email or password")

        const token = jwt.sign(
            {
                id: user.id,
                name: `${user.first_name} ${user.last_name}`,
                role: user.role
            },
            process.env.JWT_SECRET,
            { expiresIn: '8h' }
        );
        return { token, user }
    }

    async logoutUser(token) {
        const decoded = jwt.decode(token);
        const expiry = decoded.exp - Math.floor(Date.now() / 1000);

        if (expiry > 0) {
            await redisClient.setEx(`blacklist${token}`, expiry, 'true');
        }
        return true
    }

    async getCurrentProfile(userId) {
        const user = await userRepository.findById(userId);
        if(!user) throw new Error("User not found");
        return user
    }
}

export default new AuthService();
