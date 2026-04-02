import express from 'express';
import cors from 'cors';
import authRoutes from './src/routes/auth.routes.js';
import userRoutes from './src/routes/user.routes.js';
import { responseMiddleware } from './src/middlewares/response.middleware.js';
import { notFound, errorHandler } from './src/middlewares/error.middleware.js';

const app = express();

app.use(express.json());
app.use(cors());
app.use(responseMiddleware);

app.use('/api/auth', authRoutes);
app.use('/api/users', userRoutes);

app.use(notFound);
app.use(errorHandler);

export default app;