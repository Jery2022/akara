import React, { useState, useEffect, useCallback } from 'react';
import EditEmployeeModal from './utils/EditEmployeeModal';
import CreateEmployeeModal from './utils/CreateEmployeeModal';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';

// Composant de l'onglet Employé
function EmployeesTab({ employees:initialEmployees, setEmployees, api }) {
  const token = localStorage.getItem('authToken');
  
  // Initialiser l'état local des clients avec un tableau vide pour éviter l'erreur .filter
  const [employees, setLocalEmployees] = useState(Array.isArray(initialEmployees) ? initialEmployees : []);

  // Synchroniser l'état local avec la prop externe si elle change
  useEffect(() => {
    if (Array.isArray(initialEmployees)) {
      setLocalEmployees(initialEmployees); 
    }
  }, [initialEmployees]);

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  // --- États pour les modales ---
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false); // État pour la modale de création
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentEmployee, setCurrentEmployee] = useState(null); // Employé à modifier
 
  // État pour gérer le message affiché (succès, erreur)
  const [message, setMessage] = useState(null); // { type: 'success' | 'error', text: '...' }
 
  // --- États pour la confirmation de suppression ---
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null); // Fonction à exécuter si l'utilisateur confirme

  // // État pour gérer le chargement des actions (création/modification/suppression)
  // const [loadingAction, setLoadingAction] = useState(false);
  
  // État pour la recherche
  const [search, setSearch] = useState('');

  
  // Fonction pour charger les employés (Read)
  const fetchEmployees = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(api, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur HTTP: ${response.status} - ${errorBody.message || response.statusText}`);
      }
      const result = await response.json();

      // Assurez-vous que la propriété 'data' est un tableau
      if (result && Array.isArray(result.data)) {
        setEmployees(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de ventes dans la propriété "data" ou au niveau supérieur :', result);
        setEmployees([]); // Initialiser à un tableau vide pour éviter les erreurs .map
      }
    } catch (err) {
      console.error('Erreur lors du chargement des employés :', err);
      setError(`Impossible de charger les employés. Veuillez réessayer. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setEmployees]);

  // Charger les ventes au montage du composant
  useEffect(() => {
    fetchEmployees();
  }, [fetchEmployees]);

  // Filtrer les employés pour la recherche
  const filteredEmployees = employees?.filter((employee) =>
    employee.name?.toLowerCase().includes(search.toLowerCase()) ||
    employee.fonction?.toLowerCase().includes(search.toLowerCase()) ||
    employee.quality?.toLowerCase().includes(search.toLowerCase()) ||
    employee.category?.toLowerCase().includes(search.toLowerCase())
  ) || [];

  // Fonctions pour la modale de création
  const openCreateModal = () => {
    setIsCreateModalOpen(true);
  };

  const closeCreateModal = () => {
    setIsCreateModalOpen(false);
  };

  // Fonctions pour la modale d'édition
  const openEditModal = (employee) => {
    setCurrentEmployee(employee);
    setIsEditModalOpen(true);
  };

  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentEmployee(null);
  };


  // Gère la soumission du formulaire de modification depuis la modale
  const handleUpdateEmployee = async (updatedEmployee) => {
    // setLoadingAction(true);
    // closeMessage(); // Efface les messages précédents
    try {
      const response = await fetch(`${api}/${updatedEmployee.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(updatedEmployee),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(
          `Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`
        );
      }

      await fetchEmployees(); // Recharger les données pour resynchroniser
      closeEditModal();
      setMessage({ type: 'success', text: 'Employé mis à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour de l'employé: ${err.message || err.toString()}` });
      console.error(err);
    }  
  };

  // Gère la création d'un nouvel employé
  const handleCreateEmployee = async (newEmployeeData) => {
    // setLoadingAction(true);
    // closeMessage(); // Efface les messages précédents
    try {
      const response = await fetch(api, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(newEmployeeData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }

      await fetchEmployees(); // Recharger les données pour resynchroniser
      closeCreateModal();
      setMessage({ type: 'success', text: 'Employé créé avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la création de l'employé: ${err.message || err.toString()}` });
      console.error(err);
    }  
    };

  // Gère la suppression d'un employé (Delete)
  const handleDelete = (id) => {
    setConfirmAction(() => async () => {
    try {
      const response = await fetch(`${api}/${id}`, {
        method: 'DELETE',
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(
          `Erreur lors de la suppression: ${errorBody.message || response.statusText}`
        );
      }
      await fetchEmployees(); // Recharger les données pour resynchroniser
      setMessage({ type: 'success', text: 'Employé supprimé avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la suppression: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setShowConfirmModal(false);
       setConfirmAction(null);
    }
  });
   setShowConfirmModal(true);
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer cette vente ?' });
  };

 // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  
  if (loading) {
    return (
      <div className="text-center text-gray-600 dark:text-gray-400">
        Chargement des **employés**...
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center text-red-600 dark:text-red-400">{error}</div>
    );
  }

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Gestion des Employés
      </h2>

      {/* Barre de recherche et bouton d'ajout */}
      <div className="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <input
          type="text"
          placeholder="Rechercher un employé..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-full md:w-1/3 px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"
        />
        <button
          onClick={openCreateModal}
          className="px-4 py-2 bg-emerald-600 text-white font-semibold rounded-md shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:bg-emerald-700 dark:hover:bg-emerald-800 flex items-center gap-2"
          // disabled={loadingAction}
        >
          <PlusCircle className="h-5 w-5" />
          Ajouter un nouvel employé
        </button>
      </div>

      {/* Tableau d'affichage des employés */}
      <div className="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow">
        <table className="min-w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Nom</th>
              <th className="px-4 py-2 text-left">Fonction</th>
              <th className="px-4 py-2 text-left">Salaire</th>
              <th className="px-4 py-2 text-left">Téléphone</th>
              <th className="px-4 py-2 text-left">Email</th>
              <th className="px-4 py-2 text-left">Qualité</th>
              <th className="px-4 py-2 text-left">Catégorie</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
            {filteredEmployees.length === 0 ? (
              <tr>
                <td colSpan="8" className="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                  Aucun employé trouvé.
                </td>
              </tr>
            ) : (
              filteredEmployees.map((employee) => (
                <tr
                  key={employee.id}
                  className="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors duration-200"
                >
                  <td className="px-4 py-2 whitespace-nowrap">{employee.name}</td>
                  <td className="px-4 py-2 whitespace-nowrap">{employee.fonction}</td>
                  <td className="px-4 py-2 whitespace-nowrap">{employee.salary}</td>
                  <td className="px-4 py-2 whitespace-nowrap">{employee.phone}</td>
                  <td className="px-4 py-2 whitespace-nowrap">{employee.email}</td>
                  <td className="px-4 py-2 whitespace-nowrap">{employee.quality}</td>
                  <td className="px-4 py-2 whitespace-nowrap">{employee.category}</td>
                  <td className="px-4 py-2 whitespace-nowrap flex gap-2">
                    <button
                      onClick={() => openEditModal(employee)}
                      className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-500"
                      // disabled={loadingAction}
                    >
                      <Pencil className="h-5 w-5" />
                    </button>
                    <button
                      onClick={() => handleDelete(employee.id)}
                      className="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-500"
                      // disabled={loadingAction}
                    >
                      <Trash2 className="h-5 w-5" />
                    </button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Modale de modification */}
      {isEditModalOpen && currentEmployee && (
        <EditEmployeeModal
          employee={currentEmployee}
          onClose={closeEditModal}
          onSave={handleUpdateEmployee}
        />
      )}

      {/* Modale de création */}
      {isCreateModalOpen && (
        <CreateEmployeeModal
          onClose={closeCreateModal}
          onSave={handleCreateEmployee}
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
