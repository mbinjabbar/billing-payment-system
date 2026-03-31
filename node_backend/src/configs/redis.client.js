import { createClient } from 'redis';

const redisClient = createClient({
    url: process.env.REDIS_URL || 'redis://localhost:6379'
});

redisClient.on('connect', () => console.log("✓ Redis Connected"));
redisClient.on('error', (err) => console.log("Redis Error:", err));

try {
    await redisClient.connect();
} catch (err) {
    console.error("Could not connect to Redis:", err);
}

export default redisClient;