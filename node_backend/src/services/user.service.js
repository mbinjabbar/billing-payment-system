import userRepository from '../repositories/user.repository.js';
import { hashPassword } from '../utils/helpers.js';
import { NotFoundError, ConflictError } from '../errors/errors.js';

class UserService {
    async getAllUsers({ page = 1, limit = 10, search } = {}) {
        return await userRepository.findAll({ page, limit, search });
    }

    async getUserById(id) {
        const user = await userRepository.findById(id);
        if (!user) throw new NotFoundError('User not found');
        return user;
    }

    async createUser(data) {
        const existing = await userRepository.findByEmail(data.email);
        if (existing) throw new ConflictError('Email already registered');

        const hashedPassword = await hashPassword(data.password);
        return await userRepository.create({ ...data, password: hashedPassword });
    }

    async updateUser(id, data) {
        if (data.password) {
            data.password = await hashPassword(data.password);
        }
        const updatedUser = await userRepository.update(id, data);
        if (!updatedUser) throw new NotFoundError('User not found');
        return updatedUser;
    }

    async deleteUser(id) {
        const result = await userRepository.delete(id);
        if (!result) throw new NotFoundError('User not found');
        return result;
    }
}

export default new UserService();