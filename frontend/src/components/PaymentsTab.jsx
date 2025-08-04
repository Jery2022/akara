import React, { useState, useEffect } from 'react';
import ConfirmationModal from './utils/ConfirmationModal';
import MessageDisplay from './utils/MessageDisplay';
import CreatePaymentModal from './utils/CreatePaymentModal';
import EditPaymentModal from './utils/EditPaymentModal';
import { PlusCircle, Pencil, Trash2 } from 'lucide-react';
import { formatDate } from './utils/dateUtils'; // Assurez-vous d'avoir une fonction d'utilitaire pour formater les dates

/**
 * Composant pour la gestion de l'onglet des paiements.
 * Affiche une liste de paiements et permet les opérations CRUD (Créer, Lire, Mettre à jour, Supprimer).
 * Les données sont gérées par un composant parent pour une source de vérité unique.
 *
 * @param {object[]} payments - La liste des paiements à afficher.
 * @param {object[]} customers - La liste des clients pour afficher les noms.
 * @param {object[]} employees - La liste des employés (utilisateurs) pour afficher les noms.
 * @param {string} api - L'URL de l'API pour les paiements.
 * @param {function} refetchPayments - Fonction pour recharger les données après une opération.
 */
function PaymentsTab({ payments:initialPayments, customers, employees, api , refetchPayments }) {
    const token = localStorage.getItem('authToken');

  // Initialiser l'état local des paiements avec un tableau vide pour éviter l'erreur .filter
  const [payments, setLocalPayments] = useState(Array.isArray(initialPayments) ? initialPayments : []);

  // Synchroniser l'état local avec la prop externe si elle change
  useEffect(() => {
    if (Array.isArray(initialPayments)) { 
      setLocalPayments(initialPayments); 
    }
  }, [initialPayments]);

    // États pour les modales de création/édition
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [currentPayment, setCurrentPayment] = useState(null);

    // États pour les messages et la confirmation
    const [message, setMessage] = useState(null);
    const [showConfirmModal, setShowConfirmModal] = useState(false);
    const [confirmAction, setConfirmAction] = useState(null);

    // État pour la recherche
    const [search, setSearch] = useState('');

    // Fonction d'aide pour trouver un client par son ID
    const getCustomerName = (customerId) => {
        const customer = (customers || []).find(c => c.id === customerId);
        return customer ? `${customer.firstName} ${customer.lastName}` : 'Inconnu';
    };

    // Fonction d'aide pour trouver un employé par son ID
    const getEmployeeName = (userId) => {
        const employee = (employees || []).find(e => e.id === userId);
        return employee ? `${employee.firstName} ${employee.lastName}` : 'Inconnu';
    };

    // Filtrer les paiements pour la recherche
    const filteredPayments = (payments || []).filter((payment) =>
        payment.description?.toLowerCase().includes(search.toLowerCase()) ||
        getCustomerName(payment.customer_id)?.toLowerCase().includes(search.toLowerCase()) ||
        getEmployeeName(payment.user_id)?.toLowerCase().includes(search.toLowerCase()) ||
        payment.type?.toLowerCase().includes(search.toLowerCase())
    );

    // --- Fonctions pour les modales ---
    const openCreateModal = () => setIsCreateModalOpen(true);
    const closeCreateModal = () => setIsCreateModalOpen(false);

    const openEditModal = (payment) => {
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
        try {
            const response = await fetch(api, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify(newPaymentData),
            });

            if (!response.ok) {
                const errorBody = await response.json();
                throw new Error(`Erreur lors de la création: ${errorBody.message || response.statusText}`);
            }

            await refetchPayments();
            closeCreateModal();
            setMessage({ type: 'success', text: 'Paiement ajouté avec succès !' });
        } catch (err) {
            setMessage({ type: 'error', text: `Erreur lors de l'ajout du paiement: ${err.message || err.toString()}` });
            console.error(err);
        }
    };

    // Gère la mise à jour d'un paiement
    const handleUpdatePayment = async (updatedPaymentData) => {
        try {
            const response = await fetch(`${api}/${updatedPaymentData.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify(updatedPaymentData),
            });

            if (!response.ok) {
                const errorBody = await response.json();
                throw new Error(`Erreur lors de la mise à jour: ${errorBody.message || response.statusText}`);
            }

            await refetchPayments();
            closeEditModal();
            setMessage({ type: 'success', text: 'Paiement mis à jour avec succès !' });
        } catch (err) {
            setMessage({ type: 'error', text: `Erreur lors de la mise à jour du paiement: ${err.message || err.toString()}` });
            console.error(err);
        }
    };

    // Gère la suppression d'un paiement
    const handleDelete = (paymentId) => {
        setConfirmAction(() => async () => {
            try {
                const response = await fetch(`${api}/${paymentId}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                    },
                });

                if (!response.ok) {
                    const errorBody = await response.json();
                    throw new Error(`Erreur lors de la suppression: ${errorBody.message || response.statusText}`);
                }

                await refetchPayments();
                setMessage({ type: 'success', text: 'Paiement supprimé avec succès !' });
            } catch (err) {
                setMessage({ type: 'error', text: `Erreur lors de la suppression: ${err.message || err.toString()}` });
                console.error(err);
            } finally {
                setShowConfirmModal(false);
                setConfirmAction(null);
            }
        });
        setShowConfirmModal(true);
        setMessage({ type: 'confirm', text: 'Voulez-vous vraiment supprimer ce paiement ?' });
    };

    // Fonction pour fermer les messages
    const closeMessage = () => {
        setMessage(null);
    };

    // // Affichage quand les paiements sont vides
    // if (!payments || payments.length === 0) {
    //     return (
    //         <div className="min-h-screen p-4 flex items-center justify-center">
    //             <div className="text-center text-gray-600 dark:text-gray-400">
    //                 Aucun paiement trouvé.
    //             </div>
    //         </div>
    //     );
    // }

    return (
        <div className="min-h-screen bg-gray-100 p-4 font-sans antialiased">
            <h2 className="text-2xl font-bold text-emerald-700 flex items-center mb-4">
                Gestion des Paiements
            </h2>
            <header className="bg-white shadow-md rounded-lg p-6 mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div className="w-full md:w-auto flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                    <input
                        type="text"
                        placeholder="Rechercher un paiement..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-full md:w-64 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                    <button
                        onClick={openCreateModal}
                        className="flex items-center justify-center space-x-2 w-full md:w-auto bg-blue-600 text-white font-bold py-2 px-4 rounded-md shadow-md hover:bg-blue-700 transition-colors duration-200"
                    >
                        <PlusCircle size={20} />
                        <span>Ajouter un paiement</span>
                    </button>
                </div>
            </header>

            <div className="bg-white shadow-md rounded-lg p-6">
                {filteredPayments.length === 0 ? (
                    <p className="text-center text-gray-500">Aucun paiement trouvé.</p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full bg-white rounded-md overflow-hidden">
                            <thead className="bg-gray-200">
                                <tr>
                                    <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">ID</th>
                                    <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Client</th>
                                    <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Montant</th>
                                    <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Type</th>
                                    <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Date</th>
                                    <th className="py-3 px-6 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">Employé</th>
                                    <th className="py-3 px-6 text-center text-sm font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {filteredPayments.map((payment) => (
                                    <tr key={payment.id} className="hover:bg-gray-50 transition-colors duration-150">
                                        <td className="py-4 px-6 whitespace-nowrap text-sm font-medium text-gray-900">{payment.id}</td>
                                        <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">
                                            {getCustomerName(payment.customer_id)}
                                        </td>
                                        <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">
                                            {new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XAF' }).format(payment.amount)}
                                        </td>
                                        <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">{payment.type}</td>
                                        <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">
                                            {formatDate(payment.date_payment)}
                                        </td>
                                        <td className="py-4 px-6 whitespace-nowrap text-sm text-gray-600">
                                            {getEmployeeName(payment.user_id)}
                                        </td>
                                        <td className="py-4 px-6 whitespace-nowrap text-right text-sm font-medium">
                                            <div className="flex items-center justify-center space-x-2">
                                                <button
                                                    onClick={() => openEditModal(payment)}
                                                    className="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                                                    aria-label="Modifier le paiement"
                                                >
                                                    <Pencil size={20} />
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(payment.id)}
                                                    className="text-red-600 hover:text-red-900 transition-colors duration-150"
                                                    aria-label="Supprimer le paiement"
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
                    onClose={closeCreateModal}
                    onSave={handleCreatePayment}
                    customers={customers}
                    employees={employees}
                />
            )}

            {/* Modale de modification */}
            {isEditModalOpen && currentPayment && (
                <EditPaymentModal
                    onClose={closeEditModal}
                    onSave={handleUpdatePayment}
                    paymentToEdit={currentPayment}
                    customers={customers}
                    employees={employees}
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
