import { useState } from "react";
import axios from "axios";
import toast from "react-hot-toast";
import UserNavbar from "./UserNavbar";

export default function BackupPage() {
    const token = localStorage.getItem("token");

    // Selection State
    const [selection, setSelection] = useState({
        master: true, // Combined Record
        citizen: true,
        vehicle: true,
        tax: true,
        insurance: true,
        fitness: true,
        permit: true,
        pucc: true,
        speed_gov: true,
        vltd: true
    });

    const [downloading, setDownloading] = useState(false);

    // Toggle one checkbox
    const handleToggle = (key) => {
        setSelection(prev => ({ ...prev, [key]: !prev[key] }));
    };

    // Toggle All
    const handleSelectAll = () => {
        const allSelected = Object.values(selection).every(v => v);
        const newState = {};
        Object.keys(selection).forEach(k => newState[k] = !allSelected);
        setSelection(newState);
    };

    // Handle Download
    const handleDownload = async () => {
        const activeKeys = Object.keys(selection).filter(k => selection[k]);

        if (activeKeys.length === 0) {
            toast.error("Please select at least one file.");
            return;
        }

        setDownloading(true);
        const queryString = activeKeys.join(',');

        try {
            // We use axios with 'blob' response type to handle file download with Auth Token
            const response = await axios.get(`http://127.0.0.1:8000/api/export/backup?include=${queryString}`, {
                headers: { Authorization: `Bearer ${token}` },
                responseType: 'blob', // Important
            });

            // Create a download link programmatically
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `RTO_Backup_${new Date().toISOString().slice(0,10)}.zip`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            toast.success("Backup Downloaded Successfully!");
        } catch (error) {
            console.error(error);
            toast.error("Download failed.");
        } finally {
            setDownloading(false);
        }
    };

    return (
        <div className="bg-light min-vh-100">
            <UserNavbar />

            <div className="container mt-4" style={{maxWidth: '900px'}}>

                <div className="card border-0 shadow-sm">
                    <div className="card-header bg-white py-3 border-bottom">
                        <h4 className="mb-0 text-primary fw-bold">Download Data Backup</h4>
                    </div>

                    <div className="card-body p-4">

                        {/* Info Alert */}
                        <div className="alert alert-info border-0 shadow-sm" role="alert">
                            <i className="bi bi-info-circle-fill me-2"></i>
                            The system will generate a <strong>.ZIP</strong> file containing separate CSV files for each selection below.
                        </div>

                        {/* Toggle All Button */}
                        <div className="d-flex justify-content-end mb-3">
                            <button className="btn btn-outline-primary btn-sm fw-bold" onClick={handleSelectAll}>
                                <i className="bi bi-check-all me-1"></i> Toggle All
                            </button>
                        </div>

                        <div className="row g-3">

                            {/* 1. MASTER FILE (Highlighted) */}
                            <div className="col-md-12">
                                <div
                                    className={`border rounded p-3 d-flex align-items-center bg-light border-primary`}
                                    onClick={() => handleToggle('master')}
                                    style={{cursor: 'pointer', transition: '0.2s'}}
                                >
                                    <div className="form-check me-3">
                                        <input className="form-check-input fs-5" type="checkbox" checked={selection.master} onChange={()=>{}} style={{pointerEvents:'none'}} />
                                    </div>
                                    <div>
                                        <div className="fw-bold text-primary">MASTER COMBINED RECORD</div>
                                        <small className="text-muted">Contains all Citizens, Vehicles, and basic details in one big sheet.</small>
                                    </div>
                                </div>
                            </div>

                            {/* 2. INDIVIDUAL TABLES GRID */}
                            {Object.keys(selection).filter(k => k !== 'master').map((key) => (
                                <div className="col-md-6" key={key}>
                                    <div
                                        className={`border rounded p-3 d-flex align-items-center h-100 ${selection[key] ? 'border-success bg-success-subtle' : 'bg-white'}`}
                                        onClick={() => handleToggle(key)}
                                        style={{cursor: 'pointer', transition: '0.2s'}}
                                    >
                                        <div className="form-check me-3">
                                            <input className="form-check-input fs-5" type="checkbox" checked={selection[key]} onChange={()=>{}} style={{pointerEvents:'none'}} />
                                        </div>
                                        <div className="d-flex align-items-center">
                                            <i className="bi bi-filetype-csv me-2 text-secondary fs-4"></i>
                                            <span className="fw-bold text-uppercase text-dark">{key.replace('_', ' ')} Table</span>
                                        </div>
                                    </div>
                                </div>
                            ))}

                        </div>

                        <hr className="my-4" />

                        {/* Download Button */}
                        <div className="text-center">
                            <button
                                className="btn btn-success btn-lg px-5 shadow fw-bold"
                                onClick={handleDownload}
                                disabled={downloading}
                            >
                                {downloading ? (
                                    <span><span className="spinner-border spinner-border-sm me-2"></span> Generating...</span>
                                ) : (
                                    <span><i className="bi bi-download me-2"></i> Download Backup (.ZIP)</span>
                                )}
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    );
}
