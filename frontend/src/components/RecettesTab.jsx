import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreateRecetteModal from './utils/CreateRecetteModal';
import EditRecetteModal from './utils/EditRecetteModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';

// Composant de l'onglet Recettes
function RecettesTab({ recettes: initialRecettes, setRecettes, produits, customers, contrats, api }) {
  const token = localStorage.getItem('authToken');

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentRecette, setCurrentRecette] = useState(null);

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);

  // État pour la recherche
  const [search, setSearch] = useState('');

  // Fonction pour charger les recettes (Read)
  const fetchRecettes = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${api}/recettes`, {
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
        setRecettes(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de recettes :', result);
        setRecettes([]);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des recettes :', err);
      setError(`Impossible de charger les recettes. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setRecettes]);

  // Charger les recettes au montage du composant
  useEffect(() => {
    fetchRecettes();
  }, [fetchRecettes]);

  // Filtrer les recettes pour la recherche
  const filteredRecettes = (Array.isArray(initialRecettes) ? initialRecettes : []).filter((recette) => {
    const produitNom = produits.find(p => p.id === recette.produit_id)?.name?.toLowerCase() || '';
    const customerNom = customers.find(c => c.id === recette.customer_id)?.name?.toLowerCase() || '';
    const contratNom = contrats.find(c => c.id === recette.contrat_id)?.ref?.toLowerCase() || '';
    return produitNom.includes(search.toLowerCase()) ||
      customerNom.includes(search.toLowerCase()) ||
      contratNom.includes(search.toLowerCase());
  });

  // --- Fonctions pour les modales ---
  const openCreateModal = () => setIsCreateModalOpen(true);
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (recette) => {
    setCurrentRecette(recette);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentRecette(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'une nouvelle recette
  const handleCreateRecette = async (newRecetteData) => {
    setSaving(true);
    try {
      const response = await fetch(`${api}/recettes`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newRecetteData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }
      
      await fetchRecettes();
      closeCreateModal();
      setMessage({ type: 'success', text: 'Recette ajoutée avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout de la recette: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la mise à jour d'une recette
  const handleUpdateRecette = async (updatedRecetteData) => {
    setSaving(true);
    try {
      const response = await fetch(`${api}/recettes/${updatedRecetteData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(updatedRecetteData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }
      
      await fetchRecettes();
      closeEditModal();
      setMessage({ type: 'success', text: 'Recette mise à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour de la recette: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la suppression d'une recette
  const handleDelete = (recetteId) => {
    setConfirmAction(() => async () => {
      setSaving(true);
      try {
        const response = await fetch(`${api}/recettes/${recetteId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }
        
        await fetchRecettes();
        setMessage({ type: 'success', text: 'Recette supprimée avec succès !' });
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
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer cette recette ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600 dark:text-gray-400">
          Chargement des **recettes**...
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
        Gestion des Recettes
      </h2>
      <header className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher une recette..."
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
            <span>Ajouter une recette</span>
          </button>
        </div>
      </header>

      <div className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        {filteredRecettes.length === 0 ? (
          <p className="text-center text-gray-500 dark:text-gray-400">Aucune recette trouvée.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-gray-200 dark:bg-gray-700">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Produit</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Client</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Contrat</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Quantité</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Prix</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Total</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Date</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                {filteredRecettes.map((recette) => (
                  <tr key={recette.id} className="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{produits.find(p => p.id === recette.produit_id)?.name || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{customers.find(c => c.id === recette.customer_id)?.name || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{contrats.find(c => c.id === recette.contrat_id)?.ref || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{recette.quantity}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(recette.price)}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(recette.total)}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{recette.date_recette.split(' ')[0]}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => openEditModal(recette)}
                          className="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors duration-150"
                          aria-label="Modifier la recette"
                          disabled={saving}
                        >
                          <Pencil size={20} />
                        </button>
                        <button
                          onClick={() => handleDelete(recette.id)}
                          className="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-150"
                          aria-label="Supprimer la recette"
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
        <CreateRecetteModal
          onClose={closeCreateModal}
          onSave={handleCreateRecette}
          produits={produits}
          customers={customers}
          contrats={contrats}
          loading={saving}
        />
      )}
      
      {/* Modale de modification */}
      {isEditModalOpen && currentRecette && (
        <EditRecetteModal
          onClose={closeEditModal}
          onSave={handleUpdateRecette}
          recetteToEdit={currentRecette}
          produits={produits}
          customers={customers}
          contrats={contrats}
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

export default RecettesTab;
