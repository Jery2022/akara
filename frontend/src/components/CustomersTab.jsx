import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal'; // Assurez-vous que ce fichier existe
import MessageDisplay from './utils/MessageDisplay';     // Assurez-vous que ce fichier existe
import CreateCustomerModal from './utils/CreateCustomerModal'; // Nouveau composant
import EditCustomerModal from './utils/EditCustomerModal';   // Nouveau composant

// Composant de l'onglet Clients
function CustomersTab({ customers:initialCustomers, setCustomers, api }) {
  const token = localStorage.getItem('authToken');

    // Initialiser l'état local des clients avec un tableau vide pour éviter l'erreur .filter
  const [customers, setLocalCustomers] = useState(Array.isArray(initialCustomers) ? initialCustomers : []);

  // Synchroniser l'état local avec la prop externe si elle change
  useEffect(() => {
    if (Array.isArray(initialCustomers)) { 
      setLocalCustomers(initialCustomers); 
    }
  }, [initialCustomers]);

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentCustomer, setCurrentCustomer] = useState(null); // Client à modifier

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null); // { type: 'success' | 'error', text: '...' }
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null); // Fonction à exécuter si l'utilisateur confirme

  // État pour la recherche
  const [search, setSearch] = useState('');

  // Fonction pour charger les clients (Read)
  const fetchCustomers = useCallback(async () => {
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
        setCustomers(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de clients dans la propriété "data" ou au niveau supérieur :', result);
        setCustomers([]); // Initialiser à un tableau vide pour éviter les erreurs .map
      }
    } catch (err) {
      console.error('Erreur lors du chargement des clients :', err);
      setError(`Impossible de charger les clients. Veuillez réessayer. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setCustomers]);

  // Charger les clients au montage du composant
  useEffect(() => {
    fetchCustomers();
  }, [fetchCustomers]);

  // Filtrer les clients pour la recherche
  const filteredCustomers = customers.filter((customer) =>
    customer.name?.toLowerCase().includes(search.toLowerCase()) ||
    customer.refContact?.toLowerCase().includes(search.toLowerCase()) ||
    customer.phone?.toLowerCase().includes(search.toLowerCase()) ||
    customer.email?.toLowerCase().includes(search.toLowerCase())
  );

  // --- Fonctions pour les modales ---
  const openCreateModal = () => setIsCreateModalOpen(true);
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (customer) => {
    setCurrentCustomer(customer);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentCustomer(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'un nouveau client
  const handleCreateCustomer = async (newCustomerData) => {
    try {
      const response = await fetch(api, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(newCustomerData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }

      await fetchCustomers(); // Recharger les données pour resynchroniser
      closeCreateModal();
      setMessage({ type: 'success', text: 'Client ajouté avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout du client: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la mise à jour d'un client
  const handleUpdateCustomer = async (updatedCustomerData) => {
    try {
      const response = await fetch(`${api}/${updatedCustomerData.id}`, { // Utilisation de l'ID dans l'URL
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(updatedCustomerData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }

      await fetchCustomers(); // Recharger les données pour resynchroniser
      closeEditModal();
      setMessage({ type: 'success', text: 'Client mis à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour du client: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la suppression d'un client
  const handleDelete = (id) => {
    setConfirmAction(() => async () => {
      try {
        const response = await fetch(`${api}/${id}`, { // Utilisation de l'ID dans l'URL
          method: 'DELETE',
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }

        await fetchCustomers(); // Recharger les données pour resynchroniser
        setMessage({ type: 'success', text: 'Client supprimé avec succès !' });
      } catch (err) {
        setMessage({ type: 'error', text: `Erreur lors de la suppression: ${err.message || err.toString()}` });
        console.error(err);
      } finally {
        setShowConfirmModal(false);
        setConfirmAction(null);
      }
    });
    setShowConfirmModal(true);
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer ce client ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="text-center text-gray-600 dark:text-gray-400">
        Chargement des **clients**...
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
        Gestion des Clients
      </h2>

      <div className="mb-4 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 md:space-x-4">
        <input
          type="text"
          placeholder="Rechercher par nom, contact, téléphone, email..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-full md:w-2/3 p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 focus:ring-emerald-500 focus:border-emerald-500"
        />
        <button
          onClick={openCreateModal}
          className="w-full md:w-auto px-4 py-2 bg-emerald-600 text-white font-semibold rounded-md shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:bg-emerald-700 dark:hover:bg-emerald-800"
        >
          Ajouter un nouveau client
        </button>
      </div>

      <div className="overflow-x-auto rounded-lg shadow-md">
        <table className="min-w-full table-auto text-sm bg-white dark:bg-gray-800">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">ID</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Nom</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Personne contact</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Téléphone</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Email</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">ID Contrat</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            {filteredCustomers.length === 0 ? (
              <tr>
                <td colSpan="7" className="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                  Aucun **client** trouvé.
                </td>
              </tr>
            ) : (
              filteredCustomers.map((customer) => (
                <tr
                  key={customer.id}
                  className="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
                >
                  <td className="px-4 py-3">{customer.id}</td>
                  <td className="px-4 py-3">{customer.name}</td>
                  <td className="px-4 py-3">{customer.refContact}</td>
                  <td className="px-4 py-3">{customer.phone}</td>
                  <td className="px-4 py-3">{customer.email}</td>
                  <td className="px-4 py-3">{customer.contrat_id || 'N/A'}</td>
                  <td className="px-4 py-3 space-x-2">
                    <button
                      onClick={() => openEditModal(customer)}
                      className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-500"
                    >
                      Modifier
                    </button>
                    <button
                      onClick={() => handleDelete(customer.id)}
                      className="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-500"
                    >
                      Supprimer
                    </button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Modale de création */}
      {isCreateModalOpen && (
        <CreateCustomerModal
          onClose={closeCreateModal}
          onSave={handleCreateCustomer}
        />
      )}

      {/* Modale de modification */}
      {isEditModalOpen && currentCustomer && (
        <EditCustomerModal
          customer={currentCustomer}
          onClose={closeEditModal}
          onSave={handleUpdateCustomer}
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

export default CustomersTab;
