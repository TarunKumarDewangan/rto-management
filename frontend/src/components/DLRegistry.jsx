import React, { useState, useEffect } from 'react';
import api from '../api';
import toast from 'react-hot-toast';
import UserNavbar from './UserNavbar';

// Helper
const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '-';

export default function DLRegistry() {
    const [list, setList] = useState([]);
    const [loading, setLoading] = useState(false);

    // FILTERS STATE
    const [search, setSearch] = useState("");
    const [dates, setDates] = useState({ from: '', to: '' });
    const [toggles, setToggles] = useState({
        crossed31: false,
        expires2Months: false // <--- UPDATED
    });

    const [showModal, setShowModal] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [form, setForm] = useState({
        name: "", mobile_number: "", dob: "",
        dl_number: "", valid_from: "", valid_upto: "",
        remarks: ""
    });

    const user = JSON.parse(localStorage.getItem('user'));
    const canEdit = user?.role !== 'level_0';

    // TRIGGER FETCH ON FILTER CHANGE
    useEffect(() => { fetchData(); }, [search, dates, toggles]);

    const fetchData = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if(search) params.append('search', search);
            if(dates.from) params.append('from_date', dates.from);
            if(dates.to) params.append('to_date', dates.to);
            if(toggles.crossed31) params.append('crossed_31_days', 'true');

            // SEND NEW PARAM
            if(toggles.expires2Months) params.append('expires_2_months', 'true');

            const res = await api.get(`/api/dls?${params.toString()}`);
            setList(res.data);
        } catch (error) { console.error(error); }
        finally { setLoading(false); }
    };

    const handleToggle = (key) => {
        setToggles(prev => ({ ...prev, [key]: !prev[key] }));
    };

    const handleReset = () => {
        setSearch("");
        setDates({ from: '', to: '' });
        setToggles({ crossed31: false, expires2Months: false });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if(editingId) {
                await api.put(`/api/dls/${editingId}`, form);
                toast.success("Updated");
            } else {
                await api.post('/api/dls', form);
                toast.success("Saved");
            }
            setShowModal(false);
            fetchData();
        } catch (error) { toast.error("Failed to save"); }
    };

    const openEdit = (item) => {
        setEditingId(item.id);
        setForm({
            name: item.name,
            mobile_number: item.mobile_number,
            dob: item.dob || "",
            dl_number: item.dl_number || "",
            valid_from: item.valid_from || "",
            valid_upto: item.valid_upto || "",
            remarks: item.remarks || ""
        });
        setShowModal(true);
    };

    const handleDelete = async (id) => {
        if(!confirm("Delete?")) return;
        try { await api.delete(`/api/dls/${id}`); toast.success("Deleted"); fetchData(); } catch (e) { toast.error("Failed"); }
    };

    const resetForm = () => {
        setEditingId(null);
        setForm({ name: "", mobile_number: "", dob: "", dl_number: "", valid_from: "", valid_upto: "", remarks: "" });
        setShowModal(true);
    };

    return (
        <div className="bg-light min-vh-100">
            <UserNavbar />
            <div className="container-fluid px-4 mt-4 pb-5">

                <div className="d-flex justify-content-between align-items-center mb-4">
                    <h3 className="fw-bold text-dark"><i className="bi bi-card-heading text-primary me-2"></i> DL Registry</h3>
                    <button className="btn btn-primary fw-bold px-4" onClick={resetForm}><i className="bi bi-plus-lg"></i> New DL Entry</button>
                </div>

                {/* FILTERS UI */}
                <div className="card border-0 shadow-sm mb-4">
                    <div className="card-body p-4">
                        <div className="row g-3 align-items-end">
                            {/* Search */}
                            <div className="col-md-3">
                                <label className="form-label small fw-bold text-muted mb-1">Search Details</label>
                                <input type="text" className="form-control" placeholder="Name, Mobile, DL No..." value={search} onChange={e=>setSearch(e.target.value)} />
                            </div>

                            {/* Dates */}
                            <div className="col-md-2">
                                <label className="form-label small fw-bold text-muted mb-1">Expiry From</label>
                                <input type="date" className="form-control" value={dates.from} onChange={e=>setDates({...dates, from: e.target.value})} />
                            </div>
                            <div className="col-md-2">
                                <label className="form-label small fw-bold text-muted mb-1">Expiry To</label>
                                <input type="date" className="form-control" value={dates.to} onChange={e=>setDates({...dates, to: e.target.value})} />
                            </div>

                            {/* Toggles */}
                            <div className="col-md-3 ps-md-4">
                                <div className="form-check form-switch mb-2">
                                    <input className="form-check-input" type="checkbox" checked={toggles.crossed31} onChange={()=>handleToggle('crossed31')} />
                                    <label className="form-check-label small fw-bold text-primary">Crossed 31 Days (Active)</label>
                                </div>

                                {/* 2 MONTHS TOGGLE */}
                                <div className="form-check form-switch">
                                    <input className="form-check-input" type="checkbox" checked={toggles.expires2Months} onChange={()=>handleToggle('expires2Months')} />
                                    <label className="form-check-label small fw-bold text-warning">Expires in 2 Months</label>
                                </div>
                            </div>

                            {/* Reset */}
                            <div className="col-md-2 text-end">
                                <button className="btn btn-secondary w-100 fw-bold" onClick={handleReset}>
                                    <i className="bi bi-arrow-counterclockwise me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* TABLE */}
                <div className="card border-0 shadow-sm">
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="table-light">
                                <tr>
                                    <th className="ps-4">#</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>DL Number</th>
                                    <th>DOB</th>
                                    <th>Valid From</th>
                                    <th>Valid Upto</th>
                                    <th>Remark</th>
                                    <th className="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {loading ? <tr><td colSpan="9" className="text-center py-5"><div className="spinner-border text-primary"></div></td></tr> :
                                list.map((item, index) => (
                                    <tr key={item.id}>
                                        <td className="ps-4 fw-bold text-muted">{index + 1}</td>
                                        <td className="fw-bold text-uppercase text-primary">{item.name}</td>
                                        <td>{item.mobile_number}</td>
                                        <td className="fw-bold">{item.dl_number || '-'}</td>
                                        <td>{formatDate(item.dob)}</td>
                                        <td>{formatDate(item.valid_from)}</td>
                                        <td className={new Date(item.valid_upto) < new Date() ? "text-danger fw-bold" : "text-success fw-bold"}>
                                            {formatDate(item.valid_upto)}
                                        </td>
                                        <td className="text-muted fst-italic small">{item.remarks || '-'}</td>
                                        <td className="text-end pe-4">
                                            <button onClick={()=>openEdit(item)} className="btn btn-sm btn-outline-primary me-2"><i className="bi bi-pencil-square"></i></button>
                                            {canEdit && <button onClick={()=>handleDelete(item.id)} className="btn btn-sm btn-outline-danger"><i className="bi bi-trash"></i></button>}
                                        </td>
                                    </tr>
                                ))}
                                {list.length === 0 && <tr><td colSpan="9" className="text-center py-5 text-muted">No DL records found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* MODAL */}
            {showModal && (
                <div className="modal d-block" style={{backgroundColor:'rgba(0,0,0,0.5)'}}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg">
                            <div className="modal-header bg-primary text-white py-2">
                                <h5 className="modal-title fw-bold">{editingId ? 'Edit DL' : 'New DL Entry'}</h5>
                                <button className="btn-close btn-close-white" onClick={()=>setShowModal(false)}></button>
                            </div>
                            <div className="modal-body p-4">
                                <form onSubmit={handleSubmit}>
                                    <div className="row g-3">
                                        <div className="col-md-6"><label className="small fw-bold">Name *</label><input type="text" className="form-control" value={form.name} onChange={e=>setForm({...form, name:e.target.value.toUpperCase()})} required /></div>
                                        <div className="col-md-6"><label className="small fw-bold">Mobile *</label><input type="number" className="form-control" value={form.mobile_number} onChange={e=>setForm({...form, mobile_number:e.target.value})} required /></div>

                                        <div className="col-md-6"><label className="small fw-bold">DOB</label><input type="date" className="form-control" value={form.dob} onChange={e=>setForm({...form, dob:e.target.value})} /></div>
                                        <div className="col-md-6"><label className="small fw-bold">DL Number</label><input type="text" className="form-control" value={form.dl_number} onChange={e=>setForm({...form, dl_number:e.target.value.toUpperCase()})} /></div>

                                        <div className="col-md-6"><label className="small fw-bold">Valid From</label><input type="date" className="form-control" value={form.valid_from} onChange={e=>setForm({...form, valid_from:e.target.value})} /></div>
                                        <div className="col-md-6"><label className="small fw-bold">Valid Upto *</label><input type="date" className="form-control border-primary" value={form.valid_upto} onChange={e=>setForm({...form, valid_upto:e.target.value})} required /></div>

                                        <div className="col-12"><label className="small fw-bold">Remark</label><textarea className="form-control" rows="2" value={form.remarks} onChange={e=>setForm({...form, remarks:e.target.value})}></textarea></div>
                                    </div>
                                    <div className="d-grid mt-4">
                                        <button type="submit" className="btn btn-primary fw-bold">Save DL Entry</button>
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
