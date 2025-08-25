import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreateQuittanceModal from './utils/CreateQuittanceModal';
import EditQuittanceModal from './utils/EditQuittanceModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';

// Composant de l'onglet Quittances
function QuittancesTab({ quittances: initialQuittances, setQuittances, api }) {
  const token = localStorage.getItem('authToken');

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false); // Nouvel état pour les opérations de sauvegarde
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentQuittance, setCurrentQuittance] = useState(null);

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);

  // État pour la recherche
  const [search, setSearch] = useState('');


  // Fonction pour charger les quittances (Read)
  const fetchQuittances = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${api}/quittances`, {
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
        setQuittances(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de quittances :', result);
        setQuittances([]);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des quittances :', err);
      setError(`Impossible de charger les quittances. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setQuittances]);

  // Charge les quittances au montage du composant
  useEffect(() => {
    fetchQuittances();
  }, [fetchQuittances]);
  
  //console.log('LOG 0 : ', employees ); //LOG

  // Filtrer les quittances pour la recherche
  const filteredQuittances = (Array.isArray(initialQuittances) ? initialQuittances : []).filter((quittance) => {
    return (
      quittance.periode_service?.toLowerCase().includes(search.toLowerCase()) ||
      quittance.type?.toLowerCase().includes(search.toLowerCase()) ||
      quittance.employee_name?.toLowerCase().includes(search.toLowerCase())
    );
  });

  // --- Fonctions pour les modales ---
  const openCreateModal = () => setIsCreateModalOpen(true);
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (quittance) => {
    setCurrentQuittance(quittance);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentQuittance(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'une nouvelle quittance
  const handleCreateQuittance = async (newQuittanceData) => {
    setSaving(true);
    try {
      const response = await fetch(`${api}/quittances`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newQuittanceData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }
      
      await fetchQuittances();
      closeCreateModal();
      setMessage({ type: 'success', text: 'Quittance ajoutée avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout de la quittance: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la mise à jour d'une quittance
  const handleUpdateQuittance = async (updatedQuittanceData) => {
    setSaving(true);
    try {
      const response = await fetch(`${api}/quittances/${updatedQuittanceData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(updatedQuittanceData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }
      
      await fetchQuittances();
      closeEditModal();
      setMessage({ type: 'success', text: 'Quittance mise à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour de la quittance: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la suppression d'une quittance
  const handleDelete = (quittanceId) => {
    setConfirmAction(() => async () => {
      setSaving(true);
      try {
        const response = await fetch(`${api}/quittances/${quittanceId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }
        
        await fetchQuittances();
        setMessage({ type: 'success', text: 'Quittance supprimée avec succès !' });
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
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer cette quittance ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600 dark:text-gray-400">
          Chargement des **quittances**...
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
        Gestion des Quittances
      </h2>
      <header className="bg-white shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher une quittance..."
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
            <span>Ajouter une quittance</span>
          </button>
        </div>
      </header>

      <div className="bg-white shadow-md rounded-lg p-6">
        {filteredQuittances.length === 0 ? (
          <p className="text-center text-gray-500">Aucune quittance trouvée.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full bg-white rounded-md overflow-hidden">
              <thead className="bg-gray-200">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Employé</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Montant</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Date Paiement</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Type</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {filteredQuittances.map((quittance) => (
                  <tr key={quittance.id} className="hover:bg-gray-50 transition-colors duration-150">
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{quittance.employee_name}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">
                      {new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(quittance.montant)}
                    </td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{quittance.date_paiement}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{quittance.type}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => openEditModal(quittance)}
                          className="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                          aria-label="Modifier la quittance"
                          disabled={saving}
                        >
                          <Pencil size={20} />
                        </button>
                        <button
                          onClick={() => handleDelete(quittance.id)}
                          className="text-red-600 hover:text-red-900 transition-colors duration-150"
                          aria-label="Supprimer la quittance"
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
        <CreateQuittanceModal
          onClose={closeCreateModal}
          onSave={handleCreateQuittance}
          employees={[]}
          loading={saving}
        />
      )}
      
      {/* Modale de modification */}
      {isEditModalOpen && currentQuittance && (
        <EditQuittanceModal
          onClose={closeEditModal}
          onSave={handleUpdateQuittance}
          quittanceToEdit={currentQuittance}
          employees={[]}
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

export default QuittancesTab;
