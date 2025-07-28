import React, { useState } from 'react';

function CreateContractModal({ onClose, onSave }) {
  const [newContract, setNewContract] = useState({
    name: '',
    objet: '',
    start_date: '',
    end_date: '',
    sign_date: '',
    type: '',
    amount: '',
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setNewContract((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    // Validation simple des champs
    if (
      !newContract.name ||
      !newContract.objet ||
      !newContract.start_date ||
      !newContract.end_date ||
      !newContract.sign_date ||
      !newContract.type ||
      !newContract.amount
    ) {
      alert('Veuillez remplir tous les champs du nouveau contrat.');
      return;
    }

    onSave(newContract); // Appelle la fonction onSave du parent avec les nouvelles données
  };

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2 relative">
        <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
          Ajouter un nouveau contrat
        </h2>
        <button
          onClick={onClose}
          className="absolute top-3 right-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl"
        >
          &times;
        </button>
        <form
          onSubmit={handleSubmit}
          className="grid grid-cols-1 md:grid-cols-2 gap-4"
        >
          <div>
            <label
              htmlFor="name"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Libellé
            </label>
            <input
              type="text"
              id="name"
              name="name"
              value={newContract.name}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              placeholder="Libellé du contrat"
              required
            />
          </div>
          <div>
            <label
              htmlFor="objet"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Objet
            </label>
            <input
              type="text"
              id="objet"
              name="objet"
              value={newContract.objet}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              placeholder="Objet du contrat"
              required
            />
          </div>
          <div>
            <label
              htmlFor="start_date"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Date de début
            </label>
            <input
              type="date"
              id="start_date"
              name="start_date"
              value={newContract.start_date}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label
              htmlFor="end_date"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Date de fin
            </label>
            <input
              type="date"
              id="end_date"
              name="end_date"
              value={newContract.end_date}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label
              htmlFor="sign_date"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Date de signature
            </label>
            <input
              type="date"
              id="sign_date"
              name="sign_date"
              value={newContract.sign_date}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label
              htmlFor="type"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Type de contrat
            </label>
            <input
              type="text"
              id="type"
              name="type"
              value={newContract.type}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label
              htmlFor="amount"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Montant
            </label>
            <input
              type="number"
              id="amount"
              name="amount"
              value={newContract.amount}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div className="md:col-span-2 flex justify-end gap-3 mt-4">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 hover:bg-gray-50"
            >
              Annuler
            </button>
            <button
              type="submit"
              className="px-4 py-2 bg-emerald-600 text-white font-semibold rounded-md shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:bg-emerald-700 dark:hover:bg-emerald-800"
            >
              Créer le contrat
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default CreateContractModal;
