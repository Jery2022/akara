import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import AutocompleteSelect from './AutocompleteSelect';
import { X } from 'lucide-react';

function EditDepenseModal({ api, depenseToEdit, onClose, onSave, loading, errorMessage, onClearBackendError }) {
  const [formState, setFormState] = useState({
    id: '',
    name: '',
    produit_id: '',
    suppliers_id: '',
    contrat_id: '',
    quantity: '',
    price: '',
    date_depense: '',
    description: '',
    nature: 'achat',
    category: 'fournitures'
  });

  const [produits, setProduits] = useState([]);
  const [suppliers, setSuppliers] = useState([]);
  const [contrats, setContrats] = useState([]);
  const [validationError, setValidationError] = useState('');
  const token = localStorage.getItem('authToken');

  useEffect(() => {
    const fetchData = async (endpoint, setter) => {
      try {
        const response = await fetch(`${api}/${endpoint}`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!response.ok) throw new Error(`Failed to fetch ${endpoint}`);
        const result = await response.json();
        setter(result.data || []);
      } catch (error) {
        console.error(`Error fetching ${endpoint}:`, error);
        setValidationError(`Impossible de charger les ${endpoint}.`);
      }
    };

    if (api) {
      fetchData('produits', setProduits);
      fetchData('suppliers', setSuppliers);
      fetchData('contrats', setContrats);
    }
  }, [api, token]);

  useEffect(() => {
    if (depenseToEdit) {
      setFormState({
        id: depenseToEdit.id,
        name: depenseToEdit.name || '',
        produit_id: depenseToEdit.produit_id || '',
        suppliers_id: depenseToEdit.suppliers_id || '',
        contrat_id: depenseToEdit.contrat_id || '',
        quantity: depenseToEdit.quantity || '',
        price: depenseToEdit.price || '',
        date_depense: depenseToEdit.date_depense ? depenseToEdit.date_depense.split(' ')[0] : '',
        description: depenseToEdit.description || '',
        nature: depenseToEdit.nature || 'achat',
        category: depenseToEdit.category || 'fournitures'
      });
    }
  }, [depenseToEdit]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormState(prevState => ({ ...prevState, [name]: value }));
  };

  const handleAutocompleteChange = (name, value) => {
    setFormState((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setValidationError('');

    const requiredFields = ['name', 'produit_id', 'quantity', 'price', 'date_depense', 'description', 'nature', 'category'];
    for (const field of requiredFields) {
        if (!formState[field]) {
            setValidationError(`Le champ ${field} est obligatoire.`);
            return;
        }
    }

    const payload = {
      ...formState,
      quantity: Number(formState.quantity),
      price: Number(formState.price),
      produit_id: formState.produit_id || null,
      suppliers_id: formState.suppliers_id || null,
      contrat_id: formState.contrat_id || null,
    };
    onSave(payload);
  };

  const handleCloseError = () => {
    setValidationError('');
    if (onClearBackendError) onClearBackendError();
  };

  if (!depenseToEdit) {
    return null;
  }

  return createPortal(
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[90vh] flex flex-col overflow-hidden">
        <h2 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">Modifier la Dépense</h2>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="editDepenseForm" onSubmit={handleSubmit} className="space-y-4" noValidate>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="sm:col-span-2">
                <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom / Référence : <span className="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value={formState.name} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
              <div className="sm:col-span-2">
                <label htmlFor="produit_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Produit : <span className="text-red-500">*</span></label>
                <AutocompleteSelect
                  options={produits}
                  value={formState.produit_id}
                  onChange={(value) => handleAutocompleteChange('produit_id', value)}
                  placeholder="Rechercher un produit"
                />
              </div>
              <div>
                <label htmlFor="quantity" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité :<span className="text-red-500">*</span></label>
                <input type="number" id="quantity" name="quantity" value={formState.quantity} onChange={handleChange} required min="1" step="1" className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
              <div>
                <label htmlFor="price" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix :<span className="text-red-500">*</span></label>
                <input type="number" id="price" name="price" value={formState.price} onChange={handleChange} required min="0.01" step="0.01" className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
              <div>
                <label htmlFor="date_depense" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de la dépense</label>
                <input type="date" id="date_depense" name="date_depense" value={formState.date_depense} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
              <div>
                <label htmlFor="nature" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nature : <span className="text-red-500">*</span></label>
                <select id="nature" name="nature" value={formState.nature} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                  <option value="achat">Achat</option>
                  <option value="location">Location</option>
                  <option value="service">Service</option>
                </select>
              </div>
              <div>
                <label htmlFor="category" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Catégorie :<span className="text-red-500">*</span></label>
                <select id="category" name="category" value={formState.category} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                  <option value="fournitures">Fournitures</option>
                  <option value="électricité">Électricité</option>
                  <option value="téléphone">Téléphone</option>
                  <option value="carburant">Carburant</option>
                  <option value="eau">Eau</option>
                  <option value="mobiliers">Mobiliers</option>
                  <option value="fiscalité">Fiscalité</option>
                  <option value="impôts">Impôts</option>
                  <option value="taxes">Taxes</option>
                </select>
              </div>
              <div className="sm:col-span-2">
                <label htmlFor="suppliers_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Fournisseur</label>
                <AutocompleteSelect
                  options={suppliers}
                  value={formState.suppliers_id}
                  onChange={(value) => handleAutocompleteChange('suppliers_id', value)}
                  placeholder="Rechercher un fournisseur"
                />
              </div>
              <div className="sm:col-span-2">
                <label htmlFor="contrat_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Contrat</label>
                <AutocompleteSelect
                  options={contrats}
                  value={formState.contrat_id}
                  onChange={(value) => handleAutocompleteChange('contrat_id', value)}
                  placeholder="Rechercher un contrat"
                />
              </div>
            </div>
            <div>
              <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Description <span className="text-red-500">*</span></label>
              <textarea id="description" name="description" value={formState.description} onChange={handleChange} rows="3" required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
            </div>
          </form>
        </div>
        <div className="flex flex-col p-4 border-t dark:border-gray-700">
          {(errorMessage || validationError) && (
            <div className="relative mb-3 text-red-500 text-sm bg-red-100 border border-red-400 rounded p-3 pr-10" role="alert">
              <span>{validationError || errorMessage}</span>
              <button onClick={handleCloseError} className="absolute top-1/2 right-2 transform -translate-y-1/2 text-red-500 hover:text-red-700" aria-label="Fermer la notification">
                <X size={18} />
              </button>
            </div>
          )}
          <div className="flex justify-end space-x-3">
            <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700" disabled={loading}>Annuler</button>
            <button type="submit" form="editDepenseForm" className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800" disabled={loading}>Modifier</button>
          </div>
        </div>
      </div>
    </div>,
    document.body
  );
}

export default EditDepenseModal;
