// Recettes Tab Component

import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreateRecetteModal from './utils/CreateRecetteModal';
import EditRecetteModal from './utils/EditRecetteModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';


/**
 * Composant pour la gestion de l'onglet des recettes.
 * Affiche une liste de recettes et permet les opérations CRUD (Créer, Lire, Mettre à jour, Supprimer).
 * Les données sont gérées par un composant parent pour une source de vérité unique.
 *
 * @param {object[]} recettes - La liste des recettes à afficher.
 * @param {object[]} produits - La liste des produits à afficher.
 * @param {object[]} customers - La liste des clients pour afficher les noms.
 * @param {object[]} contrats - La liste des contrats pour afficher les noms.
 * @param {string} api - L'URL de l'API pour les recettes.
 * @param {function} refetchRecettes - Fonction pour recharger les données après une opération.
 */
function RecettesTab({ 
  recettes, setRecettes, 
  produits, setProduits,
  customers, setCustomers,
  contrats, setContrats,
  api,
  refetchRecettes
}) {

  const token = localStorage.getItem('authToken');

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(true);
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

  // // Fonction d'aide pour trouver un client par son ID
  // const getCustomerName = (customerId) => {
  //   const customer = (customers || []).find(c => c.id === customerId);
  //   return customer ? `${customer.refContact}` : 'Inconnu';
  // };

  // // Fonction d'aide pour trouver un produit par son ID
  // const getProduitName = (produitId) => {
  //   const produit = (produits || []).find(p => p.id === produitId);
  //   return produit ? `${produit.name}` : 'Inconnu';
  // };

  // // Fonction d'aide pour trouver un contrat par son ID
  // const getContratRef = (contratId) => {
  //   const contrat = (contrats || []).find(c => c.id === contratId);
  //   return contrat ? `${contrat.ref}` : 'Inconnu';
  // };

  // Fonction générique pour effectuer des appels API
  const apiCall = useCallback(async (url, options = {}) => {
    try {
      const response = await fetch(url, {
        ...options,
        headers: {
          ...options.headers,
          'Authorization': `Bearer ${token}`,
        },
      });
      
      // Vérifie si la réponse est JSON avant de la parser
      const contentType = response.headers.get("content-type");
      if (!response.ok) {
        if (contentType && contentType.includes("application/json")) {
          const errorData = await response.json();
          throw new Error(errorData.message || 'Erreur réseau');
        } else {
          // Si la réponse n'est pas JSON, lisez-la comme du texte
          const errorText = await response.text();
          throw new Error(`Erreur réseau: Réponse du serveur non-JSON (Statut: ${response.status}). Contenu: ${errorText.substring(0, 100)}...`);
        }
      }

      if (contentType && contentType.includes("application/json")) {
        return await response.json();
      } else {
        throw new Error('Réponse de l\'API invalide: le type de contenu n\'est pas application/json.');
      }
    } catch (err) {
      console.error('API Error:', err);
      throw err;
    }
  }, [token]);

  // Fonction principale pour récupérer toutes les données
  const fetchAllData = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const [produitsData, customersData, contratsData, recettesData] = await Promise.all([
        apiCall(`${api}/produits`),
        apiCall(`${api}/customers`),
        apiCall(`${api}/contrats`),
        apiCall(`${api}/recettes`),
      ]);

      //console.log(contratsData); //LOG

      setProduits(produitsData.data || []);
      setCustomers(customersData.data || []);
      setContrats(contratsData.data || []);
      setRecettes(recettesData.data || []);
    } catch (err) {
      setError(`Impossible de charger les données. Détails: ${err.message}`);
    } finally {
      setLoading(false);
    }
  }, [api, apiCall, setProduits, setCustomers, setContrats, setRecettes]);

  //console.log(contrats); //LOG

  useEffect(() => {
    fetchAllData();
  }, [fetchAllData]);

  // Vérifier si les données sont des tableaux valides avant d'effectuer des opérations
  const isRecettesArray = Array.isArray(recettes);
  const isProduitsArray = Array.isArray(produits);
  const isCustomersArray = Array.isArray(customers);
  const isContratsArray = Array.isArray(contrats);

  // Afficher un message de chargement si les données ne sont pas encore prêtes
  if (!isRecettesArray || !isProduitsArray || !isCustomersArray || !isContratsArray) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600">
          Chargement des **recettes**...
        </div>
      </div>
    );
  }

  // Filtrer les recettes pour la recherche
  const filteredRecettes = recettes.filter((recette) => {
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

  const closeMessage = () => {
    setMessage(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'une nouvelle recette
  const handleCreateRecette = async (newRecetteData) => {
    try {
      const payload = {
        ...newRecetteData,
        quantity: parseInt(newRecetteData.quantity, 10),
        price: parseFloat(newRecetteData.price),
        total: parseFloat(newRecetteData.total),
      };

      await apiCall(`${api}/recettes`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      await fetchAllData();
      closeCreateModal();
      setMessage({ type: 'success', text: 'Recette ajoutée avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout de la recette: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la mise à jour d'une recette
  const handleUpdateRecette = async (updatedRecetteData) => {
    try {
      const payload = {
        ...updatedRecetteData,
        quantity: parseInt(updatedRecetteData.quantity, 10),
        price: parseFloat(updatedRecetteData.price),
        total: parseFloat(updatedRecetteData.total),
      };

      await apiCall(`${api}/recettes/${payload.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      
      await fetchAllData();
      closeEditModal();
      setMessage({ type: 'success', text: 'Recette mise à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour de la recette: ${err.message || err.toString()}` });
      console.error(err);
    }
  };

  // Gère la suppression d'une recette
  const handleDeleteRecette = (recetteId) => {
    setConfirmAction(() => async () => {
      try {
        await apiCall(`${api}/recettes/${recetteId}`, {
          method: 'DELETE',
        });
        
        await fetchAllData();
        setMessage({ type: 'success', text: 'Recette supprimée avec succès !' });
      } catch (err) {
        setMessage({ type: 'error', text: `Erreur lors de la suppression: ${err.message || err.toString()}` });
        console.error(err);
      } finally {
        setShowConfirmModal(false);
        setConfirmAction(null);
      }
    });
    setShowConfirmModal(true);
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer cette recette ?' });
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600">
          Chargement des **recettes**...
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-red-600">{error}</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100 p-4 font-sans antialiased">
      <h2 className="text-2xl font-bold text-emerald-700 flex items-center mb-4">
        Gestion des Recettes
      </h2>
      <header className="bg-white shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher une recette..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full md:w-64 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <button
            onClick={openCreateModal}
            className="flex items-center justify-center space-x-2 w-full md:w-auto bg-blue-600 text-white font-bold py-2 px-4 rounded-md shadow-md hover:bg-blue-700 transition-colors duration-200"
          >
            <PlusCircle size={20} />
            <span>Ajouter une recette</span>
          </button>
        </div>
      </header>

      <div className="bg-white shadow-md rounded-lg p-6">
        {filteredRecettes.length === 0 ? (
          <p className="text-center text-gray-500">Aucune recette trouvée.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full bg-white rounded-md overflow-hidden">
              <thead className="bg-gray-200">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">ID</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Produit</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Client</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Contrat</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Quantité</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Prix</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Total</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Date</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {filteredRecettes.map((recette) => (
                  <tr key={recette.id} className="hover:bg-gray-50 transition-colors duration-150">
                    <td className="py-4 px-6 whitespace-nowrap text-sm font-medium text-gray-900">{recette.id}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{produits.find(p => p.id === recette.produit_id)?.name || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{customers.find(c => c.id === recette.customer_id)?.name || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{contrats.find(c => c.id === recette.contrat_id)?.ref || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{recette.quantity}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(recette.price)}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(recette.total)}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{recette.date_recette.split(' ')[0]}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => openEditModal(recette)}
                          className="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                          aria-label="Modifier la recette"
                        >
                          <Pencil size={20} />
                        </button>
                        <button
                          onClick={() => handleDeleteRecette(recette.id)}
                          className="text-red-600 hover:text-red-900 transition-colors duration-150"
                          aria-label="Supprimer la recette"
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
};

export default RecettesTab;
