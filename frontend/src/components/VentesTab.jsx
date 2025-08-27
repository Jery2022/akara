import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreateVenteModal from './utils/CreateVenteModal';
import EditVenteModal from './utils/EditVenteModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';

// Composant de l'onglet Ventes
function VentesTab({ ventes: initialVentes, setVentes, api }) {
  const token = localStorage.getItem('authToken');

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentVente, setCurrentVente] = useState(null);

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);

  // État pour la recherche
  const [search, setSearch] = useState('');

  // Fonction pour charger les ventes (Read)
  const fetchVentes = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${api}/ventes`, {
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
        setVentes(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de ventes :', result);
        setVentes([]);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des ventes :', err);
      setError(`Impossible de charger les ventes. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setVentes]);

  // Charger les ventes au montage du composant
  useEffect(() => {
    fetchVentes();
  }, [fetchVentes]);

  // Filtrer les ventes pour la recherche
  const filteredVentes = (Array.isArray(initialVentes) ? initialVentes : []).filter((vente) =>
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
    setSaving(true);
    try {
      const response = await fetch(`${api}/ventes`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newVenteData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }
      
      await fetchVentes();
      closeCreateModal();
      setMessage({ type: 'success', text: 'Vente ajoutée avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout de la vente: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la mise à jour d'une vente
  const handleUpdateVente = async (updatedVenteData) => {
    setSaving(true);
    try {
      const response = await fetch(`${api}/ventes/${updatedVenteData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(updatedVenteData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }
      
      await fetchVentes();
      closeEditModal();
      setMessage({ type: 'success', text: 'Vente mise à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour de la vente: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la suppression d'une vente
  const handleDelete = (venteId) => {
    setConfirmAction(() => async () => {
      setSaving(true);
      try {
        const response = await fetch(`${api}/ventes/${venteId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }
        
        await fetchVentes();
        setMessage({ type: 'success', text: 'Vente supprimée avec succès !' });
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
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer cette vente ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600 dark:text-gray-400">
          Chargement des **ventes**...
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
        Gestion des Ventes
      </h2>
      <header className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher une vente..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full md:w-64 p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <button
            onClick={openCreateModal}
            className="flex items-center justify-center space-x-2 w-full md:w-auto bg-emerald-600 text-white font-bold py-2 px-4 rounded-md shadow-md hover:bg-emerald-700 transition-colors duration-200"
            disabled={saving}
          >
            <PlusCircle size={20} />
            <span>Ajouter une vente</span>
          </button>
        </div>
      </header>

      <div className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        {filteredVentes.length === 0 ? (
          <p className="text-center text-gray-500 dark:text-gray-400">Aucune vente trouvée.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-gray-200 dark:bg-gray-700">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Type</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Date</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Catégorie</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Utilisateur</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Client</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Contrat</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Description</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                {filteredVentes.map((vente) => (
                  <tr key={vente.id} className="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{vente.type}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                      {new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(vente.amount)}
                    </td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{vente.date_vente ? vente.date_vente.split(' ')[0] : 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{vente.category}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{vente.user_name || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{vente.customer_name || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{vente.contrat_type || 'N/A'}</td>
                    <td className="py-4 px-6 text-sm text-gray-600 dark:text-gray-300 truncate max-w-xs">{vente.description || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => openEditModal(vente)}
                          className="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors duration-150"
                          aria-label="Modifier la vente"
                          disabled={saving}
                        >
                          <Pencil size={20} />
                        </button>
                        <button
                          onClick={() => handleDelete(vente.id)}
                          className="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-150"
                          aria-label="Supprimer la vente"
                          disabled={saving}
                        >
                          <Trash2 size={20} />
                        </button>
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
        <CreateVenteModal
          onClose={closeCreateModal}
          onSave={handleCreateVente}
          loading={saving}
        />
      )}
      
      {/* Modale de modification */}
      {isEditModalOpen && currentVente && (
        <EditVenteModal
          onClose={closeEditModal}
          onSave={handleUpdateVente}
          venteToEdit={currentVente}
          loading={saving}
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
