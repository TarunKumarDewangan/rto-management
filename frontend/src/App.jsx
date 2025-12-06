// import { BrowserRouter, Routes, Route } from 'react-router-dom';
// import { Toaster } from 'react-hot-toast';

// import Login from './components/Login';
// import AdminDashboard from './components/AdminDashboard';
// import UserDashboard from './components/UserDashboard';
// import CitizenList from './components/CitizenList';
// import CreateCitizen from './components/CreateCitizen';
// import CitizenDetails from './components/CitizenDetails';
// import AccountStatement from './components/AccountStatement';
// import ExpiryReports from './components/ExpiryReports';
// import BackupPage from './components/BackupPage';

// function App() {
//   return (
//     <BrowserRouter>
//       <Toaster position="bottom-right" reverseOrder={false} />

//       <Routes>
//         <Route path="/" element={<Login />} />
//         <Route path="/admin" element={<AdminDashboard />} />
//         <Route path="/dashboard" element={<UserDashboard />} />
//         <Route path="/citizens" element={<CitizenList />} />
//         <Route path="/create-citizen" element={<CreateCitizen />} />
//         <Route path="/citizens/:id" element={<CitizenDetails />} />
//         <Route path="/citizens/:id/accounts" element={<AccountStatement />} />
//         <Route path="/reports/expiry" element={<ExpiryReports />} />
//         <Route path="/backup" element={<BackupPage />} />
//       </Routes>
//     </BrowserRouter>
//   );
// }

// export default App;


import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';

import Login from './components/Login';
import AdminDashboard from './components/AdminDashboard';
import UserDashboard from './components/UserDashboard';
import CitizenList from './components/CitizenList';
import CreateCitizen from './components/CreateCitizen';
import CitizenDetails from './components/CitizenDetails';
import AccountStatement from './components/AccountStatement';
import ExpiryReports from './components/ExpiryReports';
import BackupPage from './components/BackupPage';

// --- 1. PRIVATE ROUTE ---
// If user is NOT logged in, kick them to Login page
const PrivateRoute = ({ children }) => {
  const token = localStorage.getItem('token');
  return token ? children : <Navigate to="/" replace />;
};

// --- 2. PUBLIC ROUTE ---
// If user IS already logged in, send them to Dashboard (Don't show Login page)
const PublicRoute = ({ children }) => {
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user'));

  if (token) {
    if (user?.role === 'admin') return <Navigate to="/admin" replace />;
    return <Navigate to="/dashboard" replace />;
  }
  return children;
};

function App() {
  return (
    <BrowserRouter>
      <Toaster position="bottom-right" reverseOrder={false} />

      <Routes>
        {/* Public Route: Login */}
        <Route path="/" element={
          <PublicRoute>
            <Login />
          </PublicRoute>
        } />

        {/* Protected Routes */}
        <Route path="/admin" element={<PrivateRoute><AdminDashboard /></PrivateRoute>} />
        <Route path="/dashboard" element={<PrivateRoute><UserDashboard /></PrivateRoute>} />
        <Route path="/citizens" element={<PrivateRoute><CitizenList /></PrivateRoute>} />
        <Route path="/create-citizen" element={<PrivateRoute><CreateCitizen /></PrivateRoute>} />
        <Route path="/citizens/:id" element={<PrivateRoute><CitizenDetails /></PrivateRoute>} />
        <Route path="/citizens/:id/accounts" element={<PrivateRoute><AccountStatement /></PrivateRoute>} />
        <Route path="/reports/expiry" element={<PrivateRoute><ExpiryReports /></PrivateRoute>} />
        <Route path="/backup" element={<PrivateRoute><BackupPage /></PrivateRoute>} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
