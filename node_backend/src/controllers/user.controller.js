import userService from '../services/user.service.js';

const getAllUsers = async (req, res, next) => {
    try {
        const page  = parseInt(req.query.page)  || 1;
        const limit = parseInt(req.query.limit) || 10;

        const result = await userService.getAllUsers({ page, limit });
        return res.api.success(result, 'Users retrieved successfully');
    } catch (error) {
        next(error);
    }
};

const createUser = async (req, res, next) => {
    try {
        const user = await userService.createUser(req.body);
        return res.api.success(user, 'User created successfully', 201);
    } catch (error) {
        next(error);
    }
};

const getUserById = async (req, res, next) => {
    try {
        const user = await userService.getUserById(req.params.id);
        return res.api.success(user, 'User retrieved successfully');
    } catch (error) {
        next(error);
    }
};

const updateUser = async (req, res, next) => {
    try {
        const user = await userService.updateUser(req.params.id, req.body);
        return res.api.success(user, 'User updated successfully');
    } catch (error) {
        next(error);
    }
};

const deleteUser = async (req, res, next) => {
    try {
        await userService.deleteUser(req.params.id);
        return res.api.success(null, 'User deleted successfully');
    } catch (error) {
        next(error);
    }
};

export { getAllUsers, createUser, getUserById, updateUser, deleteUser };