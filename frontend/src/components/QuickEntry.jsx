import React, { useState } from 'react';
import api from '../api';
import toast from 'react-hot-toast';
import UserNavbar from './UserNavbar';

export default function QuickEntry() {
    const [loading, setLoading] = useState(false);
    const [activeTab, setActiveTab] = useState('PUCC'); // 'PUCC', 'INSURANCE' or 'PERMIT'

    const [form, setForm] = useState({
        name: "",
        mobile_number: "",
        registration_no: "",
        type: "",
        valid_from: "",
        valid_until: "",
        // Insurance Specific
        company: "",
        policy_number: "",
        // Permit Specific
        permit_number: "",
        permit_type: ""
    });

    const themeColor = activeTab === 'PUCC' ? 'success' : activeTab === 'INSURANCE' ? 'primary' : 'warning';

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            // Send 'mode' to backend so it knows which table to insert into
            const payload = { ...form, mode: activeTab };

            await api.post('/api/quick-entry', payload);

            toast.success(`${activeTab} Saved Successfully!`);

            // Reset Form (Keep Customer info if needed, but here we reset all for next customer)
            setForm({
                name: "",
                mobile_number: "",
                registration_no: "",
                type: "",
                valid_from: "",
                valid_until: "",
                company: "",
                policy_number: "",
                permit_number: "",
                permit_type: ""
            });
        } catch (error) {
            toast.error(error.response?.data?.message || "Failed to save");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="bg-light min-vh-100">
            <UserNavbar />

            <div className="container mt-5" style={{maxWidth: '600px'}}>

                {/* TABS */}
                <div className="d-flex justify-content-center mb-3">
                    <div className="btn-group shadow-sm">
                        <button
                            className={`btn fw-bold px-4 ${activeTab === 'PUCC' ? 'btn-success' : 'btn-outline-secondary bg-white'}`}
                            onClick={() => setActiveTab('PUCC')}
                        >
                            <i className="bi bi-lightning-charge-fill me-2"></i> PUCC Entry
                        </button>
                        <button
                            className={`btn fw-bold px-4 ${activeTab === 'INSURANCE' ? 'btn-primary' : 'btn-outline-secondary bg-white'}`}
                            onClick={() => setActiveTab('INSURANCE')}
                        >
                            <i className="bi bi-shield-check me-2"></i> Insurance Entry
                        </button>
                        <button
                            className={`btn fw-bold px-4 ${activeTab === 'PERMIT' ? 'btn-warning' : 'btn-outline-secondary bg-white'}`}
                            onClick={() => setActiveTab('PERMIT')}
                        >
                            <i className="bi bi-file-earmark-text-fill me-2"></i> Permit Entry
                        </button>
                    </div>
                </div>

                <div className="card border-0 shadow-lg">
                    <div className={`card-header text-white py-3 bg-${themeColor}`}>
                        <h5 className="mb-0 fw-bold">
                            {activeTab === 'PUCC' ? '⚡ PUCC Quick Entry' : activeTab === 'INSURANCE' ? '🛡️ Insurance Quick Entry' : '📄 Permit Quick Entry'}
                        </h5>
                    </div>
                    <div className="card-body p-4">
                        <form onSubmit={handleSubmit}>

                            {/* Customer Details */}
                            <h6 className={`fw-bold mb-3 text-${themeColor}`}>Customer Details</h6>
                            <div className="row g-3 mb-3">
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Owner Name *</label>
                                    <input type="text" className="form-control" value={form.name} onChange={e => setForm({...form, name: e.target.value.toUpperCase()})} required />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Mobile Number *</label>
                                    <input type="number" className="form-control" value={form.mobile_number} onChange={e => setForm({...form, mobile_number: e.target.value})} required />
                                </div>
                            </div>

                            {/* Vehicle Details */}
                            <h6 className={`fw-bold mb-3 mt-4 text-${themeColor}`}>Vehicle Details</h6>
                            <div className="row g-3 mb-3">
                                <div className="col-md-8">
                                    <label className="form-label small fw-bold">Vehicle Reg No *</label>
                                    <input
                                        type="text"
                                        className="form-control fw-bold fs-5 text-uppercase"
                                        placeholder="CG04..."
                                        value={form.registration_no}
                                        onChange={e => setForm({...form, registration_no: e.target.value.toUpperCase()})}
                                        required
                                    />
                                </div>
                                <div className="col-md-4">
                                    <label className="form-label small fw-bold">Vehicle Type</label>
                                    <select
                                        className="form-select"
                                        value={form.type}
                                        onChange={e => setForm({...form, type: e.target.value})}
                                    >
                                        <option value="">Select...</option>
                                        <option value="Motorcycle">Motorcycle</option>
                                        <option value="Car">Car</option>
                                        <option value="LMV">LMV</option>
                                        <option value="LGV">LGV</option>
                                        <option value="MGV">MGV</option>
                                        <option value="HGV">HGV</option>
                                        <option value="TRUCK">TRUCK</option>
                                        <option value="OTHERS">OTHERS</option>
                                    </select>
                                </div>
                            </div>

                            {/* DYNAMIC DOCUMENT SECTION */}
                            <h6 className={`fw-bold mb-3 mt-4 text-${themeColor}`}>
                                {activeTab === 'PUCC' ? 'PUCC Validity' : activeTab === 'INSURANCE' ? 'Insurance Details' : 'Permit Details'}
                            </h6>

                            {/* INSURANCE EXTRA FIELDS */}
                            {activeTab === 'INSURANCE' && (
                                <div className="row g-3 mb-3">
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Company Name</label>
                                        <input type="text" className="form-control" placeholder="e.g. Bajaj" value={form.company} onChange={e => setForm({...form, company: e.target.value.toUpperCase()})} />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Policy Number</label>
                                        <input type="text" className="form-control" placeholder="Optional" value={form.policy_number} onChange={e => setForm({...form, policy_number: e.target.value.toUpperCase()})} />
                                    </div>
                                </div>
                            )}

                            {/* PERMIT EXTRA FIELDS */}
                            {activeTab === 'PERMIT' && (
                                <div className="row g-3 mb-3">
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Permit No</label>
                                        <input type="text" className="form-control" placeholder="Optional" value={form.permit_number} onChange={e => setForm({...form, permit_number: e.target.value.toUpperCase()})} />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold">Permit Type</label>
                                        <input type="text" className="form-control" placeholder="e.g. National" value={form.permit_type} onChange={e => setForm({...form, permit_type: e.target.value.toUpperCase()})} />
                                    </div>
                                </div>
                            )}

                            {/* DATES (Shared) */}
                            <div className="row g-3 mb-4">
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">{activeTab === 'PERMIT' ? 'Permit From' : 'Valid From (Start)'}</label>
                                    <input type="date" className="form-control" value={form.valid_from} onChange={e => setForm({...form, valid_from: e.target.value})} />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">{activeTab === 'PERMIT' ? 'Permit Upto *' : 'Valid Upto (Expiry) *'}</label>
                                    <input type="date" className="form-control border-danger" value={form.valid_until} onChange={e => setForm({...form, valid_until: e.target.value})} required />
                                </div>
                            </div>

                            <div className="d-grid">
                                <button type="submit" className={`btn fw-bold py-2 btn-${themeColor}`} disabled={loading}>
                                    {loading ? 'Saving...' : `Save ${activeTab} Entry`}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
