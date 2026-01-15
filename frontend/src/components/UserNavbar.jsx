import { useState, useEffect, useRef } from "react";
import { Link, useNavigate, useLocation } from "react-router-dom";
import api from "../api";
import toast from "react-hot-toast"; // Import Toast

export default function UserNavbar() {
    const navigate = useNavigate();
    const location = useLocation();
    const user = JSON.parse(localStorage.getItem('user'));

    // --- SEARCH STATES ---
    const [query, setQuery] = useState("");
    const [results, setResults] = useState([]);
    const [showDropdown, setShowDropdown] = useState(false);
    const searchRef = useRef(null);

    // --- MENU & MODAL STATES ---
    const [isOpen, setIsOpen] = useState(false);
    const [showPasswordModal, setShowPasswordModal] = useState(false);

    // --- PASSWORD FORM STATE ---
    const [passForm, setPassForm] = useState({
        current_password: "",
        new_password: "",
        new_password_confirmation: ""
    });
    const [loadingPass, setLoadingPass] = useState(false);

    const logout = () => {
        localStorage.clear();
        navigate('/');
    };

    const isActive = (path) => location.pathname === path ? 'active fw-bold text-primary' : 'text-secondary';

    const getDisplayName = (name) => {
        if (!name) return "User";
        return name.length > 10 ? name.substring(0, 10) + "..." : name;
    };

    // --- SEARCH LOGIC ---
    useEffect(() => {
        const delayDebounceFn = setTimeout(async () => {
            if (query.length > 1) {
                try {
                    const res = await api.get(`/api/global-search?query=${query}`);
                    setResults(res.data);
                    setShowDropdown(true);
                } catch (error) { console.error("Search Error", error); }
            } else {
                setResults([]); setShowDropdown(false);
            }
        }, 300);
        return () => clearTimeout(delayDebounceFn);
    }, [query]);

    const handleSelectResult = (citizenId) => {
        navigate(`/citizens/${citizenId}`);
        setQuery(""); setShowDropdown(false);
    };

    useEffect(() => {
        function handleClickOutside(event) {
            if (searchRef.current && !searchRef.current.contains(event.target)) {
                setShowDropdown(false);
            }
        }
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, [searchRef]);

    // --- CHANGE PASSWORD HANDLER ---
    const handleChangePassword = async (e) => {
        e.preventDefault();

        if (passForm.new_password !== passForm.new_password_confirmation) {
            return toast.error("New passwords do not match!");
        }

        setLoadingPass(true);
        try {
            await api.post('/api/change-password', passForm);
            toast.success("Password Changed Successfully!");
            setShowPasswordModal(false);
            setPassForm({ current_password: "", new_password: "", new_password_confirmation: "" }); // Reset
        } catch (error) {
            toast.error(error.response?.data?.message || "Failed to update password.");
        } finally {
            setLoadingPass(false);
        }
    };

    return (
        <>
            <nav className="navbar navbar-expand-lg bg-white shadow-sm sticky-top py-2">
                <div className="container-fluid px-4">
                    <Link className="navbar-brand fw-bold text-primary fs-4" to="/dashboard">
                        <i className="bi bi-car-front-fill me-2"></i>RTO Hub
                    </Link>

                    <button className="navbar-toggler border-0" type="button" onClick={() => setIsOpen(!isOpen)}>
                        <span className="navbar-toggler-icon"></span>
                    </button>

                    <div className={`collapse navbar-collapse ${isOpen ? 'show' : ''} mt-2 mt-lg-0`}>
                        <ul className="navbar-nav me-auto mb-2 mb-lg-0">
                            <li className="nav-item"><Link className={`nav-link ${isActive('/dashboard')}`} to="/dashboard" onClick={()=>setIsOpen(false)}>Dashboard</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/citizens')}`} to="/citizens" onClick={()=>setIsOpen(false)}>Citizens</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/reports/expiry')}`} to="/reports/expiry" onClick={()=>setIsOpen(false)}>Expiry Reports</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/backup')}`} to="/backup" onClick={()=>setIsOpen(false)}>Backup</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/quick-entry')}`} to="/quick-entry" onClick={()=>setIsOpen(false)}>Quick Entry</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/license-registry')}`} to="/license-registry" onClick={()=>setIsOpen(false)}>LL/DL Registry</Link></li>
                        </ul>

                        {/* SEARCH */}
                        <div className="mx-lg-4 position-relative w-100" style={{maxWidth: '400px'}} ref={searchRef}>
                            <div className="input-group">
                                <span className="input-group-text bg-light border-end-0"><i className="bi bi-search text-muted"></i></span>
                                <input type="text" className="form-control bg-light border-start-0" placeholder="Search..." value={query} onChange={(e) => setQuery(e.target.value)} onFocus={() => query.length > 1 && setShowDropdown(true)} />
                                {query && <button className="btn btn-light border border-start-0" onClick={() => {setQuery(''); setShowDropdown(false);}}><i className="bi bi-x"></i></button>}
                            </div>
                            {showDropdown && (
                                <div className="position-absolute w-100 mt-1 bg-white border rounded shadow-lg overflow-hidden" style={{zIndex: 1050, maxHeight: '300px', overflowY: 'auto'}}>
                                    {results.length > 0 ? (
                                        results.map((res, index) => (
                                            <div key={index} className="p-2 border-bottom d-flex justify-content-between align-items-center" style={{cursor: 'pointer'}} onClick={() => handleSelectResult(res.id)} onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#f8f9fa'} onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'white'}>
                                                <div><div className="fw-bold text-dark">{res.title}</div><small className="text-muted">{res.subtitle}</small></div>
                                                <span className="badge bg-secondary text-uppercase" style={{fontSize:'10px'}}>{res.type}</span>
                                            </div>
                                        ))
                                    ) : (<div className="p-3 text-center text-muted small">No results found</div>)}
                                </div>
                            )}
                        </div>

                        {/* USER INFO & ACTIONS */}
                        <div className="d-flex align-items-center justify-content-between border-top pt-3 pt-lg-0 border-lg-0 mt-3 mt-lg-0">
                            <div className="d-flex align-items-center">
                                <div className="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style={{width:'35px', height:'35px'}}>
                                    {user?.name?.charAt(0).toUpperCase()}
                                </div>
                                <div className="lh-1 d-none d-lg-block text-end" style={{minWidth: '100px'}}>
                                    <small className="text-muted d-block" style={{fontSize: '10px'}}>{user?.email}</small>
                                    <span className="fw-bold text-dark small" title={user?.name}>{getDisplayName(user?.name)}</span>
                                </div>
                            </div>

                            <div className="d-flex gap-2 ms-3">
                                {/* Settings Button */}
                                <button onClick={() => setShowPasswordModal(true)} className="btn btn-outline-secondary btn-sm" title="Change Password">
                                    <i className="bi bi-gear-fill"></i>
                                </button>
                                {/* Logout Button */}
                                <button onClick={logout} className="btn btn-outline-danger btn-sm">
                                    <i className="bi bi-box-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            {/* --- CHANGE PASSWORD MODAL --- */}
            {showPasswordModal && (
                <div className="modal d-block" style={{backgroundColor: 'rgba(0,0,0,0.5)'}}>
                    <div className="modal-dialog modal-dialog-centered modal-sm">
                        <div className="modal-content border-0 shadow-lg">
                            <div className="modal-header bg-light py-2">
                                <h6 className="modal-title fw-bold"><i className="bi bi-shield-lock me-2"></i>Change Password</h6>
                                <button className="btn-close" onClick={() => setShowPasswordModal(false)}></button>
                            </div>
                            <div className="modal-body p-3">
                                <form onSubmit={handleChangePassword}>
                                    <div className="mb-3">
                                        <label className="form-label small fw-bold">Current Password</label>
                                        <input type="password" class="form-control" value={passForm.current_password} onChange={(e) => setPassForm({...passForm, current_password: e.target.value})} required />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label small fw-bold">New Password</label>
                                        <input type="password" class="form-control" value={passForm.new_password} onChange={(e) => setPassForm({...passForm, new_password: e.target.value})} required minLength="6" />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label small fw-bold">Confirm New Password</label>
                                        <input type="password" class="form-control" value={passForm.new_password_confirmation} onChange={(e) => setPassForm({...passForm, new_password_confirmation: e.target.value})} required minLength="6" />
                                    </div>
                                    <div className="d-grid">
                                        <button type="submit" className="btn btn-primary fw-bold" disabled={loadingPass}>
                                            {loadingPass ? "Updating..." : "Update Password"}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
