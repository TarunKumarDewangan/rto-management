// import axios from 'axios';

// // --- SMART URL SELECTION ---
// // If we are running on localhost, use Local API.
// // If we are on the Live Site (rtodatahub.in), use Live API.

// const isProduction = window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';

// const BASE_URL = isProduction
//     ? 'https://api.rtodatahub.in'   // Live Backend
//     : 'http://127.0.0.1:8000';      // Local Backend

// // Create the instance
// const api = axios.create({
//     baseURL: BASE_URL,
//     headers: {
//         'Content-Type': 'application/json',
//         'Accept': 'application/json'
//     }
// });

// // Add Token automatically
// api.interceptors.request.use((config) => {
//     const token = localStorage.getItem('token');
//     if (token) {
//         config.headers.Authorization = `Bearer ${token}`;
//     }
//     return config;
// });

// export default api;


import axios from 'axios';

// --- FORCE PRODUCTION URL ---
const BASE_URL = 'https://api.rtodatahub.in';

// Create the instance
const api = axios.create({
    baseURL: BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Add Token to every request automatically
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default api;
