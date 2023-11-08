
import process from "process";

const baseUrlEnv = process.env.TEST_BASE;

export default {
    credentials: {
        username: process.env.TEST_USER || '',
        password: process.env.TEST_PASS || ''
    },
    baseUrl: process.env.TEST_BASE || 'https://127.0.0.1:8443/terminvereinbarung/admin'
    }
