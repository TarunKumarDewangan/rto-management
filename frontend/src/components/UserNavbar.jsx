import { useState, useEffect, useRef } from "react";
import { Link, useNavigate, useLocation } from "react-router-dom";
import api from "../api";
import toast from "react-hot-toast";

export default function UserNavbar() {
    const navigate = useNavigate();
    const location = useLocation();

    // Load User from Storage
    const [user, setUser] = useState(() => {
        return JSON.parse(localStorage.getItem('user')) || {};
    });

    // --- SEARCH STATES ---
    const [query, setQuery] = useState("");
    const [results, setResults] = useState([]);
    const [showDropdown, setShowDropdown] = useState(false);
    const searchRef = useRef(null);

    // --- SETTINGS MODAL ---
    const [showSettings, setShowSettings] = useState(false);
    const [activeTab, setActiveTab] = useState('days'); // 'days' or 'password'
    const [isOpen, setIsOpen] = useState(false); // Mobile Menu

    // Forms
    const [passForm, setPassForm] = useState({ current_password: "", new_password: "", new_password_confirmation: "" });
    const [settingsForm, setSettingsForm] = useState({
        days_tax: user.days_tax || 15,
        days_insurance: user.days_insurance || 15,
        days_fitness: user.days_fitness || 15,
        days_permit: user.days_permit || 15,
        days_pucc: user.days_pucc || 7,
        days_vltd: user.days_vltd || 15,
        days_speed: user.days_speed || 15,
        days_ll: user.days_ll || 30, // Separate LL
        days_dl: user.days_dl || 60,   // Separate DL
        whatsapp_key: user.whatsapp_key || "",
        whatsapp_host: user.whatsapp_host || ""
    });

    const logout = () => { localStorage.clear(); navigate('/'); };
    const isActive = (path) => location.pathname === path ? 'active fw-bold text-primary' : 'text-secondary';
    const getDisplayName = (name) => (!name) ? "User" : (name.length > 10 ? name.substring(0, 10) + "..." : name);

    // Search Logic
    useEffect(() => {
        const delayDebounceFn = setTimeout(async () => {
            if (query.length > 1) {
                try {
                    const res = await api.get(`/api/global-search?query=${query}`);
                    setResults(res.data);
                    setShowDropdown(true);
                } catch (error) { console.error("Search Error"); }
            } else { setResults([]); setShowDropdown(false); }
        }, 300);
        return () => clearTimeout(delayDebounceFn);
    }, [query]);

    const handleSelectResult = (item) => {
        if (item.link) navigate(item.link); else navigate(`/citizens/${item.id}`);
        setQuery(""); setShowDropdown(false);
    };

    // Close dropdown on outside click
    useEffect(() => {
        function handleClickOutside(event) {
            if (searchRef.current && !searchRef.current.contains(event.target)) setShowDropdown(false);
        }
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, [searchRef]);

    // --- HANDLERS ---
    const handleSaveSettings = async (e) => {
        e.preventDefault();
        try {
            const res = await api.post('/api/user/settings', settingsForm);
            toast.success("Settings Updated!");
            
            // Update local user data
            const updatedUser = { ...user, ...res.data.user };
            localStorage.setItem('user', JSON.stringify(updatedUser));
            window.dispatchEvent(new Event('storage')); // Notify other components
            
            setShowSettings(false);
        } catch (error) { toast.error("Failed to save."); }
    };

    const handleChangePassword = async (e) => {
        e.preventDefault();
        if (passForm.new_password !== passForm.new_password_confirmation) return toast.error("Mismatch!");
        try {
            await api.post('/api/change-password', passForm);
            toast.success("Password Updated!");
            setPassForm({ current_password: "", new_password: "", new_password_confirmation: "" });
        } catch (error) { toast.error("Failed."); }
    };

    return (
        <>
            <nav className="navbar navbar-expand-lg bg-white shadow-sm sticky-top py-2">
                <div className="container-fluid px-4">
                    <Link className="navbar-brand fw-bold text-primary fs-4" to="/dashboard"><i className="bi bi-car-front-fill me-2"></i>RTO Hub</Link>
                    <button className="navbar-toggler border-0" type="button" onClick={() => setIsOpen(!isOpen)}><span className="navbar-toggler-icon"></span></button>

                    <div className={`collapse navbar-collapse ${isOpen ? 'show' : ''} mt-2 mt-lg-0`}>
                        <ul className="navbar-nav me-auto mb-2 mb-lg-0">
                            <li className="nav-item"><Link className={`nav-link ${isActive('/dashboard')}`} to="/dashboard">Dashboard</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/citizens')}`} to="/citizens">Citizens</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/reports/expiry')}`} to="/reports/expiry">Expiry Reports</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/backup')}`} to="/backup">Backup</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/quick-entry')}`} to="/quick-entry">Quick Entry</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/license-registry')}`} to="/license-registry">LL/DL</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/dl-registry')}`} to="/dl-registry">DL Registry</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/whatsapp-logs')}`} to="/whatsapp-logs">WA Logs</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/birthdays')}`} to="/birthdays">Birthdays</Link></li>
                            <li className="nav-item"><Link className={`nav-link ${isActive('/festivals')}`} to="/festivals">Festivals</Link></li>
                        </ul>

                        {/* Search Bar */}
                        <div className="mx-lg-4 position-relative w-100" style={{maxWidth: '400px'}} ref={searchRef}>
                            <div className="input-group">
                                <span className="input-group-text bg-light border-end-0"><i className="bi bi-search text-muted"></i></span>
                                <input type="text" className="form-control bg-light border-start-0" placeholder="Search..." value={query} onChange={(e) => setQuery(e.target.value)} onFocus={() => query.length > 1 && setShowDropdown(true)} />
                                {query && <button className="btn btn-light border border-start-0" onClick={() => {setQuery(''); setShowDropdown(false);}}><i className="bi bi-x"></i></button>}
                            </div>
                            {showDropdown && (
                                <div className="position-absolute w-100 mt-1 bg-white border rounded shadow-lg overflow-hidden" style={{zIndex: 1050, maxHeight: '300px', overflowY: 'auto'}}>
                                    {results.length > 0 ? results.map((res, index) => (
                                        <div key={index} className="p-2 border-bottom d-flex justify-content-between align-items-center" style={{cursor: 'pointer'}} onClick={() => handleSelectResult(res)} onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#f8f9fa'} onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'white'}>
                                            <div><div className="fw-bold text-dark">{res.title}</div><small className="text-muted">{res.subtitle}</small></div>
                                            <span className={`badge ${res.type.includes('Registry') ? 'bg-info text-dark' : 'bg-secondary'} text-uppercase`} style={{fontSize:'10px'}}>{res.type}</span>
                                        </div>
                                    )) : <div className="p-3 text-center text-muted small">No results found</div>}
                                </div>
                            )}
                        </div>

                        {/* User Actions */}
                        <div className="d-flex align-items-center justify-content-between border-top pt-3 pt-lg-0 border-lg-0 mt-3 mt-lg-0">
                            <div className="d-flex align-items-center">
                                <div className="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style={{width:'35px', height:'35px'}}>{user?.name?.charAt(0).toUpperCase()}</div>
                                <div className="lh-1 d-none d-lg-block text-end" style={{minWidth: '100px'}}>
                                    <small className="text-muted d-block" style={{fontSize: '10px'}}>{user?.email}</small>
                                    <span className="fw-bold text-dark small" title={user?.name}>{getDisplayName(user?.name)}</span>
                                </div>
                            </div>
                            <div className="d-flex gap-2 ms-3">
                                <button onClick={() => setShowSettings(true)} className="btn btn-outline-secondary btn-sm" title="Settings"><i className="bi bi-gear-fill"></i></button>
                                <button onClick={logout} className="btn btn-outline-danger btn-sm"><i className="bi bi-box-arrow-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            {/* SETTINGS MODAL */}
            {showSettings && (
                <div className="modal d-block" style={{backgroundColor: 'rgba(0,0,0,0.5)'}}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg">
                            <div className="modal-header bg-light py-2">
                                <h6 className="modal-title fw-bold">Settings</h6>
                                <button className="btn-close" onClick={() => setShowSettings(false)}></button>
                            </div>
                            <div className="modal-body p-0">
                                {/* Tabs */}
                                <div className="d-flex border-bottom">
                                    <button className={`btn flex-grow-1 rounded-0 ${activeTab === 'days' ? 'btn-primary' : 'btn-light'}`} onClick={()=>setActiveTab('days')}>Reminder Days</button>
                                    <button className={`btn flex-grow-1 rounded-0 ${activeTab === 'wa' ? 'btn-primary' : 'btn-light'}`} onClick={()=>setActiveTab('wa')}>WhatsApp API</button>
                                    <button className={`btn flex-grow-1 rounded-0 ${activeTab === 'password' ? 'btn-primary' : 'btn-light'}`} onClick={()=>setActiveTab('password')}>Password</button>
                                </div>

                                <div className="p-4">
                                    {activeTab === 'days' ? (
                                        <form onSubmit={handleSaveSettings}>
                                            <p className="small text-muted mb-3 text-center">Set how many days <strong>before expiry</strong> to send WhatsApp.</p>
                                            <div className="row g-2">
                                                <div className="col-6"><label className="small fw-bold">Tax</label><input type="number" className="form-control" value={settingsForm.days_tax} onChange={e=>setSettingsForm({...settingsForm, days_tax: e.target.value})} /></div>
                                                <div className="col-6"><label className="small fw-bold">Insurance</label><input type="number" className="form-control" value={settingsForm.days_insurance} onChange={e=>setSettingsForm({...settingsForm, days_insurance: e.target.value})} /></div>
                                                <div className="col-6"><label className="small fw-bold">Fitness</label><input type="number" className="form-control" value={settingsForm.days_fitness} onChange={e=>setSettingsForm({...settingsForm, days_fitness: e.target.value})} /></div>
                                                <div className="col-6"><label className="small fw-bold">Permit</label><input type="number" className="form-control" value={settingsForm.days_permit} onChange={e=>setSettingsForm({...settingsForm, days_permit: e.target.value})} /></div>
                                                <div className="col-6"><label className="small fw-bold">PUCC</label><input type="number" className="form-control" value={settingsForm.days_pucc} onChange={e=>setSettingsForm({...settingsForm, days_pucc: e.target.value})} /></div>
                                                <div className="col-6"><label className="small fw-bold">VLTD / Speed</label><input type="number" className="form-control" value={settingsForm.days_vltd} onChange={e=>setSettingsForm({...settingsForm, days_vltd: e.target.value, days_speed: e.target.value})} /></div>

                                                <div className="col-12 mt-2"><hr className="my-1 text-muted"/></div>

                                                {/* SEPARATE LICENSE FIELDS */}
                                                <div className="col-6"><label className="small fw-bold text-primary">LL (Learner)</label><input type="number" className="form-control border-primary" value={settingsForm.days_ll} onChange={e=>setSettingsForm({...settingsForm, days_ll: e.target.value})} /></div>
                                                <div className="col-6"><label className="small fw-bold text-success">DL (Driver)</label><input type="number" className="form-control border-success" value={settingsForm.days_dl} onChange={e=>setSettingsForm({...settingsForm, days_dl: e.target.value})} /></div>
                                            </div>
                                            <button className="btn btn-success w-100 mt-3 fw-bold">Save Settings</button>
                                        </form>
                                    ) : activeTab === 'wa' ? (
                                        <form onSubmit={handleSaveSettings}>
                                            <div className="mb-3">
                                                <label className="small fw-bold">WhatsApp API Key</label>
                                                <input type="text" className="form-control" placeholder="Enter API Key" value={settingsForm.whatsapp_key} onChange={e=>setSettingsForm({...settingsForm, whatsapp_key: e.target.value})} />
                                            </div>
                                            <div className="mb-3">
                                                <label className="small fw-bold">API Host URL</label>
                                                <input type="text" className="form-control" placeholder="e.g. api.wa-sender.com" value={settingsForm.whatsapp_host} onChange={e=>setSettingsForm({...settingsForm, whatsapp_host: e.target.value})} />
                                            </div>
                                            <button className="btn btn-success w-100 fw-bold mb-4">Save API Settings</button>

                                            <hr className="text-muted" />
                                            <p className="small text-muted mb-2 text-center">Test your connection below</p>
                                            <div className="mb-3">
                                                <div className="input-group">
                                                    <span className="input-group-text bg-light">+91</span>
                                                    <input type="text" className="form-control" placeholder="Test Mobile No" id="test_mobile" />
                                                    <button type="button" className="btn btn-primary fw-bold" onClick={async () => {
                                                        const mobile = document.getElementById('test_mobile').value;
                                                        if (!mobile || mobile.length !== 10) return toast.error("Enter valid 10 digit number");
                                                        try {
                                                            toast.loading("Sending...");
                                                            await api.post('/api/admin/test-whatsapp', { 
                                                                mobile, 
                                                                whatsapp_key: settingsForm.whatsapp_key, 
                                                                whatsapp_host: settingsForm.whatsapp_host 
                                                            });
                                                            toast.dismiss();
                                                            toast.success("Test Message Sent!");
                                                        } catch (e) { 
                                                            toast.dismiss();
                                                            toast.error(e.response?.data?.message || "Test Failed"); 
                                                        }
                                                    }}>Test</button>
                                                </div>
                                            </div>
                                        </form>
                                    ) : (
                                        <form onSubmit={handleChangePassword}>
                                            <div className="mb-2"><label className="small">Current</label><input type="password" class="form-control" value={passForm.current_password} onChange={(e) => setPassForm({...passForm, current_password: e.target.value})} /></div>
                                            <div className="mb-2"><label className="small">New</label><input type="password" class="form-control" value={passForm.new_password} onChange={(e) => setPassForm({...passForm, new_password: e.target.value})} /></div>
                                            <div className="mb-3"><label className="small">Confirm</label><input type="password" class="form-control" value={passForm.new_password_confirmation} onChange={(e) => setPassForm({...passForm, new_password_confirmation: e.target.value})} /></div>
                                            <button className="btn btn-primary w-100">Update Password</button>
                                        </form>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
