import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreateFactureModal from './utils/CreateFactureModal';
import EditFactureModal from './utils/EditFactureModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';

// Composant de l'onglet Factures
function FacturesTab({ factures: initialFactures, setFactures, api }) {
  const token = localStorage.getItem('authToken');


  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentFacture, setCurrentFacture] = useState(null);

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);

  // État pour la recherche
  const [search, setSearch] = useState('');

  // Fonction pour charger les factures (Read)
  const fetchFactures = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${api}/factures`, {
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
        setFactures(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de factures :', result);
        setFactures([]);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des factures :', err);
      setError(`Impossible de charger les factures. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setFactures]);

  // Charger les factures au montage du composant
  useEffect(() => {
    fetchFactures();
  }, [fetchFactures]);

  // Filtrer les factures pour la recherche
  const filteredFactures = (Array.isArray(initialFactures) ? initialFactures : []).filter((facture) =>
    facture.customer_name?.toLowerCase().includes(search.toLowerCase()) ||
    facture.amount_ttc?.toString().includes(search)
  );

  // --- Fonctions pour les modales ---
  const openCreateModal = () => setIsCreateModalOpen(true);
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (facture) => {
    setCurrentFacture(facture);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentFacture(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'une nouvelle facture
  const handleCreateFacture = async (newFactureData) => {
    setSaving(true);
    try {
      const response = await fetch(`${api}/factures`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newFactureData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }
      
      await fetchFactures();
      closeCreateModal();
      setMessage({ type: 'success', text: 'Facture ajoutée avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout de la facture: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la mise à jour d'une facture
  const handleUpdateFacture = async (updatedFactureData) => {
    setSaving(true);
    try {
      const response = await fetch(`${api}/factures/${updatedFactureData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(updatedFactureData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }
      
      await fetchFactures();
      closeEditModal();
      setMessage({ type: 'success', text: 'Facture mise à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour de la facture: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la suppression d'une facture
  const handleDelete = (factureId) => {
    setConfirmAction(() => async () => {
      setSaving(true);
      try {
        const response = await fetch(`${api}/factures/${factureId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }
        
        await fetchFactures();
        setMessage({ type: 'success', text: 'Facture supprimée avec succès !' });
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
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer cette facture ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600 dark:text-gray-400">
          Chargement des **factures**...
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
        Gestion des Factures
      </h2>
      <header className="bg-white shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher une facture..."
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
            <span>Ajouter une facture</span>
          </button>
        </div>
      </header>

      <div className="bg-white shadow-md rounded-lg p-6">
        {filteredFactures.length === 0 ? (
          <p className="text-center text-gray-500">Aucune facture trouvée.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full bg-white rounded-md overflow-hidden">
              <thead className="bg-gray-200">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Client</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Montant TTC</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Date de facture</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {filteredFactures.map((facture) => (
                  <tr key={facture.id} className="hover:bg-gray-50 transition-colors duration-150">
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{facture.customer_name}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">
                      {new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(facture.amount_ttc)}
                    </td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{facture.date_facture}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => openEditModal(facture)}
                          className="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                          aria-label="Modifier la facture"
                          disabled={saving}
                        >
                          <Pencil size={20} />
                        </button>
                        <button
                          onClick={() => handleDelete(facture.id)}
                          className="text-red-600 hover:text-red-900 transition-colors duration-150"
                          aria-label="Supprimer la facture"
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
        <CreateFactureModal
          onClose={closeCreateModal}
          onSave={handleCreateFacture}
          loading={saving}
        />
      )}
      
      {/* Modale de modification */}
      {isEditModalOpen && currentFacture && (
        <EditFactureModal
          onClose={closeEditModal}
          onSave={handleUpdateFacture}
          factureToEdit={currentFacture}
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

export default FacturesTab;
