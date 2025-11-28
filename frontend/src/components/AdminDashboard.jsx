import { useState, useEffect } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import toast from "react-hot-toast";

export default function AdminDashboard() {
    const navigate = useNavigate();
    const token = localStorage.getItem("token");
    const user = JSON.parse(localStorage.getItem("user"));

    // --- DATA STATES ---
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);

    // --- MODAL STATES ---
    const [showModal, setShowModal] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [currentUserId, setCurrentUserId] = useState(null);

    // --- FORM STATES ---
    const [formData, setFormData] = useState({
        name: "",
        email: "",
        password: "",
        whatsapp_key: "",
        whatsapp_host: ""
    });

    // --- TESTING STATE ---
    const [testMobile, setTestMobile] = useState("");
    const [testing, setTesting] = useState(false);

    // --- INITIAL CHECK ---
    useEffect(() => {
        if (!token || user?.role !== 'admin') {
            navigate('/');
        } else {
            fetchUsers();
        }
    }, [token, navigate]);

    // --- API CALLS ---
    const fetchUsers = async () => {
        try {
            const res = await axios.get('http://127.0.0.1:8000/api/users', {
                headers: { Authorization: `Bearer ${token}` }
            });
            setUsers(res.data);
            setLoading(false);
        } catch (error) {
            toast.error("Failed to load users.");
        }
    };

    const handleTestWhatsApp = async () => {
        if (!formData.whatsapp_key || !formData.whatsapp_host) {
            toast.error("Please enter API Key and Host first.");
            return;
        }
        if (!testMobile || testMobile.length !== 10) {
            toast.error("Please enter a valid 10-digit mobile number.");
            return;
        }

        setTesting(true);
        try {
            await axios.post('http://127.0.0.1:8000/api/admin/test-whatsapp', {
                mobile: testMobile,
                whatsapp_key: formData.whatsapp_key,
                whatsapp_host: formData.whatsapp_host
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            toast.success("Test Message Sent Successfully!");
        } catch (error) {
            console.error(error);
            toast.error("Message Failed. Check credentials.");
        } finally {
            setTesting(false);
        }
    };

    // --- FORM HANDLERS ---
    const handleInputChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const openCreateModal = () => {
        setIsEditing(false);
        setFormData({ name: "", email: "", password: "", whatsapp_key: "", whatsapp_host: "" });
        setTestMobile("");
        setShowModal(true);
    };

    const openEditModal = (user) => {
        setIsEditing(true);
        setCurrentUserId(user.id);
        setFormData({
            name: user.name,
            email: user.email,
            password: "", // Leave blank
            whatsapp_key: user.whatsapp_key || "",
            whatsapp_host: user.whatsapp_host || ""
        });
        setTestMobile("");
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (isEditing) {
                await axios.put(`http://127.0.0.1:8000/api/users/${currentUserId}`, formData, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                toast.success("User updated!");
            } else {
                await axios.post('http://127.0.0.1:8000/api/users', formData, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                toast.success("User created!");
            }
            setShowModal(false);
            fetchUsers();
        } catch (error) {
            console.error(error);
            toast.error("Operation failed. Check inputs.");
        }
    };

    const toggleStatus = async (id) => {
        try {
            await axios.patch(`http://127.0.0.1:8000/api/users/${id}/status`, {}, {
                headers: { Authorization: `Bearer ${token}` }
            });
            toast.success("Status updated");
            fetchUsers();
        } catch (error) { toast.error("Failed to update status"); }
    };

    const deleteUser = async (id) => {
        if (!confirm("Are you sure you want to delete this user?")) return;
        try {
            await axios.delete(`http://127.0.0.1:8000/api/users/${id}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            toast.success("User deleted");
            fetchUsers();
        } catch (error) { toast.error("Failed to delete"); }
    };

    const handleLogout = () => {
        localStorage.clear();
        navigate('/');
    };

    return (
        <div className="bg-light min-vh-100 d-flex flex-column">
            {/* Navbar */}
            <nav className="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm px-4">
                <a className="navbar-brand fw-bold" href="#">RTO Admin</a>
                <div className="ms-auto d-flex align-items-center text-white">
                    <span className="me-3 small">Welcome, {user?.name}</span>
                    <button onClick={handleLogout} className="btn btn-outline-light btn-sm">Logout</button>
                </div>
            </nav>

            <div className="container mt-4 flex-grow-1">
                <div className="d-flex justify-content-between align-items-center mb-4">
                    <h3 className="text-primary fw-bold">User Management</h3>
                    <button onClick={openCreateModal} className="btn btn-success shadow-sm">
                        <i className="bi bi-person-plus-fill me-2"></i> Create User
                    </button>
                </div>

                {/* Users Table */}
                <div className="card border-0 shadow-sm">
                    <div className="card-body p-0">
                        <div className="table-responsive">
                            <table className="table table-hover mb-0 align-middle">
                                <thead className="table-light">
                                    <tr>
                                        <th className="ps-4">Name</th>
                                        <th>Email</th>
                                        <th>WhatsApp Config</th>
                                        <th>Status</th>
                                        <th className="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {loading ? (
                                        <tr><td colSpan="5" className="text-center py-5"><div className="spinner-border text-primary"></div></td></tr>
                                    ) : users.length > 0 ? (
                                        users.map(u => (
                                            <tr key={u.id}>
                                                <td className="ps-4 fw-bold">{u.name}</td>
                                                <td>{u.email}</td>
                                                <td>
                                                    {u.whatsapp_key ? (
                                                        <span className="badge bg-info text-dark">Configured</span>
                                                    ) : (
                                                        <span className="badge bg-secondary">Not Set</span>
                                                    )}
                                                </td>
                                                <td>
                                                    <button
                                                        onClick={() => toggleStatus(u.id)}
                                                        className={`badge border-0 ${u.is_active ? 'bg-success' : 'bg-danger'}`}
                                                        style={{cursor: 'pointer'}}
                                                    >
                                                        {u.is_active ? 'Active' : 'Inactive'}
                                                    </button>
                                                </td>
                                                <td className="text-end pe-4">
                                                    <button onClick={() => openEditModal(u)} className="btn btn-sm btn-primary me-2">
                                                        <i className="bi bi-pencil-square"></i> Edit
                                                    </button>
                                                    <button onClick={() => deleteUser(u.id)} className="btn btn-sm btn-danger">
                                                        <i className="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr><td colSpan="5" className="text-center py-4 text-muted">No users found.</td></tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal */}
            {showModal && (
                <div className="modal d-block" style={{backgroundColor: 'rgba(0,0,0,0.5)'}}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg">
                            <div className="modal-header bg-white border-bottom-0">
                                <h5 className="modal-title fw-bold text-primary">{isEditing ? 'Edit User' : 'Add New User'}</h5>
                                <button className="btn-close" onClick={() => setShowModal(false)}></button>
                            </div>
                            <div className="modal-body pt-0">
                                <form onSubmit={handleSubmit}>
                                    {/* Basic Details */}
                                    <div className="mb-3">
                                        <label className="form-label fw-bold small">Full Name</label>
                                        <input type="text" name="name" className="form-control" value={formData.name} onChange={handleInputChange} required />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label fw-bold small">Email Address</label>
                                        <input type="email" name="email" className="form-control" value={formData.email} onChange={handleInputChange} required />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label fw-bold small">
                                            Password {isEditing && <span className="text-muted fw-normal">(Leave blank to keep current)</span>}
                                        </label>
                                        <input type="password" name="password" className="form-control" value={formData.password} onChange={handleInputChange} required={!isEditing} />
                                    </div>

                                    <hr className="text-muted my-4"/>

                                    {/* WhatsApp Config */}
                                    <h6 className="text-primary fw-bold mb-3"><i className="bi bi-whatsapp me-2"></i>WhatsApp API Configuration</h6>

                                    <div className="row g-2">
                                        <div className="col-12">
                                            <label className="form-label small text-muted">API Key</label>
                                            <input type="text" name="whatsapp_key" className="form-control" placeholder="Enter API Key" value={formData.whatsapp_key} onChange={handleInputChange} />
                                        </div>
                                        <div className="col-12 mt-2">
                                            <label className="form-label small text-muted">API Host URL</label>
                                            <input type="text" name="whatsapp_host" className="form-control" placeholder="e.g. api.wa-sender.com" value={formData.whatsapp_host} onChange={handleInputChange} />
                                        </div>
                                    </div>

                                    {/* Test Connection Section */}
                                    <div className="bg-light p-3 rounded border mt-3">
                                        <label className="form-label small fw-bold text-secondary">Test Connection</label>
                                        <div className="input-group input-group-sm">
                                            <input
                                                type="text"
                                                className="form-control"
                                                placeholder="Enter Mobile No (10 digit)"
                                                value={testMobile}
                                                onChange={(e) => setTestMobile(e.target.value)}
                                            />
                                            <button
                                                type="button"
                                                className="btn btn-info text-white"
                                                onClick={handleTestWhatsApp}
                                                disabled={testing}
                                            >
                                                {testing ? (
                                                    <span><span className="spinner-border spinner-border-sm me-1"></span>Sending...</span>
                                                ) : (
                                                    <span><i className="bi bi-send me-1"></i> Test</span>
                                                )}
                                            </button>
                                        </div>
                                        <small className="text-muted d-block mt-1" style={{fontSize: '10px'}}>
                                            Click "Test" to send a sample message before saving.
                                        </small>
                                    </div>

                                    <div className="d-grid mt-4">
                                        <button type="submit" className="btn btn-primary fw-bold py-2">
                                            {isEditing ? 'Update User' : 'Create User'}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
