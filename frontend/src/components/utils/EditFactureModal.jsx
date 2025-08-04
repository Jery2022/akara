
import React, { useState } from 'react';
import { createPortal } from 'react-dom';

/**
 * Composant de modale pour la modification d'une facture existante.
 * @param {object} props Les propriétés du composant.
 * @param {object} props.factureToEdit L'objet facture à modifier.
 * @param {Array} props.customers La liste des clients pour le sélecteur.
 * @param {Array} props.contrats La liste des contrats pour le sélecteur.
 * @param {Function} props.onClose Fonction pour fermer la modale.
 * @param {Function} props.onSave Fonction pour sauvegarder les modifications.
 */
function EditFactureModal ({ factureToEdit, onClose, onSave, customers, contrats }) {
    // Initialise l'état du formulaire avec les données de la facture à modifier.
    const [formState, setFormState] = useState({
        ...factureToEdit,
        date_emission: factureToEdit?.date_emission ? factureToEdit.date_emission.split(' ')[0] : '',
        date_echeance: factureToEdit?.date_echeance ? factureToEdit.date_echeance.split(' ')[0] : '',
        customer_id: factureToEdit?.customer_id || '',
        contrat_id: factureToEdit?.contrat_id || '',
    });

    // Gère les changements dans les champs du formulaire.
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormState(prevState => ({ ...prevState, [name]: value }));
    };

    // Gère la soumission du formulaire, appelle la fonction onSave.
    const handleSubmit = (e) => {
        e.preventDefault();
        onSave(formState);
    };

    // La modale n'est rendue que si un objet factureToEdit est fourni
    if (!factureToEdit) {
        return null;
    }

    return createPortal(
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div className="bg-white p-6 rounded-xl shadow-2xl max-w-lg w-full">
                <h2 className="text-2xl font-bold mb-4 text-gray-800">Modifier la Facture</h2>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {/* Montant de la facture */}
                        <div>
                            <label htmlFor="edit-amount" className="block text-sm font-medium text-gray-700">Montant <span className="text-red-500">*</span></label>
                            <input type="number" id="edit-amount" name="amount" value={formState.amount} onChange={handleChange} required min="0.01" step="0.01" className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        {/* Date d'émission */}
                        <div>
                            <label htmlFor="edit-date_emission" className="block text-sm font-medium text-gray-700">Date d'émission</label>
                            <input type="date" id="edit-date_emission" name="date_emission" value={formState.date_emission} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        {/* Date d'échéance */}
                        <div>
                            <label htmlFor="edit-date_echeance" className="block text-sm font-medium text-gray-700">Date d'échéance</label>
                            <input type="date" id="edit-date_echeance" name="date_echeance" value={formState.date_echeance} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        {/* Client (select) */}
                        <div>
                            <label htmlFor="edit-customer_id" className="block text-sm font-medium text-gray-700">Client <span className="text-red-500">*</span></label>
                            <select id="edit-customer_id" name="customer_id" value={formState.customer_id || ''} onChange={handleChange} required className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Sélectionnez un client</option>
                                {customers.map(c => (<option key={c.id} value={c.id}>{c.name}</option>))}
                            </select>
                        </div>
                        {/* Contrat (select) */}
                        <div>
                            <label htmlFor="edit-contrat_id" className="block text-sm font-medium text-gray-700">Contrat</label>
                            <select id="edit-contrat_id" name="contrat_id" value={formState.contrat_id || ''} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Sélectionnez un contrat</option>
                                {contrats.map(c => (<option key={c.id} value={c.id}>{c.ref}</option>))}
                            </select>
                        </div>
                        {/* Statut (select) */}
                        <div>
                            <label htmlFor="edit-status" className="block text-sm font-medium text-gray-700">Statut</label>
                            <select id="edit-status" name="status" value={formState.status} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                                <option value="pending">En attente</option>
                                <option value="paid">Payée</option>
                                <option value="overdue">En retard</option>
                            </select>
                        </div>
                    </div>
                    {/* Description (textarea) */}
                    <div>
                        <label htmlFor="edit-description" className="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="edit-description" name="description" value={formState.description || ''} onChange={handleChange} rows="3" className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                    <div className="flex justify-end space-x-2 mt-6">
                        <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">Annuler</button>
                        <button type="submit" className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors shadow-md">Modifier</button>
                    </div>
                </form>
            </div>
        </div>,
        document.body
    );
};

export default EditFactureModal