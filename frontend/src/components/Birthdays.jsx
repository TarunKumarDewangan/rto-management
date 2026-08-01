import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import api from "../api";
import UserNavbar from "./UserNavbar";

const toDateInput = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

const formatDob = (dateString) => {
    if (!dateString) return "-";
    return new Date(dateString).toLocaleDateString('en-GB');
};

const getTurningAge = (birthDate, onDate) => {
    if (!birthDate) return "-";
    const birth = new Date(birthDate);
    const ref = new Date(onDate);
    return ref.getFullYear() - birth.getFullYear();
};

export default function Birthdays() {
    const today = toDateInput(new Date());

    const [filters, setFilters] = useState({ name: '', mobile_number: '', date: today, show_all: '' });
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(false);

    const fetchBirthdays = async (pageNo = 1, filterOverride = null) => {
        setLoading(true);
        try {
            const params = new URLSearchParams({ ...(filterOverride || filters), page: pageNo });
            for (const [key, value] of params.entries()) { if (!value) params.delete(key); }
            const res = await api.get(`/api/birthdays?${params.toString()}`);
            setData(res.data);
        } catch {
            console.error("Failed to load birthdays");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { fetchBirthdays(1); }, []);

    const handleFilterChange = (e) => setFilters({ ...filters, [e.target.name]: e.target.value });
    const handleSearch = (e) => { e.preventDefault(); fetchBirthdays(1); };
    const handleReset = () => {
        const next = { name: '', mobile_number: '', date: today, show_all: '' };
        setFilters(next);
        fetchBirthdays(1, next);
    };
    const handleToggleShowAll = (e) => {
        const next = { ...filters, show_all: e.target.checked ? '1' : '' };
        setFilters(next);
        fetchBirthdays(1, next);
    };

    return (
        <div className="bg-light min-vh-100">
            <UserNavbar />
            <div className="container mt-4 pb-5">
                <h3 className="text-primary fw-bold mb-4">
                    <i className="bi bi-balloon-heart-fill me-2"></i>
                    {filters.show_all === '1' ? "Birthdays (All Citizens)" : "Today's Birthdays"}
                </h3>

                <div className="card border-0 shadow-sm mb-4">
                    <div className="card-header bg-white fw-bold">Filter Records</div>
                    <div className="card-body">
                        <form onSubmit={handleSearch}>
                            <div className="row g-3">
                                <div className="col-md-4">
                                    <label className="form-label small fw-bold">Name</label>
                                    <input type="text" className="form-control" name="name" value={filters.name} onChange={handleFilterChange} placeholder="Search Name" />
                                </div>
                                <div className="col-md-3">
                                    <label className="form-label small fw-bold">Mobile</label>
                                    <input type="text" className="form-control" name="mobile_number" value={filters.mobile_number} onChange={handleFilterChange} placeholder="Search Mobile" />
                                </div>
                                <div className="col-md-3">
                                    <label className="form-label small fw-bold">Date</label>
                                    <input type="date" className="form-control" name="date" value={filters.date} onChange={handleFilterChange} disabled={filters.show_all === '1'} />
                                </div>
                            </div>
                            <div className="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div className="form-check form-switch">
                                    <input
                                        className="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="showAllSwitch"
                                        checked={filters.show_all === '1'}
                                        onChange={handleToggleShowAll}
                                    />
                                    <label className="form-check-label fw-bold text-primary" htmlFor="showAllSwitch">
                                        Show All Citizens (with DOB entered)
                                    </label>
                                </div>
                            </div>
                            <div className="mt-3 d-flex justify-content-end gap-2">
                                <button type="button" className="btn btn-secondary" onClick={handleReset}>Reset</button>
                                <button type="submit" className="btn btn-primary px-4 fw-bold">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div className="card border-0 shadow-sm">
                    <div className="card-body p-0">
                        <div className="table-responsive">
                            <table className="table table-hover mb-0 align-middle">
                                <thead className="table-light">
                                    <tr>
                                        <th className="ps-4">Name</th>
                                        <th>Mobile</th>
                                        <th>Date of Birth</th>
                                        <th>Turning Age</th>
                                        <th>Location</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {loading ? (
                                        <tr><td colSpan="6" className="text-center py-5">Loading...</td></tr>
                                    ) : data?.data?.length > 0 ? (
                                        data.data.map((c) => (
                                            <tr key={c.id}>
                                                <td className="ps-4 fw-bold text-primary">{c.name}</td>
                                                <td>{c.mobile_number}</td>
                                                <td>{formatDob(c.birth_date)}</td>
                                                <td>{getTurningAge(c.birth_date, filters.show_all === '1' ? new Date() : filters.date)}</td>
                                                <td>{[c.city_district, c.state].filter(Boolean).join(', ') || '-'}</td>
                                                <td>
                                                    <Link to={`/citizens/${c.id}`} className="btn btn-sm btn-outline-primary" title="View Citizen">
                                                        <i className="bi bi-eye"></i>
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr><td colSpan="6" className="text-center py-5 text-muted">No birthdays found.</td></tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {data && data.last_page > 1 && (
                        <div className="card-footer bg-white d-flex justify-content-end py-3">
                            <nav>
                                <ul className="pagination mb-0">
                                    <li className={`page-item ${data.current_page === 1 ? 'disabled' : ''}`}>
                                        <button className="page-link" onClick={() => fetchBirthdays(data.current_page - 1)}>Prev</button>
                                    </li>
                                    <li className="page-item active"><span className="page-link">Page {data.current_page} of {data.last_page}</span></li>
                                    <li className={`page-item ${data.current_page === data.last_page ? 'disabled' : ''}`}>
                                        <button className="page-link" onClick={() => fetchBirthdays(data.current_page + 1)}>Next</button>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
