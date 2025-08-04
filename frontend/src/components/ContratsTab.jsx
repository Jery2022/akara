import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay'; 
import EditContractModal from './utils/EditContractModal';
import CreateContractModal from './utils/CreateContractModal';  
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';

// Composant de l'onglet Contrats
function ContratsTab({ contrats:initialContrats, setContrats, api }) {
  const token = localStorage.getItem('authToken');



    // Initialiser l'état local des contrats avec un tableau vide pour éviter l'erreur .filter
  const [contrats, setLocalContrat] = useState(Array.isArray(initialContrats) ? initialContrats : []);

  // Synchroniser l'état local avec la prop externe si elle change
  useEffect(() => {
    if (Array.isArray(initialContrats)) { 
      setLocalContrat(initialContrats); 
    }
  }, [initialContrats]);

  // État pour gérer le chargement des données (lecture)
  const [loading, setLoading] = useState(true);
  // État pour gérer les erreurs de chargement
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false); // État pour la modale de création
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentContrat, setCurrentContrat] = useState(null); // État pour la modale de modification

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null); // { type: 'success' | 'error', text: '...' }
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null); // Fonction à exécuter si l'utilisateur confirme

  // État pour la recherche
  const [search, setSearch] = useState('');


  // Fonction pour charger les contrats (Read)
  const fetchContrats = useCallback(async () => { 
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
        setContrats(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de fournisseurs dans la propriété "data" ou au niveau supérieur :', result);
        setContrats([]); // Initialiser à un tableau vide pour éviter les erreurs .map
      }
    } catch (err) {
      console.error('Erreur lors du chargement des contrats :', err);
      setError('Impossible de charger les contrats. Veuillez réessayer.');
    } finally {
      setLoading(false);
    }
  }, [api, token, setContrats]);

  // Charger les contrats au montage du composant
  useEffect(() => {
    fetchContrats();
  }, [fetchContrats]);

  // Filtrer les employés pour la recherche
  const filteredContrats = contrats?.filter((contrat) =>
    contrat.ref?.toLowerCase().includes(search.toLowerCase()) ||
    contrat.objet?.toLowerCase().includes(search.toLowerCase()) ||
    contrat.status?.toLowerCase().includes(search.toLowerCase()) ||
    contrat.type?.toLowerCase().includes(search.toLowerCase())
  );

console.log('log 1 :', contrats); //LOG

  // Fonctions pour la modale de création
  const openCreateModal = () => { setIsCreateModalOpen(true); };
  const closeCreateModal = () => { setIsCreateModalOpen(false); };

  // Fonctions pour la modale d'édition
  const openEditModal = (contrat) => {
    setCurrentContrat(contrat);
    setIsEditModalOpen(true);
  };

  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentContrat(null);
  };


  // Gère la soumission du formulaire de modification depuis la modale
  const handleUpdateContrat = async (updatedContrat) => {
    try {
      const response = await fetch(`${api}/${updatedContrat.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(updatedContrat),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }

      await fetchContrats(); // Recharger les données pour resynchroniser
      closeEditModal();
      setMessage({ type: 'success', text: 'Contrat mis à jour avec succès !' });
    
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour du contrat: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la création d'un nouveau contrat (appelé par la modale de création)
  const handleCreateContrat = async (newContratData) => {
    try {
      const response = await fetch(api, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(newContratData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }

      await fetchContrats(); // Recharger les données pour resynchroniser
      closeCreateModal();
      setMessage({ type: 'success', text: 'Contrat ajouté avec succès !' });
   
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout du contrat: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la suppression d'un contrat (Delete)
  const handleDelete = async (id) => {
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
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }

        await fetchContrats(); // Recharger les données pour resynchroniser
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
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer ce contrat ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="text-center text-gray-600 dark:text-gray-400">
        Chargement des **contrats**...
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
        Gestion des Contrats
      </h2>

      {/* Bouton pour ouvrir le formulaire de création en modale */}
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
          <PlusCircle className="h-5 w-5" />
          Ajouter un nouveau fournisseur
        </button>
      </div>

      {/* Tableau d'affichage des contrats */}
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Libellé</th>
              <th className="px-4 py-2 text-left">Objet</th>
              <th className="px-4 py-2 text-left">Date début</th>
              <th className="px-4 py-2 text-left">Date fin</th>
              <th className="px-4 py-2 text-left">Type</th>
              <th className="px-4 py-2 text-left">Statut</th>
              <th className="px-4 py-2 text-left">Montant</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {filteredContrats.length === 0 ? (
              <tr>
                <td
                  colSpan="8"
                  className="px-4 py-4 text-center text-gray-500 dark:text-gray-400"
                >
                  Aucun **contrat** trouvé.
                </td>
              </tr>
            ) : (
              filteredContrats.map((contrat) => (
                <tr
                  key={contrat.id}
                  className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
                >
                  <td className="px-4 py-2">{contrat.ref}</td>
                  <td className="px-4 py-2">{contrat.objet}</td>
                  <td className="px-4 py-2">{contrat.date_debut}</td>
                  <td className="px-4 py-2">{contrat.date_fin}</td>
                  <td className="px-4 py-2">{contrat.type}</td>
                  <td className="px-4 py-2">{contrat.status}</td>
                  <td className="px-4 py-2">{contrat.montant}</td>
                  <td className="px-4 py-2">
                    <button
                      onClick={() => openEditModal(contrat)}
                      className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-500 mr-2"
                    >
                      <Pencil className="h-5 w-5" />
                    </button>
                    <button
                      onClick={() => handleDelete(contrat.id)}
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

      {/* Modale de modification */}
      {isEditModalOpen && currentContrat && (
        <EditContractModal
          contract={currentContrat}
          onClose={closeEditModal}
          onSave={handleUpdateContrat}
        />
      )}

      {/* Modale de création */}
      {isCreateModalOpen && (
        <CreateContractModal
          onClose={closeCreateModal}
          onSave={handleCreateContrat} // Passe la fonction de création
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
