import { Sequelize } from "sequelize";

const sequelize = new Sequelize(process.env.DATABASE_URL, {
    dialect: 'mysql',
    logging: false
});

export const connectDB = async () => {
    await sequelize.authenticate();
    console.log("✓ Database connected");
}

export default sequelize;