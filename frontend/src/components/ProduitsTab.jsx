import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreateProduitModal from './utils/CreateProduitModal';
import EditProduitModal from './utils/EditProduitModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';

// Composant de l'onglet Produits
function ProduitsTab({ produits:initialProduits, setProduits, api }) {
  const token = localStorage.getItem('authToken');

  // Initialiser l'état local des produits avec un tableau vide pour éviter l'erreur .filter
  const [produits, setLocalProduits] = useState(Array.isArray(initialProduits) ? initialProduits : []);

  // Synchroniser l'état local avec la prop externe si elle change
  useEffect(() => {
    if (Array.isArray(initialProduits)) { 
      setLocalProduits(initialProduits); 
    }
  }, [initialProduits]);

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentProduit, setCurrentProduit] = useState(null);

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);

  // État pour la recherche
  const [search, setSearch] = useState('');

  // Fonction pour charger les produits (Read)
  const fetchProduits = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(api, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur HTTP: ${response.status} - ${errorBody.message || response.statusText}`);
      }
      const result = await response.json();

      //console.log(result.data); //log pour débogage

      // Vérification que la propriété 'data' est un tableau
      if (result && Array.isArray(result.data)) {
        setProduits(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de produits :', result);
        setProduits([]);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des produits :', err);
      setError(`Impossible de charger les produits. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setProduits]);

  // Charger les produits au montage du composant 
  useEffect(() => {
    fetchProduits();
  }, [fetchProduits]);

  // Filtrer les produits pour la recherche
  const filteredProduits = (produits || []).filter((produit) =>
    produit.name?.toLowerCase().includes(search.toLowerCase()) ||
    produit.description?.toLowerCase().includes(search.toLowerCase())
  );
  
  // --- Fonctions pour les modales ---
  const openCreateModal = () => setIsCreateModalOpen(true);
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (produit) => {
    setCurrentProduit(produit);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentProduit(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'un nouveau produit
  const handleCreateProduit = async (newProduitData) => {
    try {
      const response = await fetch(api, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newProduitData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }
      
      await fetchProduits();
      closeCreateModal();
      setMessage({ type: 'success', text: 'Produit ajouté avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout du produit: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la mise à jour d'un produit
  const handleUpdateProduit = async (updatedProduitData) => {
    try {
      const response = await fetch(`${api}/${updatedProduitData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(updatedProduitData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }
      
      await fetchProduits();
      closeEditModal();
      setMessage({ type: 'success', text: 'Produit mis à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour du produit: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la suppression d'un produit
  const handleDelete = (produitId) => {
    setConfirmAction(() => async () => {
      try {
        const response = await fetch(`${api}/${produitId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }
        
        await fetchProduits();
        setMessage({ type: 'success', text: 'Produit supprimé avec succès !' });
      } catch (err) {
        setMessage({ type: 'error', text: `Erreur lors de la suppression: ${err.message || err.toString()}` });
        console.error(err);
      } finally {
        setShowConfirmModal(false);
        setConfirmAction(null);
      }
    });
    setShowConfirmModal(true);
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer ce produit ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600 dark:text-gray-400">
          Chargement des **produits**...
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
          Gestion des Produits
      </h2>
      <header className="bg-white shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher un produit..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full md:w-64 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <button
            onClick={openCreateModal}
            className="flex items-center justify-center space-x-2 w-full md:w-auto bg-blue-600 text-white font-bold py-2 px-4 rounded-md shadow-md hover:bg-blue-700 transition-colors duration-200"
          >
            <PlusCircle size={20} />
            <span>Ajouter un produit</span>
          </button>
        </div>
      </header>

      <div className="bg-white shadow-md rounded-lg p-6">
        {filteredProduits.length === 0 ? (
          <p className="text-center text-gray-500">Aucun produit trouvé.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full bg-white rounded-md overflow-hidden">
              <thead className="bg-gray-200">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">ID</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Nom</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Prix</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Description</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {filteredProduits.map((produit) => (
                  <tr key={produit.id} className="hover:bg-gray-50 transition-colors duration-150">
                    <td className="py-4 px-6 whitespace-nowrap text-sm font-medium text-gray-900">{produit.id}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{produit.name}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">
                      {new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(produit.price)}
                    </td>
                    <td className="py-4 px-6 text-sm text-gray-600">{produit.description || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => openEditModal(produit)}
                          className="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                          aria-label="Modifier le produit"
                        >
                          <Pencil size={20} />
                        </button>
                        <button
                          onClick={() => handleDelete(produit.id)}
                          className="text-red-600 hover:text-red-900 transition-colors duration-150"
                          aria-label="Supprimer le produit"
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
        <CreateProduitModal
          onClose={closeCreateModal}
          onSave={handleCreateProduit}
        />
      )}
      
      {/* Modale de modification */}
      {isEditModalOpen && currentProduit && (
        <EditProduitModal
          onClose={closeEditModal}
          onSave={handleUpdateProduit}
          produitToEdit={currentProduit}
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

export default ProduitsTab;
