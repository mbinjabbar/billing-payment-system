import { Op } from 'sequelize';
import User from '../models/User.model.js';
import { paginate } from '../utils/helpers.js';

class UserRepository {
    async findAll({ page = 1, limit = 10, search = ''} = {}) {
        const where = search?.trim() ? {
            [Op.or]: [
                { first_name: { [Op.like]: `%${search}%`}},
                { last_name: { [Op.like]: `%${search}%`}},
                { email: { [Op.like]: `%${search}%`}},
                { role: { [Op.like]: `%${search}%`}},
            ]
        } : {};
        return await paginate(User, { page, limit, where, attributes: { exclude: ['password'] } });
    }

    async findByEmail(email) {
        return await User.findOne({
            where: { email }
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