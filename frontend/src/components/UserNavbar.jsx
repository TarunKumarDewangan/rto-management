import { Link, useNavigate, useLocation } from "react-router-dom";

export default function UserNavbar() {
    const navigate = useNavigate();
    const location = useLocation();
    const user = JSON.parse(localStorage.getItem('user'));

    const logout = () => {
        localStorage.clear();
        navigate('/');
    };

    const isActive = (path) => location.pathname === path ? 'active fw-semibold' : '';

    return (
        <nav className="navbar navbar-expand-lg bg-white shadow-sm py-2 px-4">
            <div className="container-fluid">
                <Link className="navbar-brand fw-bold text-primary" to="/dashboard">RTO Management</Link>

                <div className="collapse navbar-collapse ms-4">
                    <ul className="navbar-nav me-auto mb-2 mb-lg-0">
                        <li className="nav-item">
                            <Link className={`nav-link ${isActive('/dashboard')}`} to="/dashboard">Dashboard</Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${isActive('/citizens')}`} to="/citizens">Citizens</Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${isActive('/reports/expiry')}`} to="/reports/expiry">Expiry Reports</Link>
                        </li>
                        {/* --- NEW BACKUP LINK --- */}
                        <li className="nav-item">
                            <Link className={`nav-link ${isActive('/backup')}`} to="/backup">Backup</Link>
                        </li>
                    </ul>
                </div>

                <div className="d-flex align-items-center gap-3">
                     <div className="text-end d-none d-md-block lh-1">
                        <small className="text-muted d-block" style={{fontSize: '12px'}}>Signed in as</small>
                        <span className="fw-bold text-primary">{user?.name}</span>
                    </div>
                    <button onClick={logout} className="btn btn-outline-danger btn-sm ms-2">Logout</button>
                </div>
            </div>
        </nav>
    );
}
