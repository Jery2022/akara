import React, { useState } from 'react';
import { createPortal } from 'react-dom';

/**
 * Composant de modale pour la création d'un nouvel achat.
 * Il gère l'état du formulaire et appelle une fonction onSave
 * fournie par le composant parent à la soumission.
 * @param {object} props Les propriétés du composant.
 * @param {Function} props.onClose Fonction pour fermer la modale.
 * @param {Function} props.onSave Fonction pour sauvegarder le nouvel achat.
 * @param {Array} props.suppliers La liste des fournisseurs pour le sélecteur.
 * @param {Array} props.contrats La liste des contrats pour le sélecteur.
 */
function CreateAchatModal ({ onClose, onSave, suppliers, contrats }) {
    // Initialisation de l'état du formulaire avec des valeurs par défaut.
    const [formState, setFormState] = useState({
        type: '',
        amount: '',
        date_achat: new Date().toISOString().slice(0, 10),
        category: '',
        supplier_id: '',
        contrat_id: '',
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
                <h2 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">Ajouter un Achat</h2>
                <div className="flex-grow overflow-y-auto p-6">
                    <form id="achatForm" onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {/* Type de l'achat */}
                            <div>
                                <label htmlFor="type" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Type <span className="text-red-500">*</span></label>
                                <input type="text" id="type" name="type" value={formState.type} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                            </div>
                            {/* Montant */}
                            <div>
                                <label htmlFor="amount" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Montant <span className="text-red-500">*</span></label>
                                <input type="number" id="amount" name="amount" value={formState.amount} onChange={handleChange} required min="0.01" step="0.01" className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                            </div>
                            {/* Date d'achat */}
                            <div>
                                <label htmlFor="date_achat" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de l'achat</label>
                                <input type="date" id="date_achat" name="date_achat" value={formState.date_achat} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                            </div>
                            {/* Catégorie */}
                            <div>
                                <label htmlFor="category" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Catégorie <span className="text-red-500">*</span></label>
                                <input type="text" id="category" name="category" value={formState.category} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                            </div>
                            {/* Fournisseur (select) */}
                            <div>
                                <label htmlFor="supplier_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Fournisseur</label>
                                <select id="supplier_id" name="supplier_id" value={formState.supplier_id} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                    <option value="">Sélectionnez un fournisseur</option>
                                    {suppliers.map(s => (<option key={s.id} value={s.id}>{s.name}</option>))}
                                </select>
                            </div>
                            {/* Contrat (select) */}
                            <div>
                                <label htmlFor="contrat_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Contrat</label>
                                <select id="contrat_id" name="contrat_id" value={formState.contrat_id} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                    <option value="">Sélectionnez un contrat</option>
                                    {contrats.map(c => (<option key={c.id} value={c.id}>{c.ref}</option>))}
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
                    <button type="submit" form="achatForm" className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800">Ajouter</button>
                </div>
            </div>
        </div>,
        document.body
    );
};

export default CreateAchatModal;
