import { useState, useEffect } from "react";
import axios from "axios";
import { Link } from "react-router-dom";
import UserNavbar from "./UserNavbar";

export default function CitizenList() {
    const [citizens, setCitizens] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState("");

    const token = localStorage.getItem('token');

    // 1. Fetch Citizens from API
    useEffect(() => {
        fetchCitizens();
    }, []);

    const fetchCitizens = async () => {
        try {
            const res = await axios.get('http://127.0.0.1:8000/api/citizens', {
                headers: { Authorization: `Bearer ${token}` }
            });
            setCitizens(res.data);
            setLoading(false);
        } catch (error) {
            console.error("Error fetching citizens:", error);
            setLoading(false);
        }
    };

    // 2. Filter Logic for Search Bar
    const filteredCitizens = citizens.filter(citizen =>
        citizen.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        citizen.mobile_number.includes(searchTerm) ||
        (citizen.city_district && citizen.city_district.toLowerCase().includes(searchTerm.toLowerCase()))
    );

    return (
        <div className="bg-light min-vh-100">
            {/* Navigation Bar */}
            <UserNavbar />

            <div className="container mt-4">

                {/* Main Card Container */}
                <div className="card border-0 shadow-sm">

                    {/* Card Header */}
                    <div className="card-header bg-white py-3">
                        <div className="row align-items-center">

                            {/* Title */}
                            <div className="col-md-4">
                                <h5 className="mb-0 fw-bold text-primary">Citizen Records</h5>
                            </div>

                            {/* Search & Add Button */}
                            <div className="col-md-8 d-flex justify-content-end gap-2">

                                {/* Search Input */}
                                <div className="input-group" style={{maxWidth: '300px'}}>
                                    <span className="input-group-text bg-light border-end-0">
                                        <i className="bi bi-search text-muted"></i>
                                    </span>
                                    <input
                                        type="text"
                                        className="form-control border-start-0 bg-light"
                                        placeholder="Search Name, Mobile, Vehicle..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </div>

                                {/* Add New Button (Linked to Create Page) */}
                                <Link to="/create-citizen" className="btn btn-success text-white fw-semibold">
                                    <i className="bi bi-plus-lg me-1"></i> Add New
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* Table Section */}
                    <div className="card-body p-0">
                        <div className="table-responsive">
                            <table className="table table-hover mb-0 align-middle">
                                <thead className="table-light">
                                    <tr>
                                        <th className="ps-4">#</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Location</th>
                                        <th className="text-center">Vehicles</th>
                                        <th className="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {/* Loading State */}
                                    {loading ? (
                                        <tr>
                                            <td colSpan="6" className="text-center py-5 text-muted">
                                                <div className="spinner-border spinner-border-sm me-2"></div>
                                                Loading records...
                                            </td>
                                        </tr>
                                    ) : filteredCitizens.length > 0 ? (
                                        /* Data Rows */
                                        filteredCitizens.map((citizen, index) => (
                                            <tr key={citizen.id}>
                                                <td className="ps-4 fw-bold text-muted">{index + 1}</td>
                                                <td>
    <Link to={`/citizens/${citizen.id}`} className="fw-bold text-decoration-none text-primary">
        {citizen.name}
    </Link>
</td>
                                                <td>{citizen.mobile_number}</td>
                                                <td>{citizen.city_district || <span className="text-muted">-</span>}</td>
                                                <td className="text-center">
                                                    <span className="badge bg-secondary rounded-pill px-3">
                                                        {citizen.vehicles_count}
                                                    </span>
                                                </td>
                                                <td className="text-end pe-4">
                                                    <button className="btn btn-sm btn-outline-primary me-2" title="View/Edit">
                                                        <i className="bi bi-eye"></i>
                                                    </button>
                                                    <button className="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i className="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        /* Empty State */
                                        <tr>
                                            <td colSpan="6" className="text-center py-5 bg-light text-muted">
                                                No matching records found.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {/* End Card Body */}

                </div>
            </div>
        </div>
    );
}
