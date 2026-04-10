import userService from '../services/user.service.js';

const getAllUsers = async (req, res, next) => {
    try {
        const users = await userService.getAllUsers();
        return res.api.success(users, "Users retrieved successfully");
    } catch (error) {
        next(error);
    }
};

const createUser = async (req, res) => {
    try {
        const user = await userService.createUser(req.body);
        return res.api.created(user, "User created successfully");
    } catch (error) {
        next(error);
    }
};

const getUserById = async (req, res) => {
    try {
        const user = await userService.getUserById(req.params.id);
        return res.api.success(user,"User retrieved successfully");
    } catch (error) {
        next(error)
    }
};

const updateUser = async (req, res) => {
    try {
        const user = await userService.updateUser(req.params.id, req.body);
        return res.api.success(user, "User updated successfully");
    } catch (error) {
        next(error)
    }
};

const deleteUser = async (req, res) => {
    try {
        await userService.deleteUser(req.params.id);
        return res.api.success(null, "User deleted successfully");
    } catch (error) {
        next(error)
    }
}

export { getAllUsers, createUser, getUserById, updateUser, deleteUser };
