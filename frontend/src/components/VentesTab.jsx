import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';  
import MessageDisplay from './utils/MessageDisplay';      
import CreateVenteModal from './utils/CreateVenteModal';    
import EditVenteModal from './utils/EditVenteModal';    
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';  

// Composant de l'onglet Ventes
function VentesTab({ ventes:initialVentes, setVentes, api }) {
  const token = localStorage.getItem('authToken');

  // Initialiser l'état local des clients avec un tableau vide pour éviter l'erreur .filter
  const [ventes, setLocalVentes] = useState(Array.isArray(initialVentes) ? initialVentes : []);

  // Synchroniser l'état local avec la prop externe si elle change
  useEffect(() => {
    if (Array.isArray(initialVentes)) {
      setLocalVentes(initialVentes); 
    }
  }, [initialVentes]);

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentVente, setCurrentVente] = useState(null); // Vente à modifier

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null); // { type: 'success' | 'error', text: '...' }
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null); // Fonction à exécuter si l'utilisateur confirme

  // État pour la recherche
  const [search, setSearch] = useState('');

  // Fonction pour charger les ventes (Read)
  const fetchVentes = useCallback(async () => {
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
        setVentes(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de ventes dans la propriété "data" ou au niveau supérieur :', result);
        setVentes([]); // Initialiser à un tableau vide pour éviter les erreurs .map
      }
    } catch (err) {
      console.error('Erreur lors du chargement des ventes :', err);
      setError(`Impossible de charger les ventes. Veuillez réessayer. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setVentes]);

  // Charger les ventes au montage du composant
  useEffect(() => {
    fetchVentes();
  }, [fetchVentes]);

  // Filtrer les ventes pour la recherche
  const filteredVentes = ventes.filter((vente) =>
    vente.type?.toLowerCase().includes(search.toLowerCase()) ||
    vente.category?.toLowerCase().includes(search.toLowerCase()) ||
    vente.user_name?.toLowerCase().includes(search.toLowerCase()) ||
    vente.customer_name?.toLowerCase().includes(search.toLowerCase()) ||
    vente.contrat_type?.toLowerCase().includes(search.toLowerCase()) ||
    (vente.amount && String(vente.amount).includes(search)) ||
    (vente.date_vente && String(vente.date_vente).includes(search))
  );

  // --- Fonctions pour les modales ---
  const openCreateModal = () => setIsCreateModalOpen(true);
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (vente) => {
    setCurrentVente(vente);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentVente(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'une nouvelle vente
  const handleCreateVente = async (newVenteData) => {
    try {
      const response = await fetch(api, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(newVenteData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }

      await fetchVentes(); // Recharger les données pour resynchroniser
      closeCreateModal();
      setMessage({ type: 'success', text: 'Vente ajoutée avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout de la vente: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la mise à jour d'une vente
  const handleUpdateVente = async (updatedVenteData) => {
    try {
      const response = await fetch(`${api}/${updatedVenteData.id}`, { // Utilisation de l'ID dans l'URL
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(updatedVenteData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }

      await fetchVentes(); // Recharger les données pour resynchroniser
      closeEditModal();
      setMessage({ type: 'success', text: 'Vente mise à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour de la vente: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la suppression d'une vente
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

        await fetchVentes(); // Recharger les données pour resynchroniser
        setMessage({ type: 'success', text: 'Vente supprimée avec succès !' });
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
        Chargement des **ventes**...
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
        Gestion des Ventes
      </h2>

      <div className="mb-4 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 md:space-x-4">
        <input
          type="text"
          placeholder="Rechercher par type, catégorie, utilisateur, client, contrat..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-full md:w-2/3 p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 focus:ring-emerald-500 focus:border-emerald-500"
        />
        <button
          onClick={openCreateModal}
          className="w-full md:w-auto px-4 py-2 bg-emerald-600 text-white font-semibold rounded-md shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:bg-emerald-700 dark:hover:bg-emerald-800"
        >
          <PlusCircle className="h-5 w-5" />
          Enregistrer une nouvelle vente
        </button>
      </div>

      <div className="overflow-x-auto rounded-lg shadow-md">
        <table className="min-w-full table-auto text-sm bg-white dark:bg-gray-800">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">ID</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Type</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Montant</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Date</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Catégorie</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Utilisateur</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Client</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Contrat</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Description</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            {filteredVentes.length === 0 ? (
              <tr>
                <td colSpan="10" className="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                  Aucune **vente** trouvée.
                </td>
              </tr>
            ) : (
              filteredVentes.map((vente) => (
                <tr
                  key={vente.id}
                  className="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
                >
                  <td className="px-4 py-3">{vente.id}</td>
                  <td className="px-4 py-3">{vente.type}</td>
                  <td className="px-4 py-3">{vente.amount}</td>
                  <td className="px-4 py-3">{vente.date_vente ? vente.date_vente.split(' ')[0] : 'N/A'}</td> {/* Affiche seulement la date */}
                  <td className="px-4 py-3">{vente.category}</td>
                  <td className="px-4 py-3">{vente.user_name || 'N/A'}</td>
                  <td className="px-4 py-3">{vente.customer_name || 'N/A'}</td>
                  <td className="px-4 py-3">{vente.contrat_type || 'N/A'}</td>
                  <td className="px-4 py-3 truncate max-w-xs">{vente.description || 'N/A'}</td>
                  <td className="px-4 py-3 space-x-2">
                    <button
                      onClick={() => openEditModal(vente)}
                      className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-500"
                    >
                      <Pencil className="h-5 w-5" />
                    </button>
                    <button
                      onClick={() => handleDelete(vente.id)}
                      className="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-500"
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

      {/* Modale de création */}
      {isCreateModalOpen && (
        <CreateVenteModal
          onClose={closeCreateModal}
          onSave={handleCreateVente}
        />
      )}

      {/* Modale de modification */}
      {isEditModalOpen && currentVente && (
        <EditVenteModal
          vente={currentVente}
          onClose={closeEditModal}
          onSave={handleUpdateVente}
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

export default VentesTab;
