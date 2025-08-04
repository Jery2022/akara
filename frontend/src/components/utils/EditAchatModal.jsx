
import React, { useState} from 'react';
import { createPortal } from 'react-dom';

/**
 * Composant de modale pour la modification d'un achat existant.
 * @param {object} props Les propriétés du composant.
 * @param {object} props.achatToEdit L'objet achat à modifier.
 * @param {Array} props.suppliers La liste des fournisseurs pour le sélecteur.
 * @param {Array} props.contrats La liste des contrats pour le sélecteur.
 * @param {Function} props.onClose Fonction pour fermer la modale.
 * @param {Function} props.onSave Fonction pour sauvegarder les modifications.
 */
function EditAchatModal ({ achatToEdit, onClose, onSave, suppliers, contrats }) {
    // Initialise l'état du formulaire avec les données de l'achat à modifier.
    // La date est formatée pour être compatible avec l'input de type 'date'.
    const [formState, setFormState] = useState({
        ...achatToEdit,
        date_achat: achatToEdit?.date_achat ? achatToEdit.date_achat.split(' ')[0] : '',
        // Convertit les IDs optionnels en chaînes vides pour le sélecteur si null
        supplier_id: achatToEdit?.supplier_id || '',
        contrat_id: achatToEdit?.contrat_id || '',
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

    // La modale n'est rendue que si un objet achatToEdit est fourni
    if (!achatToEdit) {
        return null;
    }

    return createPortal(
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div className="bg-white p-6 rounded-xl shadow-2xl max-w-lg w-full">
                <h2 className="text-2xl font-bold mb-4 text-gray-800">Modifier l'Achat</h2>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {/* Type de l'achat */}
                        <div>
                            <label htmlFor="edit-type" className="block text-sm font-medium text-gray-700">Type <span className="text-red-500">*</span></label>
                            <input type="text" id="edit-type" name="type" value={formState.type} onChange={handleChange} required className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        {/* Montant */}
                        <div>
                            <label htmlFor="edit-amount" className="block text-sm font-medium text-gray-700">Montant <span className="text-red-500">*</span></label>
                            <input type="number" id="edit-amount" name="amount" value={formState.amount} onChange={handleChange} required min="0.01" step="0.01" className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        {/* Date d'achat */}
                        <div>
                            <label htmlFor="edit-date_achat" className="block text-sm font-medium text-gray-700">Date de l'achat</label>
                            <input type="date" id="edit-date_achat" name="date_achat" value={formState.date_achat} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        {/* Catégorie */}
                        <div>
                            <label htmlFor="edit-category" className="block text-sm font-medium text-gray-700">Catégorie <span className="text-red-500">*</span></label>
                            <input type="text" id="edit-category" name="category" value={formState.category} onChange={handleChange} required className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        {/* Fournisseur (select) */}
                        <div>
                            <label htmlFor="edit-supplier_id" className="block text-sm font-medium text-gray-700">Fournisseur</label>
                            <select id="edit-supplier_id" name="supplier_id" value={formState.supplier_id || ''} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Sélectionnez un fournisseur</option>
                                {suppliers.map(s => (<option key={s.id} value={s.id}>{s.name}</option>))}
                            </select>
                        </div>
                        {/* Contrat (select) */}
                        <div>
                            <label htmlFor="edit-contrat_id" className="block text-sm font-medium text-gray-700">Contrat</label>
                            <select id="edit-contrat_id" name="contrat_id" value={formState.contrat_id || ''} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Sélectionnez un contrat</option>
                                {contrats.map(c => (<option key={c.id} value={c.ref}>{c.ref}</option>))}
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

export default EditAchatModal; 