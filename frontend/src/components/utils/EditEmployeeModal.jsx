import React, { useState, useEffect } from 'react';

function EditEmployeeModal({ employee, onClose, onSave }) {
  const [editedEmployee, setEditedEmployee] = useState(employee);

  useEffect(() => {
    setEditedEmployee(employee);
  }, [employee]);

  // Gère les changements dans les champs du formulaire et met à jour l'état local.
  const handleChange = (e) => {
    const { name, value } = e.target;
    setEditedEmployee((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  // Gère la soumission du formulaire. 
  const handleSubmit = (e) => {
    e.preventDefault();
    // Appelle la fonction 'onSave' passée par le composant parent avec les données modifiées.
    onSave(editedEmployee);
  };

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2 relative">
        <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
          Modifier l'employé : {employee.name}
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
              value={editedEmployee.name}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label
              htmlFor="fonction"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Fonction de l'employé
            </label>
            <input
              type="text"
              id="fonction"
              name="fonction"
              value={editedEmployee.fonction}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label
              htmlFor="salary"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Salaire de l'employé
            </label>
            <input
              type="number"
              id="salary"
              name="salary"
              value={editedEmployee.salary}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label
              htmlFor="phone"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Téléphone
            </label>
            <input
              type="text"
              id="phone"
              name="phone"
              value={editedEmployee.phone} 
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label
              htmlFor="email"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Email
            </label>
            <input
              type="email"
              id="email"
              name="email"
              value={editedEmployee.email}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label
              htmlFor="quality"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Qualité de l'employé
            </label>
            <select
              id="quality"
              name="quality"
              value={editedEmployee.quality}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            >
              <option value="">Sélectionner une qualité</option> {/* Ajouté */}
              <option value="ouvrier">Ouvrier</option>
              <option value="technicien">Technicien</option>
              <option value="ingénieur">Ingénieur</option>
              <option value="ceo">Dirigeant</option>
            </select>
          </div>
          <div>
            <label
              htmlFor="category"
              className="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Catégorie de l'employé
            </label>
            <select
              id="category"
              name="category"
              value={editedEmployee.category}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            >
              <option value="">Sélectionner une catégorie</option>{' '}
              {/* Ajouté */}
              <option value="agent">Agent</option>
              <option value="agent de maitrise">Agent de maîtrise</option>
              <option value="cadre">Cadre</option>
              <option value="cadre supérieur">Cadre supérieur</option>
            </select>
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
              Sauvegarder les modifications
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default EditEmployeeModal;
