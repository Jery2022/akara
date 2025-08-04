// frontend/src/components/utils/EditEntrepotModal.jsx

import React, { useState, useEffect } from 'react';

function EditEntrepotModal({ entrepot, onClose, onSave }) {
  const [formData, setFormData] = useState(entrepot);

  useEffect(() => {
    setFormData(entrepot);
  }, [entrepot]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSave(formData);
  };

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full">
        <h3 className="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
          Modifier l'entrepôt ou magasin: {entrepot.name}
        </h3>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label htmlFor="nom" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Nom de l'entrepot ou magasin:
            </label>
            <input
              type="text"
              id="nom"
              name="nom"
              value={formData.name}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label htmlFor="adresse" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Adresse:
            </label>
            <input
              type="text"
              id="adresse"
              name="adresse"
              value={formData.adresse}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label htmlFor="responsable" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Personne responsable:
            </label>
            <input
              type="text"
              id="responsable"
              name="responsable"
              value={formData.responsable}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Email:
            </label>
            <input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label htmlFor="telephone" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Téléphone (ex. +241.xx.xx.xx):
            </label>
            <input
              type="text"
              id="telephone"
              name="telephone"
              value={formData.telephone}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label
              htmlFor="quality_stockage"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Qualité de stockage
            </label>
            <select
              id="quality_stockage"
              name="quality_stockage"
              value={formData.quality_stockage}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            >
              <option value="">Sélectionner une qualité</option> {/* Ajouté */}
              <option value="bonne">Bonne</option>
              <option value="moyenne">Moyenne</option>
              <option value="mauvaise">Mauvaise</option>
            </select>
          </div>
          <div>
            <label
              htmlFor="black_list"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              En liste noire
            </label>
            <select
              id="black_list"
              name="black_list"
              value={formData.black_list}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            >
              <option value="">Sélectionner un statut</option> {/* Ajouté */}
              <option value="oui">Oui</option>
              <option value="non">Non</option>
            </select>
          </div>
          <div className="flex justify-end space-x-3 mt-6">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700"
            >
              Annuler
            </button>
            <button
              type="submit"
              className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800"
            >
              Enregistrer
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default EditEntrepotModal;