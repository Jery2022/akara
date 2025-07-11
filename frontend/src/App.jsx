import React from 'react';
//import { v4 as uuidv4 } from 'uuid';
import { useState, useEffect } from 'react';
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
import ToastContainer from './components/ToastContainer';
import { ToastProvider, useToast } from './components/ToastProvider';
import './App.css'; // Import global styles
//import './tailwind.css'; // Import Tailwind CSS styles

export default function App() {
  const [darkMode, setDarkMode] = useState(false);
  const [activeTab, setActiveTab] = useState('dashboard');
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(false);
  const [authError, setAuthError] = useState('');
  const { addToast, removeToast } = useToast();

  useEffect(() => {
    // Vérification du mode sombre dans les préférences utilisateur
    const prefersDark = window.matchMedia(
      '(prefers-color-scheme: dark)'
    ).matches;
    setDarkMode(prefersDark);
  }, []);

  // Vérification de l'authentification persistante
  useEffect(() => {
    async function checkAuth() {
      try {
        const res = await fetch(`${API_URL}/index.php?route=checkAuth`, {
          credentials: 'include',
        });
        if (res.ok) {
          const data = await res.json();
          setUser(data.user || null);
        } else {
          setUser(null);
        }
      } catch (err) {
        console.error(
          "Erreur lors de la vérification de l'authentification :",
          err
        );
      }
    }
    checkAuth();
  }, []);

  // URL de l'API backend
  const API_URL = 'https://akara-prod-green-hill-9016.fly.dev/'; // Remplacez par l'URL de votre API

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
  const [ventes, setVentes] = useState([]);
  const [factures, setFactures] = useState([]);
  const [quittances, setQuittances] = useState([]);

  // Gestion du mode sombre
  useEffect(() => {
    if (darkMode) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }, [darkMode]);

  // Chargement initial optimisé (parallélisé)
  useEffect(() => {
    async function fetchAll() {
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
          'ventes',
          'factures',
          'quittances',
          'payments',
        ];
        const [
          stock,
          employees,
          suppliers,
          customers,
          payments,
          produits,
          contrats,
          entrepots,
          recettes,
          depenses,
          achats,
          ventes,
          factures,
          quittances,
        ] = await Promise.all(
          routes.map(async (route) => {
            const res = await fetch(`${API_URL}/index.php?route=${route}`, {
              credentials: 'include',
            });
            if (!res.ok) throw new Error(`Erreur réseau : ${res.status}`);
            return await res.json();
          })
        );
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
        setVentes(ventes);
        setFactures(factures);
        setQuittances(quittances);
      } catch (err) {
        console.error('Erreur lors du chargement des données :', err);
        addToast(
          'Erreur lors du chargement des données : ' + err.message,
          'danger'
        );
      }
    }
    if (user) fetchAll();
  }, [user, API_URL, addToast]);

  // Authentification serveur
  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setAuthError('');
    const form = e.target;
    const email = form.elements.email.value;
    const password = form.elements.password.value;

    try {
      const res = await fetch(`${API_URL}/index.php?route=login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
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
      setUser(data.user || { name: email });
      setLoading(false);
    } catch (err) {
      const errorMsg = 'Erreur serveur';
      setAuthError(errorMsg);
      addToast(errorMsg, 'error'); // <-- Ajout du toast ici aussi
      setLoading(false);
    }
  };

  // Déconnexion
  const handleLogout = async () => {
    try {
      await fetch(`${API_URL}/index.php?route=logout`, {
        method: 'POST',
        credentials: 'include',
      });
    } catch (err) {
      // ignore erreur logout
    }
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
            Les Compagnons du BTP
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
    <ToastProvider>
      <div className="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 dark:from-gray-900 dark:to-gray-800 text-gray-800 dark:text-gray-100 transition-colors duration-300">
        {/* Header */}
        <header className="bg-white dark:bg-gray-800 shadow-md p-4 flex justify-between items-center sticky top-0 z-10">
          <h1 className="text-xl md:text-2xl font-bold text-emerald-700 dark:text-emerald-400">
            Les Compagnons du BTP
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
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`w-full text-left px-4 py-2 rounded-lg ${
                  activeTab === tab
                    ? 'bg-emerald-600 text-white'
                    : 'hover:bg-gray-200 dark:hover:bg-gray-700'
                }`}
              >
                {label}
              </button>
            ))}
          </nav>

          {/* Main Content */}
          <main className="flex-1 p-4 md:p-6 overflow-auto">
            {activeTab === 'dashboard' && (
              <DashboardTab stock={stockItems} payments={payments} />
            )}
            {activeTab === 'stock' && (
              <StockManagementTab
                items={stockItems}
                setItems={setStockItems}
                api={`${API_URL}/index.php?route=stock`}
              />
            )}
            {activeTab === 'employees' && (
              <EmployeesTab
                employees={employees}
                setEmployees={setEmployees}
                api={`${API_URL}/index.php?route=employees`}
              />
            )}
            {activeTab === 'suppliers' && (
              <SuppliersTab
                suppliers={suppliers}
                setSuppliers={setSuppliers}
                api={`${API_URL}/index.php?route=suppliers`}
              />
            )}
            {activeTab === 'customers' && (
              <CustomersTab
                customers={customers}
                setCustomers={setCustomers}
                api={`${API_URL}/index.php?route=customers`}
              />
            )}
            {activeTab === 'payments' && (
              <PaymentsTab
                payments={payments}
                setPayments={setPayments}
                api={`${API_URL}/index.php?route=payments`}
              />
            )}
            {activeTab === 'produits' && (
              <ProduitsTab
                products={produits}
                setProduits={setProduits}
                api={`${API_URL}/index.php?route=produits`}
              />
            )}
            {activeTab === 'contrats' && (
              <ContratsTab
                contracts={contrats}
                setContrats={setContrats}
                api={`${API_URL}/index.php?route=contrats`}
              />
            )}
            {activeTab === 'entrepots' && (
              <EntrepotsTab
                entrepots={entrepots}
                setEntrepots={setEntrepots}
                api={`${API_URL}/index.php?route=entrepots`}
              />
            )}
            {activeTab === 'recettes' && (
              <RecettesTab
                recettes={recettes}
                setRecettes={setRecettes}
                api={`${API_URL}/index.php?route=recettes`}
              />
            )}
            {activeTab === 'depenses' && (
              <DepensesTab
                depenses={depenses}
                setDepenses={setDepenses}
                api={`${API_URL}/index.php?route=depenses`}
              />
            )}
            {activeTab === 'achats' && (
              <AchatsTab
                achats={achats}
                setAchats={setAchats}
                api={`${API_URL}/index.php?route=achats`}
              />
            )}
            {activeTab === 'ventes' && (
              <VentesTab
                ventes={ventes}
                setVentes={setVentes}
                api={`${API_URL}/index.php?route=ventes`}
              />
            )}
            {activeTab === 'factures' && (
              <FacturesTab
                factures={factures}
                setFactures={setFactures}
                api={`${API_URL}/index.php?route=factures`}
              />
            )}
            {activeTab === 'quittances' && (
              <QuittancesTab
                quittances={quittances}
                setQuittances={setQuittances}
                api={`${API_URL}/index.php?route=quittances`}
              />
            )}
          </main>
        </div>
      </div>
      <ToastContainer toasts={addToast} removeToast={removeToast} />
      {/* <ToastContainer toasts={toasts} removeToast={removeToast} />  afficher un message d'erreur si nécessaire */}
    </ToastProvider>
  );
}
