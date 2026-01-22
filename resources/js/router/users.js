// routes/users.js
import express from 'express';
import pool from '../db.js'; // your PostgreSQL pool connection

const router = express.Router();

// Get total users
router.get('/total', async (req, res) => {
    try {
        const result = await pool.query('SELECT COUNT(*) AS total_users FROM users');
        res.json({ total: parseInt(result.rows[0].total_users) });
    } catch (err) {
        console.error(err.message);
        res.status(500).json({ error: 'Server error' });
    }
});

export default router;
