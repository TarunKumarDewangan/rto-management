import { useState, useEffect } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import UserNavbar from "./UserNavbar";

export default function CreateCitizen() {
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    // 1. Check if token exists on load
    useEffect(() => {
        if (!token) {
            alert("Session expired. Please login again.");
            navigate('/');
        }
    }, [token, navigate]);

    const [formData, setFormData] = useState({
        name: '', mobile_number: '', email: '', birth_date: '',
        relation_type: '', relation_name: '', address: '', state: '', city_district: ''
    });

    const [loading, setLoading] = useState(false);

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            await axios.post('http://127.0.0.1:8000/api/citizens', formData, {
                headers: { Authorization: `Bearer ${token}` }
            });
            alert("Citizen Registered Successfully!");
            navigate('/citizens');
        } catch (error) {
            console.error(error);
            // 2. Handle 401 (Unauthorized) Error
            if (error.response && error.response.status === 401) {
                alert("Your session has expired. Please login again.");
                localStorage.clear(); // Clear invalid token
                navigate('/'); // Go to Login
            } else {
                alert("Error registering citizen. Please check all fields.");
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="bg-light min-vh-100 d-flex flex-column">
            <UserNavbar />

            <div className="container mt-4 mb-5 flex-grow-1">
                <div className="card shadow-sm border-0">
                    <div className="card-header bg-primary text-white py-3">
                        <h5 className="mb-0 fw-bold">Register New Citizen</h5>
                    </div>
                    <div className="card-body p-4">
                        <form onSubmit={handleSubmit}>
                            {/* Row 1 */}
                            <div className="row mb-3">
                                <div className="col-md-6">
                                    <label className="form-label text-muted small fw-bold">Full Name *</label>
                                    <input type="text" name="name" className="form-control" onChange={handleChange} required />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small fw-bold">Mobile Number *</label>
                                    <input type="text" name="mobile_number" className="form-control" onChange={handleChange} required />
                                </div>
                            </div>

                            {/* Row 2 */}
                            <div className="row mb-3">
                                <div className="col-md-6">
                                    <label className="form-label text-muted small fw-bold">Email (Optional)</label>
                                    <input type="email" name="email" className="form-control" onChange={handleChange} />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small fw-bold">Date of Birth</label>
                                    <input type="date" name="birth_date" className="form-control" onChange={handleChange} />
                                </div>
                            </div>

                            {/* Row 3 */}
                            <div className="row mb-3">
                                <div className="col-md-6">
                                    <label className="form-label text-muted small fw-bold">Relation Type</label>
                                    <select name="relation_type" className="form-select" onChange={handleChange}>
                                        <option value="">Select...</option>
                                        <option value="Father">Father</option>
                                        <option value="Husband">Husband</option>
                                        <option value="Wife">Wife</option>
                                        <option value="Son">Son</option>
                                        <option value="Daughter">Daughter</option>
                                    </select>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small fw-bold">Relation Name</label>
                                    <input type="text" name="relation_name" className="form-control" placeholder="Father/Husband Name" onChange={handleChange} />
                                </div>
                            </div>

                            {/* Row 4 */}
                            <div className="mb-3">
                                <label className="form-label text-muted small fw-bold">Address</label>
                                <textarea name="address" className="form-control" rows="2" onChange={handleChange}></textarea>
                            </div>

                            {/* Row 5 */}
                            <div className="row mb-4">
                                <div className="col-md-6">
                                    <label className="form-label text-muted small fw-bold">State</label>
                                    <input type="text" name="state" className="form-control" onChange={handleChange} />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small fw-bold">City / District</label>
                                    <input type="text" name="city_district" className="form-control" onChange={handleChange} />
                                </div>
                            </div>

                            {/* Buttons */}
                            <div className="d-flex justify-content-end gap-2 border-top pt-3">
                                <button type="button" className="btn btn-secondary px-4" onClick={() => navigate('/citizens')}>Cancel</button>
                                <button type="submit" className="btn btn-primary px-4" disabled={loading}>
                                    {loading ? 'Saving...' : 'Save & Continue'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div className="text-center text-muted py-3 mt-auto" style={{fontSize: '12px'}}>
                &copy; 2025 RTO Data Management System
            </div>
        </div>
    );
}
