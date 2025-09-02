// frontend/src/components/utils/EditCustomerModal.jsx

import React, { useState, useEffect } from 'react';

function EditCustomerModal({ api, customer, onClose, onSave }) {
  const [formData, setFormData] = useState(customer);
  const [contrats, setContrats] = useState([]);
  const token = localStorage.getItem('authToken');

  useEffect(() => {
    setFormData(customer);
  }, [customer]);

  useEffect(() => {
    const fetchContrats = async () => {
      try {
        const response = await fetch(`${api}/contrats`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!response.ok) throw new Error('Failed to fetch contrats');
        const result = await response.json();
        if (result.data) {
          // Filtrer les contrats pour ne garder que ceux de type 'client'
          const clientContrats = result.data.filter(c => c.type === 'client');
          setContrats(clientContrats);
        }
      } catch (error) {
        console.error("Erreur lors de la récupération des contrats:", error);
      }
    };
    fetchContrats();
  }, [api, token]);

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
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[500px] flex flex-col overflow-hidden">
        <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">
          Modifier le client
        </h3>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="editCustomerForm" onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nom:
              </label>
              <input
                type="text"
                id="name"
                name="name"
                value={formData.name}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label htmlFor="refContact" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Personne contact:
              </label>
              <input
                type="text"
                id="refContact"
                name="refContact"
                value={formData.refContact}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label htmlFor="phone" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Téléphone:
              </label>
              <input
                type="tel"
                id="phone"
                name="phone"
                value={formData.phone}
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
              <label htmlFor="contrat_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Contrat (Optionnel):
              </label>
              <select
                id="contrat_id"
                name="contrat_id"
                value={formData.contrat_id || ''}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              >
                <option value="">Aucun contrat</option>
                {contrats.map(contrat => (
                  <option key={contrat.id} value={contrat.id}>
                    {contrat.name}
                  </option>
                ))}
              </select>
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
            form="editCustomerForm"
            className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800"
          >
            Enregistrer
          </button>
        </div>
      </div>
    </div>
  );
}

export default EditCustomerModal;
