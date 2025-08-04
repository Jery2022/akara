import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';  
import MessageDisplay from './utils/MessageDisplay';      
import CreateStockModal from './utils/CreateStockModal';  
import EditStockModal from './utils/EditStockModal';  
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';  

/**
 * Composant pour la gestion de l'onglet du stock.
 * Gère les opérations CRUD et l'affichage des articles de stock.
 * @param {object} props Les propriétés du composant.
 * @param {Array} props.items La liste des articles de stock.
 * @param {Function} props.setItems Fonction pour mettre à jour la liste des articles.
 * @param {string} props.api L'URL de l'API pour les articles de stock.
 */
function StockManagementTab({ items, setItems, api }) {
  const token = localStorage.getItem('authToken');

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentStockItem, setCurrentStockItem] = useState(null); // Article à modifier

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null); // { type: 'success' | 'error', text: '...' }
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null); // Fonction à exécuter si l'utilisateur confirme

  // État pour la recherche
  const [search, setSearch] = useState('');

  // Fonction pour charger les articles de stock (Read)
  const fetchItems = useCallback(async () => {
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

      // Verification de la propriété 'data' comme un tableau
      if (result && Array.isArray(result.data)) {
        setItems(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau d\'articles dans la propriété "data" ou au niveau supérieur :', result);
        setItems([]); // Initialiser à un tableau vide pour éviter les erreurs .map
      }
    } catch (err) {
      console.error('Erreur lors du chargement des articles de stock :', err);
      setError(`Impossible de charger les articles de stock. Veuillez réessayer. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setItems]);

  // Charger les articles de stock au montage du composant
  useEffect(() => {
    fetchItems();
  }, [fetchItems]); 

  // Filtrer les articles pour la recherche en s'assurant que 'items' est un tableau
  const filteredItems = Array.isArray(items) ? items.filter((item) =>
    item.produit_nom?.toLowerCase().includes(search.toLowerCase()) || // Recherche sur le nom du produit
    item.unit?.toLowerCase().includes(search.toLowerCase()) ||
    item.entrepot_nom?.toLowerCase().includes(search.toLowerCase()) ||
    item.supplier_name?.toLowerCase().includes(search.toLowerCase())
  ) : [];

  // --- Fonctions pour les modales ---
  const openCreateModal = () => setIsCreateModalOpen(true);
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (item) => {
    setCurrentStockItem(item);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentStockItem(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'un nouvel article de stock
  const handleCreateItem = async (newItemData) => {
    try {
      const response = await fetch(api, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(newItemData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }

      await fetchItems(); // Recharger les données pour resynchroniser
      closeCreateModal();
      setMessage({ type: 'success', text: 'Article de stock ajouté avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout de l'article: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la mise à jour d'un article de stock
  const handleUpdateItem = async (updatedItemData) => {
    try {
      const response = await fetch(`${api}/${updatedItemData.id}`, { // Utilisation de l'ID dans l'URL
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(updatedItemData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }

      await fetchItems(); // Recharger les données pour resynchroniser
      closeEditModal();
      setMessage({ type: 'success', text: 'Article de stock mis à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour de l'article: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la suppression d'un article de stock (logique)
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

        await fetchItems(); // Recharger les données pour resynchroniser
        setMessage({ type: 'success', text: 'Article de stock supprimé (logiquement) avec succès !' });
      } catch (err) {
        setMessage({ type: 'error', text: `Erreur lors de la suppression: ${err.message || err.toString()}` });
        console.error(err);
      } finally {
        setShowConfirmModal(false);
        setConfirmAction(null);
      }
    });
    setShowConfirmModal(true);
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer (désactiver) cet article de stock ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="text-center text-gray-600 dark:text-gray-400">
        Chargement des **articles de stock**...
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
        Gestion des Stocks
      </h2>

      <div className="mb-4 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 md:space-x-4">
        <input
          type="text"
          placeholder="Rechercher par nom de produit, unité, entrepôt, fournisseur..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-full md:w-2/3 p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 focus:ring-emerald-500 focus:border-emerald-500"
        />
        <button
          onClick={openCreateModal}
          className="w-full md:w-auto px-4 py-2 bg-emerald-600 text-white font-semibold rounded-md shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:bg-emerald-700 dark:hover:bg-emerald-800"
        >
          <PlusCircle className="h-5 w-5" />
          Ajouter un nouvel article
        </button>
      </div>

      <div className="overflow-x-auto rounded-lg shadow-md">
        <table className="min-w-full table-auto text-sm bg-white dark:bg-gray-800">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">ID</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Produit</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Quantité</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Unité</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Min</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Fournisseur</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Entrepôt</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Statut</th>
              <th className="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            {filteredItems.length === 0 ? (
              <tr>
                <td colSpan="9" className="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                  Aucun **article de stock** trouvé.
                </td>
              </tr>
            ) : (
              filteredItems.map((item) => (
                <tr key={item.id} className="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750">
                  <td>{item.id}</td>
                  <td>{item.produit_nom || 'N/A'}</td> {/* Afficher le nom du produit */}
                  <td>{item.quantity}</td>
                  <td>{item.unit}</td>
                  <td>{item.min}</td>
                  <td>{item.supplier_name || 'N/A'}</td> {/* Afficher le nom du fournisseur */}
                  <td>{item.entrepot_nom || 'N/A'}</td> {/* Afficher le nom de l'entrepôt */}
                  <td>{item.quantity <= item.min ? (<span className="text-red-600 font-medium">En rupture</span>) : (<span className="text-green-600 font-medium">OK</span>)}</td>
                  <td className="px-4 py-3 space-x-2"><button onClick={() => openEditModal(item)} className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-500"><Pencil className="h-5 w-5" /></button>
                  <button onClick={() => handleDelete(item.id)} className="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-500"><Trash2 className="h-5 w-5" /></button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Modale de création */}
      {isCreateModalOpen && (
        <CreateStockModal
          onClose={closeCreateModal}
          onSave={handleCreateItem}
        />
      )}

      {/* Modale de modification */}
      {isEditModalOpen && currentStockItem && (
        <EditStockModal
          stockItem={currentStockItem}
          onClose={closeEditModal}
          onSave={handleUpdateItem}
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

export default StockManagementTab;
