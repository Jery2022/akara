import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay'; 
import CreateSupplierModal from './utils/CreateSupplierModal'; 
import EditSupplierModal from './utils/EditSupplierModal';    

// Composant de l'onglet Fournisseurs
function SuppliersTab({ suppliers:initialSuppliers, setSuppliers, api }) {
  const token = localStorage.getItem('authToken');

    // Initialiser l'état local des clients avec un tableau vide pour éviter l'erreur .filter
  const [suppliers, setLocalSuppliers] = useState(Array.isArray(initialSuppliers) ? initialSuppliers : []);

  // Synchroniser l'état local avec la prop externe si elle change
  useEffect(() => {
    if (Array.isArray(initialSuppliers)) {
      setLocalSuppliers(initialSuppliers); 
    }
  }, [initialSuppliers]);

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentSupplier, setCurrentSupplier] = useState(null);  

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null); // { type: 'success' | 'error', text: '...' }
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null); // Fonction à exécuter si l'utilisateur confirme

  // État pour la recherche
  const [search, setSearch] = useState('');

  // Fonction pour charger les fournisseurs (Read)
  const fetchSuppliers = useCallback(async () => {
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

      // Vérification que la propriété 'data' est un tableau
      if (result && Array.isArray(result.data)) {
        setSuppliers(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de fournisseurs dans la propriété "data" ou au niveau supérieur :', result);
        setSuppliers([]); // Initialiser à un tableau vide pour éviter les erreurs .map
      }
    } catch (err) {
      console.error('Erreur lors du chargement des fournisseurs :', err);
      setError(`Impossible de charger les fournisseurs. Veuillez réessayer. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setSuppliers]);

  // Charger les fournisseurs au montage du composant
  useEffect(() => {
    fetchSuppliers();
  }, [fetchSuppliers]);

  // Filtrer les fournisseurs pour la recherche
  const filteredSuppliers = suppliers.filter((supplier) =>
    supplier.name?.toLowerCase().includes(search.toLowerCase()) ||
    supplier.refContact?.toLowerCase().includes(search.toLowerCase()) ||
    supplier.phone?.toLowerCase().includes(search.toLowerCase()) ||
    supplier.email?.toLowerCase().includes(search.toLowerCase())
  );

  // --- Fonctions pour les modales ---
  const openCreateModal = () => setIsCreateModalOpen(true);
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (supplier) => {
    setCurrentSupplier(supplier);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentSupplier(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'un nouveau fournisseur
  const handleCreateSupplier = async (newSupplierData) => {
    try {
      const response = await fetch(api, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(newSupplierData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }

      await fetchSuppliers(); // Recharger les données pour resynchroniser
      closeCreateModal();
      setMessage({ type: 'success', text: 'Fournisseur ajouté avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout du fournisseur: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la mise à jour d'un fournisseur
  const handleUpdateSupplier = async (updatedSupplierData) => {
    try {
      const response = await fetch(`${api}/${updatedSupplierData.id}`, { // Utilisation de l'ID dans l'URL
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(updatedSupplierData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }

      await fetchSuppliers(); // Recharger les données pour resynchroniser
      closeEditModal();
      setMessage({ type: 'success', text: 'Fournisseur mis à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour du fournisseur: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la suppression d'un fournisseur
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

        await fetchSuppliers(); // Recharger les données pour resynchroniser
        setMessage({ type: 'success', text: 'Fournisseur supprimé avec succès !' });
      } catch (err) {
        setMessage({ type: 'error', text: `Erreur lors de la suppression: ${err.message || err.toString()}` });
        console.error(err);
      } finally {
        setShowConfirmModal(false);
        setConfirmAction(null);
      }
    });
    setShowConfirmModal(true);
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer ce fournisseur ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="text-center text-gray-600 dark:text-gray-400">
        Chargement des **fournisseurs**...
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
        Gestion des Fournisseurs
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
          Ajouter un nouveau fournisseur
        </button>
      </div>

      <div className="overflow-x-auto rounded-lg shadow-md">
        <table className="min-w-full table-auto text-sm bg-white dark:bg-gray-800">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">ID</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Nom</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Réf. Contact</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Téléphone</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Email</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">ID Contrat</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            {filteredSuppliers.length === 0 ? (
              <tr>
                <td colSpan="7" className="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                  Aucun **fournisseur** trouvé.
                </td>
              </tr>
            ) : (
              filteredSuppliers.map((supplier) => (
                <tr
                  key={supplier.id}
                  className="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
                ><td>{supplier.id}</td><td>{supplier.name}</td><td>{supplier.refContact}</td><td>{supplier.phone}</td><td>{supplier.email}</td><td>{supplier.contrat_id || 'N/A'}</td><td className="px-4 py-3 space-x-2"><button onClick={() => openEditModal(supplier)} className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-500">Modifier</button><button onClick={() => handleDelete(supplier.id)} className="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-500">Supprimer</button></td></tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Modale de création */}
      {isCreateModalOpen && (
        <CreateSupplierModal
          onClose={closeCreateModal}
          onSave={handleCreateSupplier}
        />
      )}

      {/* Modale de modification */}
      {isEditModalOpen && currentSupplier && (
        <EditSupplierModal
          supplier={currentSupplier}
          onClose={closeEditModal}
          onSave={handleUpdateSupplier}
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

export default SuppliersTab;
