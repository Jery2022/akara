import React, { useState } from 'react';
import { createPortal } from 'react-dom';

/**
 * Composant de modale pour la création d'une nouvelle facture.
 * Il gère l'état du formulaire et appelle une fonction onSave
 * fournie par le composant parent à la soumission.
 * @param {object} props Les propriétés du composant.
 * @param {Function} props.onClose Fonction pour fermer la modale.
 * @param {Function} props.onSave Fonction pour sauvegarder la nouvelle facture.
 * @param {Array} props.customers La liste des clients pour le sélecteur.
 * @param {Array} props.contrats La liste des contrats pour le sélecteur.
 */
function CreateFactureModal ({ onClose, onSave, customers, contrats }) {
    // Initialisation de l'état du formulaire avec des valeurs par défaut.
    const [formState, setFormState] = useState({
        amount: '',
        date_emission: new Date().toISOString().slice(0, 10),
        date_echeance: '',
        customer_id: '',
        contrat_id: '',
        status: 'pending',
        description: ''
    });

    // Gère les changements dans les champs du formulaire.
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormState(prevState => ({
            ...prevState,
            [name]: value
        }));
    };

    // Gère la soumission du formulaire, appelle la fonction onSave.
    const handleSubmit = (e) => {
        e.preventDefault();
        onSave(formState);
        onClose(); // Ferme la modale après la soumission.
    };

    return createPortal(
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[500px] flex flex-col overflow-hidden">
                <h2 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">Créer une Facture</h2>
                <div className="flex-grow overflow-y-auto p-6">
                    <form id="factureForm" onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {/* Montant de la facture */}
                            <div>
                                <label htmlFor="amount" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Montant <span className="text-red-500">*</span></label>
                                <input type="number" id="amount" name="amount" value={formState.amount} onChange={handleChange} required min="0.01" step="0.01" className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                            </div>
                            {/* Date d'émission */}
                            <div>
                                <label htmlFor="date_emission" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date d'émission</label>
                                <input type="date" id="date_emission" name="date_emission" value={formState.date_emission} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                            </div>
                            {/* Date d'échéance */}
                            <div>
                                <label htmlFor="date_echeance" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date d'échéance</label>
                                <input type="date" id="date_echeance" name="date_echeance" value={formState.date_echeance} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                            </div>
                            {/* Client (select) */}
                            <div>
                                <label htmlFor="customer_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Client <span className="text-red-500">*</span></label>
                                <select id="customer_id" name="customer_id" value={formState.customer_id} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                    <option value="">Sélectionnez un client</option>
                                    {customers.map(c => (<option key={c.id} value={c.id}>{c.name}</option>))}
                                </select>
                            </div>
                            {/* Contrat (select) */}
                            <div>
                                <label htmlFor="contrat_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Contrat</label>
                                <select id="contrat_id" name="contrat_id" value={formState.contrat_id || ''} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                    <option value="">Sélectionnez un contrat</option>
                                    {contrats.map(c => (<option key={c.id} value={c.id}>{c.ref}</option>))}
                                </select>
                            </div>
                            {/* Statut (select) */}
                            <div>
                                <label htmlFor="status" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Statut</label>
                                <select id="status" name="status" value={formState.status} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                    <option value="pending">En attente</option>
                                    <option value="paid">Payée</option>
                                    <option value="overdue">En retard</option>
                                </select>
                            </div>
                        </div>
                        {/* Description (textarea) */}
                        <div>
                            <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea id="description" name="description" value={formState.description} onChange={handleChange} rows="3" className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
                        </div>
                    </form>
                </div>
                <div className="flex justify-end space-x-3 p-4 border-t dark:border-gray-700">
                    <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700">Annuler</button>
                    <button type="submit" form="factureForm" className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800">Ajouter</button>
                </div>
            </div>
        </div>,
        document.body
    );
};

export default CreateFactureModal
