import React, { useState, useEffect, useCallback } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreatePaymentModal from './utils/CreatePaymentModal';
import EditPaymentModal from './utils/EditPaymentModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';
import { formatDate } from './utils/dateUtils';

// Composant de l'onglet Paiements
function PaymentsTab({ payments: initialPayments, setPayments, customers, employees, api }) {
  const token = localStorage.getItem('authToken');

  // États pour la gestion du chargement et des erreurs
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  // États pour les modales de création/édition
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentPayment, setCurrentPayment] = useState(null);
  const [createError, setCreateError] = useState(null);
  const [editError, setEditError] = useState(null);

  // États pour les messages et la confirmation
  const [message, setMessage] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);

  // État pour la recherche
  const [search, setSearch] = useState('');
  const [sortCriteria, setSortCriteria] = useState('date_payment_desc');

  // Fonction pour charger les paiements (Read)
  const fetchPayments = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${api}/payments`, {
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
        setPayments(result.data);
      } else {
        console.warn('La réponse de l\'API n\'a pas retourné un tableau de paiements :', result);
        setPayments([]);
      }
    } catch (err) {
      console.error('Erreur lors du chargement des paiements :', err);
      setError(`Impossible de charger les paiements. Détails: ${err.message || err.toString()}`);
    } finally {
      setLoading(false);
    }
  }, [api, token, setPayments]);

  // Charger les paiements au montage du composant
  useEffect(() => {
    fetchPayments();
  }, [fetchPayments]);

  // Filtrer et trier les paiements
  const sortedAndFilteredPayments = (Array.isArray(initialPayments) ? initialPayments : [])
    .filter((payment) =>
      payment.description?.toLowerCase().includes(search.toLowerCase()) ||
      payment.customer_name?.toLowerCase().includes(search.toLowerCase()) ||
      payment.employee_name?.toLowerCase().includes(search.toLowerCase()) ||
      payment.type?.toLowerCase().includes(search.toLowerCase())
    )
    .sort((a, b) => {
      const parts = sortCriteria.split('_');
      const order = parts.pop();
      const key = parts.join('_');

      const valA = a[key];
      const valB = b[key];

      if (valA == null || valB == null) {
        return 0;
      }

      let comparison = 0;
      if (typeof valA === 'string' && typeof valB === 'string') {
        comparison = valA.localeCompare(valB);
      } else if (typeof valA === 'number' && typeof valB === 'number') {
        comparison = valA - valB;
      } else if (key === 'date_payment') {
        comparison = new Date(valA) - new Date(valB);
      }

      return order === 'asc' ? comparison : -comparison;
    });

  // --- Fonctions pour les modales ---
  const openCreateModal = () => {
    setCreateError(null);
    setIsCreateModalOpen(true);
  };
  const closeCreateModal = () => setIsCreateModalOpen(false);

  const openEditModal = (payment) => {
    setEditError(null);
    setCurrentPayment(payment);
    setIsEditModalOpen(true);
  };
  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentPayment(null);
  };

  // --- Gestion des opérations CRUD ---

  // Gère la création d'un nouveau paiement
  const handleCreatePayment = async (newPaymentData) => {
    setSaving(true);
    setCreateError(null);
    try {
      const response = await fetch(`${api}/payments`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newPaymentData),
      });

      const result = await response.json();

      if (!response.ok) {
        setCreateError(result.message || `Erreur HTTP ${response.status}`);
      } else {
        await fetchPayments();
        closeCreateModal();
        setMessage({ type: 'success', text: 'Paiement ajouté avec succès !' });
      }
    } catch (err) {
      setCreateError(`Erreur lors de l'ajout du paiement: ${err.message || err.toString()}`);
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la mise à jour d'un paiement
  const handleUpdatePayment = async (updatedPaymentData) => {
    setSaving(true);
    setEditError(null);
    try {
      const response = await fetch(`${api}/payments/${updatedPaymentData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(updatedPaymentData),
      });

      const result = await response.json();

      if (!response.ok) {
        setEditError(result.message || `Erreur HTTP ${response.status}`);
      } else {
        await fetchPayments();
        closeEditModal();
        setMessage({ type: 'success', text: 'Paiement mis à jour avec succès !' });
      }
    } catch (err) {
      setEditError(`Erreur lors de la mise à jour du paiement: ${err.message || err.toString()}`);
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  // Gère la suppression d'un paiement
  const handleDelete = (paymentId) => {
    setConfirmAction(() => async () => {
      setSaving(true);
      try {
        const response = await fetch(`${api}/payments/${paymentId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorBody = await response.json();
          throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
        }
        
        await fetchPayments();
        setMessage({ type: 'success', text: 'Paiement supprimé avec succès !' });
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
    setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer ce paiement ?' });
  };

  // Fonction pour fermer les messages
  const closeMessage = () => {
    setMessage(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 flex items-center justify-center">
        <div className="text-center text-gray-600 dark:text-gray-400">
          Chargement des **paiements**...
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
        Gestion des Paiements
      </h2>
      <header className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
          <input
            type="text"
            placeholder="Rechercher un paiement..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full md:w-64 p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <select
            value={sortCriteria}
            onChange={(e) => setSortCriteria(e.target.value)}
            className="w-full md:w-auto p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="date_payment_desc">Date (Plus récent)</option>
            <option value="date_payment_asc">Date (Plus ancien)</option>
            <option value="amount_desc">Montant (Décroissant)</option>
            <option value="amount_asc">Montant (Croissant)</option>
            <option value="customer_name_asc">Client (A-Z)</option>
            <option value="customer_name_desc">Client (Z-A)</option>
          </select>
          <button
            onClick={openCreateModal}
            className="flex items-center justify-center space-x-2 w-full md:w-auto bg-emerald-600 text-white font-bold py-2 px-4 rounded-md shadow-md hover:bg-emerald-700 transition-colors duration-200"
            disabled={saving}
          >
            <PlusCircle size={20} />
            <span>Ajouter un paiement</span>
          </button>
        </div>
      </header>

      <div className="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        {sortedAndFilteredPayments.length === 0 ? (
          <p className="text-center text-gray-500 dark:text-gray-400">Aucun paiement trouvé.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-gray-200 dark:bg-gray-700">
                <tr>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Client</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Type</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Date</th>
                  <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Employé</th>
                  <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                {sortedAndFilteredPayments.map((payment) => (
                  <tr key={payment.id} className="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                      {payment.customer_name || 'Inconnu'}
                    </td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                      {new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XAF' }).format(payment.amount)}
                    </td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{payment.type}</td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                      {formatDate(payment.date_payment)}
                    </td>
                    <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                      {payment.employee_name || 'N/A'}
                    </td>
                    <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => openEditModal(payment)}
                          className="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors duration-150"
                          aria-label="Modifier le paiement"
                          disabled={saving}
                        >
                          <Pencil size={20} />
                        </button>
                        <button
                          onClick={() => handleDelete(payment.id)}
                          className="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-150"
                          aria-label="Supprimer le paiement"
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
        <CreatePaymentModal
          api={api}
          onClose={closeCreateModal}
          onSave={handleCreatePayment}
          loading={saving}
          errorMessage={createError}
          onClearBackendError={() => setCreateError(null)}
        />
      )}
      
      {/* Modale de modification */}
      {isEditModalOpen && currentPayment && (
        <EditPaymentModal
          api={api}
          onClose={closeEditModal}
          onSave={handleUpdatePayment}
          paymentToEdit={currentPayment}
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

export default PaymentsTab;
