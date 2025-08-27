import React, { useState } from 'react';

function CreateContractModal({ onClose, onSave }) {
  const [newContract, setNewContract] = useState({
    ref: '',
    objet: '',
    date_debut: '',
    date_fin: '',
    date_signature: '',
    type: '',
    montant: '',
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
      !newContract.ref ||
      !newContract.objet ||
      !newContract.date_debut ||
      !newContract.date_fin ||
      !newContract.date_signature ||
      !newContract.type ||
      !newContract.montant
    ) {
      alert('Veuillez remplir tous les champs du nouveau contrat.');
      return;
    }

    onSave(newContract); // Appelle la fonction onSave du parent avec les nouvelles données
  };

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[500px] flex flex-col overflow-hidden">
        <h2 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">
          Ajouter un nouveau contrat
        </h2>
        <div className="flex-grow overflow-y-auto p-6">
          <form
            id="contractForm"
            onSubmit={handleSubmit}
            className="grid grid-cols-1 md:grid-cols-2 gap-4"
          >
            <div>
              <label
                htmlFor="ref"
                className="block text-sm font-medium text-gray-700 dark:text-gray-300"
              >
                Libellé
              </label>
              <input
                type="text"
                id="ref"
                name="ref"
                value={newContract.ref}
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
                htmlFor="date_debut"
                className="block text-sm font-medium text-gray-700 dark:text-gray-300"
              >
                Date de début
              </label>
              <input
                type="date"
                id="date_debut"
                name="date_debut"
                value={newContract.date_debut}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label
                htmlFor="date_fin"
                className="block text-sm font-medium text-gray-700 dark:text-gray-300"
              >
                Date de fin
              </label>
              <input
                type="date"
                id="date_fin"
                name="date_fin"
                value={newContract.date_fin}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label
                htmlFor="date_signature"
                className="block text-sm font-medium text-gray-700 dark:text-gray-300"
              >
                Date de signature
              </label>
              <input
                type="date"
                id="date_signature"
                name="date_signature"
                value={newContract.date_signature}
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
              <select
                id="type"
                name="type"
                value={newContract.type}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              >
                <option value="">Sélectionner un type</option>{' '}
                {/* Option par défaut */}
                <option value="client">Client</option>
                <option value="fournisseur">Fournisseur</option>
                <option value="employe">Employé</option>
              </select>
            </div>
            <div>
              <label
                htmlFor="montant"
                className="block text-sm font-medium text-gray-700 dark:text-gray-300"
              >
                Montant
              </label>
              <input
                type="number"
                id="montant"
                name="montant"
                value={newContract.montant}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
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
            form="contractForm"
            className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800"
          >
            Créer le contrat
          </button>
        </div>
      </div>
    </div>
  );
}

export default CreateContractModal;
