import { useNavigate } from "react-router-dom";

export default function Layout({ children, title }) {
    const navigate = useNavigate();
    const user = JSON.parse(localStorage.getItem('user'));

    const logout = () => {
        localStorage.clear();
        navigate('/');
    };

    return (
        <div className="d-flex flex-column min-vh-100 bg-light">
            {/* Navbar */}
            <nav className="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
                <div className="container">
                    <a className="navbar-brand fw-bold" href="#">RTO Admin</a>
                    <div className="d-flex align-items-center text-white">
                        <span className="me-3">Welcome, {user?.name}</span>
                        <button onClick={logout} className="btn btn-outline-light btn-sm">
                            <i className="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </div>
                </div>
            </nav>

            {/* Main Content */}
            <div className="container mt-4">
                <div className="d-flex justify-content-between align-items-center mb-4">
                    <h2 className="text-primary fw-bold">{title}</h2>
                </div>
                {children}
            </div>
        </div>
    );
}
