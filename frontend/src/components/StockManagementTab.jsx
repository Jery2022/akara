import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreateStockModal from './utils/CreateStockModal';
import EditStockModal from './utils/EditStockModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';

// Composant de l'onglet Stock
function StockManagementTab({ api }) {
  const [items, setItems] = useState([]);
  const [processedItems, setProcessedItems] = useState([]);

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentStockItem, setCurrentStockItem] = useState(null);
  const [createError, setCreateError] = useState(null);

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);

  // État pour la recherche
  const [search, setSearch] = useState('');
  const [sortCriteria, setSortCriteria] = useState('produit_nom_asc');


  // Fonction pour charger les articles de stock (Read)
  const fetchItems = useCallback(async () => {
    const token = localStorage.getItem('authToken');
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${api}/stock`, {
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
        setItems(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau d\'articles en stock :', result);
        setItems([]);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des articles en stock :', err);
      setError(`Impossible de charger les articles en stock. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, setItems]);

  // Charger les articles de stock au montage du composant
  useEffect(() => {
    fetchItems();
  }, [fetchItems]);

  useEffect(() => {
    if (Array.isArray(items)) {
      const newProcessedItems = items.map(item => ({
        ...item,
        // Assurez-vous que les champs de tri textuels existent
        produit_nom: item.produit_nom || '',
        entrepot_nom: item.entrepot_nom || '',
      }));
      setProcessedItems(newProcessedItems);
    }
  }, [items]);

  // Filtrer et trier les articles
  const sortedAndFilteredItems = (Array.isArray(processedItems) ? processedItems : [])
    .filter((item) =>
      item.produit_nom?.toLowerCase().includes(search.toLowerCase()) ||
      item.unit?.toLowerCase().includes(search.toLowerCase()) ||
      item.entrepot_nom?.toLowerCase().includes(search.toLowerCase()) ||
      item.supplier_name?.toLowerCase().includes(search.toLowerCase())
    )
    .sort((a, b) => {
      const parts = sortCriteria.split('_');
      const order = parts.pop();
      const key = parts.join('_');
      
      const valA = a[key];
      const valB = b[key];

      // Gérer les cas où les valeurs pourraient être null ou undefined
      if (valA == null || valB == null) {
        return 0;
      }

      let comparison = 0;
      if (typeof valA === 'string' && typeof valB === 'string') {
        comparison = valA.localeCompare(valB);
      } else if (typeof valA === 'number' && typeof valB === 'number') {
        comparison = valA - valB;
      }

      return order === 'asc' ? comparison : -comparison;
    });

  // --- Fonctions pour les modales ---
  const openCreateModal = () => {
    setCreateError(null); // Réinitialiser l'erreur lors de l'ouverture
    setIsCreateModalOpen(true);
  };
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (item) => {
    setCurrentStockItem(item);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentStockItem(null);
  };

  const clearCreateError = () => setCreateError(null);

  // --- Gestion des opérations CRUD ---

  // Gère la création d'un nouvel article de stock
  const handleCreateItem = async (newItemData) => {
    const token = localStorage.getItem('authToken');
    setSaving(true);
    setCreateError(null);
    try {
      const response = await fetch(`${api}/stock`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newItemData),
      });

      const result = await response.json();

      if (!response.ok) {
        // Si le statut est 409, c'est une erreur de conflit (doublon)
        if (response.status === 409) {
          setCreateError(result.message); // Afficher le message d'erreur dans la modale
        } else {
          throw new Error(result.message || `Erreur HTTP ${response.status}`);
        }
      } else {
        await fetchItems();
        closeCreateModal();
        setMessage({ type: 'success', text: 'Article de stock ajouté avec succès !' });
      }
    } catch (err) {
      // Pour les autres erreurs, on peut les afficher dans le MessageDisplay global
      setMessage({ type: 'error', text: `Erreur lors de l'ajout de l'article.` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la mise à jour d'un article de stock
  const handleUpdateItem = async (updatedItemData) => {
    const token = localStorage.getItem('authToken');
    setSaving(true);
    try {
      // Filtrer les données pour ne garder que les champs attendus par le backend
      const payload = {
        id: updatedItemData.id,
        produit_id: updatedItemData.produit_id,
        quantity: updatedItemData.quantity,
        unit: updatedItemData.unit,
        min: updatedItemData.min,
        supplier_id: updatedItemData.supplier_id,
        entrepot_id: updatedItemData.entrepot_id,
      };

      const response = await fetch(`${api}/stock/${payload.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }
      
      await fetchItems();
      closeEditModal();
      setMessage({ type: 'success', text: 'Article de stock mis à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour de l'article: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la suppression d'un article de stock
  const handleDelete = (itemId) => {
    setConfirmAction(() => async () => {
      const token = localStorage.getItem('authToken');
      setSaving(true);
      try {
        const response = await fetch(`${api}/stock/${itemId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }
        
        await fetchItems();
        setMessage({ type: 'success', text: 'Article de stock supprimé avec succès !' });
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
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer cet article de stock ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600 dark:text-gray-400">
          Chargement des **articles de stock**...
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
        Gestion des Stocks
      </h2>
      <header className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher un article..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full md:w-64 p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <select
            value={sortCriteria}
            onChange={(e) => setSortCriteria(e.target.value)}
            className="w-full md:w-auto p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="produit_nom_asc">Produit (A-Z)</option>
            <option value="produit_nom_desc">Produit (Z-A)</option>
            <option value="quantity_asc">Quantité (Croissant)</option>
            <option value="quantity_desc">Quantité (Décroissant)</option>
            <option value="entrepot_nom_asc">Entrepôt (A-Z)</option>
            <option value="entrepot_nom_desc">Entrepôt (Z-A)</option>
          </select>
          <button
            onClick={openCreateModal}
            className="flex items-center justify-center space-x-2 w-full md:w-auto bg-emerald-600 text-white font-bold py-2 px-4 rounded-md shadow-md hover:bg-emerald-700 transition-colors duration-200"
            disabled={saving}
          >
            <PlusCircle size={20} />
            <span>Ajouter un article</span>
          </button>
        </div>
      </header>

      <div className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        {sortedAndFilteredItems.length === 0 ? (
          <p className="text-center text-gray-500 dark:text-gray-400">Aucun article de stock trouvé.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-gray-200 dark:bg-gray-700">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Produit</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Quantité</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Unité</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Min</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Fournisseur</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Entrepôt</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                {sortedAndFilteredItems.map((item) => (
                  <tr key={item.id} className="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{item.produit_nom || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{item.quantity}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{item.unit}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{item.min}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{item.supplier_name || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{item.entrepot_nom || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{item.quantity <= item.min ? (<span className="text-red-600 dark:text-red-400 font-medium">En rupture</span>) : (<span className="text-green-600 dark:text-green-400 font-medium">OK</span>)}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => openEditModal(item)}
                          className="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors duration-150"
                          aria-label="Modifier l'article"
                          disabled={saving}
                        >
                          <Pencil size={20} />
                        </button>
                        <button
                          onClick={() => handleDelete(item.id)}
                          className="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-150"
                          aria-label="Supprimer l'article"
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
        <CreateStockModal
          api={api}
          onClose={closeCreateModal}
          onSave={handleCreateItem}
          loading={saving}
          errorMessage={createError}
          existingStockItems={items}
          onClearBackendError={clearCreateError}
        />
      )}
      
      {/* Modale de modification */}
      {isEditModalOpen && currentStockItem && (
        <EditStockModal
          api={api}
          onClose={closeEditModal}
          onSave={handleUpdateItem}
          stockItem={currentStockItem}
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

export default StockManagementTab;
