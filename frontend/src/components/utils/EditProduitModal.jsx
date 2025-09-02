// frontend/src/components/utils/EditProduitModal.jsx

import React, { useState, useEffect } from 'react';
import { X } from 'lucide-react';
import AutocompleteSelect from './AutocompleteSelect';

function EditProduitModal({ api, onClose, onSave, produitToEdit, errorMessage, onClearBackendError }) {
  const [formState, setFormState] = useState({
    id: '',
    name: '',
    description: '',
    price: '',
    unit: '',
    provenance: 'local',
    disponibility: 'oui',
    delai_livraison: 0,
    supplier_id: '',
    entrepot_id: ''
  });
  const [suppliers, setSuppliers] = useState([]);
  const [entrepots, setEntrepots] = useState([]);
  const [validationError, setValidationError] = useState('');

  useEffect(() => {
    if (produitToEdit) {
      setFormState({
        id: produitToEdit.id,
        name: produitToEdit.name || '',
        description: produitToEdit.description || '',
        price: produitToEdit.price || '',
        unit: produitToEdit.unit || '',
        provenance: produitToEdit.provenance || 'local',
        disponibility: produitToEdit.disponibility || 'oui',
        delai_livraison: produitToEdit.delai_livraison || 0,
        supplier_id: produitToEdit.supplier_id || '',
        entrepot_id: produitToEdit.entrepot_id || ''
      });
    }
  }, [produitToEdit]);

  useEffect(() => {
    const fetchData = async (url, setData) => {
      const token = localStorage.getItem('authToken');
      try {
        const response = await fetch(url, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        const result = await response.json();
        if (result.data) {
          setData(result.data);
        }
      } catch (error) {
        console.error(`Failed to fetch data from ${url}`, error);
      }
    };

    fetchData(`${api}/suppliers`, setSuppliers);
    fetchData(`${api}/entrepots`, setEntrepots);
  }, [api]);

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

    if (!formState.name || formState.price === '' || !formState.unit) {
      setValidationError('Le nom, le prix et l\'unité du produit sont obligatoires.');
      return;
    }
    if (parseFloat(formState.price) < 0) {
      setValidationError('Le prix ne peut pas être négatif.');
      return;
    }

    onSave(formState);
  };

  const handleCloseError = () => {
    setValidationError('');
    if (onClearBackendError) {
      onClearBackendError();
    }
  };

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[90vh] flex flex-col overflow-hidden">
        <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">Modifier le produit</h3>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="editProduitForm" onSubmit={handleSubmit} className="space-y-4">
            <input type="hidden" name="id" value={formState.id} />
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du produit</label>
              <input type="text" name="name" value={formState.name} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix</label>
              <input type="number" step="0.01" name="price" value={formState.price} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Unité (Ex: kg, L, pcs)</label>
              <input type="text" name="unit" value={formState.unit} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Provenance</label>
              <select name="provenance" value={formState.provenance} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                <option value="local">Locale</option>
                <option value="étranger">Étrangère</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Disponibilité</label>
              <select name="disponibility" value={formState.disponibility} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                <option value="oui">Oui</option>
                <option value="non">Non</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Délai de livraison (jours)</label>
              <input type="number" name="delai_livraison" value={formState.delai_livraison} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Fournisseur</label>
              <AutocompleteSelect options={suppliers} value={formState.supplier_id} onChange={(value) => handleAutocompleteChange('supplier_id', value)} placeholder="Rechercher un fournisseur" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Entrepôt</label>
              <AutocompleteSelect options={entrepots} value={formState.entrepot_id} onChange={(value) => handleAutocompleteChange('entrepot_id', value)} placeholder="Rechercher un entrepôt" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
              <textarea name="description" value={formState.description} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
          </form>
        </div>
        <div className="flex flex-col p-4 border-t dark:border-gray-700">
          {(errorMessage || validationError) && (
            <div className="relative mb-3 text-red-500 text-sm bg-red-100 border border-red-400 rounded p-3 pr-10">
              <span>{validationError || errorMessage}</span>
              <button onClick={handleCloseError} className="absolute top-1/2 right-2 transform -translate-y-1/2 text-red-500 hover:text-red-700" aria-label="Fermer la notification">
                <X size={18} />
              </button>
            </div>
          )}
          <div className="flex justify-end space-x-3">
            <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700">
              Annuler
            </button>
            <button type="submit" form="editProduitForm" className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800">
              Enregistrer les modifications
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default EditProduitModal;
