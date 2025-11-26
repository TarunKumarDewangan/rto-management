import React, { useState, useEffect } from "react";
import { useParams, Link, useNavigate } from "react-router-dom";
import axios from "axios";
import toast from "react-hot-toast";
import UserNavbar from "./UserNavbar";

export default function AccountStatement() {
    const { id } = useParams();
    const token = localStorage.getItem("token");

    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [selectedIds, setSelectedIds] = useState([]);

    // --- INLINE EDITING STATE ---
    const [editingTxId, setEditingTxId] = useState(null);
    const [editForm, setEditForm] = useState({ amount: "", payment_date: "", remarks: "" });

    // --- FETCH DATA ---
    const fetchStatement = async () => {
        try {
            const res = await axios.get(`http://127.0.0.1:8000/api/citizens/${id}/statement`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setData(res.data);
            setLoading(false);
        } catch (error) { toast.error("Failed to load."); }
    };

    useEffect(() => { fetchStatement(); }, [id]);

    // --- CHECKBOX LOGIC ---
    const handleSelectAll = (e) => {
        if (e.target.checked) {
            setSelectedIds(data.statement.map(i => i.id));
        } else {
            setSelectedIds([]);
        }
    };

    const handleSelectOne = (itemId) => {
        if (selectedIds.includes(itemId)) {
            setSelectedIds(selectedIds.filter(id => id !== itemId));
        } else {
            setSelectedIds([...selectedIds, itemId]);
        }
    };

    // --- EDIT/DELETE LOGIC ---
    const handleDeletePayment = async (paymentId) => {
        if(!confirm("Delete this payment?")) return;
        try {
            await axios.delete(`http://127.0.0.1:8000/api/payments/${paymentId}`, { headers: { Authorization: `Bearer ${token}` } });
            toast.success("Deleted"); fetchStatement();
        } catch (error) { toast.error("Error deleting."); }
    };

    const startEdit = (pay) => {
        setEditingTxId(pay.id);
        setEditForm({ amount: pay.amount, payment_date: pay.payment_date, remarks: pay.remarks || "" });
    };

    const cancelEdit = () => setEditingTxId(null);

    const saveEdit = async (paymentId) => {
        try {
            await axios.put(`http://127.0.0.1:8000/api/payments/${paymentId}`, editForm, { headers: { Authorization: `Bearer ${token}` } });
            toast.success("Updated"); setEditingTxId(null); fetchStatement();
        } catch (error) { toast.error("Error updating."); }
    };

    // --- PRINT LOGIC (Browser Native) ---
    const handlePrint = () => {
        window.print();
    };

    if (loading) return <div className="text-center mt-5"><div className="spinner-border text-primary"></div></div>;

    // Totals
    const totalBilled = data.statement.reduce((sum, item) => sum + Number(item.bill_amount), 0);
    const totalPaid = data.statement.reduce((sum, item) => sum + Number(item.paid), 0);
    const totalDue = totalBilled - totalPaid;

    return (
        <div className="bg-light min-vh-100">

            {/* --- CSS FOR PRINTING --- */}
            <style>
                {`
                    @media print {
                        .no-print { display: none !important; }
                        .print-only { display: block !important; }
                        body { background-color: white; }
                        .container { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
                        .card { border: none !important; box-shadow: none !important; }
                        /* Hide rows that are NOT selected */
                        tr.not-selected { display: none !important; }
                    }
                `}
            </style>

            <div className="no-print">
                <UserNavbar />
            </div>

            <div className="container mt-4 pb-5">

                {/* Header */}
                <div className="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h3 className="text-primary fw-bold mb-1">Account Statement</h3>
                        <h5 className="fw-bold">{data.citizen.name}</h5>
                        <p className="text-muted mb-0">Mobile: {data.citizen.mobile_number}</p>
                    </div>
                    <div className="d-flex gap-2 no-print">
                        <Link to={`/citizens/${id}`} className="btn btn-outline-secondary">
                            <i className="bi bi-arrow-left me-1"></i> Back
                        </Link>
                        <button onClick={handlePrint} className="btn btn-primary" disabled={selectedIds.length === 0}>
                            <i className="bi bi-printer me-1"></i> Print Selected ({selectedIds.length})
                        </button>
                    </div>
                    {/* Date only shows on Print */}
                    <div className="d-none d-print-block text-end">
                        <small>Date: {new Date().toLocaleDateString('en-GB')}</small>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="row g-3 mb-4 text-center">
                    <div className="col-md-4"><div className="card border-0 shadow-sm p-3"><small className="text-muted fw-bold">TOTAL BILLED</small><h3 className="text-primary mb-0">₹{totalBilled.toLocaleString()}</h3></div></div>
                    <div className="col-md-4"><div className="card border-0 shadow-sm p-3"><small className="text-muted fw-bold">TOTAL PAID</small><h3 className="text-success mb-0">₹{totalPaid.toLocaleString()}</h3></div></div>
                    <div className="col-md-4"><div className="card border-0 shadow-sm p-3" style={{backgroundColor: totalDue > 0 ? '#ffe6e6' : '#e6fffa'}}><small className="text-muted fw-bold">BALANCE DUE</small><h3 className={totalDue > 0 ? "text-danger mb-0" : "text-success mb-0"}>₹{totalDue.toLocaleString()}</h3></div></div>
                </div>

                {/* --- TABLE --- */}
                <div className="card border-0 shadow-sm">
                    <div className="table-responsive">
                        <table className="table mb-0 align-middle table-bordered">
                            <thead className="table-dark text-white text-center">
                                <tr>
                                    <th style={{width:'40px'}} className="no-print">
                                        <input type="checkbox" onChange={handleSelectAll} checked={selectedIds.length === data.statement.length && data.statement.length > 0} />
                                    </th>
                                    <th>Date</th><th>Vehicle</th><th>Service</th><th className="text-end">Bill</th><th className="text-end">Paid</th><th className="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.statement.map((item) => {
                                    const isSelected = selectedIds.includes(item.id);
                                    // Add class 'not-selected' if checkbox is off (CSS hides this during print)
                                    const rowClass = isSelected ? "selected-row" : "not-selected";

                                    return (
                                    <React.Fragment key={item.id}>

                                        {/* 1. MAIN BILL ROW */}
                                        <tr className={`bg-white fw-bold text-center ${rowClass}`}>
                                            <td className="no-print">
                                                <input type="checkbox" checked={isSelected} onChange={() => handleSelectOne(item.id)} />
                                            </td>
                                            <td>{new Date(item.date).toLocaleDateString('en-GB')}</td>
                                            <td>{item.vehicle}</td>
                                            <td>{item.service}</td>
                                            <td className="text-end">₹{Number(item.bill_amount)}</td>
                                            <td className="text-end">₹{Number(item.paid)}</td>
                                            <td className={`text-end ${item.balance > 0 ? 'text-danger' : 'text-success'}`}>₹{Number(item.balance)}</td>
                                        </tr>

                                        {/* 2. PAYMENT ROWS */}
                                        {item.payments.map((pay) => (
                                            <tr key={pay.id} className={`${rowClass}`} style={{backgroundColor: '#f8f9fa', fontSize: '13px'}}>
                                                <td className="no-print"></td>

                                                {/* --- INLINE EDIT LOGIC --- */}
                                                {editingTxId === pay.id ? (
                                                    // EDIT MODE
                                                    <>
                                                        <td colSpan="4" className="ps-5">
                                                            <div className="input-group input-group-sm">
                                                                <span className="input-group-text">Date</span>
                                                                <input type="date" className="form-control" value={editForm.payment_date} onChange={e => setEditForm({...editForm, payment_date: e.target.value})} />
                                                                <span className="input-group-text">Note</span>
                                                                <input type="text" className="form-control" value={editForm.remarks} onChange={e => setEditForm({...editForm, remarks: e.target.value})} />
                                                            </div>
                                                        </td>
                                                        <td className="text-end">
                                                            <input type="number" className="form-control form-control-sm text-end" style={{width:'80px', display:'inline-block'}} value={editForm.amount} onChange={e => setEditForm({...editForm, amount: e.target.value})} />
                                                        </td>
                                                        <td className="text-end pe-3 no-print">
                                                            <button onClick={() => saveEdit(pay.id)} className="btn btn-success btn-sm px-2 py-0 me-1"><i className="bi bi-check-lg"></i></button>
                                                            <button onClick={cancelEdit} className="btn btn-secondary btn-sm px-2 py-0"><i className="bi bi-x-lg"></i></button>
                                                        </td>
                                                    </>
                                                ) : (
                                                    // VIEW MODE
                                                    <>
                                                        <td colSpan="4" className="text-muted ps-5 fst-italic text-start">
                                                            <i className="bi bi-arrow-return-right me-2"></i>
                                                            Recv ({new Date(pay.payment_date).toLocaleDateString('en-GB')}): {pay.remarks || 'Cash/Online'}
                                                        </td>
                                                        <td className="text-end text-success fw-bold">- ₹{Number(pay.amount)}</td>
                                                        <td className="text-end pe-3 no-print">
                                                            <button onClick={() => startEdit(pay)} className="btn btn-link p-0 me-2 text-primary"><i className="bi bi-pencil-square"></i></button>
                                                            <button onClick={() => handleDeletePayment(pay.id)} className="btn btn-link p-0 text-danger"><i className="bi bi-trash"></i></button>
                                                        </td>
                                                        {/* Empty cell for balance col in print view */}
                                                        <td className="d-none d-print-table-cell"></td>
                                                    </>
                                                )}
                                            </tr>
                                        ))}
                                    </React.Fragment>
                                )})}
                                {data.statement.length === 0 && <tr><td colSpan="7" className="text-center py-4 text-muted">No records found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Print Footer */}
                <div className="d-none d-print-block mt-5 pt-5">
                    <div className="d-flex justify-content-between px-5">
                        <div className="border-top border-dark pt-2 text-center" style={{width: '200px'}}>Customer Signature</div>
                        <div className="border-top border-dark pt-2 text-center" style={{width: '200px'}}>Authorized Signatory</div>
                    </div>
                </div>

            </div>
        </div>
    );
}
