import React, { useState, useEffect } from 'react';
import api from '../api';
import toast from 'react-hot-toast';
import UserNavbar from './UserNavbar';

// Helper for Date Format
const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '-';

export default function LicenseFlow() {
    const [list, setList] = useState([]);
    const [loading, setLoading] = useState(false);
    const [sendingId, setSendingId] = useState(null);

    // Filters
    const [search, setSearch] = useState("");
    const [dates, setDates] = useState({ from: '', to: '' });
    const [toggles, setToggles] = useState({
        crossed31: false,
        pendingDues: false,
        expiresSoon: false
    });

    // Modal & Form State
    const [showModal, setShowModal] = useState(false);
    const [step, setStep] = useState(1);
    const [editingId, setEditingId] = useState(null);
    const [enterDL, setEnterDL] = useState(false);

    // Permission
    const user = JSON.parse(localStorage.getItem('user'));
    const canEdit = user?.role !== 'level_0';

    const [form, setForm] = useState({
        applicant_name: "", dob: "", mobile_number: "", application_no: "", address: "",
        ll_number: "", categories: [], ll_valid_from: "", ll_valid_upto: "", ll_status: "Form Complete",
        dl_status: "", dl_app_no: "", dl_number: "", dl_valid_from: "", dl_valid_upto: "",
        ll_bill_amount: "", ll_paid_amount: "",
        dl_bill_amount: "", dl_paid_amount: ""
    });

    // Dropdown Options
    const categoryOptions = ["MCWG", "MCWOG", "LMV", "TRANS", "ERIK", "OTHERS"];
    const llStatuses = ["Form Complete", "LL Approved", "Test Pending", "Test Failed"];
    const dlStatuses = ["Processing Office", "Form Completed", "RTO Side Pending", "DL Done"];

    useEffect(() => { fetchLicenses(); }, [search, dates, toggles]);

    const fetchLicenses = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if(search) params.append('search', search);
            if(dates.from) params.append('from_date', dates.from);
            if(dates.to) params.append('to_date', dates.to);
            if(toggles.crossed31) params.append('crossed_31_days', 'true');
            if(toggles.pendingDues) params.append('pending_dues', 'true');
            if(toggles.expiresSoon) params.append('expires_soon', 'true');

            const res = await api.get(`/api/licenses?${params.toString()}`);
            setList(res.data);
        } catch (error) { console.error(error); }
        finally { setLoading(false); }
    };

    const handleWhatsApp = async (id) => {
        setSendingId(id);
        try {
            await api.post('/api/licenses/send-whatsapp', { id });
            toast.success("WhatsApp Sent!");
        } catch (e) { toast.error("Failed to send"); }
        finally { setSendingId(null); }
    };

    const handleToggle = (key) => {
        setToggles(prev => ({ ...prev, [key]: !prev[key] }));
    };

    const handleReset = () => {
        setSearch("");
        setDates({ from: '', to: '' });
        setToggles({ crossed31: false, pendingDues: false, expiresSoon: false });
    };

    // --- FORM HANDLERS ---
    const handleFinanceChange = (field, value) => {
        setForm(prev => {
            const newState = { ...prev, [field]: value };
            if(field === 'll_bill_amount') newState.dl_bill_amount = value;
            if(field === 'll_paid_amount') newState.dl_paid_amount = value;
            if(field === 'dl_bill_amount') newState.ll_bill_amount = value;
            if(field === 'dl_paid_amount') newState.ll_paid_amount = value;
            return newState;
        });
    };

    const handleCategoryChange = (e) => {
        const val = e.target.value;
        setForm(prev => {
            const newCats = e.target.checked ? [...prev.categories, val] : prev.categories.filter(c => c !== val);
            return { ...prev, categories: newCats };
        });
    };

    const handleStep1Submit = (e) => {
        e.preventDefault();
        setForm(prev => ({ ...prev, dl_bill_amount: prev.ll_bill_amount, dl_paid_amount: prev.ll_paid_amount }));
        if (enterDL) setStep(2); else finalSubmit();
    };

    const finalSubmit = async () => {
        try {
            const payload = { ...form };
            payload.dl_bill_amount = payload.ll_bill_amount;
            payload.dl_paid_amount = payload.ll_paid_amount;

            if(editingId) {
                await api.put(`/api/licenses/${editingId}`, payload);
                toast.success("Updated");
            } else {
                await api.post('/api/licenses', payload);
                toast.success("Saved");
            }
            setShowModal(false);
            fetchLicenses();
        } catch (error) { toast.error("Failed"); }
    };

    const openEdit = (item) => {
        setEditingId(item.id);
        setForm({
            ...item,
            categories: item.categories ? item.categories.split(',') : [],
            ll_bill_amount: item.ll_bill_amount || "",
            ll_paid_amount: item.ll_paid_amount || "",
            dl_bill_amount: item.ll_bill_amount || "",
            dl_paid_amount: item.ll_paid_amount || ""
        });
        setStep(1);
        setEnterDL(!!item.dl_number);
        setShowModal(true);
    };

    const handleDelete = async (id) => {
        if(!confirm("Delete?")) return;
        try { await api.delete(`/api/licenses/${id}`); toast.success("Deleted"); fetchLicenses(); } catch (e) { toast.error("Failed"); }
    };

    const resetForm = () => {
        setEditingId(null); setStep(1); setEnterDL(false);
        setForm({
            applicant_name: "", dob: "", mobile_number: "", application_no: "", address: "",
            ll_number: "", categories: [], ll_valid_from: "", ll_valid_upto: "", ll_status: "Form Complete",
            ll_bill_amount: "", ll_paid_amount: "",
            dl_status: "", dl_app_no: "", dl_number: "", dl_valid_from: "", dl_valid_upto: "",
            dl_bill_amount: "", dl_paid_amount: ""
        });
        setShowModal(true);
    };

    const pendingDue = (Number(form.ll_bill_amount) || 0) - (Number(form.ll_paid_amount) || 0);

    return (
        <div className="bg-light min-vh-100">
            <UserNavbar />
            <div className="container-fluid px-4 mt-4 pb-5">

                {/* HEADER */}
                <div className="d-flex justify-content-between align-items-center mb-4">
                    <h3 className="fw-bold text-dark">Learning License Registry</h3>
                    <button className="btn btn-primary fw-bold px-4 shadow-sm" onClick={resetForm}>
                        <i className="bi bi-plus-lg me-1"></i> New Entry
                    </button>
                </div>

                {/* FILTERS PANEL */}
                <div className="card border-0 shadow-sm mb-4">
                    <div className="card-body p-4">
                        <div className="row g-3 align-items-end">
                            {/* Search */}
                            <div className="col-md-3">
                                <label className="form-label small fw-bold text-muted mb-1">Search Details</label>
                                <input type="text" className="form-control" placeholder="Name, Mobile, App No..." value={search} onChange={e=>setSearch(e.target.value)} />
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
                                    <label className="form-check-label small fw-bold text-primary">Crossed 31 Days (DL Ready)</label>
                                </div>
                                <div className="form-check form-switch mb-2">
                                    <input className="form-check-input" type="checkbox" checked={toggles.pendingDues} onChange={()=>handleToggle('pendingDues')} />
                                    <label className="form-check-label small fw-bold text-danger">Pending Dues</label>
                                </div>
                                <div className="form-check form-switch">
                                    <input className="form-check-input" type="checkbox" checked={toggles.expiresSoon} onChange={()=>handleToggle('expiresSoon')} />
                                    <label className="form-check-label small fw-bold text-warning">Expires in 1 Month</label>
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

                {/* TABLE (Standard Font) */}
                <div className="card border-0 shadow-sm">
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="table-light text-uppercase small fw-bold text-muted">
                                <tr>
                                    <th className="ps-4">#</th>
                                    <th>Applicant Name</th>
                                    <th>Mobile</th>
                                    <th>App / LL Details</th>
                                    <th>DOB</th>
                                    <th>Validity Period</th>
                                    <th>Fees (Ask / Paid)</th>
                                    <th>Due</th>
                                    <th className="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {loading ? <tr><td colSpan="9" className="text-center py-5"><div className="spinner-border text-primary"></div></td></tr> :
                                list.map((item, index) => {
                                    const balance = Number(item.ll_bill_amount) - Number(item.ll_paid_amount);
                                    return (
                                        <tr key={item.id}>
                                            <td className="ps-4 fw-bold text-muted">{index + 1}</td>

                                            {/* NAME */}
                                            <td className="fw-bold text-primary text-uppercase">{item.applicant_name}</td>

                                            {/* MOBILE */}
                                            <td>{item.mobile_number}</td>

                                            {/* APP / LL NO */}
                                            <td>
                                                <div className="d-flex flex-column" style={{fontSize: '13px'}}>
                                                    <span className="text-muted">App: <span className="text-dark fw-bold">{item.application_no || '-'}</span></span>
                                                    <span className="text-muted">LL: <span className="text-primary fw-bold">{item.ll_number || '-'}</span></span>
                                                </div>
                                            </td>

                                            {/* DOB */}
                                            <td>{formatDate(item.dob)}</td>

                                            {/* DATES */}
                                            <td>
                                                <div className="d-flex flex-column" style={{fontSize: '12px'}}>
                                                    <span className="text-success fw-bold">{formatDate(item.ll_valid_from)}</span>
                                                    <span className="text-muted" style={{fontSize: '10px'}}>TO</span>
                                                    <span className="text-danger fw-bold">{formatDate(item.ll_valid_upto)}</span>
                                                </div>
                                            </td>

                                            {/* FEES */}
                                            <td>
                                                <div className="d-flex flex-column" style={{fontSize: '13px'}}>
                                                    <span className="text-muted">Ask: {Number(item.ll_bill_amount).toLocaleString()}</span>
                                                    <span className="text-success fw-bold">Pd: {Number(item.ll_paid_amount).toLocaleString()}</span>
                                                </div>
                                            </td>

                                            {/* BALANCE BADGE */}
                                            <td>
                                                {balance > 0 ? (
                                                    <span className="badge bg-danger rounded-pill px-3">₹{balance}</span>
                                                ) : (
                                                    <span className="badge bg-success rounded-pill px-3">Paid</span>
                                                )}
                                            </td>

                                            {/* ACTIONS */}
                                            <td className="text-end pe-4">
                                                <div className="d-flex justify-content-end gap-2">
                                                    <button
                                                        onClick={() => handleWhatsApp(item.id)}
                                                        className="btn btn-sm btn-success text-white"
                                                        title="Send WhatsApp"
                                                        disabled={sendingId === item.id}
                                                    >
                                                        {sendingId === item.id ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-whatsapp"></i>}
                                                    </button>

                                                    <button onClick={()=>openEdit(item)} className="btn btn-sm btn-primary text-white" title="Edit">
                                                        <i className="bi bi-pencil-square"></i>
                                                    </button>

                                                    {canEdit && (
                                                        <button onClick={()=>handleDelete(item.id)} className="btn btn-sm btn-danger text-white" title="Delete">
                                                            <i className="bi bi-trash"></i>
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                                {list.length === 0 && <tr><td colSpan="9" className="text-center py-5 text-muted">No records found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {/* MODAL (Same as before, simplified logic for display) */}
            {showModal && (
                <div className="modal d-block" style={{backgroundColor:'rgba(0,0,0,0.5)'}}>
                    <div className="modal-dialog modal-lg modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg">
                            <div className="modal-header bg-dark text-white py-2">
                                <h5 className="modal-title fw-bold">{step === 1 ? 'Step 1: LL Details' : 'Step 2: DL Details'}</h5>
                                <button className="btn-close btn-close-white" onClick={()=>setShowModal(false)}></button>
                            </div>
                            <div className="modal-body p-4">
                                {/* STEP 1 */}
                                {step === 1 && (
                                    <form onSubmit={handleStep1Submit}>
                                        <div className="row g-3">
                                            <div className="col-md-6"><label className="form-label small fw-bold">Name *</label><input type="text" className="form-control" value={form.applicant_name} onChange={e=>setForm({...form, applicant_name:e.target.value.toUpperCase()})} required /></div>
                                            <div className="col-md-3"><label className="form-label small fw-bold">DOB *</label><input type="date" className="form-control" value={form.dob} onChange={e=>setForm({...form, dob:e.target.value})} required /></div>
                                            <div className="col-md-3"><label className="form-label small fw-bold">Mobile *</label><input type="number" className="form-control" value={form.mobile_number} onChange={e=>setForm({...form, mobile_number:e.target.value})} required /></div>

                                            <div className="col-md-4"><label className="form-label small fw-bold">App No</label><input type="text" className="form-control" value={form.application_no} onChange={e=>setForm({...form, application_no:e.target.value.toUpperCase()})} /></div>
                                            <div className="col-md-4"><label className="form-label small fw-bold">LL No</label><input type="text" className="form-control" value={form.ll_number} onChange={e=>setForm({...form, ll_number:e.target.value.toUpperCase()})} /></div>
                                            <div className="col-md-4"><label className="form-label small fw-bold">Status</label><select className="form-select" value={form.ll_status} onChange={e=>setForm({...form, ll_status:e.target.value})}>{llStatuses.map(s=><option key={s} value={s}>{s}</option>)}</select></div>

                                            {/* SHARED PAYMENTS */}
                                            <div className="col-12 mt-2">
                                                <div className="card bg-light border-0 p-3">
                                                    <h6 className="fw-bold text-secondary mb-2"><i className="bi bi-cash-coin"></i> Fees & Payment (Shared)</h6>
                                                    <div className="row g-2">
                                                        <div className="col-md-4"><label className="small fw-bold">Asked Amount</label><input type="number" className="form-control" placeholder="0" value={form.ll_bill_amount} onChange={e=>handleFinanceChange('ll_bill_amount', e.target.value)} /></div>
                                                        <div className="col-md-4"><label className="small fw-bold text-success">Paid Amount</label><input type="number" className="form-control border-success" placeholder="0" value={form.ll_paid_amount} onChange={e=>handleFinanceChange('ll_paid_amount', e.target.value)} /></div>
                                                        <div className="col-md-4"><label className="small fw-bold text-danger">Pending Due</label><input type="text" className="form-control border-danger bg-white fw-bold text-danger" value={pendingDue} disabled /></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="col-12"><label className="form-label small fw-bold">Address</label><input type="text" className="form-control" value={form.address} onChange={e=>setForm({...form, address:e.target.value.toUpperCase()})} /></div>
                                            <div className="col-12"><label className="form-label small fw-bold d-block mb-2">Category</label><div className="d-flex flex-wrap gap-3">{categoryOptions.map(cat => (<div className="form-check" key={cat}><input className="form-check-input" type="checkbox" value={cat} checked={form.categories.includes(cat)} onChange={handleCategoryChange} /><label className="form-check-label small">{cat}</label></div>))}</div></div>
                                            <div className="col-md-6"><label className="form-label small fw-bold">LL Valid From</label><input type="date" className="form-control" value={form.ll_valid_from} onChange={e=>setForm({...form, ll_valid_from:e.target.value})} /></div>
                                            <div className="col-md-6"><label className="form-label small fw-bold">LL Valid Upto</label><input type="date" className="form-control" value={form.ll_valid_upto} onChange={e=>setForm({...form, ll_valid_upto:e.target.value})} /></div>
                                        </div>
                                        <div className="bg-light p-3 mt-4 rounded border"><label className="fw-bold small d-block mb-2">Enter DL Details?</label><div className="d-flex gap-4"><div className="form-check"><input className="form-check-input" type="radio" name="enterDL" id="dlNo" checked={!enterDL} onChange={()=>setEnterDL(false)} /><label className="form-check-label" htmlFor="dlNo">No (Save LL Only)</label></div><div className="form-check"><input className="form-check-input" type="radio" name="enterDL" id="dlYes" checked={enterDL} onChange={()=>setEnterDL(true)} /><label className="form-check-label fw-bold text-primary" htmlFor="dlYes">Yes (Go to DL)</label></div></div></div>
                                        <div className="mt-3 text-end"><button type="submit" className={`btn fw-bold px-4 ${enterDL ? 'btn-primary' : 'btn-success'}`}>{enterDL ? 'Next ➝' : 'Save Entry'}</button></div>
                                    </form>
                                )}

                                {/* STEP 2 */}
                                {step === 2 && (
                                    <div>
                                        <div className="alert alert-info py-2 small mb-3"><strong>Applicant:</strong> {form.applicant_name}</div>
                                        <div className="row g-3">
                                            <div className="col-md-6"><label className="form-label small fw-bold">DL Status</label><select className="form-select" value={form.dl_status} onChange={e=>setForm({...form, dl_status:e.target.value})}><option value="">Select...</option>{dlStatuses.map(s=><option key={s} value={s}>{s}</option>)}</select></div>
                                            <div className="col-md-6"><label className="form-label small fw-bold">DL App No</label><input type="text" className="form-control" value={form.dl_app_no} onChange={e=>setForm({...form, dl_app_no:e.target.value.toUpperCase()})} /></div>
                                            <div className="col-md-12"><label className="form-label small fw-bold">DL Number</label><input type="text" className="form-control fw-bold" value={form.dl_number} onChange={e=>setForm({...form, dl_number:e.target.value.toUpperCase()})} /></div>

                                            {/* SHARED PAYMENTS */}
                                            <div className="col-12 mt-1">
                                                <div className="card bg-light border-0 p-3">
                                                    <h6 className="fw-bold text-secondary mb-2"><i className="bi bi-credit-card"></i> Fees & Payment (Shared)</h6>
                                                    <div className="row g-2">
                                                        <div className="col-md-4"><label className="small fw-bold">Asked Amount</label><input type="number" className="form-control" placeholder="0" value={form.ll_bill_amount} onChange={e=>handleFinanceChange('ll_bill_amount', e.target.value)} /></div>
                                                        <div className="col-md-4"><label className="small fw-bold text-success">Paid Amount</label><input type="number" className="form-control border-success" placeholder="0" value={form.ll_paid_amount} onChange={e=>handleFinanceChange('ll_paid_amount', e.target.value)} /></div>
                                                        <div className="col-md-4"><label className="small fw-bold text-danger">Pending Due</label><input type="text" className="form-control border-danger bg-white fw-bold text-danger" value={pendingDue} disabled /></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="col-md-6"><label className="form-label small fw-bold">DL Valid From</label><input type="date" className="form-control" value={form.dl_valid_from} onChange={e=>setForm({...form, dl_valid_from:e.target.value})} /></div>
                                            <div className="col-md-6"><label className="form-label small fw-bold">DL Valid Upto</label><input type="date" className="form-control" value={form.dl_valid_upto} onChange={e=>setForm({...form, dl_valid_upto:e.target.value})} /></div>
                                        </div>
                                        <div className="mt-4 d-flex justify-content-between"><button onClick={()=>setStep(1)} className="btn btn-secondary">Back</button><button onClick={finalSubmit} className="btn btn-success fw-bold px-4">Save Final</button></div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
