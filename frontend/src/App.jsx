import { useState, useEffect, useRef } from 'react';

export default function App() {
  const [darkMode, setDarkMode] = useState(false);
  const [activeTab, setActiveTab] = useState('dashboard');
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(false);
  const [authError, setAuthError] = useState('');
  const API_URL = 'http://localhost:8000';

  // Données locales avant connexion au backend
  const [stockItems, setStockItems] = useState([]);
  const [employees, setEmployees] = useState([]);
  const [suppliers, setSuppliers] = useState([]);
  const [customers, setCustomers] = useState([]);
  const [payments, setPayments] = useState([]);

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
          'payments',
        ];
        const [stock, employees, suppliers, customers, payments] =
          await Promise.all(
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
      } catch (err) {
        console.error('Erreur lors du chargement des données :', err);
      }
    }
    if (user) fetchAll();
  }, [user]);

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
        setAuthError('Identifiants invalides');
        setLoading(false);
        return;
      }
      const data = await res.json();
      setUser(data.user || { name: email });
      setLoading(false);
    } catch (err) {
      setAuthError('Erreur serveur');
      setLoading(false);
    }
  };

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
        </main>
      </div>
    </div>
  );
}

// Dashboard Component
function DashboardTab({ stock, payments }) {
  const lowStock = stock.filter((item) => item.quantity <= item.min);
  const totalRevenue = payments
    .filter((p) => p.type === 'income')
    .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
  const totalExpenses = payments
    .filter((p) => p.type === 'expense')
    .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
  const profit = totalRevenue - totalExpenses;

  return (
    <div className="space-y-6">
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400">
        Tableau de bord
      </h2>
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <Card
          title="Recettes totales"
          value={`${totalRevenue.toLocaleString()} FCFA`}
          color="green"
        />
        <Card
          title="Dépenses"
          value={`${totalExpenses.toLocaleString()} FCFA`}
          color="red"
        />
        <Card
          title="Bénéfice net"
          value={`${profit.toLocaleString()} FCFA`}
          color="emerald"
        />
      </div>

      <div className="bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-500 p-4 rounded shadow-inner">
        <h3 className="font-medium text-yellow-800 dark:text-yellow-200">
          Alertes Stocks Faibles
        </h3>
        <ul className="mt-2 space-y-1">
          {lowStock.length > 0 ? (
            lowStock.map((alert) => (
              <li
                key={alert.id}
                className="text-sm text-yellow-700 dark:text-yellow-300"
              >
                {alert.name}: seulement {alert.quantity} unités restantes
              </li>
            ))
          ) : (
            <li>Aucun stock faible.</li>
          )}
        </ul>
      </div>

      <ChartSection payments={payments} />
    </div>
  );
}

// Stock Management Component avec API
function StockManagementTab({ items, setItems, api }) {
  const [search, setSearch] = useState('');
  const [newItem, setNewItem] = useState({
    name: '',
    quantity: '',
    unit: '',
    min: '',
  });

  const filteredItems = items.filter((item) =>
    item.name?.toLowerCase().includes(search.toLowerCase())
  );

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!newItem.name || !newItem.quantity || !newItem.unit || !newItem.min)
      return;

    try {
      const res = await fetch(api, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newItem),
      });

      if (!res.ok) throw new Error('Erreur lors de l’ajout');

      const data = await res.json();
      setItems([...items, { ...newItem, id: data.id }]);
      setNewItem({ name: '', quantity: '', unit: '', min: '' });
    } catch (err) {
      alert('Impossible d’ajouter cet article.');
      console.error(err);
    }
  };

  const handleUpdate = async (id, field, value) => {
    const updated = items.map((item) =>
      item.id === id ? { ...item, [field]: value } : item
    );
    setItems(updated);

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((item) => item.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer cet élément ?')) return;

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });

      setItems(items.filter((item) => item.id !== id));
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Gestion des Stocks
      </h2>
      <div className="mb-4">
        <input
          type="text"
          placeholder="Rechercher..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-full p-2 border rounded"
        />
      </div>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">ID</th>
              <th className="px-4 py-2 text-left">Nom</th>
              <th className="px-4 py-2 text-left">Quantité</th>
              <th className="px-4 py-2 text-left">Unité</th>
              <th className="px-4 py-2 text-left">Seuil minimum</th>
              <th className="px-4 py-2 text-left">Statut</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {filteredItems.map((item) => (
              <tr
                key={item.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">{item.id}</td>
                <td className="px-4 py-2">
                  <input
                    value={item.name}
                    onChange={(e) =>
                      handleUpdate(item.id, 'name', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="number"
                    value={item.quantity}
                    onChange={(e) =>
                      handleUpdate(
                        item.id,
                        'quantity',
                        parseInt(e.target.value)
                      )
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={item.unit}
                    onChange={(e) =>
                      handleUpdate(item.id, 'unit', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="number"
                    value={item.min}
                    onChange={(e) =>
                      handleUpdate(item.id, 'min', parseInt(e.target.value))
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  {item.quantity <= item.min ? (
                    <span className="text-red-600 font-medium">En rupture</span>
                  ) : (
                    <span className="text-green-600 font-medium">OK</span>
                  )}
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(item.id)}
                    className="text-red-600 hover:text-red-800"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="mt-4">
        <h3 className="font-medium mb-2">Ajouter un article</h3>
        <form onSubmit={handleSubmit} className="flex gap-2 flex-wrap">
          <input
            placeholder="Nom"
            value={newItem.name}
            onChange={(e) => setNewItem({ ...newItem, name: e.target.value })}
            className="p-2 border rounded"
          />
          <input
            placeholder="Qté"
            type="number"
            value={newItem.quantity}
            onChange={(e) =>
              setNewItem({ ...newItem, quantity: e.target.value })
            }
            className="p-2 border rounded"
          />
          <input
            placeholder="Unité"
            value={newItem.unit}
            onChange={(e) => setNewItem({ ...newItem, unit: e.target.value })}
            className="p-2 border rounded"
          />
          <input
            placeholder="Min"
            type="number"
            value={newItem.min}
            onChange={(e) => setNewItem({ ...newItem, min: e.target.value })}
            className="p-2 border rounded"
          />
          <button
            type="submit"
            className="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded"
          >
            Ajouter
          </button>
        </form>
      </div>
    </div>
  );
}

// Employees Tab Component
function EmployeesTab({ employees, setEmployees, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = employees.map((e) =>
      e.id === id ? { ...e, [field]: value } : e
    );
    setEmployees(updated);

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((e) => e.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer cet employé ?')) return;

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });
      setEmployees(employees.filter((e) => e.id !== id));
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Employés
      </h2>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Nom</th>
              <th className="px-4 py-2 text-left">Rôle</th>
              <th className="px-4 py-2 text-left">Salaire</th>
              <th className="px-4 py-2 text-left">Dernier paiement</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {employees.map((emp) => (
              <tr
                key={emp.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">
                  <input
                    value={emp.name}
                    onChange={(e) =>
                      handleUpdate(emp.id, 'name', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={emp.role}
                    onChange={(e) =>
                      handleUpdate(emp.id, 'role', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={emp.salary}
                    onChange={(e) =>
                      handleUpdate(emp.id, 'salary', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="date"
                    value={emp.last_payment}
                    onChange={(e) =>
                      handleUpdate(emp.id, 'last_payment', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(emp.id)}
                    className="text-red-600 hover:text-red-800"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

// Suppliers Tab Component
function SuppliersTab({ suppliers, setSuppliers, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = suppliers.map((s) =>
      s.id === id ? { ...s, [field]: value } : s
    );
    setSuppliers(updated);

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((s) => s.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer ce fournisseur ?'))
      return;

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });
      setSuppliers(suppliers.filter((s) => s.id !== id));
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Fournisseurs
      </h2>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Nom</th>
              <th className="px-4 py-2 text-left">Contact</th>
              <th className="px-4 py-2 text-left">Téléphone</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {suppliers.map((supplier) => (
              <tr
                key={supplier.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">
                  <input
                    value={supplier.name}
                    onChange={(e) =>
                      handleUpdate(supplier.id, 'name', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={supplier.contact}
                    onChange={(e) =>
                      handleUpdate(supplier.id, 'contact', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={supplier.phone}
                    onChange={(e) =>
                      handleUpdate(supplier.id, 'phone', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(supplier.id)}
                    className="text-red-600 hover:text-red-800"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

// Customers Tab Component
function CustomersTab({ customers, setCustomers, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = customers.map((c) =>
      c.id === id ? { ...c, [field]: value } : c
    );
    setCustomers(updated);

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((c) => c.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer ce client ?')) return;

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });
      setCustomers(customers.filter((c) => c.id !== id));
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Clients
      </h2>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Nom</th>
              <th className="px-4 py-2 text-left">Contact</th>
              <th className="px-4 py-2 text-left">Téléphone</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {customers.map((customer) => (
              <tr
                key={customer.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">
                  <input
                    value={customer.name}
                    onChange={(e) =>
                      handleUpdate(customer.id, 'name', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={customer.contact}
                    onChange={(e) =>
                      handleUpdate(customer.id, 'contact', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={customer.phone}
                    onChange={(e) =>
                      handleUpdate(customer.id, 'phone', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(customer.id)}
                    className="text-red-600 hover:text-red-800"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

// Payments Tab Component
function PaymentsTab({ payments, setPayments, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = payments.map((p) =>
      p.id === id ? { ...p, [field]: value } : p
    );
    setPayments(updated);

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((p) => p.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer ce paiement ?')) return;

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });
      setPayments(payments.filter((p) => p.id !== id));
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Paiements
      </h2>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Date</th>
              <th className="px-4 py-2 text-left">Description</th>
              <th className="px-4 py-2 text-left">Montant</th>
              <th className="px-4 py-2 text-left">Type</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {payments.map((tx) => (
              <tr
                key={tx.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">
                  <input
                    type="date"
                    value={tx.date}
                    onChange={(e) =>
                      handleUpdate(tx.id, 'date', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={tx.description}
                    onChange={(e) =>
                      handleUpdate(tx.id, 'description', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={tx.amount}
                    onChange={(e) =>
                      handleUpdate(tx.id, 'amount', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <select
                    value={tx.type}
                    onChange={(e) =>
                      handleUpdate(tx.id, 'type', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  >
                    <option value="income">Entrée</option>
                    <option value="expense">Sortie</option>
                  </select>
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(tx.id)}
                    className="text-red-600 hover:text-red-800"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

// Chart Section Component (sécurisation du chargement Chart.js)
function ChartSection({ payments }) {
  const chartRef = useRef(null);
  const chartInstance = useRef(null);

  useEffect(() => {
    let isMounted = true;
    async function loadChart() {
      if (!window.Chart) {
        if (!document.getElementById('chartjs-script')) {
          await new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.id = 'chartjs-script';
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.async = true;
            script.onload = resolve;
            script.onerror = reject;
            document.body.appendChild(script);
          });
        } else {
          await new Promise((resolve) => {
            const interval = setInterval(() => {
              if (window.Chart) {
                clearInterval(interval);
                resolve();
              }
            }, 50);
          });
        }
      }

      if (isMounted && window.Chart && chartRef.current) {
        if (chartInstance.current) {
          chartInstance.current.destroy();
        }

        const ctx = chartRef.current.getContext('2d');
        chartInstance.current = new window.Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Recettes', 'Dépenses'],
            datasets: [
              {
                label: 'Montants (FCFA)',
                data: [
                  payments
                    .filter((p) => p.type === 'income')
                    .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0),
                  payments
                    .filter((p) => p.type === 'expense')
                    .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0),
                ],
                backgroundColor: ['#10b981', '#ef4444'],
              },
            ],
          },
          options: { responsive: true },
        });
      }
    }
    loadChart();
    return () => {
      isMounted = false;
      if (chartInstance.current) {
        chartInstance.current.destroy();
        chartInstance.current = null;
      }
    };
  }, [payments]);

  return (
    <div className="bg-white dark:bg-gray-800 p-4 rounded shadow">
      <canvas ref={chartRef} width="400" height="200"></canvas>
    </div>
  );
}

// Card Component
const bgColors = {
  green: 'bg-green-100 dark:bg-green-900',
  red: 'bg-red-100 dark:bg-red-900',
  emerald: 'bg-emerald-100 dark:bg-emerald-900',
  gray: 'bg-gray-100 dark:bg-gray-900',
};

function Card({ title, value, color = 'gray' }) {
  return (
    <div className={`${bgColors[color] || bgColors.gray} p-4 rounded shadow`}>
      <h3 className="text-sm text-gray-600 dark:text-gray-400">{title}</h3>
      <p className="text-2xl font-bold mt-1 text-gray-800 dark:text-white">
        {value}
      </p>
    </div>
  );
}

// Icon Components
function MoonIcon() {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      className="w-6 h-6"
    >
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth={2}
        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
      />
    </svg>
  );
}

function SunIcon() {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      className="w-6 h-6"
    >
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth={2}
        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
      />
    </svg>
  );
}
