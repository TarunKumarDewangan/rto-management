import { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

export default function Login() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState(''); // State for error messages
    const [isLoading, setIsLoading] = useState(false); // State for loading button
    const navigate = useNavigate();

    const handleLogin = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        setError(''); // Clear previous errors

        try {
            // 1. Call Laravel API
            const res = await axios.post('http://127.0.0.1:8000/api/login', {
                email,
                password
            });

            // 2. Store Token & User Data
            localStorage.setItem('token', res.data.access_token);
            localStorage.setItem('user', JSON.stringify(res.data.user));

            // 3. Redirect based on Role
            if (res.data.user.role === 'admin') {
                navigate('/admin');
            } else {
                navigate('/dashboard');
            }

        } catch (err) {
            // Handle Errors (401 Unauthorized, etc)
            if (err.response && err.response.status === 401) {
                setError('Invalid email or password.');
            } else {
                setError('Something went wrong. Is the backend running?');
            }
            console.error(err);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="d-flex align-items-center justify-content-center min-vh-100 bg-light">
            <div className="card shadow-sm border-0" style={{ width: '100%', maxWidth: '400px' }}>
                <div className="card-body p-4">

                    {/* Header */}
                    <div className="text-center mb-4">
                        <h3 className="fw-bold text-primary">RTO Management</h3>
                        <p className="text-muted">Sign in to your account</p>
                    </div>

                    {/* Error Alert */}
                    {error && (
                        <div className="alert alert-danger py-2" role="alert">
                            <small><i className="bi bi-exclamation-circle me-2"></i>{error}</small>
                        </div>
                    )}

                    {/* Login Form */}
                    <form onSubmit={handleLogin}>
                        <div className="mb-3">
                            <label className="form-label fw-semibold">Email Address</label>
                            <input
                                type="email"
                                className="form-control"
                                placeholder="name@example.com"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                required
                            />
                        </div>

                        <div className="mb-4">
                            <label className="form-label fw-semibold">Password</label>
                            <input
                                type="password"
                                className="form-control"
                                placeholder="Enter your password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                required
                            />
                        </div>

                        <button
                            className="btn btn-primary w-100 py-2 fw-bold"
                            disabled={isLoading}
                        >
                            {isLoading ? (
                                <span>
                                    <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Signing in...
                                </span>
                            ) : (
                                "Login"
                            )}
                        </button>
                    </form>
                </div>

                <div className="card-footer text-center bg-white border-0 pb-4">
                    <small className="text-muted">Contact Admin if you don't have an account.</small>
                </div>
            </div>
        </div>
    );
}
