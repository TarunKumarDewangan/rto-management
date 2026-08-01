import { useState, useEffect } from 'react';
import api from '../api';
import toast from 'react-hot-toast';
import UserNavbar from './UserNavbar';

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '-';

export default function Festivals() {
    const [list, setList] = useState([]);
    const [loading, setLoading] = useState(false);

    const [showModal, setShowModal] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [form, setForm] = useState({ date: "", name: "", msg_to_send: "" });

    useEffect(() => { fetchData(); }, []);

    const fetchData = async () => {
        setLoading(true);
        try {
            const res = await api.get('/api/festivals');
            setList(res.data);
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (editingId) {
                await api.put(`/api/festivals/${editingId}`, form);
                toast.success("Updated");
            } else {
                await api.post('/api/festivals', form);
                toast.success("Saved");
            }
            setShowModal(false);
            fetchData();
        } catch {
            toast.error("Failed to save");
        }
    };

    const openEdit = (item) => {
        setEditingId(item.id);
        setForm({
            date: item.date || "",
            name: item.name || "",
            msg_to_send: item.msg_to_send || ""
        });
        setShowModal(true);
    };

    const handleDelete = async (id) => {
        if (!confirm("Delete this festival?")) return;
        try {
            await api.delete(`/api/festivals/${id}`);
            toast.success("Deleted");
            fetchData();
        } catch {
            toast.error("Failed to delete");
        }
    };

    const resetForm = () => {
        setEditingId(null);
        setForm({ date: "", name: "", msg_to_send: "" });
        setShowModal(true);
    };

    return (
        <div className="bg-light min-vh-100">
            <UserNavbar />
            <div className="container-fluid px-4 mt-4 pb-5">

                <div className="d-flex justify-content-between align-items-center mb-4">
                    <h3 className="fw-bold text-dark"><i className="bi bi-stars text-warning me-2"></i> Festivals</h3>
                    <button className="btn btn-primary fw-bold px-4" onClick={resetForm}><i className="bi bi-plus-lg"></i> Add Festival</button>
                </div>

                <div className="card border-0 shadow-sm">
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="table-light">
                                <tr>
                                    <th className="ps-4">#</th>
                                    <th>Date</th>
                                    <th>Festival Name</th>
                                    <th>Msg To Send</th>
                                    <th className="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {loading ? (
                                    <tr><td colSpan="5" className="text-center py-5"><div className="spinner-border text-primary"></div></td></tr>
                                ) : list.length > 0 ? (
                                    list.map((item, index) => (
                                        <tr key={item.id}>
                                            <td className="ps-4 fw-bold text-muted">{index + 1}</td>
                                            <td className="fw-bold">{formatDate(item.date)}</td>
                                            <td className="fw-bold text-primary">{item.name}</td>
                                            <td className="text-muted small" style={{ maxWidth: '400px', whiteSpace: 'pre-wrap' }}>{item.msg_to_send || '-'}</td>
                                            <td className="text-end pe-4">
                                                <button onClick={() => openEdit(item)} className="btn btn-sm btn-outline-primary me-2"><i className="bi bi-pencil-square"></i></button>
                                                <button onClick={() => handleDelete(item.id)} className="btn btn-sm btn-outline-danger"><i className="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr><td colSpan="5" className="text-center py-5 text-muted">No festivals added yet.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {showModal && (
                <div className="modal d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg">
                            <div className="modal-header bg-primary text-white py-2">
                                <h5 className="modal-title fw-bold">{editingId ? 'Edit Festival' : 'Add Festival'}</h5>
                                <button className="btn-close btn-close-white" onClick={() => setShowModal(false)}></button>
                            </div>
                            <div className="modal-body p-4">
                                <form onSubmit={handleSubmit}>
                                    <div className="row g-3">
                                        <div className="col-md-6">
                                            <label className="form-label small fw-bold">Date *</label>
                                            <input type="date" className="form-control" value={form.date} onChange={e => setForm({ ...form, date: e.target.value })} required />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label small fw-bold">Festival Name *</label>
                                            <input type="text" className="form-control" placeholder="Festival Name" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} required />
                                        </div>
                                        <div className="col-12">
                                            <label className="form-label small fw-bold">Msg To Send</label>
                                            <textarea className="form-control" rows="4" placeholder="Message to send to citizens" value={form.msg_to_send} onChange={e => setForm({ ...form, msg_to_send: e.target.value })}></textarea>
                                        </div>
                                    </div>
                                    <div className="d-grid mt-4">
                                        <button type="submit" className="btn btn-primary fw-bold">Save Festival</button>
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
