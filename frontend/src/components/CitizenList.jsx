import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import toast from "react-hot-toast";
import UserNavbar from "./UserNavbar";
import api from "../api";

export default function CitizenList() {
  const [citizens, setCitizens] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");

  const [showEditModal, setShowEditModal] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [editForm, setEditForm] = useState({
    name: "",
    mobile_number: "",
    email: "",
    birth_date: "",
    relation_type: "",
    relation_name: "",
    address: "",
    state: "",
    city_district: "",
  });
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    fetchCitizens();
  }, []);

  const fetchCitizens = async () => {
    try {
      const res = await api.get("/citizens");
      setCitizens(res.data);
      setLoading(false);
    } catch (error) {
      console.error("Error:", error);
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (
      !confirm(
        "Are you sure? This will delete the citizen and all their vehicles/documents."
      )
    )
      return;
    try {
      await api.delete(`/citizens/${id}`);
      toast.success("Citizen Deleted");
      fetchCitizens();
    } catch (e) {
      toast.error("Error deleting.");
    }
  };

  const openEditModal = (citizen) => {
    setEditingId(citizen.id);
    setEditForm({
      name: citizen.name,
      mobile_number: citizen.mobile_number,
      email: citizen.email || "",
      birth_date: citizen.birth_date || "",
      relation_type: citizen.relation_type || "",
      relation_name: citizen.relation_name || "",
      address: citizen.address || "",
      state: citizen.state || "",
      city_district: citizen.city_district || "",
    });
    setShowEditModal(true);
  };

  const handleUpdate = async (e) => {
    e.preventDefault();
    setIsSaving(true);
    try {
      await api.put(`/citizens/${editingId}`, editForm);
      toast.success("Citizen Updated Successfully!");
      setShowEditModal(false);
      fetchCitizens();
    } catch (error) {
      toast.error("Error updating citizen.");
    } finally {
      setIsSaving(false);
    }
  };

  const filteredCitizens = citizens.filter(
    (citizen) =>
      citizen.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      citizen.mobile_number.includes(searchTerm) ||
      (citizen.city_district &&
        citizen.city_district.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  return (
    <div className="bg-light min-vh-100">
      <UserNavbar />

      <div className="container mt-4">
        <div className="card border-0 shadow-sm">
          <div className="card-header bg-white py-3">
            <div className="row align-items-center">
              <div className="col-md-4">
                <h5 className="mb-0 fw-bold text-primary">Citizen Records</h5>
              </div>
              <div className="col-md-8 d-flex justify-content-end gap-2">
                <div className="input-group" style={{ maxWidth: "300px" }}>
                  <span className="input-group-text bg-light border-end-0">
                    <i className="bi bi-search text-muted"></i>
                  </span>
                  <input
                    type="text"
                    className="form-control border-start-0 bg-light"
                    placeholder="Search Name"
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                  />
                </div>
                <Link
                  to="/create-citizen"
                  className="btn btn-success text-white fw-semibold"
                >
                  <i className="bi bi-plus-lg me-1"></i> Add New
                </Link>
              </div>
            </div>
          </div>

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
                  {loading ? (
                    <tr>
                      <td colSpan="6" className="text-center py-5">
                        <div className="spinner-border text-primary"></div>
                      </td>
                    </tr>
                  ) : filteredCitizens.length > 0 ? (
                    filteredCitizens.map((citizen, index) => (
                      <tr key={citizen.id}>
                        <td className="ps-4 fw-bold text-muted">
                          {index + 1}
                        </td>
                        <td>
                          <Link
                            to={`/citizens/${citizen.id}`}
                            className="text-decoration-none fw-semibold text-dark"
                          >
                            {citizen.name}
                          </Link>
                        </td>
                        <td>{citizen.mobile_number}</td>
                        <td>
                          {citizen.city_district || (
                            <span className="text-muted">-</span>
                          )}
                        </td>
                        <td className="text-center">
                          <span className="badge bg-secondary rounded-pill px-3">
                            {citizen.vehicles_count}
                          </span>
                        </td>
                        <td className="text-end pe-4">
                          <div className="d-flex justify-content-end gap-2">
                            <Link
                              to={`/citizens/${citizen.id}`}
                              className="btn btn-sm btn-primary text-white"
                              title="View Details"
                            >
                              <i className="bi bi-eye"></i>
                            </Link>

                            <button
                              onClick={() => openEditModal(citizen)}
                              className="btn btn-sm btn-info text-white"
                              title="Edit"
                            >
                              <i className="bi bi-pencil-square"></i>
                            </button>

                            <button
                              onClick={() => handleDelete(citizen.id)}
                              className="btn btn-sm btn-danger"
                              title="Delete"
                            >
                              <i className="bi bi-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td
                        colSpan="6"
                        className="text-center py-5 bg-light text-muted"
                      >
                        No matching records found.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      {showEditModal && (
        <div
          className="modal d-block"
          style={{ backgroundColor: "rgba(0,0,0,0.5)" }}
        >
          <div className="modal-dialog modal-lg modal-dialog-centered">
            <div className="modal-content rounded-1 border-0 shadow-lg">
              <div className="modal-header py-2 border-bottom">
                <h5 className="modal-title fw-bold text-primary">
                  Edit Citizen
                </h5>
                <button
                  className="btn-close"
                  onClick={() => setShowEditModal(false)}
                ></button>
              </div>
              <div className="modal-body p-4">
                <form onSubmit={handleUpdate}>
                  {/* form fields as above (unchanged) */}
                </form>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
