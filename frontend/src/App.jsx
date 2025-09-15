import React from 'react';
import { useState, useEffect, useCallback } from 'react';
import { Routes, Route, NavLink, Navigate } from 'react-router-dom';
import DashboardTab from './components/DashboardTab';
import StockManagementTab from './components/StockManagementTab';
import EmployeesTab from './components/EmployeesTab';
import SuppliersTab from './components/SuppliersTab';
import CustomersTab from './components/CustomersTab';
import PaymentsTab from './components/PaymentsTab';
import ProduitsTab from './components/ProduitsTab';
import ContratsTab from './components/ContratsTab';
import EntrepotsTab from './components/EntrepotsTab';
import RecettesTab from './components/RecettesTab';
import DepensesTab from './components/DepensesTab';
import AchatsTab from './components/AchatsTab';
import VentesTab from './components/VentesTab';
import FacturesTab from './components/FacturesTab';
import QuittancesTab from './components/QuittancesTab';
import { MoonIcon, SunIcon } from './components/Icon';
import { useToast } from './components/ToastProvider';
import './App.css'; // Import global styles
//import './tailwind.css'; // Import Tailwind CSS styles

export default function App() {
  const [darkMode, setDarkMode] = useState(() => {
    // 1. Vérifier le thème sauvegardé dans le localStorage
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
      return savedTheme === 'dark';
    }
    // 2. Sinon, utiliser la préférence système
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
  });
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(false);
  const [authError, setAuthError] = useState('');
  const [loadingData, setLoadingData] = useState(true); // Nouvel état pour le chargement des données
  const { addToast } = useToast();

  // URL de l'API backend. Utilise une variable d'environnement pour la production.
  // S'assure que l'URL ne se termine pas par un slash pour éviter les doubles slashes.
  const API_URL = process.env.REACT_APP_API_URL ? process.env.REACT_APP_API_URL.replace(/\/+$/, '') : '';

  console.log('API_URL:', API_URL); // Log de débogage

  // Vérification de l'authentification persistante
  useEffect(() => {
    const controller = new AbortController();
    const signal = controller.signal;

    async function checkAuth() {
      setLoading(true); // Activez le loading au début de la vérification

      // récupération du token d'authentification
      const token = localStorage.getItem('authToken');
      console.log(token); // Log de débogage 

      if (!token) {
        // Si pas de token, l'utilisateur n'est pas authentifié
        setUser(null);
        setLoading(false); // Désactivez le loading
        return;
      }

      try {
        const res = await fetch(`${API_URL}/auth`, {
          method: 'GET', // Utiliser GET pour la vérification de session
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`,
          },
          signal: signal,
        });

        if (res.ok) {
          const data = await res.json();
          setUser(data.user || null);
          if (data.user && data.user.role) {
            localStorage.setItem('userRole', data.user.role);
          }
          addToast('Session restaurée!', 'info');
        } else {
          console.log(
            'Token invalide ou session expirée, veuillez vous reconnecter.'
          );
          addToast(
            'Votre session a expiré. Veuillez vous reconnecter.',
            'warning'
          );
          localStorage.removeItem('authToken');
          localStorage.removeItem('userRole');
          setUser(null);
        }
      } catch (err) {
        if (err.name === 'AbortError') {
          console.log('Fetch aborted');
        } else {
          console.error(
            "Erreur lors de la vérification de l'authentification :",
            err
          );
          localStorage.removeItem('authToken');
          localStorage.removeItem('userRole');
          setUser(null);
          addToast(
            'Erreur de connexion au serveur. Veuillez réessayer.',
            'danger'
          );
        }
      } finally {
        setLoading(false);
      }
    }
    checkAuth();
    return () => {
      controller.abort();
    };
  }, [addToast, API_URL]);

  // Données locales avant connexion au backend
  const [stockItems, setStockItems] = useState([]);
  const [employees, setEmployees] = useState([]);
  const [suppliers, setSuppliers] = useState([]);
  const [customers, setCustomers] = useState([]);
  const [payments, setPayments] = useState([]);
  const [produits, setProduits] = useState([]);
  const [contrats, setContrats] = useState([]);
  const [entrepots, setEntrepots] = useState([]);
  const [recettes, setRecettes] = useState([]);
  const [depenses, setDepenses] = useState([]);
  const [achats, setAchats] = useState([]);
  const [factures, setFactures] = useState([]);
  const [quittances, setQuittances] = useState([]);

  // Gestion du mode sombre et persistance
  useEffect(() => {
    if (darkMode) {
      document.documentElement.classList.add('dark');
      localStorage.setItem('theme', 'dark'); // Sauvegarder le choix
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('theme', 'light'); // Sauvegarder le choix
    }
  }, [darkMode]);

  // Chargement initial optimisé (parallélisé)
  const fetchAll = useCallback(async () => {
    const token = localStorage.getItem('authToken');
    if (!token) {
      // Cas de déconnexion géré par le useEffect suivant
      return;
    }
    
    setLoadingData(true);

    try {
      const routes = [
        'stock',
        'employees',
        'suppliers',
        'customers',
        'produits',
        'contrats',
        'entrepots',
        'recettes',
        'depenses',
        'achats',
        'factures',
        'quittances',
        'payments',
      ];

      const responses = await Promise.all(
        routes.map(async (route) => {
          console.log(`Fetching data for: ${route}`);
          const res = await fetch(`${API_URL}/${route}`, {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
              Authorization: `Bearer ${token}`,
            },
          });
          if (!res.ok) {
            console.error(`Failed to fetch ${route}: ${res.status}`);
            throw new Error(`Erreur réseau pour ${route}: ${res.status}`);
          }
          const jsonResponse = await res.json();
          // S'assurer que la propriété 'data' existe et est un tableau
          return Array.isArray(jsonResponse.data) ? jsonResponse.data : [];
        })
      );

      const [
        stock,
        employees,
        suppliers,
        customers,
        produits,
        contrats,
        entrepots,
        recettes,
        depenses,
        achats,
        factures,
        quittances,
        payments,
      ] = responses;

      setStockItems(stock);
      setEmployees(employees);
      setSuppliers(suppliers);
      setCustomers(customers);
      setPayments(payments);
      setProduits(produits);
      setContrats(contrats);
      setEntrepots(entrepots);
      setRecettes(recettes);
      setDepenses(depenses);
      setAchats(achats);
      setFactures(factures);
      setQuittances(quittances);
    } catch (err) {
      console.error('Erreur détaillée lors du chargement des données :', err.message, err.stack);
      addToast(
        'Erreur lors du chargement des données : ' + err.message,
        'danger'
      );
    } finally {
      setLoadingData(false);
    }
  }, [API_URL, addToast]);

  // Déclenchement du chargement initial optimisé lorsque l'utilisateur est authentifié
  useEffect(() => {
    if (user) {
      // Si l'utilisateur est connecté, on charge les données
      setLoadingData(true);
      fetchAll();
    } else {
      // Si l'utilisateur est déconnecté (au démarrage ou après logout), on réinitialise tout
      setStockItems([]);
      setEmployees([]);
      setSuppliers([]);
      setCustomers([]);
      setPayments([]);
      setProduits([]);
      setContrats([]);
      setEntrepots([]);
      setRecettes([]);
      setDepenses([]);
      setAchats([]);
      setFactures([]);
      setQuittances([]);
      // On s'assure que le chargement est désactivé si l'utilisateur n'est pas connecté
      setLoadingData(false);
    }
  }, [user, fetchAll]);

  // Authentification serveur
  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setAuthError('');
    const form = e.target;
    const email = form.elements.email.value;
    const password = form.elements.password.value;
    try {
      const res = await fetch(`${API_URL}/auth`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      if (!res.ok) {
        const errorMsg = 'Identifiants invalides';
        setAuthError(errorMsg);
        addToast(errorMsg, 'danger');
        setLoading(false);
        return;
      }
      const data = await res.json();
      const token = data.jwt;
      localStorage.setItem('authToken', token); // sauvegarde du token dans localStorage
      if (data.user && data.user.role) {
        localStorage.setItem('userRole', data.user.role); // Sauvegarde du rôle
      }

      // Mettre à jour l'état de l'utilisateur
      setUser(data.user || { name: email });
      setLoading(false);
      addToast('Connexion réussie!', 'success');
    } catch (err) {
      const errorMsg = 'Erreur serveur';
      setAuthError(errorMsg);
      addToast(errorMsg, 'error');
      setLoading(false);
    }
  };

  // Déconnexion
  const handleLogout = async () => {
    localStorage.removeItem('authToken');
    localStorage.removeItem('userRole'); // Nettoyer le rôle
    setUser(null);
  };

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-green-50 dark:bg-gray-900">
        <form
          onSubmit={handleLogin}
          className="bg-white dark:bg-gray-800 p-6 rounded shadow space-y-4 w-full max-w-md"
        >
          <h1 className="text-xl font-bold text-emerald-700">
            AKARA
          </h1>
          <input
            name="email"
            type="text"
            placeholder="Email"
            required
            className="w-full p-2 border rounded"
            autoComplete="username"
          />
          <input
            name="password"
            type="password"
            placeholder="Mot de passe"
            required
            className="w-full p-2 border rounded"
            autoComplete="current-password"
          />
          {authError && <div className="text-red-600 text-sm">{authError}</div>}
          <button
            type="submit"
            className="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded"
            disabled={loading}
          >
            {loading ? 'Connexion...' : 'Se connecter'}
          </button>
        </form>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 dark:from-gray-900 dark:to-gray-800 text-gray-800 dark:text-gray-100 transition-colors duration-300">
      {/* Header */}
      <header className="bg-white dark:bg-gray-800 shadow-md p-4 flex justify-between items-center sticky top-0 z-10">
          <h1 className="text-xl md:text-2xl font-bold text-emerald-700 dark:text-emerald-400">
            AKARA
          </h1>
          <div className="flex gap-2">
            <button
              onClick={() => setDarkMode(!darkMode)}
              className="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition"
            >
              {darkMode ? <SunIcon /> : <MoonIcon />}
            </button>
            <button
              onClick={handleLogout}
              className="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded"
            >
              Déconnexion
            </button>
          </div>
        </header>

        {/* Sidebar */}
        <div className="flex flex-col md:flex-row">
          <nav className="md:w-64 bg-white dark:bg-gray-800 shadow-md p-4 space-y-2">
            {[
              { tab: 'dashboard', label: 'Tableau de bord' },
              { tab: 'stock', label: 'Gestion des stocks' },
              { tab: 'employees', label: 'Employés' },
              { tab: 'suppliers', label: 'Fournisseurs' },
              { tab: 'customers', label: 'Clients' },
              { tab: 'payments', label: 'Paiements' },
              { tab: 'produits', label: 'Produits' },
              { tab: 'contrats', label: 'Contrats' },
              { tab: 'entrepots', label: 'Entrepôts' },
              { tab: 'recettes', label: 'Recettes' },
              { tab: 'depenses', label: 'Dépenses' },
              { tab: 'achats', label: 'Achats' },
              { tab: 'ventes', label: 'Ventes' },
              { tab: 'factures', label: 'Factures' },
              { tab: 'quittances', label: 'Quittances' },
            ].map(({ tab, label }) => (
              <NavLink
                key={tab}
                to={`/${tab}`}
                className={({ isActive }) =>
                  `w-full text-left px-4 py-2 rounded-lg block transition-colors duration-200 ${
                    isActive
                      ? 'bg-emerald-600 text-white font-semibold'
                      : 'hover:bg-gray-200 dark:hover:bg-gray-700'
                  }`
                }
              >
                {label}
              </NavLink>
            ))}
          </nav>

          {/* Main Content */}
          <main className="flex-1 p-4 md:p-6 overflow-auto">
            {loadingData ? (
              <div className="text-center p-8">
                <p className="text-xl font-semibold">Chargement des données...</p>
              </div>
            ) : (
              <Routes>
                <Route path="/dashboard" element={<DashboardTab stock={stockItems} recettes={recettes} depenses={depenses} />} />
                <Route path="/stock" element={<StockManagementTab api={API_URL} />} />
                <Route path="/employees" element={<EmployeesTab api={API_URL} />} />
                <Route path="/suppliers" element={<SuppliersTab suppliers={suppliers} setSuppliers={setSuppliers} api={API_URL} />} />
                <Route path="/customers" element={<CustomersTab api={API_URL} />} />
                <Route path="/payments" element={<PaymentsTab payments={payments} setPayments={setPayments} customers={customers} employees={employees} api={API_URL} refetchPayments={fetchAll} />} />
                <Route path="/produits" element={<ProduitsTab produits={produits} setProduits={setProduits} api={API_URL} refetchProduits={fetchAll} />} />
                <Route path="/contrats" element={<ContratsTab contrats={contrats} setContrats={setContrats} api={API_URL} />} />
                <Route path="/entrepots" element={<EntrepotsTab entrepots={entrepots} setEntrepots={setEntrepots} api={API_URL} />} />
                <Route path="/recettes" element={<RecettesTab recettes={recettes} setRecettes={setRecettes} produits={produits} setProduits={setProduits} customers={customers} setCustomers={setCustomers} contrats={contrats} setContrats={setContrats} api={API_URL} refetchRecettes={fetchAll} />} />
                <Route path="/depenses" element={<DepensesTab depenses={depenses} setDepenses={setDepenses} api={API_URL} />} />
                <Route path="/achats" element={<AchatsTab achats={achats} setAchats={setAchats} api={API_URL} />} />
                <Route path="/ventes" element={<VentesTab api={API_URL} />} />
                <Route path="/factures" element={<FacturesTab factures={factures} setFactures={setFactures} api={API_URL} />} />
                <Route path="/quittances" element={<QuittancesTab quittances={quittances} setQuittances={setQuittances} api={API_URL} />} />
                <Route path="*" element={<Navigate to="/dashboard" replace />} />
              </Routes>
            )}
          </main>
        </div>
      </div>
  );
}
