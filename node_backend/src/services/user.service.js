import bcrypt from 'bcryptjs';
import userRepository from '../repositories/user.repository.js';

class UserService {
    async getAllUsers() {
        return await userRepository.findAll();
    }

    async getUserById(id) {
        const user = await userRepository.findById(id);
        if (!user) throw new Error("User not found");
        return user;
    }

    async createUser(data) {
        const existing = await userRepository.findByEmail(data.email);
        if (existing) throw new Error("Email already registered");

        const hashedPassword = await bcrypt.hash(data.password, 10);

        return await userRepository.create({ ...data, password: hashedPassword });
    }

    async updateUser(id, data) {
        const updatedUser = await userRepository.update(id, data);
        if (!updatedUser) throw new Error("User not found or update failed");
        return updateUser;
    }

    async deleteUpdate(id) {
        const result = await userRepository.delete(id);
        if (!result) throw new Error("User not found");
        return result;
    }
}

export default new UserService();