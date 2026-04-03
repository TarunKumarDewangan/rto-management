import React, { useState, useEffect } from 'react';
import api from '../api';
import UserNavbar from './UserNavbar';
import toast from 'react-hot-toast';

export default function WhatsAppLogs() {
    const [logs, setLogs] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchLogs();
    }, []);

    const fetchLogs = async () => {
        try {
            setLoading(true);
            const res = await api.get('/api/whatsapp-logs');
            setLogs(res.data);
            setLoading(false);
        } catch (error) {
            toast.error("Failed to fetch logs");
            setLoading(false);
        }
    };

    return (
        <div className="bg-light min-vh-100">
            <UserNavbar />
            <div className="container mt-4">
                <div className="d-flex justify-content-between align-items-center mb-4">
                    <h4 className="fw-bold text-dark mb-0">
                        <i className="bi bi-chat-left-text-fill text-primary me-2"></i>
                        Today's WhatsApp Logs
                    </h4>
                    <button className="btn btn-white shadow-sm btn-sm" onClick={fetchLogs}>
                        <i className="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>

                <div className="card border-0 shadow-sm overflow-hidden">
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="bg-white">
                                <tr>
                                    <th className="px-4 py-3 text-muted small text-uppercase">Time</th>
                                    <th className="py-3 text-muted small text-uppercase">Mobile No.</th>
                                    <th className="py-3 text-muted small text-uppercase">Message Content</th>
                                    <th className="py-3 text-muted small text-uppercase">Status</th>
                                    <th className="px-4 py-3 text-muted small text-uppercase text-end">Response</th>
                                </tr>
                            </thead>
                            <tbody>
                                {loading ? (
                                    <tr><td colSpan="5" className="text-center py-5 text-muted">Loading logs...</td></tr>
                                ) : logs.length > 0 ? logs.map((log) => (
                                    <tr key={log.id}>
                                        <td className="px-4 py-3">
                                            <div className="fw-bold small text-dark">
                                                {new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                            </div>
                                        </td>
                                        <td className="py-3">
                                            <span className="badge bg-light text-dark border fw-medium px-2 py-1">
                                                {log.mobile}
                                            </span>
                                        </td>
                                        <td className="py-3">
                                            <div className="text-muted small" style={{ maxWidth: '300px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }} title={log.message}>
                                                {log.message}
                                            </div>
                                        </td>
                                        <td className="py-3">
                                            {log.status ? (
                                                <span className="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <i className="bi bi-check-circle-fill me-1"></i> Success
                                                </span>
                                            ) : (
                                                <span className="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                    <i className="bi bi-x-circle-fill me-1"></i> Failed
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 text-end">
                                            <button className="btn btn-link btn-sm text-decoration-none p-0 text-primary small" onClick={() => alert(log.response_body || 'No response recorded')}>
                                                View Body
                                            </button>
                                        </td>
                                    </tr>
                                )) : (
                                    <tr><td colSpan="5" className="text-center py-5 text-muted">No messages sent today yet.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
}
