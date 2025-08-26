// frontend/src/components/utils/CreateProduitModal.jsx

import React, { useState } from 'react';

function CreateProduitModal({ onClose, onSave }) {
  const [formState, setFormState] = useState({ 
   name: '', 
   description: '', 
   price: '' 
});

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormState(prevState => ({ ...prevState, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSave(formState);
  };

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[500px] flex flex-col overflow-hidden">
        <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">Ajouter un nouveau produit</h3>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="produitForm" onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du produit</label>
              <input
                type="text"
                name="name"
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                value={formState.name}
                onChange={handleChange}
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix</label>
              <input
                type="number"
                step="0.01"
                name="price"
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                value={formState.price}
                onChange={handleChange}
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
              <textarea
                name="description"
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                value={formState.description || ''}
                onChange={handleChange}
              />
            </div>
          </form>
        </div>
        <div className="flex justify-end space-x-3 p-4 border-t dark:border-gray-700">
          <button
            type="button"
            onClick={onClose}
            className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700"
          >
            Annuler
          </button>
          <button
            type="submit"
            form="produitForm"
            className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800"
          >
            Ajouter le produit
          </button>
        </div>
      </div>
    </div>
  );
};

export default CreateProduitModal
