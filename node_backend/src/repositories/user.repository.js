import User from '../models/User.model.js';
import { paginate } from '../utils/helpers.js';

class UserRepository {
    async findAll({ page = 1, limit = 10 } = {}) {
        return await paginate(User, { page, limit, attributes: { exclude: ['password', 'deleted_at'] } });
    }

    async findByEmail(email) {
        return await User.findOne({
            where: { email, deleted_at: null }
        });
    }

    async findById(id) {
        return await User.findByPk(id, {
            attributes: { exclude: ['password'] }
        });
    }

    async create(userData) {
        return await User.create(userData);
    }

    async update(id, updateData) {
        const user = await User.findByPk(id);
        if (user) {
            return await user.update(updateData);
        }
        return null;
    }

    async delete(id) {
        const user = await User.findByPk(id);
        if (user) {
            return await user.destroy();
        }
        return null;
    }
}

export default new UserRepository();