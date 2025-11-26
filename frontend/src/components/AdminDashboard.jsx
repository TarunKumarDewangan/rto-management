import { useState, useEffect } from 'react';
import axios from 'axios';
import Layout from './Layout';

export default function AdminDashboard() {
    const [users, setUsers] = useState([]);
    const [showModal, setShowModal] = useState(false);

    // Form States
    const [isEditing, setIsEditing] = useState(false);
    const [currentUserId, setCurrentUserId] = useState(null);
    const [formData, setFormData] = useState({ name: '', email: '', password: '' });

    const token = localStorage.getItem('token');
    const config = { headers: { Authorization: `Bearer ${token}` } };

    // 1. Fetch Users
    const fetchUsers = async () => {
        try {
            const res = await axios.get('http://127.0.0.1:8000/api/users', config);
            setUsers(res.data);
        } catch (error) {
            console.error("Error fetching users", error);
        }
    };

    useEffect(() => {
        fetchUsers();
    }, []);

    // 2. Handle Inputs
    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    // 3. Submit Form (Create or Update)
    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (isEditing) {
                await axios.put(`http://127.0.0.1:8000/api/users/${currentUserId}`, formData, config);
                alert("User Updated!");
            } else {
                await axios.post('http://127.0.0.1:8000/api/users', formData, config);
                alert("User Created!");
            }
            setShowModal(false);
            resetForm();
            fetchUsers();
        } catch (error) {
            alert("Operation Failed. Check console.");
            console.error(error);
        }
    };

    // 4. Delete User
    const handleDelete = async (id) => {
        if (confirm("Are you sure you want to delete this user?")) {
            await axios.delete(`http://127.0.0.1:8000/api/users/${id}`, config);
            fetchUsers();
        }
    };

    // 5. Toggle Active Status
    const toggleStatus = async (id) => {
        try {
            await axios.patch(`http://127.0.0.1:8000/api/users/${id}/status`, {}, config);
            fetchUsers();
        } catch (error) {
            console.error(error);
        }
    };

    // Helper functions
    const openCreateModal = () => {
        resetForm();
        setShowModal(true);
    };

    const openEditModal = (user) => {
        setIsEditing(true);
        setCurrentUserId(user.id);
        setFormData({ name: user.name, email: user.email, password: '' }); // Password blank intentionally
        setShowModal(true);
    };

    const resetForm = () => {
        setIsEditing(false);
        setCurrentUserId(null);
        setFormData({ name: '', email: '', password: '' });
    };

    return (
        <Layout title="User Management">

            {/* Action Bar */}
            <div className="card shadow-sm mb-4">
                <div className="card-body d-flex justify-content-between align-items-center">
                    <h5 className="m-0">System Users</h5>
                    <button className="btn btn-primary" onClick={openCreateModal}>
                        <i className="bi bi-person-plus-fill me-2"></i> Add New User
                    </button>
                </div>
            </div>

            {/* Users Table */}
            <div className="card shadow-sm">
                <div className="card-body p-0">
                    <table className="table table-hover table-striped mb-0">
                        <thead className="table-dark">
                            <tr>
                                <th className="ps-4">ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th className="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.map(user => (
                                <tr key={user.id} className="align-middle">
                                    <td className="ps-4">#{user.id}</td>
                                    <td>{user.name}</td>
                                    <td>{user.email}</td>
                                    <td>
                                        <button
                                            onClick={() => toggleStatus(user.id)}
                                            className={`badge border-0 ${user.is_active ? 'bg-success' : 'bg-danger'}`}
                                            style={{cursor: 'pointer'}}
                                        >
                                            {user.is_active ? 'Active' : 'Deactive'}
                                        </button>
                                    </td>
                                    <td className="text-end pe-4">
                                        <button className="btn btn-sm btn-outline-warning me-2" onClick={() => openEditModal(user)}>
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button className="btn btn-sm btn-outline-danger" onClick={() => handleDelete(user.id)}>
                                            <i className="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            ))}
                            {users.length === 0 && (
                                <tr>
                                    <td colSpan="5" className="text-center py-4">No users found.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Simple Modal Overlay for Form */}
            {showModal && (
                <div className="modal d-block" style={{background: 'rgba(0,0,0,0.5)'}}>
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">{isEditing ? 'Edit User' : 'Create User'}</h5>
                                <button type="button" className="btn-close" onClick={() => setShowModal(false)}></button>
                            </div>
                            <div className="modal-body">
                                <form onSubmit={handleSubmit}>
                                    <div className="mb-3">
                                        <label>Name</label>
                                        <input type="text" name="name" className="form-control" value={formData.name} onChange={handleChange} required />
                                    </div>
                                    <div className="mb-3">
                                        <label>Email</label>
                                        <input type="email" name="email" className="form-control" value={formData.email} onChange={handleChange} required />
                                    </div>
                                    <div className="mb-3">
                                        <label>Password {isEditing && <small className="text-muted">(Leave blank to keep current)</small>}</label>
                                        <input type="password" name="password" className="form-control" value={formData.password} onChange={handleChange} required={!isEditing} />
                                    </div>
                                    <div className="d-flex justify-content-end">
                                        <button type="button" className="btn btn-secondary me-2" onClick={() => setShowModal(false)}>Cancel</button>
                                        <button type="submit" className="btn btn-primary">{isEditing ? 'Update' : 'Create'}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </Layout>
    );
}
