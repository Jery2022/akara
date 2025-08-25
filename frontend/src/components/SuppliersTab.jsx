import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreateSupplierModal from './utils/CreateSupplierModal';
import EditSupplierModal from './utils/EditSupplierModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';

// Composant de l'onglet Fournisseurs
function SuppliersTab({ suppliers: initialSuppliers, setSuppliers, api }) {
  const token = localStorage.getItem('authToken');

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentSupplier, setCurrentSupplier] = useState(null);

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);

  // État pour la recherche
  const [search, setSearch] = useState('');

  // Fonction pour charger les fournisseurs (Read)
  const fetchSuppliers = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${api}/suppliers`, {
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
        setSuppliers(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de fournisseurs :', result);
        setSuppliers([]);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des fournisseurs :', err);
      setError(`Impossible de charger les fournisseurs. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setSuppliers]);

  // Charger les fournisseurs au montage du composant
  useEffect(() => {
    fetchSuppliers();
  }, [fetchSuppliers]);

  // Filtrer les fournisseurs pour la recherche
  const filteredSuppliers = (Array.isArray(initialSuppliers) ? initialSuppliers : []).filter((supplier) =>
    supplier.name?.toLowerCase().includes(search.toLowerCase()) ||
    supplier.refContact?.toLowerCase().includes(search.toLowerCase()) ||
    supplier.phone?.toLowerCase().includes(search.toLowerCase()) ||
    supplier.email?.toLowerCase().includes(search.toLowerCase())
  );

  // --- Fonctions pour les modales ---
  const openCreateModal = () => setIsCreateModalOpen(true);
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (supplier) => {
    setCurrentSupplier(supplier);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentSupplier(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'un nouveau fournisseur
  const handleCreateSupplier = async (newSupplierData) => {
    setSaving(true);
    try {
      const response = await fetch(`${api}/suppliers`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newSupplierData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
      }
      
      await fetchSuppliers();
      closeCreateModal();
      setMessage({ type: 'success', text: 'Fournisseur ajouté avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de l'ajout du fournisseur: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la mise à jour d'un fournisseur
  const handleUpdateSupplier = async (updatedSupplierData) => {
    setSaving(true);
    try {
      const response = await fetch(`${api}/suppliers/${updatedSupplierData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(updatedSupplierData),
      });

      if (!response.ok) {
        const errorBody = await response.json();
        throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
      }
      
      await fetchSuppliers();
      closeEditModal();
      setMessage({ type: 'success', text: 'Fournisseur mis à jour avec succès !' });
    } catch (err) {
      setMessage({ type: 'error', text: `Erreur lors de la mise à jour du fournisseur: ${err.message || err.toString()}` });
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la suppression d'un fournisseur
  const handleDelete = (supplierId) => {
    setConfirmAction(() => async () => {
      setSaving(true);
      try {
        const response = await fetch(`${api}/suppliers/${supplierId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }
        
        await fetchSuppliers();
        setMessage({ type: 'success', text: 'Fournisseur supprimé avec succès !' });
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
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer ce fournisseur ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600 dark:text-gray-400">
          Chargement des **fournisseurs**...
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
        Gestion des Fournisseurs
      </h2>
      <header className="bg-white shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher un fournisseur..."
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
            <span>Ajouter un fournisseur</span>
          </button>
        </div>
      </header>

      <div className="bg-white shadow-md rounded-lg p-6">
        {filteredSuppliers.length === 0 ? (
          <p className="text-center text-gray-500">Aucun fournisseur trouvé.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full bg-white rounded-md overflow-hidden">
              <thead className="bg-gray-200">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Nom</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Réf. Contact</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Téléphone</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Email</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">ID Contrat</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {filteredSuppliers.map((supplier) => (
                  <tr key={supplier.id} className="hover:bg-gray-50 transition-colors duration-150">
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{supplier.name}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{supplier.refContact}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{supplier.phone}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{supplier.email}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{supplier.contrat_id || 'N/A'}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => openEditModal(supplier)}
                          className="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                          aria-label="Modifier le fournisseur"
                          disabled={saving}
                        >
                          <Pencil size={20} />
                        </button>
                        <button
                          onClick={() => handleDelete(supplier.id)}
                          className="text-red-600 hover:text-red-900 transition-colors duration-150"
                          aria-label="Supprimer le fournisseur"
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
        <CreateSupplierModal
          onClose={closeCreateModal}
          onSave={handleCreateSupplier}
          loading={saving}
        />
      )}
      
      {/* Modale de modification */}
      {isEditModalOpen && currentSupplier && (
        <EditSupplierModal
          onClose={closeEditModal}
          onSave={handleUpdateSupplier}
          supplierToEdit={currentSupplier}
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

export default SuppliersTab;
