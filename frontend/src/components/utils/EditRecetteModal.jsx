import React, { useState, useEffect } from 'react';
import AutocompleteSelect from './AutocompleteSelect';
import { X } from 'lucide-react';

function EditRecetteModal({ recetteToEdit, produits, customers, contrats, onClose, onSave, errorMessage, onClearBackendError }) {
  const [formState, setFormState] = useState({
    ...recetteToEdit,
    date_recette: recetteToEdit?.date_recette ? recetteToEdit.date_recette.split(' ')[0] : '',
  });
  const [validationError, setValidationError] = useState('');

  useEffect(() => {
    const quantity = parseFloat(formState.quantity) || 0;
    const price = parseFloat(formState.price) || 0;
    setFormState(prevState => ({ ...prevState, total: (quantity * price).toFixed(2) }));
  }, [formState.quantity, formState.price]);

  useEffect(() => {
    setFormState({
      ...recetteToEdit,
      date_recette: recetteToEdit?.date_recette ? recetteToEdit.date_recette.split(' ')[0] : '',
    });
  }, [recetteToEdit]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormState(prevState => ({ ...prevState, [name]: value }));
  };

  const handleAutocompleteChange = (name, value) => {
    setFormState((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSave(formState);
  };

  const handleCloseError = () => {
    setValidationError('');
    if (onClearBackendError) {
      onClearBackendError();
    }
  };

  const produitNom = produits.find(p => p.id === formState.produit_id)?.name || 'N/A';
  const customerNom = customers.find(c => c.id === formState.customer_id)?.name || 'N/A';

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[500px] flex flex-col overflow-hidden">
        <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">
          Modifier la Recette
        </h3>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="editRecetteForm" onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Référence <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="name"
                name="name"
                value={formState.name}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label htmlFor="produit_nom" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Produit :
              </label>
              <input
                type="text"
                id="produit_nom"
                name="produit_nom"
                value={produitNom}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400"
                disabled
                readOnly
              />
            </div>
            <div>
              <label htmlFor="customer_nom" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Client :
              </label>
              <input
                type="text"
                id="customer_nom"
                name="customer_nom"
                value={customerNom}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400"
                disabled
                readOnly
              />
            </div>
            <div>
              <label htmlFor="contrat_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Contrat (Optionnel)
              </label>
              <AutocompleteSelect
                options={contrats.map(c => ({ ...c, name: `${c.name} (${c.objet})` }))}
                value={formState.contrat_id}
                onChange={(value) => handleAutocompleteChange('contrat_id', value)}
                placeholder="Rechercher par réf. ou objet"
              />
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label htmlFor="quantity" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité <span className="text-red-500">*</span></label>
                <input type="number" id="quantity" name="quantity" value={formState.quantity} onChange={handleChange} required min="1" className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
              <div>
                <label htmlFor="price" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix unitaire <span className="text-red-500">*</span></label>
                <input type="number" id="price" name="price" value={formState.price} onChange={handleChange} required min="0.01" step="0.01" className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
              <div>
                <label htmlFor="total" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Total</label>
                <input type="text" id="total" name="total" value={formState.total} readOnly className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 cursor-not-allowed" />
              </div>
              <div>
                <label htmlFor="date_recette" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                <input type="date" id="date_recette" name="date_recette" value={formState.date_recette} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label htmlFor="nature" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nature</label>
                <select id="nature" name="nature" value={formState.nature} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                  <option value="vente">Vente</option>
                  <option value="location">Location</option>
                </select>
              </div>
              <div>
                <label htmlFor="category" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Catégorie</label>
                <select id="category" name="category" value={formState.category} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                  <option value="construction">Construction</option>
                  <option value="sécurité">Sécurité</option>
                  <option value="hygiène">Hygiène</option>
                  <option value="entretien">Entretien</option>
                  <option value="logistique">Logistique</option>
                  <option value="mobilité">Mobilité</option>
                </select>
              </div>
            </div>
            <div>
              <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
              <textarea id="description" name="description" value={formState.description} onChange={handleChange} rows="2" className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
            </div>
          </form>
        </div>
        <div className="flex flex-col p-4 border-t dark:border-gray-700">
          {(errorMessage || validationError) && (
            <div className="relative mb-3 text-red-500 text-sm bg-red-100 border border-red-400 rounded p-3 pr-10">
              <span>{validationError || errorMessage}</span>
              <button
                onClick={handleCloseError}
                className="absolute top-1/2 right-2 transform -translate-y-1/2 text-red-500 hover:text-red-700"
                aria-label="Fermer la notification"
              >
                <X size={18} />
              </button>
            </div>
          )}
          <div className="flex justify-end space-x-3">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700"
            >
              Annuler
            </button>
            <button
              type="submit"
              form="editRecetteForm"
              className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800"
            >
              Enregistrer
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default EditRecetteModal;
