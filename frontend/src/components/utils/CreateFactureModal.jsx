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
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div className="bg-white p-6 rounded-xl shadow-2xl max-w-lg w-full">
                <h2 className="text-2xl font-bold mb-4 text-gray-800">Créer une Facture</h2>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {/* Montant de la facture */}
                        <div>
                            <label htmlFor="amount" className="block text-sm font-medium text-gray-700">Montant <span className="text-red-500">*</span></label>
                            <input type="number" id="amount" name="amount" value={formState.amount} onChange={handleChange} required min="0.01" step="0.01" className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        {/* Date d'émission */}
                        <div>
                            <label htmlFor="date_emission" className="block text-sm font-medium text-gray-700">Date d'émission</label>
                            <input type="date" id="date_emission" name="date_emission" value={formState.date_emission} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        {/* Date d'échéance */}
                        <div>
                            <label htmlFor="date_echeance" className="block text-sm font-medium text-gray-700">Date d'échéance</label>
                            <input type="date" id="date_echeance" name="date_echeance" value={formState.date_echeance} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        {/* Client (select) */}
                        <div>
                            <label htmlFor="customer_id" className="block text-sm font-medium text-gray-700">Client <span className="text-red-500">*</span></label>
                            <select id="customer_id" name="customer_id" value={formState.customer_id} onChange={handleChange} required className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Sélectionnez un client</option>
                                {customers.map(c => (<option key={c.id} value={c.id}>{c.name}</option>))}
                            </select>
                        </div>
                        {/* Contrat (select) */}
                        <div>
                            <label htmlFor="contrat_id" className="block text-sm font-medium text-gray-700">Contrat</label>
                            <select id="contrat_id" name="contrat_id" value={formState.contrat_id || ''} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Sélectionnez un contrat</option>
                                {contrats.map(c => (<option key={c.id} value={c.id}>{c.ref}</option>))}
                            </select>
                        </div>
                        {/* Statut (select) */}
                        <div>
                            <label htmlFor="status" className="block text-sm font-medium text-gray-700">Statut</label>
                            <select id="status" name="status" value={formState.status} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                                <option value="pending">En attente</option>
                                <option value="paid">Payée</option>
                                <option value="overdue">En retard</option>
                            </select>
                        </div>
                    </div>
                    {/* Description (textarea) */}
                    <div>
                        <label htmlFor="description" className="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" value={formState.description} onChange={handleChange} rows="3" className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                    <div className="flex justify-end space-x-2 mt-6">
                        <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">Annuler</button>
                        <button type="submit" className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors shadow-md">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>,
        document.body
    );
};

export default CreateFactureModal