import './src/configs/env.js';
import app from './app.js';
import sequelize from './src/configs/db.js';

const PORT = process.env.PORT || 3000;

const connectDB = async () => {
    await sequelize.authenticate();
    console.log("✓ Database connected");
}

const startServer = async () => {
    try {
        await connectDB();
        app.listen(PORT, () => {
            console.log(`✓ Server is live at: http://localhost:${PORT}`)
        });
    } catch (err) {
        console.error("✗ Startup failed:", err.message);
        process.exit(1);
    }
}

process.on("unhandledRejection", (err) => {
    console.error("Unhandled rejection:", err.message);
    process.exit(1);
});

process.on("uncaughtException", (err) => {
    console.error("Uncaught exception:", err.message);
    process.exit(1);
});

startServer();