import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreateEmployeeModal from './utils/CreateEmployeeModal';
import EditEmployeeModal from './utils/EditEmployeeModal';
import { PlusCircle, Pencil, Trash2, RefreshCw } from 'lucide-react';

// Composant de l'onglet Employés
function EmployeesTab({ api }) {
  const token = localStorage.getItem('authToken');
  const userRole = localStorage.getItem('userRole'); // Supposons que le rôle est stocké ici
  const canManageEmployees = userRole === 'admin' || userRole === 'rh'; // Seuls les admins et RH peuvent gérer les employés

  const [employees, setEmployees] = useState([]);

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentEmployee, setCurrentEmployee] = useState(null);
  const [createError, setCreateError] = useState(null);
  const [editError, setEditError] = useState(null);

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);

  // État pour la recherche
  const [search, setSearch] = useState('');
  const [sortCriteria, setSortCriteria] = useState('name_asc');
  const [statusFilter, setStatusFilter] = useState('active'); // 'active', 'inactive', 'all'

  // Fonction pour charger les employés (Read)
  const fetchEmployees = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${api}/employees`, { // Simplifié : plus de query params
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur HTTP: ${response.status} - ${errorBody.message || response.statusText}`);
      }
      const result = await response.json();
      if (result && Array.isArray(result.data)) {
        setEmployees(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau d\'employés :', result);
        setEmployees([]);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des employés :', err);
      setError(`Impossible de charger les employés. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token]);

  // Charger les employés au montage du composant
  useEffect(() => {
    fetchEmployees();
  }, [fetchEmployees]);

  // Filtrer et trier les employés côté client
  const sortedAndFilteredEmployees = (Array.isArray(employees) ? employees : [])
    .filter((employee) => {
      // 1. Filtrage par statut
      const statusMatch =
        statusFilter === 'all' ||
        (statusFilter === 'active' && employee.is_active) ||
        (statusFilter === 'inactive' && !employee.is_active);

      if (!statusMatch) return false;

      // 2. Filtrage par recherche
      const searchLower = search.toLowerCase();
      return (
        employee.name?.toLowerCase().includes(searchLower) ||
        employee.fonction?.toLowerCase().includes(searchLower) ||
        employee.category?.toLowerCase().includes(searchLower) ||
        employee.email?.toLowerCase().includes(searchLower)
      );
    })
    .sort((a, b) => {
      const parts = sortCriteria.split('_');
      const order = parts.pop();
      const key = parts.join('_');
      
      const valA = a[key];
      const valB = b[key];

      if (valA == null || valB == null) return 0;

      let comparison = 0;
      if (typeof valA === 'string' && typeof valB === 'string') {
        comparison = valA.localeCompare(valB);
      } else if (typeof valA === 'number' && typeof valB === 'number') {
        comparison = valA - valB;
      }

      return order === 'asc' ? comparison : -comparison;
    });

  // --- Fonctions pour les modales ---
  const openCreateModal = () => {
    setCreateError(null);
    setIsCreateModalOpen(true);
  };
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (employee) => {
    setEditError(null);
    setCurrentEmployee(employee);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentEmployee(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'un nouvel employé
  const handleCreateEmployee = async (newEmployeeData) => {
    setSaving(true);
    setCreateError(null);
    try {
      const response = await fetch(`${api}/employees`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newEmployeeData),
      });

      const result = await response.json();

      if (!response.ok) {
        setCreateError(result.message || `Erreur HTTP ${response.status}`);
      } else {
        await fetchEmployees();
        closeCreateModal();
        setMessage({ type: 'success', text: 'Employé ajouté avec succès !' });
      }
    } catch (err) {
      setCreateError(`Erreur lors de l'ajout de l'employé: ${err.message || err.toString()}`);
      console.error(err);
    } finally {
      setSaving(false);
    }
  };
  
  // Gère la mise à jour d'un employé
  const handleUpdateEmployee = async (updatedEmployeeData) => {
    setSaving(true);
    setEditError(null);
    try {
      const response = await fetch(`${api}/employees/${updatedEmployeeData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(updatedEmployeeData),
      });

      const result = await response.json();

      if (!response.ok) {
        setEditError(result.message || `Erreur HTTP ${response.status}`);
      } else {
        await fetchEmployees();
        closeEditModal();
        setMessage({ type: 'success', text: 'Employé mis à jour avec succès !' });
      }
    } catch (err) {
      setEditError(`Erreur lors de la mise à jour de l'employé: ${err.message || err.toString()}`);
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la suppression d'un employé
  const handleDelete = (employeeId) => {
    setConfirmAction(() => async () => {
      setSaving(true);
      try {
        const response = await fetch(`${api}/${employeeId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }
        
        await fetchEmployees();
        setMessage({ type: 'success', text: 'Employé supprimé avec succès !' });
      } catch (err) {
        setMessage({ type: 'error', text: `Erreur lors de la suppression: ${err.message || err.toString()}` });
        console.error(err);
      } finally {
        setShowConfirmModal(false);
        setConfirmAction(null);
        setSaving(false);
      }
    });
    setShowConfirmModal(true);
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer cet employé ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600 dark:text-gray-400">
          Chargement des **employés**...
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center text-red-600 dark:text-red-400">{error}</div>
    );
  }
  
  return (
    <div className="min-h-screen bg-gray-100 dark:bg-gray-900 p-4 font-sans antialiased">
      <h2 className="text-2xl font-bold text-emerald-700 dark:text-emerald-400 flex items-center mb-4">
        Gestion des Employés
      </h2>
      <header className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher un employé..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full md:w-64 p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <select
            value={sortCriteria}
            onChange={(e) => setSortCriteria(e.target.value)}
            className="w-full md:w-auto p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="name_asc">Nom (A-Z)</option>
            <option value="name_desc">Nom (Z-A)</option>
            <option value="salary_asc">Salaire (Croissant)</option>
            <option value="salary_desc">Salaire (Décroissant)</option>
            <option value="fonction_asc">Fonction (A-Z)</option>
            <option value="fonction_desc">Fonction (Z-A)</option>
            <option value="category_asc">Catégorie (A-Z)</option>
            <option value="category_desc">Catégorie (Z-A)</option>
          </select>
          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            className="w-full md:w-auto p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="active">Actifs</option>
            <option value="inactive">Inactifs</option>
            <option value="all">Tous</option>
          </select>
          {canManageEmployees && (
            <button
              onClick={openCreateModal}
              className="flex items-center justify-center space-x-2 w-full md:w-auto bg-emerald-600 text-white font-bold py-2 px-4 rounded-md shadow-md hover:bg-emerald-700 transition-colors duration-200"
              disabled={saving}
            >
              <PlusCircle size={20} />
              <span>Ajouter un employé</span>
            </button>
          )}
        </div>
      </header>

      <div className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        {sortedAndFilteredEmployees.length === 0 ? (
          <p className="text-center text-gray-500 dark:text-gray-400">Aucun employé trouvé.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-gray-200 dark:bg-gray-700">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Nom</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Fonction</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Salaire</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Téléphone</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Email</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Qualité</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Catégorie</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                {sortedAndFilteredEmployees.map((employee) => (
                  <tr key={employee.id} className={`hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 ${!employee.is_active ? 'bg-red-100 dark:bg-red-900/20' : ''}`}>
                    <td className="py-4 px-6 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{employee.name}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{employee.fonction}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                      {new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(employee.salary)}
                    </td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{employee.phone}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{employee.email}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{employee.quality}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{employee.category}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm">
                      <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${employee.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                        {employee.is_active ? 'Actif' : 'Inactif'}
                      </span>
                    </td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        {canManageEmployees && (
                          employee.is_active ? (
                            <>
                              <button
                                onClick={() => openEditModal(employee)}
                                className="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors duration-150"
                                aria-label="Modifier l'employé"
                                disabled={saving}
                              >
                                <Pencil size={20} />
                              </button>
                              <button
                                onClick={() => handleDelete(employee.id)}
                                className="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-150"
                                aria-label="Supprimer l'employé"
                                disabled={saving}
                              >
                                <Trash2 size={20} />
                              </button>
                            </>
                          ) : (
                            <button
                              onClick={() => handleUpdateEmployee({ ...employee, is_active: true })}
                              className="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 transition-colors duration-150"
                              aria-label="Réactiver l'employé"
                              disabled={saving}
                            >
                              <RefreshCw size={20} />
                            </button>
                          )
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Modale de création */}
      {isCreateModalOpen && (
        <CreateEmployeeModal
          api={api}
          onClose={closeCreateModal}
          onSave={handleCreateEmployee}
          loading={saving}
          errorMessage={createError}
          onClearBackendError={() => setCreateError(null)}
        />
      )}
      
      {/* Modale de modification */}
      {isEditModalOpen && currentEmployee && (
        <EditEmployeeModal
          api={api}
          onClose={closeEditModal}
          onSave={handleUpdateEmployee}
          employeeToEdit={currentEmployee}
          loading={saving}
          errorMessage={editError}
          onClearBackendError={() => setEditError(null)}
        />
      )}

      {/* Modale de confirmation */}
      {showConfirmModal && message?.type === 'confirm' && (
        <ConfirmationModal
          message={message.text}
          onConfirm={confirmAction}
          onCancel={() => setShowConfirmModal(false)}
        />
      )}

      {/* Affichage des messages (succès/erreur) */}
      <MessageDisplay message={message} onClose={closeMessage} />
    </div>
  );
}

export default EmployeesTab;
