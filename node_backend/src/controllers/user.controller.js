import User from '../models/User.model.js';
import bcrypt from 'bcryptjs';

const getAllUsers = async (req, res, next) => {
    try {
        const users = await User.findAll();
        return res.api.success("Users retrieved successfully", users);
    } catch (error) {
        return res.api.error();
    }
};

const createUser = async (req, res) => {
    try {
        const { first_name, last_name, email, password, role } = req.body;
        const hashedPassword = await bcrypt.hash(password, 10);
        const user = await User.create({ first_name, last_name, email, password: hashedPassword, role });
        return res.api.created(user, "User created successfully");
    } catch (error) {
        console.error("Error creating user:", error);
        return res.api.error("Failed to create user");
    }
};
const getUserById = async (req, res) => {
    try {
        const { id } = req.params;
        const user = await User.findByPk(id);
        if (!user) {
            return res.api.error("User not found");
        }
        return res.api.success("User retrieved successfully", user);
    } catch (error) {
        return res.api.error("Failed to retrieve user");

    }
};
const updateUser = async (req, res) => {
    try {
        const { id } = req.params;
        const { first_name, last_name, email, role } = req.body;

        const user = await User.findByPk(id);
        if (!user) {
            return res.api.notFound("User not found");
        }
        await user.update({ first_name, last_name, email, role });
        return res.api.success("User updated successfully", user);
    } catch (error) {
        return res.api.error("Failed to update user");
    }
};

const deleteUser = async (req, res) => {
    try {
        const { id } = req.params;
        const user = await User.findByPk(id);
        if (!user) {
            return res.api.notFound("User not found");
        }
        await user.destroy();
        return res.api.success("User deleted successfully");
    } catch (error) {
        return res.api.error("Failed to delete user");
    }
}

export { getAllUsers, createUser, getUserById, updateUser, deleteUser };
