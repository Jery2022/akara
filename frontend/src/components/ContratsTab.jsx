import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreateContractModal from './utils/CreateContractModal';
import EditContractModal from './utils/EditContractModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';

// Composant de l'onglet Contrats
function ContratsTab({ contrats: initialContrats, setContrats, api }) {
  const token = localStorage.getItem('authToken');

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentContrat, setCurrentContrat] = useState(null);
  const [createError, setCreateError] = useState(null);
  const [editError, setEditError] = useState(null);

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);

  // État pour la recherche et le tri
  const [search, setSearch] = useState('');
  const [sortCriteria, setSortCriteria] = useState('date_debut_desc');

  // Fonction pour charger les contrats (Read)
  const fetchContrats = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${api}/contrats`, {
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
        setContrats(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de contrats :', result);
        setContrats([]);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des contrats :', err);
      setError(`Impossible de charger les contrats. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setContrats]);

  // Charger les contrats au montage du composant
  useEffect(() => {
    fetchContrats();
  }, [fetchContrats]);

  // Filtrer et trier les contrats
  const sortedAndFilteredContrats = (Array.isArray(initialContrats) ? initialContrats : [])
    .filter((contrat) =>
      contrat.name?.toLowerCase().includes(search.toLowerCase()) ||
      contrat.objet?.toLowerCase().includes(search.toLowerCase()) ||
      contrat.status?.toLowerCase().includes(search.toLowerCase()) ||
      contrat.type?.toLowerCase().includes(search.toLowerCase())
    )
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
      } else {
        // Fallback for dates or other types
        comparison = String(valA).localeCompare(String(valB));
      }

      return order === 'asc' ? comparison : -comparison;
    });

  // --- Fonctions pour les modales ---
  const openCreateModal = () => {
    setCreateError(null);
    setIsCreateModalOpen(true);
  };
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (contrat) => {
    setCurrentContrat(contrat);
    setEditError(null);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentContrat(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'un nouveau contrat
  const handleCreateContrat = async (newContratData) => {
    console.log('Données reçues par handleCreateContrat:', newContratData);
    setSaving(true);
    setCreateError(null);
    try {
      const response = await fetch(`${api}/contrats`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newContratData),
      });

      const result = await response.json();

      if (!response.ok) {
        setCreateError(result.message || `Erreur HTTP ${response.status}`);
      } else {
        await fetchContrats();
        closeCreateModal();
        setMessage({ type: 'success', text: 'Contrat ajouté avec succès !' });
      }
    } catch (err) {
      setCreateError(`Erreur lors de l'ajout du contrat: ${err.message || err.toString()}`);
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la mise à jour d'un contrat
  const handleUpdateContrat = async (updatedContratData) => {
    setSaving(true);
    setEditError(null);
    try {
      const response = await fetch(`${api}/contrats/${updatedContratData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(updatedContratData),
      });

      const result = await response.json();

      if (!response.ok) {
        setEditError(result.message || `Erreur HTTP ${response.status}`);
      } else {
        try {
          console.log("Mise à jour réussie. Tentative de rafraîchissement et de fermeture de la modale.");
          await fetchContrats();
          console.log("Rafraîchissement terminé. Fermeture de la modale.");
          closeEditModal();
          setMessage({ type: 'success', text: 'Contrat mis à jour avec succès !' });
        } catch (e) {
          console.error("Une erreur est survenue après la mise à jour :", e);
          // On ferme quand même la modale et on affiche un message d'erreur si le rafraîchissement échoue
          closeEditModal();
          setMessage({ type: 'error', text: 'Mise à jour réussie, mais le rafraîchissement a échoué.' });
        }
      }
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour du contrat.` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la suppression d'un contrat
  const handleDelete = (contratId) => {
    setConfirmAction(() => async () => {
      setSaving(true);
      try {
        const response = await fetch(`${api}/contrats/${contratId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }
        
        await fetchContrats();
        setMessage({ type: 'success', text: 'Contrat supprimé avec succès !' });
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
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer ce contrat ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600 dark:text-gray-400">
          Chargement des **contrats**...
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
        Gestion des Contrats
      </h2>
      <header className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher un contrat..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full md:w-64 p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <select
            value={sortCriteria}
            onChange={(e) => setSortCriteria(e.target.value)}
            className="w-full md:w-auto p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="date_debut_desc">Date début (Plus récent)</option>
            <option value="date_debut_asc">Date début (Plus ancien)</option>
            <option value="montant_desc">Montant (Décroissant)</option>
            <option value="montant_asc">Montant (Croissant)</option>
            <option value="name_asc">Nom (A-Z)</option>
            <option value="name_desc">Nom (Z-A)</option>
          </select>
          <button
            onClick={openCreateModal}
            className="flex items-center justify-center space-x-2 w-full md:w-auto bg-emerald-600 text-white font-bold py-2 px-4 rounded-md shadow-md hover:bg-emerald-700 transition-colors duration-200"
            disabled={saving}
          >
            <PlusCircle size={20} />
            <span>Ajouter un contrat</span>
          </button>
        </div>
      </header>

      <div className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        {sortedAndFilteredContrats.length === 0 ? (
          <p className="text-center text-gray-500 dark:text-gray-400">Aucun contrat trouvé.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-gray-200 dark:bg-gray-700">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Nom</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Objet</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Date début</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Date fin</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Type</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                {sortedAndFilteredContrats.map((contrat) => (
                  <tr key={contrat.id} className="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <td className="py-4 px-6 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{contrat.name}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{contrat.objet}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{contrat.date_debut}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{contrat.date_fin}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{contrat.type}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{contrat.status}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                      {new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(contrat.montant)}
                    </td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => openEditModal(contrat)}
                          className="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors duration-150"
                          aria-label="Modifier le contrat"
                          disabled={saving}
                        >
                          <Pencil size={20} />
                        </button>
                        <button
                          onClick={() => handleDelete(contrat.id)}
                          className="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-150"
                          aria-label="Supprimer le contrat"
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
        <CreateContractModal
          api={api}
          onClose={closeCreateModal}
          onSave={handleCreateContrat}
          loading={saving}
          errorMessage={createError}
          onClearBackendError={() => setCreateError(null)}
        />
      )}
      
      {/* Modale de modification */}
      {isEditModalOpen && currentContrat && (
        <EditContractModal
          api={api}
          onClose={closeEditModal}
          onSave={handleUpdateContrat}
          contractToEdit={currentContrat}
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

export default ContratsTab;
