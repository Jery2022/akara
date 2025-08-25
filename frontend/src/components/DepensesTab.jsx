import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreateDepenseModal from './utils/CreateDepenseModal';
import EditDepenseModal from './utils/EditDepenseModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';

// Composant de l'onglet Dépenses
function DepensesTab({ depenses: initialDepenses, setDepenses, api }) {
  const token = localStorage.getItem('authToken');

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentDepense, setCurrentDepense] = useState(null);

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);

  // État pour la recherche
  const [search, setSearch] = useState('');

  // Fonction pour charger les dépenses (Read)
  const fetchDepenses = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${api}/depenses`, {
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
        setDepenses(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de dépenses :', result);
        setDepenses([]);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des dépenses :', err);
      setError(`Impossible de charger les dépenses. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setDepenses]);

  // Charger les dépenses au montage du composant
  useEffect(() => {
    fetchDepenses();
  }, [fetchDepenses]);

  // Filtrer les dépenses pour la recherche
  const filteredDepenses = (Array.isArray(initialDepenses) ? initialDepenses : []).filter((depense) =>
    depense.description?.toLowerCase().includes(search.toLowerCase()) ||
    depense.nature?.toLowerCase().includes(search.toLowerCase()) ||
    depense.category?.toLowerCase().includes(search.toLowerCase())
  );

  // --- Fonctions pour les modales ---
  const openCreateModal = () => setIsCreateModalOpen(true);
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (depense) => {
    setCurrentDepense(depense);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentDepense(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'une nouvelle dépense
  const handleCreateDepense = async (newDepenseData) => {
    setSaving(true);
    try {
      const response = await fetch(`${api}/depenses`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newDepenseData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }
      
      await fetchDepenses();
      closeCreateModal();
      setMessage({ type: 'success', text: 'Dépense ajoutée avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout de la dépense: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la mise à jour d'une dépense
  const handleUpdateDepense = async (updatedDepenseData) => {
    setSaving(true);
    try {
      const response = await fetch(`${api}/depenses/${updatedDepenseData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(updatedDepenseData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }
      
      await fetchDepenses();
      closeEditModal();
      setMessage({ type: 'success', text: 'Dépense mise à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour de la dépense: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la suppression d'une dépense
  const handleDelete = (depenseId) => {
    setConfirmAction(() => async () => {
      setSaving(true);
      try {
        const response = await fetch(`${api}/depenses/${depenseId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }
        
        await fetchDepenses();
        setMessage({ type: 'success', text: 'Dépense supprimée avec succès !' });
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
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer cette dépense ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600 dark:text-gray-400">
          Chargement des **dépenses**...
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
    <div className="min-h-screen bg-gray-100 p-4 font-sans antialiased">
      <h2 className="text-2xl font-bold text-emerald-700 flex items-center mb-4">
        Gestion des Dépenses
      </h2>
      <header className="bg-white shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher une dépense..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full md:w-64 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <button
            onClick={openCreateModal}
            className="flex items-center justify-center space-x-2 w-full md:w-auto bg-emerald-600 text-white font-bold py-2 px-4 rounded-md shadow-md hover:bg-emerald-700 transition-colors duration-200"
            disabled={saving}
          >
            <PlusCircle size={20} />
            <span>Ajouter une dépense</span>
          </button>
        </div>
      </header>

      <div className="bg-white shadow-md rounded-lg p-6">
        {filteredDepenses.length === 0 ? (
          <p className="text-center text-gray-500">Aucune dépense trouvée.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full bg-white rounded-md overflow-hidden">
              <thead className="bg-gray-200">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Date</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Description</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Montant</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {filteredDepenses.map((depense) => (
                  <tr key={depense.id} className="hover:bg-gray-50 transition-colors duration-150">
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{depense.date_depense}</td>
                    <td className="py-4 px-6 text-sm text-gray-600">{depense.description || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">
                      {new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(depense.total)}
                    </td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => openEditModal(depense)}
                          className="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                          aria-label="Modifier la dépense"
                          disabled={saving}
                        >
                          <Pencil size={20} />
                        </button>
                        <button
                          onClick={() => handleDelete(depense.id)}
                          className="text-red-600 hover:text-red-900 transition-colors duration-150"
                          aria-label="Supprimer la dépense"
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
        <CreateDepenseModal
          onClose={closeCreateModal}
          onSave={handleCreateDepense}
          loading={saving}
        />
      )}
      
      {/* Modale de modification */}
      {isEditModalOpen && currentDepense && (
        <EditDepenseModal
          onClose={closeEditModal}
          onSave={handleUpdateDepense}
          depenseToEdit={currentDepense}
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

export default DepensesTab;
